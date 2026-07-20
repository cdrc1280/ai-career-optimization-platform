<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeResumeJob;
use App\Models\JobPosting;
use App\Models\ResumeAnalysis;
use App\Models\ResumeVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeAnalysisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $analyses = ResumeAnalysis::whereHas('resumeVersion.resume', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
            ->with(['resumeVersion', 'jobPosting'])
            ->latest()
            ->paginate(20);

        return response()->json($analyses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resume_version_id' => ['required', 'exists:resume_versions,id'],
            'job_posting_id'    => ['required', 'exists:job_postings,id'],
        ]);

        $resumeVersion = ResumeVersion::findOrFail($validated['resume_version_id']);
        $jobPosting    = JobPosting::findOrFail($validated['job_posting_id']);

        $this->authorize('view', $jobPosting);
        $this->authorize('view', $resumeVersion->resume);

        AnalyzeResumeJob::dispatch($resumeVersion, $jobPosting);

        return response()->json(['message' => 'Analysis queued.'], 202);
    }

    public function show(int $resumeVersionId, int $jobPostingId): JsonResponse
    {
        $cacheKey = 'analysis:' . $resumeVersionId . ':' . $jobPostingId;
        $analysis = Cache::remember($cacheKey, 3600, function () use ($resumeVersionId, $jobPostingId) {
            return ResumeAnalysis::where('resume_version_id', $resumeVersionId)
                ->where('job_posting_id', $jobPostingId)
                ->latest()
                ->firstOrFail();
        });

        $this->authorize('view', $analysis->resumeVersion->resume);

        return response()->json($analysis);
    }
}
