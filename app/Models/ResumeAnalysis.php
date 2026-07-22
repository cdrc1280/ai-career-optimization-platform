<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnalysis extends Model
{
    protected $fillable = [
        'resume_version_id', 'job_posting_id', 'overall_match_score', 'skills_match_score',
        'experience_match_score', 'education_match_score', 'ats_compatibility_score',
        'keyword_coverage_score', 'industry_alignment_score', 'matching_skills', 'missing_skills',
        'present_keywords', 'missing_keywords', 'recommended_keywords', 'ats_issues',
        'score_explanations', 'skill_gap_details', 'career_recommendations', 'interview_prep', 'recruiter_review', 'integrity_flags', 'status',
    ];

    protected function casts(): array
    {
        return [
            'matching_skills'        => 'array',
            'missing_skills'         => 'array',
            'present_keywords'       => 'array',
            'missing_keywords'       => 'array',
            'recommended_keywords'   => 'array',
            'ats_issues'             => 'array',
            'score_explanations'     => 'array',
            'skill_gap_details'      => 'array',
            'career_recommendations' => 'array',
            'interview_prep'         => 'array',
            'recruiter_review'       => 'array',
            'integrity_flags'        => 'array',
        ];
    }

    public function resumeVersion(): BelongsTo
    {
        return $this->belongsTo(ResumeVersion::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }
}
