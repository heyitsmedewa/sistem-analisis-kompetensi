<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\MatchingResult;
use App\Services\ProfileMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class StudentMatchingController extends Controller
{
    /**
     * Menampilkan lowongan aktif dan hasil analisis mahasiswa.
     */
    public function index(): View
    {
        $studentProfile = Auth::user()->studentProfile;

        abort_unless(
            $studentProfile,
            403,
            'Profil mahasiswa tidak ditemukan.'
        );

        $jobPostings = JobPosting::query()
            ->where('is_active', true)
            ->withCount('jobSkills')
            ->orderByDesc('created_at')
            ->paginate(10);

       $matchingResults = MatchingResult::query()
    ->where('student_profile_id', $studentProfile->id)
    ->with([
    'jobPosting',
    'details.skill',
    'aiRecommendations' => fn ($query) => $query
        ->latest()
        ->limit(1),
])
    ->orderByDesc('final_score')
    ->get();

   $eligibleRankingResults = $matchingResults
    ->filter(fn ($result) =>
        $result->jobPosting
        && $result->jobPosting->is_active
        && $result->final_score !== null
    )
    ->values();

$finalRankingResults = $eligibleRankingResults
    ->where('status', 'final')
    ->sortByDesc('final_score')
    ->values();

$provisionalRankingResults = $eligibleRankingResults
    ->where('status', 'provisional')
    ->sortByDesc('final_score')
    ->values();

        $resultsByJob = $matchingResults->keyBy('job_posting_id');

        return view('matching.index', compact(
            'jobPostings',
            'matchingResults',
            'resultsByJob',
            'finalRankingResults',
'provisionalRankingResults'
        ));
    }

    /**
     * Menjalankan Profile Matching untuk satu lowongan.
     */
    public function calculate(
        JobPosting $jobPosting,
        ProfileMatchingService $service
    ): RedirectResponse {
        $studentProfile = Auth::user()->studentProfile;

        abort_unless(
            $studentProfile,
            403,
            'Profil mahasiswa tidak ditemukan.'
        );

        
if (! $jobPosting->is_active) {
    return redirect()
        ->route('matching.index')
        ->with('error', 'Lowongan ini sudah tidak aktif dan tidak dapat dianalisis.');
}


        $service->calculate($studentProfile, $jobPosting);

        return redirect()
            ->route('matching.index')
            ->with(
                'success',
                'Analisis kecocokan berhasil dihitung.'
            );
    }

        
public function calculateAll(
    ProfileMatchingService $service
): RedirectResponse {
    $studentProfile = Auth::user()->studentProfile;

    abort_unless(
        $studentProfile,
        403,
        'Profil mahasiswa tidak ditemukan.'
    );

    $jobPostings = JobPosting::query()
        ->where('is_active', true)
        ->with('jobSkills.skill')
        ->get();

    $processedCount = 0;
    $scoredCount = 0;
    $noScoreCount = 0;
    $skippedJobs = [];

    foreach ($jobPostings as $jobPosting) {
        try {
            $result = $service->calculate(
                $studentProfile,
                $jobPosting
            );

            $processedCount++;

            if ($result->final_score !== null) {
                $scoredCount++;
            } else {
                $noScoreCount++;
            }
        } catch (ValidationException $exception) {
            $skippedJobs[] = $jobPosting->title;
        }
    }

    $skippedCount = count($skippedJobs);

    $message = "Analisis selesai. {$processedCount} lowongan berhasil diproses: "
        . "{$scoredCount} menghasilkan skor dan "
        . "{$noScoreCount} belum memiliki skor.";

    if ($skippedCount > 0) {
        $message .= " {$skippedCount} lowongan dilewati karena persyaratannya tidak valid atau belum lengkap.";
    }

    return redirect()
        ->route('matching.index')
        ->with('success', $message)
        ->with('skipped_jobs', $skippedJobs);
}



}
