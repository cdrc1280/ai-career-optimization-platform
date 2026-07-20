<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateCoverLetterRequest;
use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Notifications\CoverLetterGenerated;
use App\Services\Contracts\CoverLetterGeneratorInterface;
use App\Services\UsageLimiterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoverLetterController extends Controller
{
    public function __construct(
        private readonly CoverLetterGeneratorInterface $generator,
        private readonly UsageLimiterService $limiter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            CoverLetter::where('user_id', $request->user()->id)
                ->with(['jobPosting', 'resumeVersion'])
                ->latest()
                ->paginate(20)
        );
    }

    public function store(GenerateCoverLetterRequest $request): JsonResponse
    {
        $resumeVersion = ResumeVersion::findOrFail($request->validated('resume_version_id'));
        $jobPosting    = JobPosting::findOrFail($request->validated('job_posting_id'));

        $this->authorize('view', $resumeVersion->resume);
        $this->authorize('view', $jobPosting);

        if (! $this->limiter->canUse($request->user(), 'cover_letters')) {
            return response()->json(['message' => 'Cover letter limit reached for your plan.'], 429);
        }

        $coverLetter = $this->generator->generate($resumeVersion, $jobPosting, $request->validated('tone'));
        $this->limiter->increment($request->user(), 'cover_letters');
        
        $request->user()->notify(new CoverLetterGenerated($coverLetter));

        return response()->json($coverLetter->load(['jobPosting', 'resumeVersion']), 201);
    }

    public function update(Request $request, CoverLetter $coverLetter): JsonResponse {
        $this->authorize('update', $coverLetter);
        $validated = $request->validate(['content' => ['required', 'string']]);
        $coverLetter->update($validated);
        return response()->json($coverLetter);
    }
}
