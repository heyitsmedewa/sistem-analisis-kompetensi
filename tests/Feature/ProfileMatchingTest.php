<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\JobSkill;
use App\Models\MatchingResult;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use App\Services\ProfileMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function createScenario(): array
    {
        $user = User::create([
            'name' => 'Test Student',
            'email' => 'student-test@example.com',
            'password' => 'password',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'TEST001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $coreSkill = Skill::create([
            'name' => 'Test Core Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $secondarySkill = Skill::create([
            'name' => 'Test Secondary Skill',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $job = JobPosting::create([
            'title' => 'Test Programmer',
            'company' => 'Test Company',
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

        return compact(
            'profile',
            'job',
            'coreSkill',
            'secondarySkill'
        );
    }

    public function test_calculate_persists_final_result_for_verified_skills(): void
    {
        $data = $this->createScenario();

        StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $data['coreSkill']->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $data['secondarySkill']->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        $result = app(ProfileMatchingService::class)->calculate(
            $data['profile'],
            $data['job']
        );

        $this->assertInstanceOf(MatchingResult::class, $result);
        $this->assertSame('final', $result->status);
    $this->assertEquals(5.0, $result->final_score);
        $this->assertCount(2, $result->details);
        $this->assertDatabaseCount('matching_results', 1);
        $this->assertDatabaseCount('matching_details', 2);
        $this->assertSame(
            ['evaluated'],
            $result->details->pluck('evaluation_status')->unique()->values()->all()
        );
    }
public function test_unverified_skill_produces_provisional_result(): void
{
    $data = $this->createScenario();

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['coreSkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['secondarySkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);

    $result = app(ProfileMatchingService::class)->calculate(
        $data['profile'],
        $data['job']
    );

    $this->assertSame('provisional', $result->status);

    // Skor tidak dihitung karena Secondary Factor belum memiliki data terverifikasi.
  $this->assertNull($result->final_score);

    $pendingDetail = $result->details->firstWhere(
        'skill_id',
        $data['secondarySkill']->id
    );

    $this->assertSame('pending', $pendingDetail->evaluation_status);
    $this->assertNull($pendingDetail->actual_level);
    $this->assertNull($pendingDetail->gap);
    $this->assertNull($pendingDetail->gap_weight);
}

    public function test_score_is_null_when_one_factor_has_no_verified_competency(): void
{
    $data = $this->createScenario();

    // Hanya Secondary Factor yang terverifikasi.
    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['secondarySkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    // Core Factor belum diverifikasi.
    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['coreSkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'pending',
    ]);

    $result = app(ProfileMatchingService::class)->calculate(
        $data['profile'],
        $data['job']
    );

    $this->assertSame('provisional', $result->status);
    $this->assertNull($result->final_score);

    $coreDetail = $result->details->firstWhere(
        'skill_id',
        $data['coreSkill']->id
    );

    $this->assertSame('pending', $coreDetail->evaluation_status);
    $this->assertNull($coreDetail->gap);
    $this->assertNull($coreDetail->gap_weight);
}
    public function test_inactive_job_is_rejected(): void
    {
        $data = $this->createScenario();

        $data['job']->update(['is_active' => false]);

        try {
            app(ProfileMatchingService::class)->calculate(
                $data['profile'],
                $data['job']
            );

            $this->fail('Lowongan nonaktif seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('job', $exception->errors());
            $this->assertDatabaseCount('matching_results', 0);
        }
    }

    public function test_result_is_updated_instead_of_duplicated(): void
    {
        $data = $this->createScenario();

        StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $data['coreSkill']->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        StudentSkill::create([
            'student_profile_id' => $data['profile']->id,
            'skill_id' => $data['secondarySkill']->id,
            'actual_level' => 3,
            'confirmation_status' => 'approved',
        ]);

        $service = app(ProfileMatchingService::class);

        $first = $service->calculate($data['profile'], $data['job']);
        $firstId = $first->id;

        StudentSkill::where('student_profile_id', $data['profile']->id)
            ->where('skill_id', $data['coreSkill']->id)
            ->update(['actual_level' => 2]);

        $second = $service->calculate($data['profile'], $data['job']);

        $this->assertSame($firstId, $second->id);
        $this->assertDatabaseCount('matching_results', 1);
        $this->assertDatabaseCount('matching_details', 2);
        $this->assertEquals(4.4, $second->final_score);
    }

    public function test_recalculation_marks_old_ai_recommendation_as_stale(): void
{
    $data = $this->createScenario();

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['coreSkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    StudentSkill::create([
        'student_profile_id' => $data['profile']->id,
        'skill_id' => $data['secondarySkill']->id,
        'actual_level' => 3,
        'confirmation_status' => 'approved',
    ]);

    $service = app(ProfileMatchingService::class);

    $result = $service->calculate($data['profile'], $data['job']);

    $recommendation = $result->aiRecommendations()->create([
        'recommendation_type' => 'career_development',
        'content' => json_encode([
            'summary' => 'Rekomendasi lama',
        ]),
        'model_name' => 'test-model',
        'status' => 'completed',
        'generated_at' => now(),
    ]);

    StudentSkill::where('student_profile_id', $data['profile']->id)
        ->where('skill_id', $data['coreSkill']->id)
        ->update(['actual_level' => 2]);

    $service->calculate($data['profile'], $data['job']);

    $this->assertDatabaseHas('ai_recommendations', [
        'id' => $recommendation->id,
        'status' => 'stale',
    ]);

    $this->assertDatabaseCount('ai_recommendations', 1);
}
}
