<?php

namespace App\Services\Contracts;

use App\Models\Resume;

interface ResumeParserInterface
{
    /**
     * Extracts structured data (contact info, work experience, education,
     * skills, certifications, projects) from the resume's stored file and
     * writes it to $resume->parsed_data.
     *
     * Implementations MUST set parse_status to 'needs_review' rather than
     * 'completed' when confidence is low, so the UI can prompt the user to
     * confirm rather than silently trusting a bad extraction.
     */
    public function parse(Resume $resume): array;
}
