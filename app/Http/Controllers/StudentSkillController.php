<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CvExtraction;
use App\Models\SkillSynonym;
use Illuminate\Support\Facades\DB;
use App\Services\MatchingSynchronizationService;

class StudentSkillController extends Controller
{
    public function index()
{
    $profile = auth()->user()->studentProfile;

    abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

    $skills = Skill::where('is_active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    $studentSkills = $profile->studentSkills()
        ->with('skill')
        ->get()
        ->keyBy('skill_id');

    // Ambil hard skill dari CV yang sudah dikonfirmasi.
    $cvHardSkills = $profile->cvs()
        ->whereHas('extractions', function ($query) {
            $query->whereNotNull('confirmed_at');
        })
        ->with(['extractions' => function ($query) {
            $query->whereNotNull('confirmed_at')
                ->orderByDesc('confirmed_at');
        }])
        ->get()
        ->flatMap(function ($cv) {
            return $cv->extractions->flatMap(function ($extraction) {
                return $extraction->confirmed_data['hard_skills'] ?? [];
            });
        })
        ->filter(fn ($item) => is_string($item) && trim($item) !== '')
        ->values();

    $activeSkills = Skill::where('is_active', true)->get();

    $approvedSynonyms = SkillSynonym::where('approval_status', 'approved')
        ->with('skill')
        ->get();

    // Tandai ID skill master yang cocok dengan hard skill dari CV.
    $cvSkillIds = collect();

    foreach ($cvHardSkills as $item) {
        $text = mb_strtolower(trim($item));

        foreach ($activeSkills as $skill) {
            $name = mb_strtolower(trim($skill->name));

            if (
                $name !== ''
                && preg_match(
                    '/(?<![\p{L}\p{N}])'
                    . preg_quote($name, '/')
                    . '(?![\p{L}\p{N}])/u',
                    $text
                )
            ) {
                $cvSkillIds->push($skill->id);
            }
        }

        foreach ($approvedSynonyms as $synonym) {
            $skill = $synonym->skill;
            $term = mb_strtolower(trim($synonym->synonym));

            if (
                $skill
                && $skill->is_active
                && $term !== ''
                && preg_match(
                    '/(?<![\p{L}\p{N}])'
                    . preg_quote($term, '/')
                    . '(?![\p{L}\p{N}])/u',
                    $text
                )
            ) {
                $cvSkillIds->push($skill->id);
            }
        }
    }

    $cvSkillIds = $cvSkillIds->unique()->values();

    return view('skills.index', compact(
        'skills',
        'studentSkills',
        'cvSkillIds'
    ));
}


public function store(
    Request $request,
    MatchingSynchronizationService $synchronizationService
) {
    $profile = auth()->user()->studentProfile;

    abort_unless($profile, 403, 'Profil mahasiswa tidak ditemukan.');

    $validated = $request->validate([
        'skills' => ['required', 'array', 'min:1'],
        'skills.*.skill_id' => [
            'required',
            'integer',
            'distinct',
            Rule::exists('skills', 'id')->where('is_active', true),
        ],
        'skills.*.actual_level' => [
            'nullable',
            'integer',
            'between:1,5',
        ],
    ]);

    DB::transaction(function () use (
        $profile,
        $validated,
        $synchronizationService
    ) {
        $hasChanges = false;

        foreach ($validated['skills'] as $item) {
            $actualLevel = isset($item['actual_level'])
                && $item['actual_level'] !== ''
                    ? (int) $item['actual_level']
                    : null;

            $studentSkill = $profile->studentSkills()
                ->where('skill_id', $item['skill_id'])
                ->first();

            // Kompetensi baru hanya diajukan jika level dipilih.
            if (! $studentSkill) {
                if ($actualLevel === null) {
                    continue;
                }

                $profile->studentSkills()->create([
                    'skill_id' => $item['skill_id'],
                    'actual_level' => $actualLevel,
                    'confirmation_status' => 'pending',
                ]);

                $hasChanges = true;

                continue;
            }

            // Kompetensi yang disetujui perlu diajukan ulang
            // jika mahasiswa mengubah level.
            if ($studentSkill->confirmation_status === 'approved') {
                if (
                    $actualLevel === null
                    || $studentSkill->actual_level === $actualLevel
                ) {
                    continue;
                }

                $studentSkill->actual_level = $actualLevel;
                $studentSkill->confirmation_status = 'pending';
                $studentSkill->save();

                $hasChanges = true;

                continue;
            }

            // Pengajuan pending tidak berubah jika level dikosongkan.
            if (
                $studentSkill->confirmation_status === 'pending'
                && $actualLevel === null
            ) {
                continue;
            }

            // Kompetensi rejected harus diajukan ulang dengan level.
            if (
                $studentSkill->confirmation_status === 'rejected'
                && $actualLevel === null
            ) {
                continue;
            }

            $levelChanged = $studentSkill->actual_level !== $actualLevel;
            $wasRejected = $studentSkill->confirmation_status === 'rejected';

            // Tidak perlu menyimpan jika tidak ada perubahan.
            if (! $levelChanged && ! $wasRejected) {
                continue;
            }

            $studentSkill->actual_level = $actualLevel;

            if ($levelChanged || $wasRejected) {
                $studentSkill->confirmation_status = 'pending';
            }

            $studentSkill->save();

            $hasChanges = true;
        }

        // Sinkronisasi hanya sekali jika ada perubahan kompetensi.
        if ($hasChanges) {
            $synchronizationService->synchronize($profile);
        }
    });

    return redirect()
        ->route('skills.index')
        ->with('success', 'Kompetensi berhasil disimpan.');
}

}
