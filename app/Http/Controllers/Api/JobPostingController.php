<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostingRequest;
use App\Jobs\ExtractJobPostingJob;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->jobPostings()->latest()->paginate(20)
        );
    }

    public function store(StoreJobPostingRequest $request): JsonResponse
    {
        $jobPosting = JobPosting::create([
            'user_id' => $request->user()->id,
            'source_url' => $request->input('source_url'),
            'source_site' => $request->input('source_site', $request->filled('source_url') ? null : 'manual'),
            'raw_description' => $request->input('raw_description', ''),
            'extraction_status' => 'pending',
        ]);

        ExtractJobPostingJob::dispatch($jobPosting);

        return response()->json($jobPosting, 201);
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('view', $jobPosting);

        return response()->json($jobPosting);
    }

    public function destroy(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('delete', $jobPosting);
        $jobPosting->delete();

        return response()->json(status: 204);
    }
}
