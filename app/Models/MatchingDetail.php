<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchingDetail extends Model
{
    protected $fillable = [
        'matching_result_id',
        'skill_id',
        'actual_level',
        'required_level',
        'gap',
        'gap_weight',
        'factor_type',
        'evaluation_status',
    ];

    protected $casts = [
        'actual_level' => 'integer',
        'required_level' => 'integer',
        'gap' => 'integer',
        'gap_weight' => 'decimal:3',
    ];

    public function matchingResult(): BelongsTo
    {
        return $this->belongsTo(MatchingResult::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}