<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageCounter extends Model
{
    protected $fillable = ['user_id', 'metric', 'count', 'period_start', 'period_end'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
