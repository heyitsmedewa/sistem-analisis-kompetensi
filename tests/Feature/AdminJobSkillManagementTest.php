<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\Role;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJobSkillManagementTest extends TestCase
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

    private function createJobSkill(): array
    {
        $jobPosting = JobPosting::create([
            'title' => 'Lowongan Test',
            'company' => 'Perusahaan Test',
            'is_active' => true,
        ]);

        $skill = Skill::create([
            'name' => 'Test Skill',
            'category' => 'Programming',
            'is_active' => true,
        ]);

        $jobSkill = JobSkill::create([
            'job_posting_id' => $jobPosting->id,
            'skill_id' => $skill->id,
            'required_level' => 3,
            'is_core_factor' => true,
            'notes' => 'Catatan awal',
        ]);

        return [$jobPosting, $jobSkill];
    }

    public function test_admin_can_update_job_skill_requirement(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-job-skill-update@example.com'
        );

        [$jobPosting, $jobSkill] = $this->createJobSkill();

        $this->actingAs($admin)
            ->put(route('admin.job-postings.skills.update', [
                $jobPosting,
                $jobSkill,
            ]), [
                'required_level' => 4,
                'is_core_factor' => 0,
                'notes' => 'Catatan diperbarui',
            ])
            ->assertRedirect(route('admin.job-postings.show', $jobPosting))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('job_skills', [
            'id' => $jobSkill->id,
            'required_level' => 4,
            'is_core_factor' => 0,
            'notes' => 'Catatan diperbarui',
        ]);
    }

    public function test_admin_can_delete_job_skill_requirement(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-job-skill-delete@example.com'
        );

        [$jobPosting, $jobSkill] = $this->createJobSkill();

        $this->actingAs($admin)
            ->delete(route('admin.job-postings.skills.destroy', [
                $jobPosting,
                $jobSkill,
            ]))
            ->assertRedirect(route('admin.job-postings.show', $jobPosting))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('job_skills', [
            'id' => $jobSkill->id,
        ]);
    }

    public function test_admin_cannot_update_job_skill_of_another_job(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-job-skill-owner@example.com'
        );

        [$firstJob, $jobSkill] = $this->createJobSkill();

        $otherJob = JobPosting::create([
            'title' => 'Lowongan Lain',
            'company' => 'Perusahaan Lain',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.job-postings.skills.update', [
                $otherJob,
                $jobSkill,
            ]), [
                'required_level' => 5,
                'is_core_factor' => 0,
                'notes' => 'Tidak boleh berubah',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('job_skills', [
            'id' => $jobSkill->id,
            'job_posting_id' => $firstJob->id,
            'required_level' => 3,
            'is_core_factor' => 1,
            'notes' => 'Catatan awal',
        ]);
    }

    public function test_admin_cannot_delete_job_skill_of_another_job(): void
    {
        $admin = $this->createUserWithRole(
            'admin',
            'admin-job-skill-owner-delete@example.com'
        );

        [$firstJob, $jobSkill] = $this->createJobSkill();

        $otherJob = JobPosting::create([
            'title' => 'Lowongan Lain',
            'company' => 'Perusahaan Lain',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.job-postings.skills.destroy', [
                $otherJob,
                $jobSkill,
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('job_skills', [
            'id' => $jobSkill->id,
            'job_posting_id' => $firstJob->id,
        ]);
    }

    public function test_student_cannot_update_or_delete_job_skill(): void
    {
        $student = $this->createUserWithRole(
            'mahasiswa',
            'student-job-skill-access@example.com'
        );

        [$jobPosting, $jobSkill] = $this->createJobSkill();

        $this->actingAs($student)
            ->put(route('admin.job-postings.skills.update', [
                $jobPosting,
                $jobSkill,
            ]), [
                'required_level' => 5,
                'is_core_factor' => 1,
                'notes' => 'Akses tidak sah',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('admin.job-postings.skills.destroy', [
                $jobPosting,
                $jobSkill,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('job_skills', [
            'id' => $jobSkill->id,
            'required_level' => 3,
        ]);
    }
}
