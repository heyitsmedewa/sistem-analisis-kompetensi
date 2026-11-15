<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\MatchingSynchronizationService;
use Mockery;

class StudentSkillSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function createStudent(): array
    {
        $user = User::create([
            'name' => 'Test Student',
            'email' => 'student-submission@example.com',
            'password' => 'password',
        ]);

        $role = Role::firstOrCreate(['name' => 'mahasiswa']);
        $user->roles()->attach($role->id);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'SUBMISSION001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        return compact('user', 'profile');
    }

    public function test_student_can_submit_a_skill_with_level(): void
    {
        $data = $this->createStudent();

        $skill = Skill::create([
            'name' => 'Test Laravel',
            'category' => 'Web Development',
            'is_active' => true,
        ]);

        $this->actingAs($data['user'])
            ->post(route('skills.store'), [
                'skills' => [
                    $skill->id => [
                        'skill_id' => $skill->id,
                        'actual_level' => '3',
                    ],
                ],
            ])
            ->assertRedirect(route('skills.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_skills', [
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $skill->id,
            'actual_level' => 3,
            'confirmation_status' => 'pending',
        ]);
    }

    public function test_unselected_skill_is_not_submitted(): void
    {
        $data = $this->createStudent();

        $skill = Skill::create([
            'name' => 'Test Python',
            'category' => 'Programming',
            'is_active' => true,
        ]);

        $this->actingAs($data['user'])
            ->post(route('skills.store'), [
                'skills' => [
                    $skill->id => [
                        'skill_id' => $skill->id,
                        'actual_level' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('skills.index'));

        $this->assertDatabaseMissing('student_skills', [
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function test_changing_approved_skill_level_requires_reverification(): void
    {
        $data = $this->createStudent();

        $skill = Skill::create([
            'name' => 'Test JavaScript',
            'category' => 'Programming',
            'is_active' => true,
        ]);

        $studentSkill = StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $skill->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        $this->actingAs($data['user'])
            ->post(route('skills.store'), [
                'skills' => [
                    $skill->id => [
                        'skill_id' => $skill->id,
                        'actual_level' => '4',
                    ],
                ],
            ])
            ->assertRedirect(route('skills.index'));

        $this->assertDatabaseHas('student_skills', [
            'id' => $studentSkill->id,
            'actual_level' => 4,
            'confirmation_status' => 'pending',
        ]);
    }

    
public function test_clearing_pending_skill_level_does_not_remove_existing_level(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test Pending Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    $studentSkill = StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'id' => $studentSkill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);
}

public function test_rejected_skill_without_level_remains_rejected(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test Rejected Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    $studentSkill = StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'rejected',
    ]);

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'id' => $studentSkill->id,
        'actual_level' => 3,
        'confirmation_status' => 'rejected',
    ]);
}
public function test_new_skill_submission_triggers_matching_synchronization_once(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test React',
        'category' => 'Web Development',
        'is_active' => true,
    ]);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->withArgs(fn ($profile) => $profile->id === $data['profile']->id);

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '3',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);
}
public function test_changing_approved_skill_level_triggers_matching_synchronization_once(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test Java',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->withArgs(fn ($profile) => $profile->id === $data['profile']->id);

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '4',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 4,
        'confirmation_status' => 'pending',
    ]);
}
public function test_resubmitting_rejected_skill_triggers_matching_synchronization_once(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test Vue',
        'category' => 'Web Development',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 2,
        'confirmation_status' => 'rejected',
    ]);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->withArgs(fn ($profile) => $profile->id === $data['profile']->id);

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '3',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);
}
public function test_unchanged_skill_submission_does_not_trigger_matching_synchronization(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test PHP',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldNotReceive('synchronize');

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->actingAs($data['user'])
        ->post(route('skills.store'), [
            'skills' => [
                $skill->id => [
                    'skill_id' => $skill->id,
                    'actual_level' => '3',
                ],
            ],
        ])
        ->assertRedirect(route('skills.index'));

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);
}
public function test_skill_change_is_rolled_back_when_matching_synchronization_fails(): void
{
    $data = $this->createStudent();

    $skill = Skill::create([
        'name' => 'Test Node.js',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->andThrow(new \RuntimeException(
            'Simulated synchronization failure'
        ));

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->withoutExceptionHandling();

    try {
        $this->actingAs($data['user'])
            ->post(route('skills.store'), [
                'skills' => [
                    $skill->id => [
                        'skill_id' => $skill->id,
                        'actual_level' => '4',
                    ],
                ],
            ]);

        $this->fail('Expected synchronization failure was not thrown.');
    } catch (\RuntimeException $exception) {
        $this->assertSame(
            'Simulated synchronization failure',
            $exception->getMessage()
        );
    }

    $this->assertDatabaseHas('student_skills', [
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $skill->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);
}
}
