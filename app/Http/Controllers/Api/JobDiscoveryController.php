<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AiClient;

class JobDiscoveryController extends Controller
{
    public function index(Request $request, AiClient $aiClient)
    {
        $keyword = $request->query('keyword', '');
        $location = $request->query('location', '');
        
        $result = $aiClient->completeJson(
            'job discovery',
            "Find jobs for keyword: $keyword in location: $location",
            '{"jobs": []}'
        );
        
        return response()->json($result['jobs'] ?? []);
    }
}
