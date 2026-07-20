<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Services\Contracts\CoverLetterGeneratorInterface;

class OpenAiCoverLetterGenerator implements CoverLetterGeneratorInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You write cover letters using only facts present in the candidate's
        resume JSON. Never invent achievements, employers, or skills. Match
        the requested tone (professional, friendly, executive, or technical)
        while staying truthful and specific to the job posting.
        PROMPT;

    private const SCHEMA_HINT = '{"content": "string"}';

    public function __construct(private readonly AiClient $aiClient) {}

    public function generate(
        ResumeVersion $resumeVersion,
        JobPosting $jobPosting,
        string $tone = 'professional'
    ): CoverLetter {
        $userPrompt = json_encode([
            'tone' => $tone,
            'resume' => $resumeVersion->content,
            'job_posting' => [
                'title' => $jobPosting->job_title,
                'company' => $jobPosting->company_name,
                'raw_description' => $jobPosting->raw_description,
            ],
        ]);

        $result = $this->aiClient->completeJson(self::SYSTEM_PROMPT, $userPrompt, self::SCHEMA_HINT);

        return CoverLetter::create([
            'user_id' => $resumeVersion->resume->user_id,
            'job_posting_id' => $jobPosting->id,
            'resume_version_id' => $resumeVersion->id,
            'tone' => $tone,
            'content' => $result['content'] ?? '',
        ]);
    }
}
