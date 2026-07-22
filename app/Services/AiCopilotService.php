<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Profile;
use App\Models\ResumeVersion;
use App\Models\JobPosting;

class AiCopilotService
{
    public function __construct(private readonly AiClient $aiClient) {}

    public function chat(ChatSession $session, string $message, ?JobPosting $jobPosting = null): string
    {
        // Save user message
        $session->messages()->create([
            'role' => 'user',
            'content' => $message
        ]);

        $user = $session->user;
        $profile = Profile::where('user_id', $user->id)->first();
        $latestResume = ResumeVersion::where('user_id', $user->id)->latest()->first();

        $systemPrompt = "You are an AI Career Copilot helping a user optimize their career. ";
        if ($profile) {
            $systemPrompt .= "Here is the user's profile: " . json_encode($profile->toArray()) . ". ";
        }
        if ($latestResume) {
            $systemPrompt .= "Here is their latest resume: " . $latestResume->content . ". ";
        }
        if ($jobPosting) {
            $systemPrompt .= "They are currently looking at this job posting: " . json_encode($jobPosting->toArray()) . ". ";
        }

        // Fetch conversation history
        $history = $session->messages()->orderBy('created_at', 'asc')->get();
        $userPrompt = "Conversation history:\n";
        foreach ($history as $msg) {
            $userPrompt .= ucfirst($msg->role) . ": " . $msg->content . "\n";
        }
        $userPrompt .= "User: " . $message;

        $schemaHint = <<<'SCHEMA'
{
  "response": "string"
}
SCHEMA;

        $result = $this->aiClient->completeJson($systemPrompt, $userPrompt, $schemaHint);
        
        $aiResponse = $result['response'] ?? 'Sorry, I could not generate a response.';

        // Save AI message
        $session->messages()->create([
            'role' => 'assistant',
            'content' => $aiResponse
        ]);

        return $aiResponse;
    }
}
