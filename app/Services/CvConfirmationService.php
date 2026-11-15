<?php

namespace App\Services;

use App\Models\Cv;
use App\Models\StudentCertification;
use App\Models\StudentEducation;
use App\Models\StudentExperience;
use App\Models\StudentProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Skill;
use App\Models\SkillSynonym;
use App\Models\StudentSkill;
use App\Services\MatchingSynchronizationService;

class CvConfirmationService
{
    /**
     * Simpan data CV yang sudah dikonfirmasi.
     *
     * Sinkronisasi hanya menyentuh data yang terhubung
     * dengan CV ini, bukan data dari CV lain.
     */
    public function confirm(
    Cv $cv,
    array $data,
    MatchingSynchronizationService $synchronizationService
): void
    {
        $profileId = $cv->student_profile_id;

        $this->validateSections($data);

       DB::transaction(function () use (
    $cv,
    $data,
    $profileId,
    $synchronizationService
) {
            $this->syncEducations(
                $cv,
                $profileId,
                $data['educations'] ?? []
            );

            $this->syncExperiences(
                $cv,
                $profileId,
                $data['experiences'] ?? []
            );

            $this->syncCertifications(
                $cv,
                $profileId,
                $data['certifications'] ?? []
            );

            $this->syncProjects(
                $cv,
                $profileId,
                $data['projects'] ?? []
            );

        $hasNewSkills = $this->syncHardSkills(
    $cv,
    $profileId,
    $data['hard_skills'] ?? []
);

            $cv->extractions()
                ->latest('id')
                ->firstOrFail()
                ->update([
                    'confirmed_data' => $data,
                    'confirmed_at' => now(),
                ]);
                if ($hasNewSkills) {
    $synchronizationService->synchronize(
        $cv->studentProfile
    );
}
        });
    }

    /**
     * Pastikan setiap bagian yang akan disinkronkan
     * berbentuk array berisi objek.
     */
    
private function validateSections(array $data): void
{
    foreach ([
        'educations',
        'experiences',
        'certifications',
        'projects',
    ] as $section) {
        if (! array_key_exists($section, $data)) {
            continue;
        }

        if (! is_array($data[$section])) {
            throw ValidationException::withMessages([
                'confirmed_data' =>
                    "Bagian {$section} harus berupa array.",
            ]);
        }

        foreach ($data[$section] as $item) {
            if (! is_array($item) || array_is_list($item)) {
                throw ValidationException::withMessages([
                    'confirmed_data' =>
                        "Setiap item pada {$section} harus berupa objek JSON.",
                ]);
            }
        }
    }

    if (array_key_exists('hard_skills', $data)) {
        if (! is_array($data['hard_skills'])) {
            throw ValidationException::withMessages([
                'confirmed_data' =>
                    'Bagian hard_skills harus berupa array.',
            ]);
        }

        foreach ($data['hard_skills'] as $skill) {
            if (! is_string($skill) || trim($skill) === '') {
                throw ValidationException::withMessages([
                    'confirmed_data' =>
                        'Setiap item hard_skills harus berupa teks yang tidak kosong.',
                ]);
            }
        }
    }
}


    private function syncEducations(
        Cv $cv,
        int $profileId,
        array $items
    ): void {
        $this->syncRecords(
            StudentEducation::class,
            $cv,
            $profileId,
            $items,
            [
                'institution' => 200,
                'degree' => 100,
                'study_program' => 150,
                'field_of_study' => 150,
                'description' => 500,
            ],
            [
                'institution' => ['institution', 'school', 'university'],
                'degree' => ['degree', 'qualification'],
                'study_program' => ['study_program', 'major'],
                'field_of_study' => ['field_of_study', 'field'],
                'start_year' => ['start_year', 'start_date'],
                'end_year' => ['end_year', 'end_date'],
                'description' => ['description'],
            ],
            ['institution']
        );
    }

    private function syncExperiences(
        Cv $cv,
        int $profileId,
        array $items
    ): void {
        $this->syncRecords(
            StudentExperience::class,
            $cv,
            $profileId,
            $items,
            [
                'organization' => 200,
                'position' => 150,
                'employment_type' => 50,
                'description' => null,
            ],
            [
                'organization' => ['organization', 'company', 'employer'],
                'position' => ['position', 'job_title', 'role'],
                'employment_type' => ['employment_type', 'type'],
                'start_date' => ['start_date', 'start_year'],
                'end_date' => ['end_date', 'end_year'],
                'is_current' => ['is_current', 'current'],
                'description' => ['description', 'responsibilities'],
            ],
            ['organization', 'position']
        );
    }

    private function syncCertifications(
        Cv $cv,
        int $profileId,
        array $items
    ): void {
        $this->syncRecords(
            StudentCertification::class,
            $cv,
            $profileId,
            $items,
            [
                'name' => 200,
                'issuer' => 200,
                'credential_url' => 500,
            ],
            [
                'name' => ['name', 'certification_name', 'title'],
                'issuer' => ['issuer', 'organization', 'provider'],
                'issued_at' => ['issued_at', 'issue_date'],
                'expires_at' => ['expires_at', 'expiry_date'],
                'credential_url' => ['credential_url', 'url'],
            ],
            ['name']
        );
    }

    private function syncProjects(
        Cv $cv,
        int $profileId,
        array $items
    ): void {
        $this->syncRecords(
            StudentProject::class,
            $cv,
            $profileId,
            $items,
            [
                'name' => 200,
                'description' => null,
                'role' => 150,
                'project_url' => 500,
            ],
            [
                'name' => ['name', 'project_name', 'title'],
                'description' => ['description', 'summary'],
                'role' => ['role', 'position'],
                'project_url' => ['project_url', 'url'],
                'started_at' => ['started_at', 'start_date'],
                'completed_at' => ['completed_at', 'end_date'],
            ],
            ['name']
        );
    }

    public function syncConfirmedHardSkills(Cv $cv): void
{
    $extraction = $cv->extractions()
        ->whereNotNull('confirmed_at')
        ->latest('id')
        ->firstOrFail();

    $data = $extraction->confirmed_data;

    if (! is_array($data) || ! is_array($data['hard_skills'] ?? null)) {
        throw ValidationException::withMessages([
            'confirmed_data' => 'Data hard skill terkonfirmasi tidak valid.',
        ]);
    }

    DB::transaction(function () use ($cv, $data) {
        $this->syncHardSkills(
            $cv,
            $cv->student_profile_id,
            $data['hard_skills']
        );
    });
}
    /**
     * Upsert data berdasarkan CV sumber.
     * Tidak menghapus atau menimpa data dari CV lain.
     */
    private function syncRecords(
        string $modelClass,
        Cv $cv,
        int $profileId,
        array $items,
        array $limits,
        array $mapping,
        array $requiredFields
    ): void {
        $existingRecords = $modelClass::query()
            ->where('student_profile_id', $profileId)
            ->where('cv_id', $cv->id)
            ->get()
            ->keyBy('id');

        $retainedIds = [];

        foreach ($items as $item) {
            $attributes = [];

            foreach ($mapping as $field => $aliases) {
                $value = $this->firstValue($item, $aliases);

                if ($field === 'is_current') {
                    $attributes[$field] = $this->toBoolean($value);
                    continue;
                }

                if (in_array($field, [
                    'start_year',
                    'end_year',
                ], true)) {
                    $attributes[$field] = $this->toYear($value);
                    continue;
                }

                if (in_array($field, [
                    'start_date',
                    'end_date',
                    'issued_at',
                    'expires_at',
                    'started_at',
                    'completed_at',
                ], true)) {
                    $attributes[$field] = $this->toDate($value);
                    continue;
                }

                $attributes[$field] = $this->toText(
                    $value,
                    $limits[$field] ?? null
                );
            }

            $hasRequiredFields = true;

            foreach ($requiredFields as $field) {
                if (blank($attributes[$field] ?? null)) {
                    $hasRequiredFields = false;
                    break;
                }
            }

            if (! $hasRequiredFields) {
                continue;
            }

            $existing = $existingRecords->first(
                function ($record) use ($attributes, $requiredFields, $retainedIds) {
                    if (in_array($record->id, $retainedIds, true)) {
                        return false;
                    }

                    foreach ($requiredFields as $field) {
                        if (
                            mb_strtolower(trim((string) $record->{$field}))
                            !== mb_strtolower(trim((string) $attributes[$field]))
                        ) {
                            return false;
                        }
                    }

                    return true;
                }
            );

            if ($existing) {
                $existing->update($attributes);
                $retainedIds[] = $existing->id;
                continue;
            }

            $record = $modelClass::create(array_merge(
                [
                    'student_profile_id' => $profileId,
                    'cv_id' => $cv->id,
                ],
                $attributes
            ));

            $retainedIds[] = $record->id;
        }

        // Tidak menghapus record lama secara otomatis.
        // Item yang tidak ditemukan pada ekstraksi terbaru bisa
        // saja hilang akibat kesalahan pembacaan AI.
    }

    private function firstValue(array $item, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $item)) {
                return $item[$alias];
            }
        }

        return null;
    }

    private function toText(mixed $value, ?int $limit): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        if ($limit !== null) {
            $text = mb_substr($text, 0, $limit);
        }

        return $text;
    }

    private function toYear(mixed $value): ?int
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (! preg_match('/^(19|20|21)\d{2}$/', $value)) {
            return null;
        }

        $year = (int) $value;

        return $year >= 1900 && $year <= 2199
            ? $year
            : null;
    }

    private function toDate(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'Y-m', 'Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat(
                '!' . $format,
                $value
            );

            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(
                mb_strtolower(trim($value)),
                ['true', 'yes', '1', 'ya'],
                true
            );
        }

        return false;
    }
    
    
private function syncHardSkills(Cv $cv, int $profileId, array $items): bool
{
    $activeSkills = Skill::query()
        ->where('is_active', true)
        ->get();

    $approvedSynonyms = SkillSynonym::query()
        ->where('approval_status', 'approved')
        ->with('skill')
        ->get();

    $hasChanges = false;

    foreach ($items as $item) {
        if (! is_string($item) || trim($item) === '') {
            continue;
        }

        $text = mb_strtolower(trim($item));
        $matchedSkills = collect();

        // Cocokkan nama skill master.
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
                $matchedSkills->push($skill);
            }
        }

        // Cocokkan sinonim yang sudah disetujui.
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
                $matchedSkills->push($skill);
            }
        }

        // Simpan skill baru yang belum dimiliki mahasiswa.
        foreach ($matchedSkills->unique('id') as $skill) {
            $alreadyExists = StudentSkill::query()
                ->where('student_profile_id', $profileId)
                ->where('skill_id', $skill->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            StudentSkill::create([
                'student_profile_id' => $profileId,
                'skill_id' => $skill->id,
                'actual_level' => null,
                'confirmation_status' => 'pending',
            ]);

            $hasChanges = true;
        }
    }

    return $hasChanges;
}

}