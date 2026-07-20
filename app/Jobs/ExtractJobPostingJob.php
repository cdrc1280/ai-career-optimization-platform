<?php

namespace App\Jobs;

use App\Models\JobPosting;
use App\Services\Contracts\JobPostingExtractorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractJobPostingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public JobPosting $jobPosting) {}

    public function handle(JobPostingExtractorInterface $extractor): void
    {
        $extractor->extract($this->jobPosting);
    }
}
