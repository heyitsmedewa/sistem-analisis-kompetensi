<?php

use App\Http\Controllers\Admin\StudentSkillVerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\StudentMatchingController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\StudentSkillController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AiRecommendationController;

Route::get('/', function () {
    return view('welcome');
});

// Autentikasi: login dan register
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Fitur mahasiswa
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    // Profil mahasiswa
    Route::get('/profile', [StudentProfileController::class, 'show'])
        ->name('profile.show');

    Route::put('/profile', [StudentProfileController::class, 'update'])
        ->name('profile.update');

    // Kompetensi mahasiswa
    Route::get('/kompetensi', [StudentSkillController::class, 'index'])
        ->name('skills.index');

    Route::post('/kompetensi', [StudentSkillController::class, 'store'])
        ->name('skills.store');

    // CV mahasiswa
    Route::get('/cv', [CvController::class, 'index'])
        ->name('cv.index');

    Route::get('/cv/{cv}', [CvController::class, 'show'])
        ->name('cv.show');

    Route::post('/cv', [CvController::class, 'store'])
        ->name('cv.store');

    Route::post('/cv/{cv}/confirm', [CvController::class, 'confirm'])
        ->name('cv.confirm');

    // Analisis Profile Matching
    Route::get('/pencocokan', [
        StudentMatchingController::class,
        'index',
    ])->name('matching.index');

    Route::post('/pencocokan/hitung-semua', [
    StudentMatchingController::class,
    'calculateAll',
])->name('matching.calculate-all');

    Route::post('/pencocokan/{jobPosting}/hitung', [
        StudentMatchingController::class,
        'calculate',
    ])->name('matching.calculate');
    Route::post(
    '/pencocokan/{matchingResult}/rekomendasi',
    [AiRecommendationController::class, 'generate']
)
    ->whereNumber('matchingResult')
    ->name('matching.recommendations.generate');
});

// Fitur admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Route admin lainnya tetap di bawah sini      

        // Verifikasi kompetensi
        Route::get('/kompetensi', [
            StudentSkillVerificationController::class,
            'index',
        ])->name('skills.verification.index');

        Route::get('/kompetensi/riwayat', [
            StudentSkillVerificationController::class,
            'history',
        ])->name('skills.verification.history');

        Route::post('/kompetensi/{studentSkill}/approve', [
            StudentSkillVerificationController::class,
            'approve',
        ])->name('skills.verification.approve');

        Route::post('/kompetensi/{studentSkill}/reject', [
            StudentSkillVerificationController::class,
            'reject',
        ])->name('skills.verification.reject');
        Route::get('/lowongan', [JobPostingController::class, 'index'])
         ->name('job-postings.index');
         Route::get('/lowongan/create', [JobPostingController::class, 'create'])
         ->name('job-postings.create');
         
Route::get('/lowongan/{jobPosting}/edit', [JobPostingController::class, 'edit'])
    ->whereNumber('jobPosting')
    ->name('job-postings.edit');

Route::put('/lowongan/{jobPosting}', [JobPostingController::class, 'update'])
    ->whereNumber('jobPosting')
    ->name('job-postings.update');

         Route::post('/lowongan', [JobPostingController::class, 'store'])
         ->name('job-postings.store');
         Route::get('/lowongan/{jobPosting}', [JobPostingController::class, 'show'])
         ->whereNumber('jobPosting')
        ->name('job-postings.show');

        Route::post('/lowongan/{jobPosting}/kompetensi', [
    JobPostingController::class,
    'addSkill',
])
    ->whereNumber('jobPosting')
    ->name('job-postings.skills.store');

    Route::put('/lowongan/{jobPosting}/kompetensi/{jobSkill}', [
    JobPostingController::class,
    'updateSkill',
])
    ->whereNumber(['jobPosting', 'jobSkill'])
    ->name('job-postings.skills.update');

Route::delete('/lowongan/{jobPosting}/kompetensi/{jobSkill}', [
    JobPostingController::class,
    'deleteSkill',
])
    ->whereNumber(['jobPosting', 'jobSkill'])
    ->name('job-postings.skills.destroy');

    
Route::patch('/lowongan/{jobPosting}/status', [
    JobPostingController::class,
    'toggleStatus',
])
    ->whereNumber('jobPosting')
    ->name('job-postings.status');

    });
