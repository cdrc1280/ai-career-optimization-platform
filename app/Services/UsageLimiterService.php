<?php

namespace App\Services;

use App\Models\User;
use App\Models\UsageCounter;
use Carbon\Carbon;

/**
 * Enforces the plan limits described in the subscription system
 * (Free: limited optimizations/cover letters/exports, Premium: unlimited).
 * Every AI-consuming action should call canUse() before running and
 * increment() after succeeding.
 */
class UsageLimiterService
{
    public function canUse(User $user, string $metric): bool
    {
        $plan = $user->subscription?->isActive() ? $user->subscription->plan : $this->freePlan();

        $limit = $plan->limitFor($metric);

        // A limit of null means unlimited for this metric on this plan.
        if ($limit === null) {
            return true;
        }

        $used = $this->currentPeriodCounter($user, $metric)->count;

        return $used < $limit;
    }

    public function increment(User $user, string $metric): void
    {
        $counter = $this->currentPeriodCounter($user, $metric);
        $counter->increment('count');
    }

    private function currentPeriodCounter(User $user, string $metric): UsageCounter
    {
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end = Carbon::now()->endOfMonth()->toDateString();

        return UsageCounter::firstOrCreate(
            ['user_id' => $user->id, 'metric' => $metric, 'period_start' => $start],
            ['count' => 0, 'period_end' => $end],
        );
    }

    private function freePlan(): \App\Models\Plan
    {
        return \App\Models\Plan::where('slug', 'free')->firstOrFail();
    }
}
