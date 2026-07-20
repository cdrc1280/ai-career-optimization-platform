<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Services\Contracts\ResumeParserInterface;
use App\Services\ProfileSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public Resume $resume) {}

    public function handle(ResumeParserInterface $parser, ProfileSyncService $syncService): void
    {
        $parsed = $parser->parse($this->resume);

        // Update the master resume version content with the parsed data
        $masterVersion = $this->resume->masterVersion;
        if ($masterVersion) {
            $masterVersion->update(['content' => $parsed]);
        }

        // Sync parsed data to the user's profile tables
        $syncService->sync($this->resume, $parsed);
    }
}
