<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'job_posting_id', 'resume_version_id', 'cover_letter_id',
        'status', 'applied_at', 'notes', 'job_url', 'match_score', 'interview_date', 'follow_up_date'
    ];

    protected function casts(): array
    {
        return ['status' => ApplicationStatus::class, 'applied_at' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function resumeVersion(): BelongsTo
    {
        return $this->belongsTo(ResumeVersion::class);
    }

    public function coverLetter(): BelongsTo
    {
        return $this->belongsTo(CoverLetter::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }
}
