<?php

namespace App\Providers;

use App\Services\AiResumeParser;
use App\Services\Contracts\CoverLetterGeneratorInterface;
use App\Services\Contracts\JobPostingExtractorInterface;
use App\Services\Contracts\ResumeAnalyzerInterface;
use App\Services\Contracts\ResumeOptimizerInterface;
use App\Services\Contracts\ResumeParserInterface;
use App\Services\OpenAiCoverLetterGenerator;
use App\Services\OpenAiJobPostingExtractor;
use App\Services\OpenAiResumeAnalyzer;
use App\Services\OpenAiResumeOptimizer;
use Illuminate\Support\ServiceProvider;

/**
 * Binds interfaces to implementations. Controllers and jobs type-hint the
 * interfaces (App\Services\Contracts\*) — never the concrete OpenAi*
 * classes directly — so swapping AI providers later is a one-line change
 * here instead of a find-and-replace across the codebase.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResumeParserInterface::class, AiResumeParser::class);
        $this->app->bind(JobPostingExtractorInterface::class, OpenAiJobPostingExtractor::class);
        $this->app->bind(ResumeAnalyzerInterface::class, OpenAiResumeAnalyzer::class);
        $this->app->bind(ResumeOptimizerInterface::class, OpenAiResumeOptimizer::class);
        $this->app->bind(CoverLetterGeneratorInterface::class, OpenAiCoverLetterGenerator::class);
    }
}
