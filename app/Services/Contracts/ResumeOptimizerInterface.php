<?php

namespace App\Services\Contracts;

use App\Models\JobPosting;
use App\Models\ResumeVersion;

interface ResumeOptimizerInterface
{
    /**
     * Produces a new ResumeVersion tailored to $jobPosting, derived from
     * $baseVersion. Implementations may reorder, rewrite, and re-prioritize
     * content, but every fact in the output MUST trace back to something
     * present in the base version's content — no invented employers, titles,
     * dates, skills, or achievements. See docs/ai-guidelines.md.
     */
    public function optimize(ResumeVersion $baseVersion, JobPosting $jobPosting, string $label): ResumeVersion;
}
