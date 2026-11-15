<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\StudentProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchingSynchronizationService
{
    public function __construct(
        private ProfileMatchingService $profileMatchingService
    ) {
    }

    /**
     * Hitung ulang seluruh lowongan aktif untuk mahasiswa.
     */
    public function synchronize(StudentProfile $studentProfile): Collection
    {
        $jobPostings = JobPosting::query()
            ->where('is_active', true)
            ->whereHas('jobSkills')
            ->with('jobSkills.skill')
            ->get();

        $results = collect();

        foreach ($jobPostings as $jobPosting) {
            $results->push(
                $this->profileMatchingService->calculate(
                    $studentProfile,
                    $jobPosting
                )
            );
        }

        return $results;
    }

    /**
     * Hitung ulang mahasiswa yang sudah memiliki hasil
     * matching pada lowongan tertentu.
     */
    public function synchronizeJobPosting(
        JobPosting $jobPosting
    ): Collection {
        return DB::transaction(function () use ($jobPosting) {
            $studentProfiles = StudentProfile::query()
                ->whereHas('matchingResults', function ($query) use ($jobPosting) {
                    $query->where('job_posting_id', $jobPosting->id);
                })
                ->get();

            $results = collect();

            foreach ($studentProfiles as $studentProfile) {
                $results->push(
                    $this->profileMatchingService->calculate(
                        $studentProfile,
                        $jobPosting->fresh()
                    )
                );
            }

            return $results;
        });
    }
}
