<?php

namespace App\Services\Contracts;

use App\Models\JobPosting;
use App\Models\ResumeAnalysis;
use App\Models\ResumeVersion;

interface ResumeAnalyzerInterface
{
    /**
     * Compares a resume version against a job posting and produces a
     * ResumeAnalysis: match scores, matching/missing skills, keyword
     * coverage, and ATS issues — each with a human-readable explanation.
     *
     * This is a read-only comparison. It never modifies the resume; that is
     * ResumeOptimizerInterface's job.
     */
    public function analyze(ResumeVersion $resumeVersion, JobPosting $jobPosting): ResumeAnalysis;
}
