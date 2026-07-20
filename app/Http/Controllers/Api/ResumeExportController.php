<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResumeVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeExportController extends Controller
{
    public function export(Request $request, ResumeVersion $resumeVersion, string $format): StreamedResponse|Response
    {
        $this->authorize('view', $resumeVersion->resume);

        abort_unless(in_array($format, ['docx', 'pdf']), 404, 'Unsupported export format.');

        $content = $resumeVersion->content ?? [];

        // Build filename
        $name    = str_replace(' ', '_', $content['personal_info']['full_name'] ?? 'Resume');
        $label   = str_replace([' ', '/'], '_', $resumeVersion->label ?? 'Version');
        $company = '';
        if ($resumeVersion->jobPosting) {
            $company = '_'.str_replace([' ', '/'], '_', $resumeVersion->jobPosting->company_name ?? '');
        }
        $filename = "{$name}_{$label}{$company}.{$format}";

        if ($format === 'pdf') {
            if ($resumeVersion->exported_pdf_path) {
                $path = storage_path('app/'.$resumeVersion->exported_pdf_path);
                if (file_exists($path)) {
                    return response()->download($path, $filename);
                }
            }
            
            $user = clone $request->user();
            $profile = $user->profile;
            $html = view('exports.resume', compact('resumeVersion', 'profile'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)->setPaper('a4');
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Generate DOCX
        $phpWord = $this->buildDocx($content, $resumeVersion->label ?? 'Resume');

        $tmpPath = tempnam(sys_get_temp_dir(), 'resume_') . '.docx';
        $writer  = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpPath);

        return response()->streamDownload(function () use ($tmpPath) {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function buildDocx(array $content, string $label): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 900,
            'marginRight'  => 900,
        ]);

        // Header - Name
        $personalInfo = $content['personal_info'] ?? [];
        $name         = $personalInfo['full_name'] ?? 'Resume';
        $section->addText($name, ['bold' => true, 'size' => 20, 'color' => '1a1a2e'], ['alignment' => 'center']);

        if ($title = $content['professional_title'] ?? null) {
            $section->addText($title, ['size' => 12, 'color' => '2563eb'], ['alignment' => 'center']);
        }

        // Contact line
        $contactParts = array_filter([
            $personalInfo['email']        ?? null,
            $personalInfo['phone']        ?? null,
            $personalInfo['location']     ?? null,
            $personalInfo['linkedin_url'] ?? null,
        ]);
        if ($contactParts) {
            $section->addText(implode('  |  ', $contactParts), ['size' => 9, 'color' => '555555'], ['alignment' => 'center']);
        }

        $section->addTextBreak(1);

        // Summary
        if ($summary = $content['summary'] ?? null) {
            $this->addSectionHeader($section, 'PROFESSIONAL SUMMARY');
            $section->addText($summary, ['size' => 10]);
            $section->addTextBreak(1);
        }

        // Work Experience
        if ($experiences = $content['work_experience'] ?? []) {
            $this->addSectionHeader($section, 'WORK EXPERIENCE');
            foreach ($experiences as $exp) {
                $dateRange = ($exp['start_date'] ?? '') . ' – ' . ($exp['is_current'] ? 'Present' : ($exp['end_date'] ?? ''));
                $section->addText(($exp['title'] ?? '') . ' | ' . ($exp['company'] ?? ''), ['bold' => true, 'size' => 11]);
                $section->addText($dateRange . ($exp['location'] ? ' · ' . $exp['location'] : ''), ['italic' => true, 'size' => 9, 'color' => '666666']);
                foreach ($exp['responsibilities'] ?? [] as $bullet) {
                    $section->addListItem($bullet, 0, ['size' => 10]);
                }
                foreach ($exp['achievements'] ?? [] as $bullet) {
                    $section->addListItem($bullet, 0, ['size' => 10, 'bold' => true]);
                }
                $section->addTextBreak(1);
            }
        }

        // Education
        if ($educations = $content['education'] ?? []) {
            $this->addSectionHeader($section, 'EDUCATION');
            foreach ($educations as $edu) {
                $section->addText(($edu['degree'] ?? '') . ' in ' . ($edu['field_of_study'] ?? ''), ['bold' => true, 'size' => 11]);
                $section->addText(($edu['institution'] ?? '') . ' · ' . ($edu['start_date'] ?? '') . ' – ' . ($edu['end_date'] ?? ''), ['size' => 10, 'color' => '444444']);
            }
            $section->addTextBreak(1);
        }

        // Skills
        if ($skills = $content['skills'] ?? []) {
            $this->addSectionHeader($section, 'SKILLS');
            $section->addText(implode('  ·  ', $skills), ['size' => 10]);
            $section->addTextBreak(1);
        }

        // Certifications
        if ($certs = $content['certifications'] ?? []) {
            $this->addSectionHeader($section, 'CERTIFICATIONS');
            foreach ($certs as $cert) {
                $section->addText(($cert['name'] ?? '') . ' — ' . ($cert['issuing_organization'] ?? ''), ['size' => 10]);
            }
            $section->addTextBreak(1);
        }

        // Projects
        if ($projects = $content['projects'] ?? []) {
            $this->addSectionHeader($section, 'PROJECTS');
            foreach ($projects as $proj) {
                $section->addText($proj['name'] ?? '', ['bold' => true, 'size' => 11]);
                if ($desc = $proj['description'] ?? null) {
                    $section->addText($desc, ['size' => 10]);
                }
                if ($techs = $proj['technologies'] ?? []) {
                    $section->addText('Technologies: ' . implode(', ', $techs), ['size' => 9, 'italic' => true, 'color' => '555555']);
                }
            }
        }

        return $phpWord;
    }

    private function addSectionHeader($section, string $title): void
    {
        $section->addText($title, ['bold' => true, 'size' => 11, 'color' => '2563eb', 'allCaps' => true]);

        // Horizontal rule via a table
        $table = $section->addTable(['borderBottomSize' => 6, 'borderBottomColor' => '2563eb']);
        $table->addRow();
        $table->addCell(9000);
    }
}
