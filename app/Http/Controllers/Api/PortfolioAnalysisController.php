<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioAnalysis;
use App\Services\AiClient;
use Illuminate\Http\Request;

class PortfolioAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $analyses = PortfolioAnalysis::where('user_id', $request->user()->id)->latest()->get();
        return response()->json($analyses);
    }

    public function store(Request $request, AiClient $aiClient)
    {
        $request->validate([
            'portfolio_url' => 'nullable|url',
            'github_username' => 'nullable|string'
        ]);

        $user = $request->user();
        
        $systemPrompt = "You are an expert technical recruiter and open-source contributor. Evaluate the provided portfolio URL and/or GitHub username for a software engineer. Provide scores and feedback.";
        $userPrompt = json_encode($request->only('portfolio_url', 'github_username'));
        
        $schemaHint = '{"overall_score": "number", "project_quality": "number", "repo_structure": "number", "readme_quality": "number", "suggestions": ["string"], "strengths": ["string"]}';
        
        $scoreData = $aiClient->completeJson($systemPrompt, $userPrompt, $schemaHint);
        
        $analysis = PortfolioAnalysis::create([
            'user_id' => $user->id,
            'portfolio_url' => $request->portfolio_url,
            'github_username' => $request->github_username,
            'score_data' => $scoreData
        ]);
        
        return response()->json($analysis);
    }
}
