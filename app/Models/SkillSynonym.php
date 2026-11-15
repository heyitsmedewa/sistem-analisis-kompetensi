<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillSynonym extends Model
{
    protected $fillable = [
        'skill_id',
        'synonym',
        'approval_status',
    ];

    protected $attributes = [
        'approval_status' => 'pending',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
