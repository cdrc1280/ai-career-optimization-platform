<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockInterview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title',
        'interview_type',
        'status',
        'final_score',
    ];

    protected $casts = [
        'final_score' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(InterviewMessage::class);
    }
}
