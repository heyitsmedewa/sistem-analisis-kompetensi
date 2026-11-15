<?php

namespace App\Http\Controllers;

use App\Models\MatchingResult;
use App\Services\AiRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiRecommendationController extends Controller
{
    /**
     * Membuat dan menyimpan rekomendasi pengembangan kompetensi.
     */
    public function generate(
        MatchingResult $matchingResult,
        AiRecommendationService $service
    ): RedirectResponse {
        $studentProfile = Auth::user()->studentProfile;

        abort_unless(
            $studentProfile,
            403,
            'Profil mahasiswa tidak ditemukan.'
        );

        // Pastikan hasil matching benar-benar milik mahasiswa login.
        abort_unless(
            (int) $matchingResult->student_profile_id ===
                (int) $studentProfile->id,
            403,
            'Kamu tidak memiliki akses ke hasil analisis ini.'
        );

        // Hasil harus memiliki skor dan status analisis yang valid.
        if (
            $matchingResult->final_score === null ||
            ! in_array(
                $matchingResult->status,
                ['provisional', 'final'],
                true
            )
        ) {
            return redirect()
                ->route('matching.index')
                ->with(
                    'error',
                    'Hitung kecocokan lowongan terlebih dahulu.'
                );
                
        }


if (
    $matchingResult->aiRecommendations()
        ->where('status', 'processing')
        ->exists()
) {
    return redirect()
        ->route('matching.index')
        ->with(
            'error',
            'Rekomendasi untuk hasil analisis ini sedang diproses.'
        );
}

if (
    $matchingResult->aiRecommendations()
        ->where('status', 'completed')
        ->exists()
) {
    return redirect()
        ->route('matching.index')
        ->with(
            'success',
            'Rekomendasi untuk hasil analisis ini sudah tersedia.'
        );
}


        try {
            $service->generateAndSave($matchingResult);

            return redirect()
                ->route('matching.index')
                ->with(
                    'success',
                    'Rekomendasi pengembangan kompetensi berhasil dibuat.'
                );
        } catch (Throwable $exception) {
            Log::error('AI recommendation generation failed.', [
                'matching_result_id' => $matchingResult->id,
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('matching.index')
                ->with(
                    'error',
                    'Rekomendasi belum berhasil dibuat. Silakan coba lagi nanti.'
                );
        }
    }
}
