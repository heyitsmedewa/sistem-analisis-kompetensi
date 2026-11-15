<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\Role;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBatchMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_calculate_all_active_jobs_in_one_request(): void
    {
        $user = User::create([
            'name' => 'Batch Test Student',
            'email' => 'batch-student@example.com',
            'password' => 'password',
        ]);

        $role = Role::firstOrCreate(['name' => 'mahasiswa']);
        $user->roles()->attach($role->id);

        StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'BATCH001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $coreSkill = Skill::create([
            'name' => 'Batch Core Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $secondarySkill = Skill::create([
            'name' => 'Batch Secondary Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        foreach ([
            ['title' => 'Lowongan Batch A', 'company' => 'Perusahaan A'],
            ['title' => 'Lowongan Batch B', 'company' => 'Perusahaan B'],
        ] as $jobData) {
            $job = JobPosting::create([
                ...$jobData,
                'is_active' => true,
            ]);

            JobSkill::create([
                'job_posting_id' => $job->id,
                'skill_id' => $coreSkill->id,
                'required_level' => 3,
                'is_core_factor' => true,
            ]);

            JobSkill::create([
                'job_posting_id' => $job->id,
                'skill_id' => $secondarySkill->id,
                'required_level' => 3,
                'is_core_factor' => false,
            ]);
        }

        $response = $this->actingAs($user)
            ->post(route('matching.calculate-all'));

        $response->assertRedirect(route('matching.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('matching_results', 2);
        $this->assertDatabaseCount('matching_details', 4);
    }

    
public function test_invalid_job_is_skipped_while_other_jobs_are_calculated(): void
{
    $user = User::create([
        'name' => 'Batch Validation Student',
        'email' => 'batch-validation@example.com',
        'password' => 'password',
    ]);

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);
    $user->roles()->attach($role->id);

    StudentProfile::create([
        'user_id' => $user->id,
        'student_number' => 'BATCH002',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $coreSkill = Skill::create([
        'name' => 'Validation Core Skill',
        'category' => 'technical',
        'is_active' => true,
    ]);

    $secondarySkill = Skill::create([
        'name' => 'Validation Secondary Skill',
        'category' => 'technical',
        'is_active' => true,
    ]);

    $validJob = JobPosting::create([
        'title' => 'Lowongan Valid',
        'company' => 'Perusahaan Valid',
        'is_active' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $validJob->id,
        'skill_id' => $coreSkill->id,
        'required_level' => 3,
        'is_core_factor' => true,
    ]);

    JobSkill::create([
        'job_posting_id' => $validJob->id,
        'skill_id' => $secondarySkill->id,
        'required_level' => 3,
        'is_core_factor' => false,
    ]);

    $invalidJob = JobPosting::create([
        'title' => 'Lowongan Tanpa Kompetensi',
        'company' => 'Perusahaan Tidak Valid',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->post(route('matching.calculate-all'));

    $response->assertRedirect(route('matching.index'));
    $response->assertSessionHas('success');
    $response->assertSessionHas('skipped_jobs', [
        'Lowongan Tanpa Kompetensi',
    ]);

    $this->assertDatabaseHas('matching_results', [
        'job_posting_id' => $validJob->id,
    ]);

    $this->assertDatabaseMissing('matching_results', [
        'job_posting_id' => $invalidJob->id,
    ]);

    $this->assertDatabaseCount('matching_results', 1);
}


public function test_recalculating_batch_does_not_duplicate_results(): void
{
    $user = User::create([
        'name' => 'Batch Repeat Student',
        'email' => 'batch-repeat@example.com',
        'password' => 'password',
    ]);

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);
    $user->roles()->attach($role->id);

    StudentProfile::create([
        'user_id' => $user->id,
        'student_number' => 'BATCH003',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $coreSkill = Skill::create([
        'name' => 'Repeat Core Skill',
        'category' => 'technical',
        'is_active' => true,
    ]);

    $secondarySkill = Skill::create([
        'name' => 'Repeat Secondary Skill',
        'category' => 'technical',
        'is_active' => true,
    ]);

    foreach (['Lowongan Repeat A', 'Lowongan Repeat B'] as $title) {
        $job = JobPosting::create([
            'title' => $title,
            'company' => 'Perusahaan Pengujian',
            'is_active' => true,
        ]);

        JobSkill::create([
            'job_posting_id' => $job->id,
            'skill_id' => $coreSkill->id,
            'required_level' => 3,
            'is_core_factor' => true,
        ]);

        JobSkill::create([
            'job_posting_id' => $job->id,
            'skill_id' => $secondarySkill->id,
            'required_level' => 3,
            'is_core_factor' => false,
        ]);
    }

    $this->actingAs($user)
        ->post(route('matching.calculate-all'))
        ->assertRedirect(route('matching.index'));

    $this->post(route('matching.calculate-all'))
        ->assertRedirect(route('matching.index'));

    $this->assertDatabaseCount('matching_results', 2);
    $this->assertDatabaseCount('matching_details', 4);
}


}
