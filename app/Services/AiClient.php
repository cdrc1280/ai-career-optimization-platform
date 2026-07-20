<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Single choke point for every AI provider call in the app. Swapping
 * OpenAI for Anthropic/another provider later means editing this one
 * class, not every service that uses AI.
 */
class AiClient
{
    public function __construct(
        private readonly string $apiKey = '',
        private readonly string $model = 'gpt-4o',
    ) {
        $this->apiKey = $apiKey !== '' ? $apiKey : (string) config('services.openai.api_key');
    }

    /**
     * Sends a system + user prompt pair and forces a JSON object response.
     * $schemaHint is a plain-English description of the expected shape,
     * appended to the system prompt — cheaper and more portable than
     * function-calling schemas across providers, at the cost of needing to
     * validate the shape yourself (see JsonSchemaValidator, not included
     * in this scaffold).
     */
    public function completeJson(string $systemPrompt, string $userPrompt, string $schemaHint): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt."\n\nRespond ONLY with valid JSON matching this shape:\n".$schemaHint],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('AI provider request failed: '.$response->body());
        }

        $content = $response->json('choices.0.message.content');

        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('AI provider returned non-JSON content.');
        }

        return $decoded;
    }
}
