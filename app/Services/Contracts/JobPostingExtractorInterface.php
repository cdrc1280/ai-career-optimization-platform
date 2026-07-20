<?php

namespace App\Services\Contracts;

use App\Models\JobPosting;

interface JobPostingExtractorInterface
{
    /**
     * Given a JobPosting with either a source_url or raw_description already
     * set, fills in the structured fields (required/preferred skills,
     * responsibilities, keywords, etc). If source_url is set, implementations
     * are responsible for fetching the page content first.
     */
    public function extract(JobPosting $jobPosting): JobPosting;
}
