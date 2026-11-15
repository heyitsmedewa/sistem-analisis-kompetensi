<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_number',
        'study_program',
        'institution',
        'education_level',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class);
    }

    public function studentSkills(): HasMany
    {
        return $this->hasMany(StudentSkill::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(StudentEducation::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(StudentExperience::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(StudentCertification::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(StudentProject::class);
    }

    public function matchingResults(): HasMany
    {
        return $this->hasMany(MatchingResult::class);
    }
}