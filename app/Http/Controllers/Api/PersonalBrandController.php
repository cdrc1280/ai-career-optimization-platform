<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonalBrand;
use App\Services\AiClient;
use Illuminate\Http\Request;

class PersonalBrandController extends Controller
{
    public function show(Request $request)
    {
        $brand = PersonalBrand::where('user_id', $request->user()->id)->first();
        return response()->json($brand);
    }

    public function generate(Request $request, AiClient $aiClient)
    {
        $user = $request->user();
        
        $systemPrompt = "You are an expert personal branding coach and recruiter. Based on the user's profile, generate a compelling LinkedIn headline, about section, elevator pitch, GitHub bio, portfolio landing copy, and 3 sample LinkedIn posts. Return as JSON.";
        $userPrompt = "Profile: " . json_encode($user->toArray());
        
        $schemaHint = '{"headline": "string", "about_section": "string", "elevator_pitch": "string", "linkedin_posts": [{"title": "string", "content": "string"}], "github_bio": "string", "portfolio_copy": {"hero_title": "string", "hero_subtitle": "string", "about_blurb": "string", "contact_cta": "string"}}';
        
        $generatedData = $aiClient->completeJson($systemPrompt, $userPrompt, $schemaHint);
        
        $brand = PersonalBrand::updateOrCreate(
            ['user_id' => $user->id],
            $generatedData
        );
        
        return response()->json($brand);
    }
}
