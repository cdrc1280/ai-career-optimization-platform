<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = [
        'user_id', 'source_url', 'source_site', 'company_name', 'job_title',
        'raw_description', 'required_skills', 'preferred_skills', 'responsibilities',
        'qualifications', 'experience_requirement', 'industry', 'keywords',
        'technologies', 'soft_skills', 'salary_range', 'work_setup', 'extraction_status',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'preferred_skills' => 'array',
            'responsibilities' => 'array',
            'qualifications' => 'array',
            'keywords' => 'array',
            'technologies' => 'array',
            'soft_skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resumeVersions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(ResumeAnalysis::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
