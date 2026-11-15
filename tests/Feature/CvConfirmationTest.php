<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvExtraction;
use App\Models\Skill;
use App\Models\SkillSynonym;
use App\Models\StudentCertification;
use App\Models\StudentEducation;
use App\Models\StudentExperience;
use App\Models\StudentProfile;
use App\Models\StudentProject;
use App\Models\StudentSkill;
use App\Models\User;
use App\Services\CvConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\MatchingSynchronizationService;

class CvConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function createCvScenario(): array
    {
        $user = User::create([
            'name' => 'CV Test Student',
            'email' => 'cv-test@example.com',
            'password' => 'password',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'CVTEST001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $cv = Cv::create([
            'student_profile_id' => $profile->id,
            'original_filename' => 'cv-test.pdf',
            'file_path' => 'cvs/' . $profile->id . '/cv-test.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'extraction_status' => 'completed',
        ]);

        $extraction = CvExtraction::create([
            'cv_id' => $cv->id,
            'raw_response' => ['source' => 'test'],
            'status' => 'completed',
            'model_name' => 'test-model',
        ]);

        return compact('user', 'profile', 'cv', 'extraction');
    }

    private function confirmCv(Cv $cv, array $data): void
{
    app(CvConfirmationService::class)->confirm(
        $cv,
        $data,
        app(\App\Services\MatchingSynchronizationService::class)
    );
}

    public function test_confirm_saves_structured_cv_data(): void
    {
        $data = $this->createCvScenario();
$this->confirmCv(
    $data['cv'],
            [
                'personal_info' => [
                    'full_name' => 'CV Test Student',
                ],
                'educations' => [
                    [
                        'institution' => 'Universitas Pengujian',
                        'degree' => 'S1',
                        'study_program' => 'Informatika',
                        'start_year' => '2022',
                        'end_year' => '2026',
                        'description' => 'Pendidikan informatika',
                    ],
                ],
                'experiences' => [
                    [
                        'organization' => 'PT Contoh',
                        'position' => 'Junior Developer',
                        'employment_type' => 'Internship',
                        'start_date' => '2025-01',
                        'end_date' => null,
                        'is_current' => true,
                        'description' => 'Membantu pengembangan aplikasi',
                    ],
                ],
                'certifications' => [
                    [
                        'name' => 'Sertifikasi Pengujian',
                        'issuer' => 'Lembaga Contoh',
                        'issued_at' => '2025-03',
                        'expires_at' => null,
                        'credential_url' => 'https://example.com/certificate',
                    ],
                ],
                'projects' => [
                    [
                        'name' => 'Aplikasi Portofolio',
                        'description' => 'Aplikasi web untuk portofolio',
                        'role' => 'Developer',
                        'project_url' => 'https://example.com/project',
                        'started_at' => '2025',
                        'completed_at' => '2026',
                    ],
                ],
                'hard_skills' => [],
            ]
        );

        $this->assertDatabaseHas('student_educations', [
            'student_profile_id' => $data['profile']->id,
            'cv_id' => $data['cv']->id,
            'institution' => 'Universitas Pengujian',
            'start_year' => 2022,
            'end_year' => 2026,
        ]);

        $this->assertDatabaseHas('student_experiences', [
            'student_profile_id' => $data['profile']->id,
            'cv_id' => $data['cv']->id,
            'organization' => 'PT Contoh',
            'position' => 'Junior Developer',
            'is_current' => 1,
        ]);

        $this->assertDatabaseHas('student_certifications', [
            'student_profile_id' => $data['profile']->id,
            'cv_id' => $data['cv']->id,
            'name' => 'Sertifikasi Pengujian',
        ]);

        $this->assertDatabaseHas('student_projects', [
            'student_profile_id' => $data['profile']->id,
            'cv_id' => $data['cv']->id,
            'name' => 'Aplikasi Portofolio',
        ]);

        $this->assertDatabaseHas('cv_extractions', [
            'id' => $data['extraction']->id,
            'confirmed_at' => $data['extraction']->fresh()->confirmed_at,
        ]);

        $this->assertSame(
            'Universitas Pengujian',
            $data['extraction']->fresh()->confirmed_data['educations'][0]['institution']
        );
    }

    public function test_matching_hard_skill_is_added_as_pending_without_level(): void
    {
        $data = $this->createCvScenario();

        $skill = Skill::create([
            'name' => 'PHP',
            'category' => 'technical',
            'is_active' => true,
        ]);

       $this->confirmCv(
    $data['cv'],
            [
                'educations' => [],
                'experiences' => [],
                'certifications' => [],
                'projects' => [],
                'hard_skills' => ['PHP'],
            ]
        );

        $this->assertDatabaseHas('student_skills', [
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $skill->id,
            'actual_level' => null,
            'confirmation_status' => 'pending',
        ]);
    }

    public function test_existing_student_skill_is_not_overwritten(): void
    {
        $data = $this->createCvScenario();

        $skill = Skill::create([
            'name' => 'PHP',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $studentSkill = StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $skill->id,
            'actual_level' => 4,
            'confirmation_status' => 'approved',
        ]);

       $this->confirmCv(
    $data['cv'],
            [
                'educations' => [],
                'experiences' => [],
                'certifications' => [],
                'projects' => [],
                'hard_skills' => ['PHP'],
            ]
        );

        $studentSkill->refresh();

        $this->assertSame(4, $studentSkill->actual_level);
        $this->assertSame('approved', $studentSkill->confirmation_status);
        $this->assertDatabaseCount('student_skills', 1);
    }
    
public function test_invalid_hard_skills_format_is_rejected(): void
{
    $data = $this->createCvScenario();

    $this->expectException(
        \Illuminate\Validation\ValidationException::class
    );

   $this->confirmCv(
    $data['cv'],
        ['hard_skills' => 'PHP']
    );
}

public function test_empty_hard_skills_array_is_accepted(): void
{
    $data = $this->createCvScenario();

   $this->confirmCv(
    $data['cv'],
        ['hard_skills' => []]
    );

    $this->assertDatabaseHas('cv_extractions', [
        'id' => $data['extraction']->id,
    ]);

    $this->assertNotNull(
        $data['extraction']->fresh()->confirmed_at
    );
}
    public function test_composite_hard_skills_match_individual_master_skills(): void
{
    $data = $this->createCvScenario();

    $php = Skill::create([
        'name' => 'PHP',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    $javascript = Skill::create([
        'name' => 'JavaScript',
        'category' => 'Programming',
        'is_active' => true,
    ]);

   $this->confirmCv(
    $data['cv'],
        [
            'educations' => [],
            'experiences' => [],
            'certifications' => [],
            'projects' => [],
            'hard_skills' => [
                'Dasar Pemrograman (Java, JavaScript, PHP)',
            ],
        ]
    );

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $php->id,
        'actual_level' => null,
        'confirmation_status' => 'pending',
    ]);

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $javascript->id,
        'actual_level' => null,
        'confirmation_status' => 'pending',
    ]);

    $this->assertDatabaseCount('student_skills', 2);
}
public function test_new_cv_hard_skills_trigger_matching_synchronization_once(): void
{
    $data = $this->createCvScenario();

    $skill = Skill::create([
        'name' => 'PHP',
        'category' => 'technical',
        'is_active' => true,
    ]);

    $mock = \Mockery::mock(MatchingSynchronizationService::class);
$mock->shouldReceive('synchronize')
    ->once()
    ->with(\Mockery::on(
        fn ($profile) => $profile instanceof StudentProfile
            && $profile->is($data['profile'])
    ))
    ->andReturn(collect());

    $this->app->instance(MatchingSynchronizationService::class, $mock);

    $this->confirmCv(
        $data['cv'],
        [
            'educations' => [],
            'experiences' => [],
            'certifications' => [],
            'projects' => [],
            'hard_skills' => ['PHP'],
        ]
    );

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => null,
        'confirmation_status' => 'pending',
    ]);
}
public function test_cv_confirmation_without_new_skills_does_not_trigger_matching_synchronization(): void
{
    $data = $this->createCvScenario();

    $skill = Skill::create([
        'name' => 'PHP',
        'category' => 'technical',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 4,
        'confirmation_status' => 'approved',
    ]);

    $mock = \Mockery::mock(MatchingSynchronizationService::class);

    $mock->shouldReceive('synchronize')
        ->never();

    $this->app->instance(MatchingSynchronizationService::class, $mock);

    $this->confirmCv(
        $data['cv'],
        [
            'educations' => [],
            'experiences' => [],
            'certifications' => [],
            'projects' => [],
            'hard_skills' => ['PHP'],
        ]
    );

    $this->assertDatabaseCount('student_skills', 1);
}
public function test_cv_confirmation_is_rolled_back_when_matching_synchronization_fails(): void
{
    $data = $this->createCvScenario();

    $skill = Skill::create([
        'name' => 'PHP',
        'category' => 'technical',
        'is_active' => true,
    ]);

    $mock = \Mockery::mock(MatchingSynchronizationService::class);

    $mock->shouldReceive('synchronize')
        ->once()
        ->andThrow(new \RuntimeException('Synchronization failed'));

    $this->app->instance(MatchingSynchronizationService::class, $mock);

    try {
        $this->confirmCv(
            $data['cv'],
            [
                'educations' => [],
                'experiences' => [],
                'certifications' => [],
                'projects' => [],
                'hard_skills' => ['PHP'],
            ]
        );

        $this->fail('Expected synchronization exception was not thrown.');
    } catch (\RuntimeException $exception) {
        $this->assertSame('Synchronization failed', $exception->getMessage());
    }

    $this->assertDatabaseMissing('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
    ]);

    $this->assertNull($data['extraction']->fresh()->confirmed_at);
}
}
