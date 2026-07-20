<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register bindings, singletons, or other container services here.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('ai', fn($request) => Limit::perMinute(10)->by($request->user()->id));

        Gate::policy(\App\Models\Resume::class, \App\Policies\ResumePolicy::class);
        Gate::policy(\App\Models\JobPosting::class, \App\Policies\JobPostingPolicy::class);
        Gate::policy(\App\Models\Application::class, \App\Policies\ApplicationPolicy::class);
    }
}
