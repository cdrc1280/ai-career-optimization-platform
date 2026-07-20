<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperience extends Model
{
    protected $fillable = [
        'profile_id', 'company', 'title', 'location', 'start_date', 'end_date',
        'is_current', 'responsibilities', 'achievements', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'responsibilities' => 'array',
            'achievements' => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
