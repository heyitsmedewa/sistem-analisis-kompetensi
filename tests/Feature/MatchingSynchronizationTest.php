<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\MatchingResult;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use App\Services\MatchingSynchronizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchingSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    private function createScenario(): array
    {
        $user = User::create([
            'name' => 'Test Student',
            'email' => 'sync-test@example.com',
            'password' => 'password',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'SYNC001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $coreSkill = Skill::create([
            'name' => 'Sync Core Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $secondarySkill = Skill::create([
            'name' => 'Sync Secondary Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        StudentSkill::create([
            'student_profile_id' => $profile->id,
            'skill_id' => $coreSkill->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        StudentSkill::create([
            'student_profile_id' => $profile->id,
            'skill_id' => $secondarySkill->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        return compact('profile', 'coreSkill', 'secondarySkill');
    }

    private function createJob(
        string $title,
        Skill $coreSkill,
        Skill $secondarySkill,
        bool $active = true
    ): JobPosting {
        $job = JobPosting::create([
            'title' => $title,
            'company' => 'Test Company',
            'is_active' => $active,
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

        return $job;
    }

    public function test_synchronization_calculates_all_active_jobs(): void
    {
        $data = $this->createScenario();

        $jobOne = $this->createJob(
            'Programmer',
            $data['coreSkill'],
            $data['secondarySkill']
        );

        $jobTwo = $this->createJob(
            'Backend Developer',
            $data['coreSkill'],
            $data['secondarySkill']
        );

        $service = app(MatchingSynchronizationService::class);

        $results = $service->synchronize($data['profile']);

        $this->assertCount(2, $results);
        $this->assertDatabaseCount('matching_results', 2);

        $this->assertTrue(
            $results->contains('job_posting_id', $jobOne->id)
        );

        $this->assertTrue(
            $results->contains('job_posting_id', $jobTwo->id)
        );
    }

    public function test_synchronization_skips_inactive_jobs(): void
    {
        $data = $this->createScenario();

        $this->createJob(
            'Active Job',
            $data['coreSkill'],
            $data['secondarySkill']
        );

        $this->createJob(
            'Inactive Job',
            $data['coreSkill'],
            $data['secondarySkill'],
            false
        );

        $results = app(MatchingSynchronizationService::class)
            ->synchronize($data['profile']);

        $this->assertCount(1, $results);
        $this->assertDatabaseCount('matching_results', 1);
    }

    public function test_synchronization_skips_jobs_without_requirements(): void
    {
        $data = $this->createScenario();

        $this->createJob(
            'Valid Job',
            $data['coreSkill'],
            $data['secondarySkill']
        );

        JobPosting::create([
            'title' => 'Job Without Requirements',
            'company' => 'Test Company',
            'is_active' => true,
        ]);

        $results = app(MatchingSynchronizationService::class)
            ->synchronize($data['profile']);

        $this->assertCount(1, $results);
        $this->assertDatabaseCount('matching_results', 1);
    }

    public function test_synchronization_updates_existing_results_without_duplicates(): void
    {
        $data = $this->createScenario();

        $job = $this->createJob(
            'Programmer',
            $data['coreSkill'],
            $data['secondarySkill']
        );

        $service = app(MatchingSynchronizationService::class);

        $firstResults = $service->synchronize($data['profile']);
        $firstId = $firstResults->first()->id;

        StudentSkill::where('student_profile_id', $data['profile']->id)
            ->where('skill_id', $data['coreSkill']->id)
            ->update(['actual_level' => 2]);

        $secondResults = $service->synchronize($data['profile']);

        $this->assertCount(1, $secondResults);
        $this->assertSame($firstId, $secondResults->first()->id);
        $this->assertDatabaseCount('matching_results', 1);
        $this->assertDatabaseHas('matching_results', [
            'id' => $firstId,
            'job_posting_id' => $job->id,
        ]);
    }
    public function test_job_posting_synchronization_updates_existing_result(): void
{
    $data = $this->createScenario();

    $job = $this->createJob(
        'Programmer',
        $data['coreSkill'],
        $data['secondarySkill']
    );

    $service = app(MatchingSynchronizationService::class);

    $firstResult = app(\App\Services\ProfileMatchingService::class)
        ->calculate($data['profile'], $job);

    $originalScore = (float) $firstResult->final_score;
    $originalId = $firstResult->id;

    $job->jobSkills()
        ->where('skill_id', $data['coreSkill']->id)
        ->update(['required_level' => 5]);

    $results = $service->synchronizeJobPosting($job);

    $this->assertCount(1, $results);
    $this->assertSame($originalId, $results->first()->id);
    $this->assertNotEquals(
        $originalScore,
        (float) $results->first()->final_score
    );

    $this->assertDatabaseCount('matching_results', 1);
}

public function test_job_posting_synchronization_does_not_create_new_results(): void
{
    $data = $this->createScenario();

    $job = $this->createJob(
        'Programmer',
        $data['coreSkill'],
        $data['secondarySkill']
    );

    $results = app(MatchingSynchronizationService::class)
        ->synchronizeJobPosting($job);

    $this->assertCount(0, $results);
    $this->assertDatabaseCount('matching_results', 0);
}
}
