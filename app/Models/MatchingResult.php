<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchingResult extends Model
{
    protected $fillable = [
        'student_profile_id',
        'job_posting_id',
        'final_score',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'final_score' => 'decimal:4',
        'calculated_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(MatchingDetail::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }
}