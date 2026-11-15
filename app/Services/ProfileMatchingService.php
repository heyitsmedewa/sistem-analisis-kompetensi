<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\MatchingResult;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileMatchingService
{
    private const GAP_WEIGHTS = [
        -5 => 0.0,
        -4 => 1.0,
        -3 => 2.0,
        -2 => 3.0,
        -1 => 4.0,
         0 => 5.0,
         1 => 4.5,
         2 => 3.5,
         3 => 2.5,
         4 => 1.5,
         5 => 0.5,
    ];

    private const CORE_FACTOR_WEIGHT = 0.60;
    private const SECONDARY_FACTOR_WEIGHT = 0.40;

    public function calculate(
        StudentProfile $studentProfile,
        JobPosting $jobPosting
    ): MatchingResult {
        $jobPosting->loadMissing('jobSkills.skill');

        if (! $jobPosting->is_active) {
            throw ValidationException::withMessages([
                'job' => 'Lowongan tidak aktif dan tidak dapat dianalisis.',
            ]);
        }

        if ($jobPosting->jobSkills->isEmpty()) {
            throw ValidationException::withMessages([
                'job' => 'Lowongan belum memiliki persyaratan kompetensi.',
            ]);
        }

        $validRequirements = $jobPosting->jobSkills->filter(
            fn ($jobSkill) =>
                $jobSkill->skill !== null
                && $jobSkill->skill->is_active
                && $jobSkill->required_level !== null
                && $jobSkill->required_level >= 1
                && $jobSkill->required_level <= 5
        );

        if ($validRequirements->count() !== $jobPosting->jobSkills->count()) {
            throw ValidationException::withMessages([
                'job' => 'Ada persyaratan kompetensi yang tidak valid. Periksa level 1–5 dan status kompetensinya.',
            ]);
        }

        $hasCoreFactor = $validRequirements->contains(
            fn ($item) => $item->is_core_factor
        );

        $hasSecondaryFactor = $validRequirements->contains(
            fn ($item) => ! $item->is_core_factor
        );

        if (! $hasCoreFactor || ! $hasSecondaryFactor) {
            throw ValidationException::withMessages([
                'job' => 'Lowongan harus memiliki minimal satu Core Factor dan satu Secondary Factor.',
            ]);
        }

        return DB::transaction(function () use (
            $studentProfile,
            $jobPosting,
            $validRequirements
        ) {
            $studentSkills = $studentProfile->studentSkills()
                ->get()
                ->keyBy('skill_id');

            $coreWeights = [];
            $secondaryWeights = [];
            $details = [];

            foreach ($validRequirements as $requirement) {
                $skillId = $requirement->skill_id;
                $requiredLevel = (int) $requirement->required_level;
                $studentSkill = $studentSkills->get($skillId);

                $confirmationStatus = $studentSkill?->confirmation_status;
                $storedLevel = $studentSkill?->actual_level;

                $hasApprovedLevel = $confirmationStatus === 'approved'
                    && $storedLevel !== null
                    && (int) $storedLevel >= 1
                    && (int) $storedLevel <= 5;

                $actualLevel = $hasApprovedLevel
                    ? (int) $storedLevel
                    : null;

                if ($hasApprovedLevel) {
                    $evaluationStatus = 'evaluated';
                } elseif ($confirmationStatus === 'approved') {
                    $evaluationStatus = 'level_missing';
                } elseif ($confirmationStatus === 'pending') {
                    $evaluationStatus = 'pending';
                } elseif ($confirmationStatus === 'rejected') {
                    $evaluationStatus = 'rejected';
                } else {
                    $evaluationStatus = 'missing';
                }

                $isCoreFactor = (bool) $requirement->is_core_factor;

$gap = null;
$gapWeight = null;

if ($actualLevel !== null) {
    $gap = $actualLevel - $requiredLevel;

    if (! array_key_exists($gap, self::GAP_WEIGHTS)) {
        throw ValidationException::withMessages([
            'job' => 'Gap kompetensi berada di luar rentang yang didukung.',
        ]);
    }

    $gapWeight = self::GAP_WEIGHTS[$gap];

    if ($isCoreFactor) {
        $coreWeights[] = $gapWeight;
    } else {
        $secondaryWeights[] = $gapWeight;
    }
}

                $details[] = [
                    'skill_id' => $skillId,
                    'actual_level' => $actualLevel,
                    'required_level' => $requiredLevel,
                    'gap' => $gap,
                    'gap_weight' => $gapWeight,
                    'factor_type' => $isCoreFactor ? 'core' : 'secondary',
                    'evaluation_status' => $evaluationStatus,
                ];
            }

            $finalScore = null;

if (count($coreWeights) > 0 && count($secondaryWeights) > 0) {
    $coreFactor = array_sum($coreWeights) / count($coreWeights);
    $secondaryFactor = array_sum($secondaryWeights) / count($secondaryWeights);

    $finalScore = (
        self::CORE_FACTOR_WEIGHT * $coreFactor
    ) + (
        self::SECONDARY_FACTOR_WEIGHT * $secondaryFactor
    );
}

            $result = MatchingResult::firstOrNew([
                'student_profile_id' => $studentProfile->id,
                'job_posting_id' => $jobPosting->id,
            ]);

            $hasUnverifiedCompetencies = collect($details)->contains(
    fn ($detail) => $detail['evaluation_status'] !== 'evaluated'
);

$result->final_score = $finalScore !== null
    ? round($finalScore, 4)
    : null;

$result->status = $hasUnverifiedCompetencies
    ? 'provisional'
    : 'final';
$result->calculated_at = now();
$result->save();

            $result->details()->delete();

           foreach ($details as $detail) {
    $result->details()->create($detail);
}

// Tandai rekomendasi lama sebagai kedaluwarsa.
$result->aiRecommendations()
    ->where('status', 'completed')
    ->update([
        'status' => 'stale',
    ]);

return $result->load([
    'jobPosting',
    'details.skill',
]);
        });
    }
}
