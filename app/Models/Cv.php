<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cv extends Model
{
    protected $fillable = [
        'student_profile_id',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'extraction_status',
        'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function extractions(): HasMany
    {
        return $this->hasMany(CvExtraction::class);
    }
}