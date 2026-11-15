<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Services\CvTextExtractor;
use App\Services\GeminiCvExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use App\Services\CvConfirmationService;
use Illuminate\Support\Facades\DB;
use Throwable;

class CvController extends Controller
{
    public function index()
    {
       $profile = auth()->user()->studentProfile;

abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');
        $cvs = $profile->cvs()
            ->latest('uploaded_at')
            ->get();

        return view('cv.index', compact('cvs'));
    }

    public function show(Cv $cv)
{
    $profile = auth()->user()->studentProfile;

    abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

    abort_unless(
        $cv->student_profile_id === $profile->id,
        403
    );

    $extraction = $cv->extractions()
        ->latest('id')
        ->first();

    return view('cv.show', compact('cv', 'extraction'));
}

    

    public function confirm(
        Request $request,
        Cv $cv,
        CvConfirmationService $confirmationService
    ) {
        $profile = auth()->user()->studentProfile;

        abort_unless(
            $profile && $cv->student_profile_id === $profile->id,
            403
        );

        $extraction = $cv->extractions()
            ->latest('id')
            ->first();

        abort_unless(
            $extraction && $extraction->status === 'completed',
            404
        );

        $validated = $request->validate([
            'confirmed_data' => ['required', 'string'],
        ]);

        $confirmedData = json_decode(
            $validated['confirmed_data'],
            true
        );

        if (
            json_last_error() !== JSON_ERROR_NONE ||
            ! is_array($confirmedData) ||
            array_is_list($confirmedData)
        ) {
            return back()
                ->withErrors([
                    'confirmed_data' =>
                        'Format JSON tidak valid. Pastikan data berbentuk objek JSON.',
                ])
                ->withInput();
        }

        DB::transaction(function () use (
            $confirmationService,
            $cv,
            $confirmedData
        ) {
          $confirmationService->confirm(
    $cv,
    $confirmedData,
    app(\App\Services\MatchingSynchronizationService::class)
);
        });

        return redirect()
            ->route('cv.show', $cv)
            ->with(
                'success',
                'Data CV berhasil dikonfirmasi dan disimpan.'
            );
    }



    public function store(
    Request $request,
    CvTextExtractor $extractor,
    GeminiCvExtractor $geminiExtractor
)
    {
        $profile = auth()->user()->studentProfile;

abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

$validated = $request->validate([
    'cv' => [
        'required',
        File::types(['pdf', 'docx'])->max(5 * 1024),
    ],
]);

$file = $validated['cv'];

        $path = $file->store('cvs/' . $profile->id, 'local');

        try {
            $cv = $profile->cvs()->create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'extraction_status' => 'pending',
                'uploaded_at' => now(),
            ]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        $extraction = $cv->extractions()->create([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $parsed = $extractor->extract(
                Storage::disk('local')->path($path),
                $file->getClientOriginalExtension()
            );

            \Illuminate\Support\Facades\Log::info(
    'CV text extracted for AI.',
    [
        'extension' => $file->getClientOriginalExtension(),
        'character_count' => mb_strlen($parsed['extracted_text']),
        'byte_count' => strlen($parsed['extracted_text']),
    ]
);

$aiResult = $geminiExtractor->extract(
    $parsed['extracted_text']
);

           $extraction->update([
    'raw_response' => $aiResult,
    'model_name' => config('services.groq.model'),
    'status' => 'completed',
    'completed_at' => now(),
]);

            $cv->update([
                'extraction_status' => 'completed',
            ]);

            return redirect()
                ->route('cv.index')
                ->with('success', 'CV berhasil diunggah dan teksnya berhasil diekstrak.');
        } catch (Throwable $e) {
            report($e);

            $extraction->update([
                'status' => 'failed',
                'error_message' => 'Teks CV gagal diekstrak. Periksa format atau isi dokumen.',
                'completed_at' => now(),
            ]);

            $cv->update([
                'extraction_status' => 'failed',
            ]);

            return redirect()
                ->route('cv.index')
                ->with('error', 'CV tersimpan, tetapi teksnya gagal diekstrak. Coba gunakan dokumen lain.');
        }
    }

    
public function test_student_without_profile_cannot_upload_cv(): void
{
    Storage::fake('local');

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $response = $this->actingAs($student)
        ->post(route('cv.store'), [
            'cv' => UploadedFile::fake()->create(
                'cv.pdf',
                100,
                'application/pdf'
            ),
        ]);

    $response->assertForbidden();

    $this->assertDatabaseCount('cvs', 0);
    $this->assertSame([], Storage::disk('local')->allFiles());
}



}
