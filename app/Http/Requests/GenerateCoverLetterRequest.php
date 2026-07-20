<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateCoverLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'resume_version_id' => ['required', 'exists:resume_versions,id'],
            'job_posting_id' => ['required', 'exists:job_postings,id'],
            'tone' => ['required', Rule::in(['professional', 'friendly', 'executive', 'technical'])],
        ];
    }
}
