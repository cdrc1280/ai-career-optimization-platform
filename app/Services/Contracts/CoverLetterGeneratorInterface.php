<?php

namespace App\Services\Contracts;

use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\ResumeVersion;

interface CoverLetterGeneratorInterface
{
    public function generate(
        ResumeVersion $resumeVersion,
        JobPosting $jobPosting,
        string $tone = 'professional'
    ): CoverLetter;
}
