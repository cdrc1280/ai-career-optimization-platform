<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\ResumeAnalysis;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Simple aggregate logic for demo purposes
        $totalUsers = User::count();
        $totalAnalyses = ResumeAnalysis::count();
        $avgScore = ResumeAnalysis::average('overall_score') ?? 0;

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            
            Stat::make('Total Analyses', $totalAnalyses)
                ->description('Resumes analyzed')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([1, 5, 2, 10, 8, 12, 15])
                ->color('info'),
                
            Stat::make('Avg Match Score', number_format($avgScore, 1) . '%')
                ->description('Platform average')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
