<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'price_monthly_cents' => 0,
            'limits' => [
                'resume_optimizations' => 3,
                'cover_letters' => 3,
                'exports' => 5,
                'ai_analyses' => 5,
            ],
            'features' => ['basic_ai_analysis'],
            'is_active' => true,
        ]);

        Plan::updateOrCreate(['slug' => 'premium'], [
            'name' => 'Premium',
            'price_monthly_cents' => 99900, // adjust to your pricing (PHP centavos or USD cents)
            'limits' => [
                'resume_optimizations' => null, // unlimited
                'cover_letters' => null,
                'exports' => null,
                'ai_analyses' => null,
            ],
            'features' => [
                'advanced_ats_analysis', 'resume_history', 'interview_preparation', 'career_recommendations',
            ],
            'is_active' => true,
        ]);

        Plan::updateOrCreate(['slug' => 'enterprise'], [
            'name' => 'Enterprise',
            'price_monthly_cents' => 0, // custom/contact sales
            'limits' => [
                'resume_optimizations' => null,
                'cover_letters' => null,
                'exports' => null,
                'ai_analyses' => null,
            ],
            'features' => ['team_management', 'hr_tools', 'recruiter_dashboard', 'analytics', 'shared_workspaces'],
            'is_active' => true,
        ]);
    }
}
