<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvExtraction extends Model
{
    protected $fillable = [
        'cv_id',
        'raw_response',
        'confirmed_data',
        'status',
        'model_name',
        'error_message',
        'started_at',
        'completed_at',
        'confirmed_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'confirmed_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}