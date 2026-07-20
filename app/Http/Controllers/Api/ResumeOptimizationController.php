<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptimizeResumeRequest;
use App\Jobs\OptimizeResumeJob;
use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Services\UsageLimiterService;
use Illuminate\Http\JsonResponse;

class ResumeOptimizationController extends Controller
{
    public function __construct(private readonly UsageLimiterService $limiter) {}

    public function store(OptimizeResumeRequest $request): JsonResponse
    {
        $baseVersion = ResumeVersion::findOrFail($request->validated('resume_version_id'));
        $jobPosting = JobPosting::findOrFail($request->validated('job_posting_id'));

        $this->authorize('view', $baseVersion->resume);
        $this->authorize('view', $jobPosting);

        if (! $this->limiter->canUse($request->user(), 'resume_optimizations')) {
            return response()->json([
                'message' => 'You have reached your plan\'s resume optimization limit for this period.',
            ], 429);
        }

        OptimizeResumeJob::dispatch($baseVersion, $jobPosting, $request->validated('label'), $request->user());

        return response()->json(['message' => 'Optimization queued.'], 202);
    }

    public function compare(ResumeVersion $resumeVersion)
    {
        $this->authorize('view', $resumeVersion->resume);

        $parent = $resumeVersion->parentVersion;

        return response()->json([
            'base' => $parent?->content,
            'optimized' => $resumeVersion->content,
            'change_log' => $resumeVersion->content['_change_log'] ?? [],
        ]);
    }
}
