<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Services\AiCopilotService;

class CopilotController extends Controller
{
    public function __construct(private readonly AiCopilotService $copilotService) {}

    public function index(Request $request)
    {
        $sessions = ChatSession::where('user_id', $request->user()->id)->latest()->get();
        return response()->json($sessions);
    }

    public function show(ChatSession $session, Request $request)
    {
        if ($session->user_id !== $request->user()->id) {
            abort(403);
        }
        return response()->json($session->messages()->orderBy('created_at', 'asc')->get());
    }

    public function chat(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|exists:chat_sessions,id',
            'message' => 'required|string',
            'job_posting_id' => 'nullable|exists:job_postings,id',
        ]);

        if ($request->session_id) {
            $session = ChatSession::where('id', $request->session_id)->where('user_id', $request->user()->id)->firstOrFail();
        } else {
            $session = ChatSession::create([
                'user_id' => $request->user()->id,
                'title' => substr($request->message, 0, 50)
            ]);
        }

        $jobPosting = $request->job_posting_id ? \App\Models\JobPosting::find($request->job_posting_id) : null;

        $response = $this->copilotService->chat($session, $request->message, $jobPosting);

        return response()->json([
            'session' => $session,
            'response' => $response
        ]);
    }
}

