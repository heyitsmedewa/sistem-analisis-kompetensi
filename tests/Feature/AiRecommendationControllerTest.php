<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\MatchingResult;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AiRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AiRecommendationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createStudentWithMatchingResult(): array
    {
        $role = Role::firstOrCreate(['name' => 'mahasiswa']);

        $user = User::create([
            'name' => 'Test Student',
            'email' => 'student-ai@example.com',
            'password' => 'password',
        ]);

        $user->roles()->attach($role);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'AI001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $job = JobPosting::create([
            'title' => 'Test Programmer',
            'company' => 'Test Company',
            'is_active' => true,
        ]);

        $result = MatchingResult::create([
            'student_profile_id' => $profile->id,
            'job_posting_id' => $job->id,
            'final_score' => 80,
            'status' => 'final',
            'calculated_at' => now(),
        ]);

        return compact('user', 'profile', 'result');
    }

    public function test_student_can_request_ai_recommendation(): void
    {
        $data = $this->createStudentWithMatchingResult();

        $service = $this->mock(AiRecommendationService::class);
        $service->shouldReceive('generateAndSave')
            ->once()
            ->withArgs(
                fn (MatchingResult $result): bool =>
                    $result->is($data['result'])
            );

        $response = $this->actingAs($data['user'])->post(
            route(
                'matching.recommendations.generate',
                $data['result']
            )
        );

        $response->assertRedirect(route('matching.index'))
            ->assertSessionHas(
                'success',
                'Rekomendasi pengembangan kompetensi berhasil dibuat.'
            );
    }

    public function test_student_cannot_request_recommendation_for_another_student(): void
    {
        $first = $this->createStudentWithMatchingResult();

        $secondUser = User::create([
            'name' => 'Other Student',
            'email' => 'other-ai@example.com',
            'password' => 'password',
        ]);

        $secondUser->roles()->attach(
            Role::where('name', 'mahasiswa')->firstOrFail()
        );

        $secondProfile = StudentProfile::create([
            'user_id' => $secondUser->id,
            'student_number' => 'AI002',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $this->actingAs($secondUser)->post(
            route(
                'matching.recommendations.generate',
                $first['result']
            )
        )->assertForbidden();

        $this->assertDatabaseCount('ai_recommendations', 0);
        $this->assertSame(
            $first['profile']->id !== $secondProfile->id,
            true
        );
    }

    public function test_ai_failure_shows_safe_error_message(): void
    {
        $data = $this->createStudentWithMatchingResult();

        $service = $this->mock(AiRecommendationService::class);
        $service->shouldReceive('generateAndSave')
            ->once()
            ->andThrow(new RuntimeException('Sensitive internal detail'));

        $response = $this->actingAs($data['user'])->post(
            route(
                'matching.recommendations.generate',
                $data['result']
            )
        );

        $response->assertRedirect(route('matching.index'))
            ->assertSessionHas(
                'error',
                'Rekomendasi belum berhasil dibuat. Silakan coba lagi nanti.'
            )
            ->assertSessionMissing('Sensitive internal detail');
    }

    
public function test_completed_recommendation_does_not_call_ai_service_again(): void
{
    $data = $this->createStudentWithMatchingResult();

    $data['result']->aiRecommendations()->create([
        'recommendation_type' => 'career_development',
        'content' => json_encode([
            'summary' => 'Test recommendation',
            'verified_gaps' => [],
            'verification_needed' => [],
            'development_plan' => [],
        ]),
        'model_name' => 'test-model',
        'status' => 'completed',
        'generated_at' => now(),
    ]);

    $service = $this->mock(AiRecommendationService::class);
    $service->shouldNotReceive('generateAndSave');

    $response = $this->actingAs($data['user'])->post(
        route(
            'matching.recommendations.generate',
            $data['result']
        )
    );

    $response->assertRedirect(route('matching.index'))
        ->assertSessionHas(
            'success',
            'Rekomendasi untuk hasil analisis ini sudah tersedia.'
        );

    $this->assertDatabaseCount('ai_recommendations', 1);
}
public function test_student_can_request_new_recommendation_when_old_one_is_stale(): void
{
    $data = $this->createStudentWithMatchingResult();

    $oldRecommendation = $data['result']->aiRecommendations()->create([
        'recommendation_type' => 'career_development',
        'content' => json_encode([
            'summary' => 'Old recommendation',
            'verified_gaps' => [],
            'verification_needed' => [],
            'development_plan' => [],
        ]),
        'model_name' => 'test-model',
        'status' => 'stale',
        'generated_at' => now(),
    ]);

    $service = $this->mock(AiRecommendationService::class);
    $service->shouldReceive('generateAndSave')
        ->once()
        ->withArgs(
            fn (MatchingResult $result): bool =>
                $result->is($data['result'])
        );

    $response = $this->actingAs($data['user'])->post(
        route(
            'matching.recommendations.generate',
            $data['result']
        )
    );

    $response->assertRedirect(route('matching.index'))
        ->assertSessionHas(
            'success',
            'Rekomendasi pengembangan kompetensi berhasil dibuat.'
        );

    $this->assertDatabaseHas('ai_recommendations', [
        'id' => $oldRecommendation->id,
        'status' => 'stale',
    ]);

    $this->assertDatabaseCount('ai_recommendations', 1);
}
public function test_student_cannot_request_recommendation_when_score_is_null(): void
{
    $data = $this->createStudentWithMatchingResult();

    $data['result']->update([
        'final_score' => null,
    ]);

    $service = $this->mock(AiRecommendationService::class);
    $service->shouldNotReceive('generateAndSave');

    $response = $this->actingAs($data['user'])->post(
        route(
            'matching.recommendations.generate',
            $data['result']
        )
    );

    $response->assertRedirect(route('matching.index'))
        ->assertSessionHas(
            'error',
            'Hitung kecocokan lowongan terlebih dahulu.'
        );

    $this->assertDatabaseCount('ai_recommendations', 0);
}
public function test_student_cannot_request_recommendation_when_status_is_invalid(): void
{
    $data = $this->createStudentWithMatchingResult();

    $data['result']->update([
        'status' => 'pending',
    ]);

    $service = $this->mock(AiRecommendationService::class);
    $service->shouldNotReceive('generateAndSave');

    $response = $this->actingAs($data['user'])->post(
        route(
            'matching.recommendations.generate',
            $data['result']
        )
    );

    $response->assertRedirect(route('matching.index'))
        ->assertSessionHas(
            'error',
            'Hitung kecocokan lowongan terlebih dahulu.'
        );

    $this->assertDatabaseCount('ai_recommendations', 0);
}
}
