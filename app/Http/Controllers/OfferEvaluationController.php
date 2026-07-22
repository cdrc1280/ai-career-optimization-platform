<?php

namespace App\Http\Controllers;

use App\Models\OfferEvaluation;
use Illuminate\Http\Request;

class OfferEvaluationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->offerEvaluations);
    }

    public function show(Request $request, OfferEvaluation $offerEvaluation)
    {
        if ($offerEvaluation->user_id !== $request->user()->id) {
            abort(403);
        }
        return response()->json($offerEvaluation);
    }

    public function store(Request $request, \App\Services\AiClient $aiClient)
    {
        $validated = $request->validate([
            'job_title' => 'required|string',
            'company_name' => 'required|string',
            'offer_details' => 'required|string',
        ]);

        $evaluationData = $aiClient->completeJson(
            'Evaluate the following job offer',
            json_encode($validated),
            '{"salary_score": 0, "benefits_score": 0, "red_flags": [""], "negotiation_tips": [""], "overall_recommendation": ""}'
        );

        $offer = $request->user()->offerEvaluations()->create([
            'job_title' => $validated['job_title'],
            'company_name' => $validated['company_name'],
            'offer_details' => $validated['offer_details'],
            'evaluation_data' => $evaluationData,
        ]);

        return response()->json($offer, 201);
    }
}
