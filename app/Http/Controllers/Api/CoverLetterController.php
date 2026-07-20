<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCoverLetterRequest;
use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Services\Contracts\CoverLetterGeneratorInterface;
use App\Services\UsageLimiterService;
use Illuminate\Http\JsonResponse;

class CoverLetterController extends Controller
{
    public function __construct(
        private readonly CoverLetterGeneratorInterface $generator,
        private readonly UsageLimiterService $limiter,
    ) {}

    public function store(GenerateCoverLetterRequest $request): JsonResponse
    {
        $resumeVersion = ResumeVersion::findOrFail($request->validated('resume_version_id'));
        $jobPosting = JobPosting::findOrFail($request->validated('job_posting_id'));

        $this->authorize('view', $resumeVersion->resume);
        $this->authorize('view', $jobPosting);

        if (! $this->limiter->canUse($request->user(), 'cover_letters')) {
            return response()->json(['message' => 'Cover letter limit reached for your plan.'], 429);
        }

        $coverLetter = $this->generator->generate($resumeVersion, $jobPosting, $request->validated('tone'));
        $this->limiter->increment($request->user(), 'cover_letters');

        return response()->json($coverLetter, 201);
    }
}
