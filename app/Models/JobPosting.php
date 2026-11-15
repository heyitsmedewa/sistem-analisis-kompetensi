<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'title',
        'company',
        'description',
        'education_requirement',
        'experience_requirement',
        'location',
        'source_name',
        'source_url',
        'collected_at',
        'is_active',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function jobSkills(): HasMany
    {
        return $this->hasMany(JobSkill::class);
    }

    public function matchingResults(): HasMany
    {
        return $this->hasMany(MatchingResult::class);
    }
}