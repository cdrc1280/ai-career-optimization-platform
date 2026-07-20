<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobPostingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'source_url' => ['nullable', 'url', 'required_without:raw_description'],
            'raw_description' => ['nullable', 'string', 'min:50', 'required_without:source_url'],
            'source_site' => ['nullable', 'string', Rule::in([
                'linkedin', 'indeed', 'jobstreet', 'glassdoor', 'greenhouse', 'lever', 'workday', 'company_site', 'manual',
            ])],
        ];
    }
}
