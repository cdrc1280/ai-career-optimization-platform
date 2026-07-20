<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResumeRequest;
use App\Jobs\ParseResumeJob;
use App\Models\Resume;
use App\Models\ResumeVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->resumes()->with('masterVersion')->latest()->paginate(20)
        );
    }

    public function store(StoreResumeRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store('resumes/'.$request->user()->id, 'private');

        $resume = DB::transaction(function () use ($request, $file, $path) {
            $resume = Resume::create([
                'user_id' => $request->user()->id,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'parse_status' => 'pending',
            ]);

            // The master version is a placeholder until parsing completes and
            // populates its content — created eagerly so every resume always
            // has an is_master=true version to branch from.
            ResumeVersion::create([
                'resume_id' => $resume->id,
                'label' => 'Master Resume',
                'content' => [],
                'is_master' => true,
            ]);

            return $resume;
        });

        ParseResumeJob::dispatch($resume);

        return response()->json($resume, 201);
    }

    public function show(Resume $resume): JsonResponse
    {
        $this->authorize('view', $resume);

        return response()->json($resume->load('versions'));
    }

    public function destroy(Resume $resume): JsonResponse
    {
        $this->authorize('delete', $resume);

        Storage::disk('private')->delete($resume->file_path);
        $resume->delete();

        return response()->json(status: 204);
    }
}
