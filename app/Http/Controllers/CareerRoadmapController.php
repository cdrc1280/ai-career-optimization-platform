<?php

namespace App\Http\Controllers;

use App\Models\CareerRoadmap;
use Illuminate\Http\Request;

class CareerRoadmapController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->careerRoadmaps);
    }

    public function show(Request $request, CareerRoadmap $roadmap)
    {
        if ($roadmap->user_id !== $request->user()->id) {
            abort(403);
        }
        return response()->json($roadmap);
    }

    public function store(Request $request, \App\Services\AiClient $aiClient)
    {
        $validated = $request->validate([
            'current_title' => 'required|string',
            'goal_title' => 'required|string',
        ]);

        $roadmapData = $aiClient->completeJson(
            'Generate a career roadmap from current_title to goal_title',
            json_encode($validated),
            '{"months": [{"month": 1, "focus": "string", "skills": ["string"], "resources": ["string"]}]}'
        );

        $roadmap = $request->user()->careerRoadmaps()->create([
            'current_title' => $validated['current_title'],
            'goal_title' => $validated['goal_title'],
            'roadmap_data' => $roadmapData,
        ]);

        return response()->json($roadmap, 201);
    }

    public function destroy(Request $request, CareerRoadmap $roadmap)
    {
        if ($roadmap->user_id !== $request->user()->id) {
            abort(403);
        }
        $roadmap->delete();
        return response()->json(null, 204);
    }
}
