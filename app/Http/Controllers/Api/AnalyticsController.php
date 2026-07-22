<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $applications = $user->applications()->get();
        $totalApps = $applications->count();
        
        $interviews = $applications->where('status', 'interview')->count();
        $offers = $applications->whereIn('status', ['offer', 'accepted'])->count();
        $rejections = $applications->where('status', 'rejected')->count();
        
        $interviewRate = $totalApps > 0 ? round(($interviews / $totalApps) * 100, 2) : 0;
        $offerRate = $totalApps > 0 ? round(($offers / $totalApps) * 100, 2) : 0;
        $rejectionRate = $totalApps > 0 ? round(($rejections / $totalApps) * 100, 2) : 0;
        
        $resumeVersions = $user->resumeVersions()->with('applications')->get()->map(function ($rv) {
            $rvApps = $rv->applications;
            $rvTotalApps = $rvApps->count();
            
            $rvInterviews = $rvApps->where('status', 'interview')->count();
            $rvOffers = $rvApps->whereIn('status', ['offer', 'accepted'])->count();
            
            $avgMatch = $rvApps->avg('match_score') ?? 0;
            
            return [
                'id' => $rv->id,
                'version_name' => $rv->name ?? 'Version ' . $rv->id,
                'applications_count' => $rvTotalApps,
                'interview_count' => $rvInterviews,
                'offer_count' => $rvOffers,
                'match_score_avg' => round($avgMatch, 2),
            ];
        })->values()->toArray();

        return response()->json([
            'total_applications' => $totalApps,
            'interview_rate' => $interviewRate,
            'offer_rate' => $offerRate,
            'rejection_rate' => $rejectionRate,
            'resume_versions' => $resumeVersions,
        ]);
    }
}
