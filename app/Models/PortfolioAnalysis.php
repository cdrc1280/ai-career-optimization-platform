<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioAnalysis extends Model
{
    protected $guarded = [];

    protected $casts = [
        'score_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
