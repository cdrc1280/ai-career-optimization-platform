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
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'resume_version_id' => ['nullable', 'exists:resume_versions,id'],
            'cover_letter_id' => ['nullable', 'exists:cover_letters,id'],
            'status' => ['sometimes', 'string'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $application = $request->user()->applications()->create($validated);

        $application->statusHistory()->create([
            'from_status' => null,
            'to_status' => $application->status->value,
            'changed_at' => now(),
        ]);

        return response()->json($application, 201);
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
