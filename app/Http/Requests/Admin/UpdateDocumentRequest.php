<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === 'admin' && (bool) $user->is_active;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:500', 'regex:/^[^\p{C}]+$/u'],
            'source_id' => ['nullable', 'integer', Rule::exists('sources', 'id')->where('is_active', true)],
            'document_type_id' => ['required', 'integer', Rule::exists('document_types', 'id')->where('is_active', true)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'language_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'license_type_id' => ['nullable', 'integer', Rule::exists('license_types', 'id')],
            'rights_status_id' => ['required', 'integer', Rule::exists('rights_statuses', 'id')],
            'doi' => ['nullable', 'string', 'max:255', 'regex:/^10\.\d{4,9}\/\S+$/i'],
            'publish_year' => ['nullable', 'integer', 'between:1000,'.(now()->year + 1)],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'doi', 'url'] as $field) {
            if (is_string($this->{$field})) {
                $this->merge([$field => trim($this->{$field})]);
            }
        }
    }
}
