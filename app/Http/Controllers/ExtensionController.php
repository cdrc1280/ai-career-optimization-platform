<?php

namespace App\Http\Controllers;

use App\Services\AiClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ExtensionController extends Controller
{
    public function token(Request $request): JsonResponse
    {
        $token = Str::random(60);
        return response()->json([
            'token' => $token,
            'expires_in' => 3600
        ]);
    }

    public function analyze(Request $request, AiClient $ai): JsonResponse
    {
        $request->validate([
            'job_posting' => 'required|string'
        ]);

        $result = $ai->completeJson(
            'You are an AI career assistant. Analyze this job posting for a browser extension.',
            $request->input('job_posting'),
            '{"match_score":"number","keywords":["string"],"summary":"string"}'
        );

        return response()->json($result);
    }
}
