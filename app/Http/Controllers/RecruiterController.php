<?php

namespace App\Http\Controllers;

use App\Services\AiClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecruiterController extends Controller
{
    public function candidates(): JsonResponse
    {
        return response()->json([
            'candidates' => [
                [
                    'id' => 1,
                    'name' => 'Alice Smith',
                    'mock_candidate_score' => 95,
                    'matching_skills' => ['PHP', 'Laravel', 'Vue.js', 'MySQL'],
                    'experience_level' => 'Senior',
                    'ats_rank' => 'A',
                    'status' => 'Screening'
                ],
                [
                    'id' => 2,
                    'name' => 'Bob Jones',
                    'mock_candidate_score' => 82,
                    'matching_skills' => ['PHP', 'Laravel'],
                    'experience_level' => 'Mid-Level',
                    'ats_rank' => 'B',
                    'status' => 'Under Review'
                ]
            ]
        ]);
    }

    public function bulkScreen(Request $request, AiClient $ai): JsonResponse
    {
        $request->validate([
            'candidates' => 'required|array',
        ]);

        $result = $ai->completeJson(
            'You are an AI recruiter. Bulk screen these candidates.',
            json_encode($request->input('candidates')),
            '{"candidates":[{"name":"string","rank":"number","match_score":"number","breakdown":{"skills":"string","experience":"string"}}],"summary":"string"}'
        );

        return response()->json($result);
    }

    public function addNote(Request $request): JsonResponse
    {
        $request->validate([
            'candidate_id' => 'required',
            'note' => 'required|string',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Note added successfully',
            'note' => $request->input('note')
        ]);
    }
}
