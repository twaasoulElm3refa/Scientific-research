<?php

namespace App\Http\Requests\Admin;

use App\Support\LookupName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentLookupRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\p{C}]+$/u'],
        ];

        return match ($this->route('type')) {
            'subcategories' => $rules + [
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')->where('is_active', true),
                ],
            ],
            'specializations' => $rules + [
                'subcategory_id' => [
                    'required',
                    'integer',
                    Rule::exists('subcategories', 'id')->where('is_active', true),
                ],
            ],
            'magazines' => $rules + [
                'source_id' => [
                    'required',
                    'integer',
                    Rule::exists('sources', 'id')->where('is_active', true),
                ],
            ],
            default => $rules,
        };
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => LookupName::clean($this->input('name'))]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter a lookup name.',
            'name.regex' => 'The lookup name contains invalid control characters.',
            'category_id.required' => 'Select a main category first.',
            'subcategory_id.required' => 'Select a subcategory first.',
            'source_id.required' => 'Select a source first.',
        ];
    }
}
