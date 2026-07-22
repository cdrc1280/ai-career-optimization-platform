<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalBrand extends Model
{
    protected $guarded = [];

    protected $casts = [
        'linkedin_posts' => 'array',
        'portfolio_copy' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
