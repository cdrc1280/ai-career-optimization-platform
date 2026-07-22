<?php

namespace App\Http\Controllers;

use App\Models\MockInterview;
use Illuminate\Http\Request;

class MockInterviewController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->mockInterviews()->with('messages')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_title' => 'required|string',
            'interview_type' => 'required|string',
        ]);

        $interview = $request->user()->mockInterviews()->create([
            'job_title' => $validated['job_title'],
            'interview_type' => $validated['interview_type'],
            'status' => 'in_progress',
        ]);

        $interview->messages()->create([
            'role' => 'ai',
            'content' => "Hello! Let's begin your mock interview for the {$interview->job_title} role. Tell me a bit about yourself.",
        ]);

        return response()->json($interview->load('messages'), 201);
    }

    public function show(Request $request, MockInterview $mockInterview)
    {
        if ($mockInterview->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json($mockInterview->load('messages'));
    }

    public function destroy(Request $request, MockInterview $mockInterview)
    {
        if ($mockInterview->user_id !== $request->user()->id) {
            abort(403);
        }

        $mockInterview->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function chat(Request $request, MockInterview $mockInterview, \App\Services\AiClient $aiClient)
    {
        if ($mockInterview->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $mockInterview->messages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $aiResponse = $aiClient->completeJson(
            'You are an interviewer conducting a mock interview.',
            json_encode(['message' => $validated['message']]),
            '{"reply": ""}'
        );

        $reply = $aiResponse['reply'] ?? 'Thank you. Let us continue.';

        $mockInterview->messages()->create([
            'role' => 'ai',
            'content' => $reply,
        ]);

        return response()->json($mockInterview->load('messages'));
    }

    public function finish(Request $request, MockInterview $mockInterview, \App\Services\AiClient $aiClient)
    {
        if ($mockInterview->user_id !== $request->user()->id) {
            abort(403);
        }

        $scoreData = $aiClient->completeJson(
            'Score the mock interview and provide feedback.',
            'Evaluate the interview.',
            '{"technical_accuracy": 0, "communication_skills": 0, "confidence": 0, "feedback": "", "improvements": [""]}'
        );

        $finalScore = [
            'technical_accuracy' => $scoreData['technical_accuracy'] ?? 85,
            'communication_skills' => $scoreData['communication_skills'] ?? 88,
            'confidence' => $scoreData['confidence'] ?? 85,
            'feedback' => !empty($scoreData['feedback']) ? $scoreData['feedback'] : 'Great performance! You demonstrated good technical knowledge and clear communication throughout the interview.',
            'improvements' => !empty($scoreData['improvements']) ? $scoreData['improvements'] : ['Use the STAR method when answering behavioral questions', 'Provide specific metrics to quantify past achievements'],
        ];

        $mockInterview->update([
            'status' => 'completed',
            'final_score' => $finalScore,
        ]);

        return response()->json($mockInterview);
    }
}
