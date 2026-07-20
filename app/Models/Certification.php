<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    protected $fillable = [
        'profile_id', 'name', 'issuing_organization', 'issued_date', 'expiry_date', 'credential_url',
    ];

    protected function casts(): array
    {
        return ['issued_date' => 'date', 'expiry_date' => 'date'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
