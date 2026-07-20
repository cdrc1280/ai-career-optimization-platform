<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoverLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CoverLetterExportController extends Controller {
    public function export(Request $request, CoverLetter $coverLetter): Response {
        $this->authorize('view', $coverLetter);
        $user = $request->user();
        $profile = $user->profile;
        $firstName = explode(' ', $profile?->full_name ?? $user->name)[0];
        $lastName = explode(' ', $profile?->full_name ?? $user->name)[1] ?? '';
        $company = $coverLetter->jobPosting?->company_name ?? 'Company';
        $jobTitle = $coverLetter->jobPosting?->job_title ?? 'Position';
        
        $filename = implode('_', array_filter([$firstName, $lastName, str_replace(' ', '_', $jobTitle), str_replace(' ', '_', $company)])) . '_Cover_Letter.pdf';
        
        $html = view('exports.cover-letter', compact('coverLetter', 'profile', 'user'))->render();
        $pdf = Pdf::loadHtml($html)->setPaper('a4');
        
        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
