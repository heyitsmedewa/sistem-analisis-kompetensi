<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Services\MatchingSynchronizationService;
use Mockery;

class AdminStudentSkillVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName, string $email): User
    {
        $user = User::create([
            'name' => ucfirst($roleName) . ' Test',
            'email' => $email,
            'password' => 'password',
        ]);

        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role->id);

        return $user;
    }

    private function createPendingSkill(int|string|null $level = 3): StudentSkill
    {
        $student = $this->createUserWithRole(
            'mahasiswa',
            'student-admin-test@example.com'
        );

        $profile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'ADMINTEST001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $skill = Skill::create([
            'name' => 'Test Skill Verification',
            'category' => 'Programming',
            'is_active' => true,
        ]);

        return StudentSkill::create([
            'student_profile_id' => $profile->id,
            'skill_id' => $skill->id,
            'actual_level' => $level,
            'confirmation_status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_skill_with_valid_level(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-approve-test@example.com'
        );

        $studentSkill = $this->createPendingSkill(3);

        $this->actingAs($admin)
            ->post(route('admin.skills.verification.approve', $studentSkill))
            ->assertRedirect(route('admin.skills.verification.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_skills', [
            'id' => $studentSkill->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);
    }

    public function test_admin_cannot_approve_skill_without_valid_level(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-invalid-test@example.com'
        );

        $studentSkill = $this->createPendingSkill(null);

        $this->actingAs($admin)
            ->post(route('admin.skills.verification.approve', $studentSkill))
            ->assertRedirect(route('admin.skills.verification.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('student_skills', [
            'id' => $studentSkill->id,
            'actual_level' => null,
            'confirmation_status' => 'pending',
        ]);
    }

    public function test_student_cannot_access_admin_verification_page(): void
    {
        $student = $this->createUserWithRole(
            'mahasiswa',
            'student-access-test@example.com'
        );

        $this->actingAs($student)
            ->get(route('admin.skills.verification.index'))
            ->assertForbidden();
    }

    public function test_admin_can_reject_pending_skill(): void
{
    $admin = $this->createUserWithRole(
        'admin',
        'admin-reject-test@example.com'
    );

    $studentSkill = $this->createPendingSkill(3);

    $this->actingAs($admin)
        ->post(route('admin.skills.verification.reject', $studentSkill))
        ->assertRedirect(route('admin.skills.verification.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('student_skills', [
        'id' => $studentSkill->id,
        'actual_level' => 3,
        'confirmation_status' => 'rejected',
    ]);
}
public function test_approving_skill_synchronizes_student_matching_results(): void
{
    $admin = $this->createUserWithRole(
        'admin',
        'admin-sync-test@example.com'
    );

    $student = $this->createUserWithRole(
        'mahasiswa',
        'student-sync-test@example.com'
    );

    $profile = StudentProfile::create([
        'user_id' => $student->id,
        'student_number' => 'SYNCTEST001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $coreSkill = Skill::create([
        'name' => 'Core Sync Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    $secondarySkill = Skill::create([
        'name' => 'Secondary Sync Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $profile->id,
        'skill_id' => $coreSkill->id,
        'actual_level' => 4,
        'confirmation_status' => 'approved',
    ]);

    $pendingSkill = StudentSkill::create([
        'student_profile_id' => $profile->id,
        'skill_id' => $secondarySkill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);

    $job = JobPosting::create([
        'title' => 'Software Developer',
        'company' => 'Perusahaan Pengujian',
        'description' => 'Lowongan untuk pengujian sinkronisasi.',
        'is_active' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $job->id,
        'skill_id' => $coreSkill->id,
        'required_level' => 4,
     'is_core_factor' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $job->id,
        'skill_id' => $secondarySkill->id,
        'required_level' => 3,
  'is_core_factor' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.skills.verification.approve', $pendingSkill))
        ->assertRedirect(route('admin.skills.verification.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('student_skills', [
        'id' => $pendingSkill->id,
        'confirmation_status' => 'approved',
    ]);

    $this->assertDatabaseHas('matching_results', [
        'student_profile_id' => $profile->id,
        'job_posting_id' => $job->id,
    ]);
}
public function test_rejecting_skill_synchronizes_student_matching_results(): void
{
    $admin = $this->createUserWithRole(
        'admin',
        'admin-reject-sync-test@example.com'
    );

    $student = $this->createUserWithRole(
        'mahasiswa',
        'student-reject-sync-test@example.com'
    );

    $profile = StudentProfile::create([
        'user_id' => $student->id,
        'student_number' => 'REJECTSYNC001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $coreSkill = Skill::create([
        'name' => 'Core Reject Sync Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    $secondarySkill = Skill::create([
        'name' => 'Secondary Reject Sync Skill',
        'category' => 'Programming',
        'is_active' => true,
    ]);

    StudentSkill::create([
        'student_profile_id' => $profile->id,
        'skill_id' => $coreSkill->id,
        'actual_level' => 4,
        'confirmation_status' => 'approved',
    ]);

    $pendingSkill = StudentSkill::create([
        'student_profile_id' => $profile->id,
        'skill_id' => $secondarySkill->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);

    $job = JobPosting::create([
        'title' => 'Software Developer Reject Test',
        'company' => 'Perusahaan Pengujian',
        'description' => 'Lowongan untuk pengujian sinkronisasi penolakan.',
        'is_active' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $job->id,
        'skill_id' => $coreSkill->id,
        'required_level' => 4,
        'is_core_factor' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $job->id,
        'skill_id' => $secondarySkill->id,
        'required_level' => 3,
        'is_core_factor' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.skills.verification.reject', $pendingSkill))
        ->assertRedirect(route('admin.skills.verification.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('student_skills', [
        'id' => $pendingSkill->id,
        'confirmation_status' => 'rejected',
    ]);

    $this->assertDatabaseHas('matching_results', [
        'student_profile_id' => $profile->id,
        'job_posting_id' => $job->id,
    ]);
}
public function test_approval_rolls_back_when_matching_synchronization_fails(): void
{
    $admin = $this->createUserWithRole(
        'admin',
        'admin-rollback-test@example.com'
    );

    $studentSkill = $this->createPendingSkill(3);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->andThrow(new \RuntimeException('Simulated synchronization failure'));

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->withoutExceptionHandling();

    try {
        $this->actingAs($admin)
            ->post(route('admin.skills.verification.approve', $studentSkill));

        $this->fail('Seharusnya sinkronisasi melempar exception.');
    } catch (\RuntimeException $exception) {
        $this->assertSame(
            'Simulated synchronization failure',
            $exception->getMessage()
        );
    }

    $this->assertDatabaseHas('student_skills', [
        'id' => $studentSkill->id,
        'confirmation_status' => 'pending',
    ]);
}
public function test_rejection_rolls_back_when_matching_synchronization_fails(): void
{
    $admin = $this->createUserWithRole(
        'admin',
        'admin-reject-rollback@example.com'
    );

    $studentSkill = $this->createPendingSkill(3);

    $mockService = Mockery::mock(MatchingSynchronizationService::class);

    $mockService->shouldReceive('synchronize')
        ->once()
        ->andThrow(new \RuntimeException('Simulated synchronization failure'));

    $this->app->instance(
        MatchingSynchronizationService::class,
        $mockService
    );

    $this->withoutExceptionHandling();

    try {
        $this->actingAs($admin)
            ->post(route('admin.skills.verification.reject', $studentSkill));

        $this->fail('Seharusnya sinkronisasi melempar exception.');
    } catch (\RuntimeException $exception) {
        $this->assertSame(
            'Simulated synchronization failure',
            $exception->getMessage()
        );
    }

    $this->assertDatabaseHas('student_skills', [
        'id' => $studentSkill->id,
        'confirmation_status' => 'pending',
    ]);
}
}
