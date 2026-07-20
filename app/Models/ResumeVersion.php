<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_id', 'parent_version_id', 'job_posting_id', 'label', 'content',
        'exported_pdf_path', 'exported_docx_path', 'is_master',
    ];

    protected function casts(): array
    {
        return ['content' => 'array', 'is_master' => 'boolean'];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(ResumeVersion::class, 'parent_version_id');
    }

    public function childVersions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class, 'parent_version_id');
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }
}
