<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'profile_id', 'institution', 'degree', 'field_of_study',
        'start_date', 'end_date', 'is_current', 'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_current' => 'boolean'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
