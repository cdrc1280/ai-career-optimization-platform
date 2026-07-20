<?php

namespace App\Jobs;

use App\Models\JobPosting;
use App\Models\ResumeVersion;
use App\Services\Contracts\ResumeAnalyzerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 90;

    public function __construct(
        public ResumeVersion $resumeVersion,
        public JobPosting $jobPosting,
    ) {}

    public function handle(ResumeAnalyzerInterface $analyzer): void
    {
        $analyzer->analyze($this->resumeVersion, $this->jobPosting);
    }
}
