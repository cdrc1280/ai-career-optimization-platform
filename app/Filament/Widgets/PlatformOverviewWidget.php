<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Subscription;
use App\Models\ResumeAnalysis;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Active Subscriptions', Subscription::where('status', 'active')->count()),
            Stat::make('Total Analyses Run', ResumeAnalysis::count()),
            Stat::make('Average Match Score', round(ResumeAnalysis::avg('overall_match_score') ?? 0, 1) . '%'),
        ];
    }
}
