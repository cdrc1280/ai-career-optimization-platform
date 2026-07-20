<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\ResumeAnalysis;
use App\Models\ResumeVersion;
use App\Services\Contracts\ResumeAnalyzerInterface;

class OpenAiResumeAnalyzer implements ResumeAnalyzerInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You are a resume-to-job-posting analysis engine. You compare a
        candidate's resume against a target job posting and score the match.

        Rules:
        - Only use information present in the resume JSON you are given. Never
          assume skills, experience, or qualifications the candidate did not
          state.
        - Every score from 0-100 must be justified by a short explanation
          referencing specific resume content or its absence.
        - "missing_skills" are skills the job requires that do not appear
          anywhere in the resume, verbatim or as a clear synonym.
        - Be conservative: if the resume is ambiguous about whether a skill
          is present, treat it as a partial match and explain the ambiguity
          rather than guessing.
        - For skill_gap_details, provide specific learning resources (real URLs).
        - For interview_prep, base suggested answers only on facts in the resume.
        PROMPT;

    private const SCHEMA_HINT = <<<'SCHEMA'
        {
          "overall_match_score": 0-100,
          "skills_match_score": 0-100,
          "experience_match_score": 0-100,
          "education_match_score": 0-100,
          "ats_compatibility_score": 0-100,
          "keyword_coverage_score": 0-100,
          "industry_alignment_score": 0-100,
          "matching_skills": ["string"],
          "missing_skills": ["string"],
          "present_keywords": ["string"],
          "missing_keywords": ["string"],
          "recommended_keywords": ["string"],
          "ats_issues": ["string"],
          "score_explanations": {"overall": "string", "skills": "string", "experience": "string", "education": "string", "ats": "string", "keywords": "string", "industry": "string"},
          "skill_gap_details": [{"skill": "string", "why_it_matters": "string", "where_in_jd": "string", "learning_resources": ["string"]}],
          "career_recommendations": {"certifications": [{"name": "string", "provider": "string", "relevance": "string"}], "courses": [{"title": "string", "platform": "string", "url": "string"}], "career_paths": ["string"], "portfolio_projects": ["string"]},
          "interview_prep": {"behavioral": [{"question": "string", "suggested_answer": "string"}], "technical": [{"question": "string", "suggested_answer": "string"}], "company_specific": [{"question": "string", "suggested_answer": "string"}], "role_specific": [{"question": "string", "suggested_answer": "string"}]}
        }
        SCHEMA;

    public function __construct(private readonly AiClient $aiClient) {}

    public function analyze(ResumeVersion $resumeVersion, JobPosting $jobPosting): ResumeAnalysis
    {
        $analysis = ResumeAnalysis::create([
            'resume_version_id' => $resumeVersion->id,
            'job_posting_id'    => $jobPosting->id,
            'status'            => 'processing',
        ]);

        $userPrompt = json_encode([
            'resume'      => $resumeVersion->content,
            'job_posting' => [
                'title'                  => $jobPosting->job_title,
                'company'                => $jobPosting->company_name,
                'required_skills'        => $jobPosting->required_skills,
                'preferred_skills'       => $jobPosting->preferred_skills,
                'responsibilities'       => $jobPosting->responsibilities,
                'qualifications'         => $jobPosting->qualifications,
                'keywords'               => $jobPosting->keywords,
                'raw_description'        => $jobPosting->raw_description,
            ],
        ]);

        try {
            $result = $this->aiClient->completeJson(self::SYSTEM_PROMPT, $userPrompt, self::SCHEMA_HINT);

            $analysis->update([
                'overall_match_score'      => $result['overall_match_score'] ?? null,
                'skills_match_score'       => $result['skills_match_score'] ?? null,
                'experience_match_score'   => $result['experience_match_score'] ?? null,
                'education_match_score'    => $result['education_match_score'] ?? null,
                'ats_compatibility_score'  => $result['ats_compatibility_score'] ?? null,
                'keyword_coverage_score'   => $result['keyword_coverage_score'] ?? null,
                'industry_alignment_score' => $result['industry_alignment_score'] ?? null,
                'matching_skills'          => $result['matching_skills'] ?? [],
                'missing_skills'           => $result['missing_skills'] ?? [],
                'present_keywords'         => $result['present_keywords'] ?? [],
                'missing_keywords'         => $result['missing_keywords'] ?? [],
                'recommended_keywords'     => $result['recommended_keywords'] ?? [],
                'ats_issues'               => $result['ats_issues'] ?? [],
                'score_explanations'       => $result['score_explanations'] ?? [],
                'skill_gap_details'        => $result['skill_gap_details'] ?? [],
                'career_recommendations'   => $result['career_recommendations'] ?? [],
                'interview_prep'           => $result['interview_prep'] ?? [],
                'status'                   => 'completed',
            ]);
        } catch (\Throwable $e) {
            $analysis->update(['status' => 'failed']);
            report($e);
            throw $e;
        }

        return $analysis;
    }
}
