<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\CvTextExtractor;
use App\Services\GeminiCvExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class CvUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_upload_cv_to_private_storage(): void
    {
        Storage::fake('local');

        $role = Role::create(['name' => 'mahasiswa']);

        $user = User::create([
            'name' => 'CV Upload Test',
            'email' => 'cv-upload@example.com',
            'password' => 'password',
        ]);

        $user->roles()->attach($role->id);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'CVUPLOAD001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $this->mock(CvTextExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn([
                    'extracted_text' => 'Contoh teks CV untuk pengujian.',
                ]);
        });

        $this->mock(GeminiCvExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn([
                    'personal_info' => [
                        'full_name' => 'CV Upload Test',
                    ],
                    'educations' => [],
                    'experiences' => [],
                    'certifications' => [],
                    'projects' => [],
                    'hard_skills' => [],
                ]);
        });

        $response = $this->actingAs($user)->post(route('cv.store'), [
            'cv' => UploadedFile::fake()->createWithContent(
                'cv-test.pdf',
                "%PDF-1.4\nContoh PDF untuk pengujian."
            ),
        ]);

        $response->assertRedirect(route('cv.index'));

        $this->assertDatabaseHas('cvs', [
            'student_profile_id' => $profile->id,
            'original_filename' => 'cv-test.pdf',
            'extraction_status' => 'completed',
        ]);

        $cv = \App\Models\Cv::where(
            'student_profile_id',
            $profile->id
        )->firstOrFail();

        Storage::disk('local')->assertExists($cv->file_path);

        $this->assertDatabaseHas('cv_extractions', [
            'cv_id' => $cv->id,
            'status' => 'completed',
        ]);
    }

public function test_cv_is_saved_with_failed_status_when_text_extraction_fails(): void
{
    Storage::fake('local');

    $role = Role::create(['name' => 'mahasiswa']);

    $user = User::create([
        'name' => 'CV Failure Test',
        'email' => 'cv-failure@example.com',
        'password' => 'password',
    ]);

    $user->roles()->attach($role->id);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'student_number' => 'CVFAILURE001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $this->mock(CvTextExtractor::class, function ($mock) {
        $mock->shouldReceive('extract')
            ->once()
            ->andThrow(new \RuntimeException(
                'Dokumen CV gagal dibaca.'
            ));
    });

    $this->mock(GeminiCvExtractor::class, function ($mock) {
        $mock->shouldNotReceive('extract');
    });

    $response = $this->actingAs($user)->post(route('cv.store'), [
        'cv' => UploadedFile::fake()->createWithContent(
            'cv-gagal.pdf',
            "%PDF-1.4\nDokumen pengujian."
        ),
    ]);

    $response->assertRedirect(route('cv.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('cvs', [
        'student_profile_id' => $profile->id,
        'original_filename' => 'cv-gagal.pdf',
        'extraction_status' => 'failed',
    ]);

    $cv = \App\Models\Cv::where(
        'student_profile_id',
        $profile->id
    )->firstOrFail();

    Storage::disk('local')->assertExists($cv->file_path);

    $this->assertDatabaseHas('cv_extractions', [
        'cv_id' => $cv->id,
        'status' => 'failed',
    ]);
}


public function test_student_cannot_view_another_students_cv(): void
{
    $role = Role::create(['name' => 'mahasiswa']);

    $owner = User::create([
        'name' => 'CV Owner',
        'email' => 'cv-owner@example.com',
        'password' => 'password',
    ]);
    $owner->roles()->attach($role->id);

    $ownerProfile = StudentProfile::create([
        'user_id' => $owner->id,
        'student_number' => 'CVOWNER001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $otherStudent = User::create([
        'name' => 'Other Student',
        'email' => 'other-student@example.com',
        'password' => 'password',
    ]);
    $otherStudent->roles()->attach($role->id);

    StudentProfile::create([
        'user_id' => $otherStudent->id,
        'student_number' => 'CVOTHER001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $cv = \App\Models\Cv::create([
        'student_profile_id' => $ownerProfile->id,
        'original_filename' => 'private-cv.pdf',
        'file_path' => 'cvs/' . $ownerProfile->id . '/private-cv.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'extraction_status' => 'completed',
    ]);

    $response = $this->actingAs($otherStudent)
        ->get(route('cv.show', $cv));

    $response->assertForbidden();
}


public function test_student_cannot_confirm_another_students_cv(): void
{
    $role = Role::create(['name' => 'mahasiswa']);

    $owner = User::create([
        'name' => 'CV Confirmation Owner',
        'email' => 'confirm-owner@example.com',
        'password' => 'password',
    ]);
    $owner->roles()->attach($role->id);

    $ownerProfile = StudentProfile::create([
        'user_id' => $owner->id,
        'student_number' => 'CONFIRMOWNER001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $otherStudent = User::create([
        'name' => 'Other Confirmation Student',
        'email' => 'confirm-other@example.com',
        'password' => 'password',
    ]);
    $otherStudent->roles()->attach($role->id);

    StudentProfile::create([
        'user_id' => $otherStudent->id,
        'student_number' => 'CONFIRMOTHER001',
        'study_program' => 'Informatika',
        'institution' => 'Kampus Pengujian',
        'education_level' => 'S1',
    ]);

    $cv = \App\Models\Cv::create([
        'student_profile_id' => $ownerProfile->id,
        'original_filename' => 'private-cv.pdf',
        'file_path' => 'cvs/' . $ownerProfile->id . '/private-cv.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'extraction_status' => 'completed',
    ]);

    $cv->extractions()->create([
        'raw_response' => ['source' => 'test'],
        'status' => 'completed',
        'model_name' => 'test-model',
    ]);

    $response = $this->actingAs($otherStudent)
        ->post(route('cv.confirm', $cv), [
            'confirmed_data' => json_encode([
                'educations' => [],
                'experiences' => [],
                'certifications' => [],
                'projects' => [],
                'hard_skills' => [],
            ]),
        ]);

    $response->assertForbidden();
}

public function test_student_without_profile_cannot_access_cv_index(): void
{
    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $response = $this->actingAs($student)
        ->get(route('cv.index'));

    $response->assertForbidden();
}


public function test_student_without_profile_cannot_upload_cv(): void
{
    Storage::fake('local');

    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $response = $this->actingAs($student)
        ->post(route('cv.store'), [
            'cv' => UploadedFile::fake()->create(
                'cv.pdf',
                100,
                'application/pdf'
            ),
        ]);

    $response->assertForbidden();

    $this->assertDatabaseCount('cvs', 0);
    $this->assertSame([], Storage::disk('local')->allFiles());
}


public function test_student_without_profile_cannot_view_cv(): void
{
    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $owner = User::factory()->create();
    $owner->roles()->attach($role);

    $ownerProfile = StudentProfile::create([
        'user_id' => $owner->id,
        'student_number' => 'CV-OWNER-001',
        'study_program' => 'STI',
    ]);

    $cv = \App\Models\Cv::create([
        'student_profile_id' => $ownerProfile->id,
        'original_filename' => 'cv-owner.pdf',
        'file_path' => 'cvs/'.$ownerProfile->id.'/cv-owner.pdf',
        'mime_type' => 'application/pdf',
        'file_size' => 1024,
        'extraction_status' => 'completed',
        'uploaded_at' => now(),
    ]);

    $response = $this->actingAs($student)
        ->get(route('cv.show', $cv));

    $response->assertForbidden();
}


public function test_student_without_profile_cannot_access_profile_page(): void
{
    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $response = $this->actingAs($student)
        ->get(route('profile.show'));

    $response->assertForbidden();
}


public function test_student_without_profile_cannot_update_profile(): void
{
    $role = Role::firstOrCreate(['name' => 'mahasiswa']);

    $student = User::factory()->create();
    $student->roles()->attach($role);

    $response = $this->actingAs($student)
        ->put(route('profile.update'), [
            'student_number' => 'TEST-001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Test',
            'education_level' => 'S1',
        ]);

    $response->assertForbidden();
}


}
