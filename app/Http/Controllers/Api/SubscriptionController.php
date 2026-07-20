<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function simulateCheckout(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id'
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Cancel existing active subscriptions
        $user->subscriptions()->where('status', 'active')->update(['status' => 'canceled']);

        // Create new subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        // Reset usage counters
        $user->usageCounters()->delete();

        return response()->json([
            'message' => 'Subscription upgraded successfully',
            'subscription' => $subscription
        ]);
    }
}
