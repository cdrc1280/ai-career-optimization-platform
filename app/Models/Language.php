<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Language extends Model
{
    protected $fillable = ['profile_id', 'name', 'proficiency'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
