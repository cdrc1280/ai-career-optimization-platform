<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'mock_interview_id',
        'role',
        'content',
    ];

    public function mockInterview()
    {
        return $this->belongsTo(MockInterview::class);
    }
}
