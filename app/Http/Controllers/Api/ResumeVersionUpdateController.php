<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeVersionUpdateController extends Controller
{
    public function update(Request $request, ResumeVersion $resumeVersion): JsonResponse
    {
        $this->authorize('view', $resumeVersion->resume);

        $validated = $request->validate([
            'label'   => ['sometimes', 'string', 'max:191'],
            'content' => ['sometimes', 'array'],
        ]);

        $resumeVersion->update($validated);

        return response()->json($resumeVersion->fresh());
    }
}
