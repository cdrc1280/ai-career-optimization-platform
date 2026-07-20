<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('application')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(ApplicationStatus::class)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'applied_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
