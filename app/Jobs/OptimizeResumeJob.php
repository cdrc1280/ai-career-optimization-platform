<?php

namespace App\Jobs;

use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Models\User;
use App\Notifications\ResumeOptimizationCompleted;
use App\Services\Contracts\ResumeOptimizerInterface;
use App\Services\UsageLimiterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public ResumeVersion $baseVersion,
        public JobPosting $jobPosting,
        public string $label,
        public User $user,
    ) {}

    public function handle(ResumeOptimizerInterface $optimizer, UsageLimiterService $limiter): void
    {
        if (! $limiter->canUse($this->user, 'resume_optimizations')) {
            // In production: notify the user their plan limit was hit
            // rather than failing silently.
            return;
        }

        $newVersion = $optimizer->optimize($this->baseVersion, $this->jobPosting, $this->label);
        $limiter->increment($this->user, 'resume_optimizations');
        if ($newVersion) {
            $this->user->notify(new ResumeOptimizationCompleted($newVersion));
        }
    }
}
