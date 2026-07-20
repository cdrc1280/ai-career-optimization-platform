<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverLetter extends Model
{
    protected $fillable = [
        'user_id', 'job_posting_id', 'resume_version_id', 'tone', 'content',
        'exported_pdf_path', 'exported_docx_path',
    ];

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
}
