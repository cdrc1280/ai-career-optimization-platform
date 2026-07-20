<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Services\Contracts\ResumeParserInterface;
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

    public function handle(ResumeParserInterface $parser): void
    {
        $parser->parse($this->resume);
    }
}
