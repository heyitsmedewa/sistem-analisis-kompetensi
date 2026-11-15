<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\MatchingResult;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMatchingRankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_matching_ranking_uses_competition_ranking_for_equal_scores(): void
    {
        $user = User::create([
            'name' => 'Ranking Test Student',
            'email' => 'ranking-student@example.com',
            'password' => 'password',
        ]);

        $role = Role::firstOrCreate(['name' => 'mahasiswa']);
        $user->roles()->attach($role->id);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'RANK001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $jobs = [
            ['title' => 'Lowongan A', 'score' => 4.50],
            ['title' => 'Lowongan B', 'score' => 4.50],
            ['title' => 'Lowongan C', 'score' => 4.00],
            ['title' => 'Lowongan D', 'score' => 3.50],
        ];

        foreach ($jobs as $jobData) {
            $job = JobPosting::create([
                'title' => $jobData['title'],
                'company' => 'Perusahaan Pengujian',
                'is_active' => true,
            ]);

            MatchingResult::create([
                'student_profile_id' => $profile->id,
                'job_posting_id' => $job->id,
                'final_score' => $jobData['score'],
                'status' => 'final',
                'calculated_at' => now(),
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('matching.index'));

        $response->assertOk();

        $response->assertSeeInOrder([
            'Lowongan A',
            'Lowongan B',
            'Lowongan C',
            'Lowongan D',
        ]);

        $html = $response->getContent();

preg_match_all(
    '/<span class="inline-flex h-8 w-8[^"]*">([\s\S]*?)<\/span>/',
    $html,
    $matches
);

$this->assertSame(
    ['1', '1', '3', '4'],
    array_map('trim', $matches[1])
);
    }

    public function test_inactive_job_postings_are_excluded_from_ranking(): void
{
    $user = User::create([
        'name' => 'Ranking Test Student',
        'email' => 'inactive-ranking@example.com',
        'password' => 'password',
    ]);

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);
    $user->roles()->attach($role->id);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'student_number' => 'RANK002',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $activeJob = JobPosting::create([
        'title' => 'Lowongan Aktif',
        'company' => 'Perusahaan Pengujian',
        'is_active' => true,
    ]);

    $inactiveJob = JobPosting::create([
        'title' => 'Lowongan Nonaktif',
        'company' => 'Perusahaan Pengujian',
        'is_active' => false,
    ]);

    foreach ([$activeJob, $inactiveJob] as $job) {
        MatchingResult::create([
            'student_profile_id' => $profile->id,
            'job_posting_id' => $job->id,
            'final_score' => 4.50,
            'status' => 'final',
            'calculated_at' => now(),
        ]);
    }

    $response = $this->actingAs($user)
        ->get(route('matching.index'));

    $response->assertOk();
    $response->assertSee('Lowongan Aktif');
    $html = $response->getContent();

preg_match(
    '/<section\b[^>]*>.*?Peringkat Kecocokan Lowongan.*?<\/section>/s',
    $html,
    $rankingSection
);

$this->assertNotEmpty($rankingSection);
$this->assertStringContainsString('Lowongan Aktif', $rankingSection[0]);
$this->assertStringNotContainsString('Lowongan Nonaktif', $rankingSection[0]);

// Lowongan nonaktif tetap tersedia di riwayat analisis.
$response->assertSee('Lowongan Nonaktif');
}

public function test_ranking_displays_latest_matching_score(): void
{
    $user = User::create([
        'name' => 'Latest Score Student',
        'email' => 'latest-score@example.com',
        'password' => 'password',
    ]);

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);
    $user->roles()->attach($role->id);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'student_number' => 'RANK003',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $job = JobPosting::create([
        'title' => 'Lowongan Skor Terbaru',
        'company' => 'Perusahaan Pengujian',
        'is_active' => true,
    ]);

    $result = MatchingResult::create([
        'student_profile_id' => $profile->id,
        'job_posting_id' => $job->id,
        'final_score' => 3.50,
        'status' => 'final',
        'calculated_at' => now(),
    ]);

    // Simulasikan hasil setelah perhitungan ulang.
    $result->update([
        'final_score' => 4.40,
        'calculated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('matching.index'));

    $response->assertOk();
    $response->assertSee('Lowongan Skor Terbaru');

    $response->assertSee('4.40');
    $response->assertDontSee('3.50');
}
}
