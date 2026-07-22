<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title',
        'company_name',
        'offer_details',
        'evaluation_data',
    ];

    protected $casts = [
        'evaluation_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
