<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApplicationRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->applications()
                ->with(['jobPosting', 'resumeVersion', 'coverLetter'])
                ->latest()
                ->paginate(20)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_posting_id' => ['nullable', 'exists:job_postings,id'],
            'company_name' => ['required_without:job_posting_id', 'string', 'max:255'],
            'job_title' => ['required_without:job_posting_id', 'string', 'max:255'],
            'job_url' => ['nullable', 'url', 'max:2000'],
            'resume_version_id' => ['nullable', 'exists:resume_versions,id'],
            'cover_letter_id' => ['nullable', 'exists:cover_letters,id'],
            'status' => ['sometimes', 'string'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $jobPostingId = $validated['job_posting_id'] ?? null;

        if (!$jobPostingId && !empty($validated['company_name'])) {
            $job = $request->user()->jobPostings()->create([
                'company_name' => $validated['company_name'],
                'job_title' => $validated['job_title'],
                'source_url' => $validated['job_url'] ?? null,
                'source_type' => 'manual',
                'raw_description' => 'Manually added via Applications Tracker.',
                'extraction_status' => 'completed',
            ]);
            $jobPostingId = $job->id;
        }

        $application = $request->user()->applications()->create([
            'job_posting_id' => $jobPostingId,
            'resume_version_id' => $validated['resume_version_id'] ?? null,
            'cover_letter_id' => $validated['cover_letter_id'] ?? null,
            'status' => $validated['status'] ?? 'saved',
            'notes' => $validated['notes'] ?? null,
        ]);

        $application->statusHistory()->create([
            'from_status' => null,
            'to_status' => $application->status->value ?? 'saved',
            'changed_at' => now(),
        ]);

        return response()->json($application->load('jobPosting'), 201);
    }

    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        $previousStatus = $application->status->value;
        $application->update($request->validated());

        if ($request->has('status') && $request->input('status') !== $previousStatus) {
            $application->statusHistory()->create([
                'from_status' => $previousStatus,
                'to_status' => $application->status->value,
                'changed_at' => now(),
            ]);
        }

        return response()->json($application);
    }

    public function destroy(Application $application): JsonResponse
    {
        $this->authorize('delete', $application);
        $application->delete();

        return response()->json(status: 204);
    }
}
