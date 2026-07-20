<?php

namespace App\Services;

use App\Models\Resume;
use App\Services\Contracts\ResumeParserInterface;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class AiResumeParser implements ResumeParserInterface
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        You convert raw resume text into structured JSON. Extract only what
        is explicitly present in the text — do not infer skills from job
        titles, do not guess dates that aren't stated, and do not normalize
        company names to ones you recognize if the text spells them
        differently. If a section is missing or unclear, omit it and add a
        note in "extraction_notes" rather than guessing, so the app can ask
        the user to fill it in manually.
        PROMPT;

    private const SCHEMA_HINT = <<<'SCHEMA'
        {
          "personal_info": {"full_name": "string|null", "email": "string|null", "phone": "string|null", "location": "string|null", "linkedin_url": "string|null", "github_url": "string|null", "portfolio_url": "string|null"},
          "professional_title": "string|null",
          "summary": "string|null",
          "work_experience": [{"company": "string", "title": "string", "location": "string|null", "start_date": "string|null", "end_date": "string|null", "is_current": "boolean", "responsibilities": ["string"], "achievements": ["string"]}],
          "education": [{"institution": "string", "degree": "string|null", "field_of_study": "string|null", "start_date": "string|null", "end_date": "string|null"}],
          "skills": ["string"],
          "certifications": [{"name": "string", "issuing_organization": "string|null", "issued_date": "string|null"}],
          "languages": [{"name": "string", "proficiency": "string|null"}],
          "projects": [{"name": "string", "description": "string|null", "technologies": ["string"]}],
          "confidence": "high|medium|low",
          "extraction_notes": ["string"]
        }
        SCHEMA;

    public function __construct(private readonly AiClient $aiClient) {}

    public function parse(Resume $resume): array
    {
        $resume->update(['parse_status' => 'processing']);

        try {
            $rawText = $this->extractRawText($resume);
            $dbPrompt = \App\Models\AiPrompt::where('name', 'resume_parser')->where('is_active', true)->value('prompt_text');
            $systemPrompt = $dbPrompt ?: self::SYSTEM_PROMPT;

            $result = $this->aiClient->completeJson($systemPrompt, $rawText, self::SCHEMA_HINT);

            $status = match ($result['confidence'] ?? 'low') {
                'high' => 'completed',
                default => 'needs_review',
            };

            $resume->update([
                'parsed_data' => $result,
                'parse_status' => $status,
            ]);

            return $result;
        } catch (\Throwable $e) {
            $resume->update(['parse_status' => 'failed', 'parse_error' => $e->getMessage()]);
            report($e);
            throw $e;
        }
    }

    private function extractRawText(Resume $resume): string
    {
        $path = Storage::disk('private')->path($resume->file_path);

        return match ($resume->mime_type) {
            'application/pdf' => (new PdfParser)->parseFile($path)->getText(),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocxText($path),
            default => throw new \RuntimeException("Unsupported resume mime type: {$resume->mime_type}"),
        };
    }

    private function extractDocxText(string $path): string
    {
        $phpWord = WordIOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText()."\n";
                }
            }
        }

        return $text;
    }
}
