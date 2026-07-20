<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Services\Contracts\JobPostingExtractorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiJobPostingExtractor implements JobPostingExtractorInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You extract structured job posting data from raw text (which may be
        HTML-stripped page content or a manually pasted job description).
        If a field is not present in the text, return an empty value for it
        rather than guessing.
        PROMPT;

    private const SCHEMA_HINT = <<<'SCHEMA'
        {
          "company_name": "string|null",
          "job_title": "string|null",
          "required_skills": ["string"],
          "preferred_skills": ["string"],
          "responsibilities": ["string"],
          "qualifications": ["string"],
          "experience_requirement": "string|null",
          "industry": "string|null",
          "keywords": ["string"],
          "technologies": ["string"],
          "soft_skills": ["string"],
          "salary_range": "string|null",
          "work_setup": "remote|hybrid|onsite|null"
        }
        SCHEMA;

    public function __construct(private readonly AiClient $aiClient) {}

    public function extract(JobPosting $jobPosting): JobPosting
    {
        $jobPosting->update(['extraction_status' => 'processing']);

        try {
            $rawText = $jobPosting->raw_description;

            if (blank($rawText) && filled($jobPosting->source_url)) {
                $rawText = $this->fetchAndStripHtml($jobPosting->source_url);
                $jobPosting->raw_description = $rawText;
            }

            $result = $this->aiClient->completeJson(self::SYSTEM_PROMPT, $rawText, self::SCHEMA_HINT);

            $jobPosting->update([
                'company_name' => $result['company_name'] ?? null,
                'job_title' => $result['job_title'] ?? null,
                'required_skills' => $result['required_skills'] ?? [],
                'preferred_skills' => $result['preferred_skills'] ?? [],
                'responsibilities' => $result['responsibilities'] ?? [],
                'qualifications' => $result['qualifications'] ?? [],
                'experience_requirement' => $result['experience_requirement'] ?? null,
                'industry' => $result['industry'] ?? null,
                'keywords' => $result['keywords'] ?? [],
                'technologies' => $result['technologies'] ?? [],
                'soft_skills' => $result['soft_skills'] ?? [],
                'salary_range' => $result['salary_range'] ?? null,
                'work_setup' => $result['work_setup'] ?? null,
                'extraction_status' => 'completed',
            ]);
        } catch (\Throwable $e) {
            $jobPosting->update(['extraction_status' => 'failed']);
            report($e);
            throw $e;
        }

        return $jobPosting->fresh();
    }

    private function fetchAndStripHtml(string $url): string
    {
        $response = Http::timeout(30)
            ->withUserAgent('Mozilla/5.0 (compatible; CareerPlatformBot/1.0)')
            ->get($url);

        $html = $response->body();

        // Naive strip for the scaffold. Production should use a proper
        // readability extractor (e.g. a DOM parser + boilerplate remover)
        // since job sites vary wildly in markup and many require
        // site-specific scraping rules (LinkedIn/Indeed/Workday auth walls).
        $text = strip_tags(preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $html));

        return Str::limit(trim(preg_replace('/\s+/', ' ', $text)), 12000, '');
    }
}
