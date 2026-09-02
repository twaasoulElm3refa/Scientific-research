<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->role === 'admin' && (bool) $user->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sourceId = $this->integer('source_id');
        $categoryId = $this->integer('category_id');
        $subcategoryId = $this->integer('subcategory_id');

        return [
            'submission_id' => ['required', 'uuid', 'unique:documents,submission_id'],
            'document_file' => [
                'required',
                File::types(['pdf', 'docx', 'pptx', 'txt']),
                'extensions:pdf,docx,pptx,txt',
            ],
            'title' => ['required', 'string', 'max:500', 'regex:/^[^\p{C}]+$/u'],
            'source_id' => [
                'nullable',
                'integer',
                Rule::exists('sources', 'id')->where('is_active', true),
            ],
            'magazine_id' => [
                'nullable',
                'integer',
                Rule::exists('magazines', 'id')->where(
                    fn ($query) => $query->where('source_id', $sourceId)->where('is_active', true)
                ),
            ],
            'document_type_id' => [
                'required',
                'integer',
                Rule::exists('document_types', 'id')->where('is_active', true),
            ],
            'doi' => ['nullable', 'string', 'max:255', 'regex:/^10\.\d{4,9}\/\S+$/i'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'issn' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'license_type_id' => ['nullable', 'integer', Rule::exists('license_types', 'id')],
            'rights_status_id' => ['required', 'integer', Rule::exists('rights_statuses', 'id')],
            'language_id' => [
                'required',
                'integer',
                Rule::exists('languages', 'id')->where('is_active', true),
            ],
            'publish_year' => ['nullable', 'integer', 'between:1000,'.(now()->year + 1)],
            'publish_date' => ['nullable', 'date_format:Y-m'],
            'pages_number' => ['nullable', 'integer', 'min:1'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'subcategory_id' => [
                'nullable',
                'integer',
                Rule::exists('subcategories', 'id')->where(
                    fn ($query) => $query->where('category_id', $categoryId)->where('is_active', true)
                ),
            ],
            'specialization_id' => [
                'nullable',
                'integer',
                Rule::exists('specializations', 'id')->where(
                    fn ($query) => $query->where('subcategory_id', $subcategoryId)->where('is_active', true)
                ),
            ],
            'author_ids' => ['required', 'array', 'min:1', 'max:100'],
            'author_ids.*' => ['required', 'integer', 'distinct', Rule::exists('authors', 'id')],
            'contributors' => ['nullable', 'array', 'max:100'],
            'contributors.*.id' => ['required', 'integer', 'distinct', Rule::exists('contributors', 'id')],
            'contributors.*.role' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'doi' => is_string($this->doi) ? trim($this->doi) : $this->doi,
            'isbn' => is_string($this->isbn) ? trim($this->isbn) : $this->isbn,
            'issn' => is_string($this->issn) ? trim($this->issn) : $this->issn,
            'url' => is_string($this->url) ? trim($this->url) : $this->url,
        ]);
    }

    public function messages(): array
    {
        return [
            'document_file.extensions' => 'The document must use a PDF, DOCX, PPTX, or TXT extension.',
            'document_file.mimetypes' => 'The document content does not match an allowed document type.',
            'title.required' => 'Enter the document title.',
            'title.regex' => 'The document title contains unsafe control characters.',
            'rights_status_id.required' => 'Select the document rights status.',
            'doi.regex' => 'Enter a valid DOI beginning with 10. and containing a slash.',
            'subcategory_id.exists' => 'The selected subcategory does not belong to the selected category.',
            'specialization_id.exists' => 'The selected specialization does not belong to the selected subcategory.',
            'magazine_id.exists' => 'The selected magazine does not belong to the selected source.',
        ];
    }
}
