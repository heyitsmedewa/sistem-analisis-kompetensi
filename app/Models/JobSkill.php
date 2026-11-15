<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkill extends Model
{
    protected $fillable = [
        'job_posting_id',
        'skill_id',
        'required_level',
        'is_core_factor',
        'notes',
    ];

    protected $casts = [
        'required_level' => 'integer',
        'is_core_factor' => 'boolean',
    ];

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}