<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    protected $fillable = [
        'matching_result_id',
        'recommendation_type',
        'content',
        'model_name',
        'status',
        'error_message',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function matchingResult(): BelongsTo
    {
        return $this->belongsTo(MatchingResult::class);
    }
}