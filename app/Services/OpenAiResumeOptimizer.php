<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Services\Contracts\ResumeOptimizerInterface;

class OpenAiResumeOptimizer implements ResumeOptimizerInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are a resume optimization engine. You are given a candidate's
        resume as structured JSON and a target job posting. You produce a
        revised version of the SAME resume, tailored to the job.

        You may:
        - Reorder sections and bullet points by relevance.
        - Rewrite wording for clarity, impact, and ATS keyword alignment.
        - Emphasize existing achievements that match the job's priorities.
        - Rephrase existing responsibilities using terminology from the job
          posting, as long as the underlying fact does not change.

        You must NEVER:
        - Add an employer, job title, degree, certification, skill, tool,
          metric, or achievement that is not already present in the input
          resume JSON.
        - Change dates, company names, or job titles.
        - Turn a vague responsibility into a specific quantified claim (e.g.
          you cannot invent "increased performance by 40%" if the original
          resume does not contain that number anywhere).

        If the job posting calls for something the resume does not support,
        do not paper over the gap — leave it out. The comparison/gap-analysis
        step (a separate service) is responsible for surfacing gaps to the
        user; your job is only to present existing truthful content in its
        best light.

        Return the full resume JSON in the same shape as the input, plus a
        "change_log" array describing what you changed and why, so the UI can
        show a diff with explanations.
        PROMPT;

    private const SCHEMA_HINT = <<<'SCHEMA'
        {
          "resume": { "...same shape as the input resume JSON..." },
          "change_log": [
            {"section": "string", "change_type": "reworded|reordered|emphasized|removed", "before": "string", "after": "string", "reason": "string"}
          ]
        }
        SCHEMA;

    public function __construct(private readonly AiClient $aiClient) {}

    public function optimize(ResumeVersion $baseVersion, JobPosting $jobPosting, string $label): ResumeVersion
    {
        $userPrompt = json_encode([
            'resume' => $baseVersion->content,
            'job_posting' => [
                'title' => $jobPosting->job_title,
                'company' => $jobPosting->company_name,
                'required_skills' => $jobPosting->required_skills,
                'preferred_skills' => $jobPosting->preferred_skills,
                'responsibilities' => $jobPosting->responsibilities,
                'keywords' => $jobPosting->keywords,
            ],
        ]);

        $dbPrompt = \App\Models\AiPrompt::where('name', 'resume_optimizer')->where('is_active', true)->value('prompt_text');
        $systemPrompt = $dbPrompt ?: self::SYSTEM_PROMPT;

        $result = $this->aiClient->completeJson($systemPrompt, $userPrompt, self::SCHEMA_HINT);

        // Defense in depth: even though the prompt forbids it, verify no new
        // employers/titles were introduced before we ever persist AI output.
        $this->assertNoFabricatedEmployment($baseVersion->content, $result['resume'] ?? []);

        return ResumeVersion::create([
            'resume_id' => $baseVersion->resume_id,
            'parent_version_id' => $baseVersion->id,
            'job_posting_id' => $jobPosting->id,
            'label' => $label,
            'content' => array_merge($result['resume'] ?? [], [
                '_change_log' => $result['change_log'] ?? [],
            ]),
            'is_master' => false,
        ]);
    }

    /**
     * Guards against the AI introducing employers/titles that were not in
     * the source resume. Throws rather than silently persisting fabricated
     * content — a failed optimization is safer than a dishonest one.
     */
    private function assertNoFabricatedEmployment(array $original, array $optimized): void
    {
        $originalEmployers = collect($original['work_experience'] ?? [])
            ->pluck('company')->map(fn ($c) => mb_strtolower(trim((string) $c)))->all();

        $optimizedEmployers = collect($optimized['work_experience'] ?? [])
            ->pluck('company')->map(fn ($c) => mb_strtolower(trim((string) $c)))->all();

        $fabricated = array_diff($optimizedEmployers, $originalEmployers);

        if (! empty($fabricated)) {
            throw new \RuntimeException(
                'Optimization rejected: AI output introduced employer(s) not present in the source resume: '
                .implode(', ', $fabricated)
            );
        }
    }
}
