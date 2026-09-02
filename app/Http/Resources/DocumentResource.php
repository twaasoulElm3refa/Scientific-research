<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->drive_file_name ?? $this->original_file_name ?? $this->title,
            'title' => $this->title,
            'original_file_name' => $this->original_file_name,
            'doi' => $this->doi,
            'isbn' => $this->isbn,
            'issn' => $this->issn,
            'url' => $this->url,
            'publish_year' => $this->publication_year,
            'publish_date' => $this->publication_date?->format('Y-m'),
            'pages_number' => $this->total_pages,
            'source' => $this->whenLoaded('source', fn () => $this->lookup($this->source)),
            'magazine' => $this->whenLoaded('magazine', fn () => $this->lookup($this->magazine)),
            'document_type' => $this->whenLoaded('documentType', fn () => $this->lookup($this->documentType)),
            'language' => $this->whenLoaded('languageRecord', fn () => $this->lookup($this->languageRecord, true)),
            'category' => $this->whenLoaded('category', fn () => $this->lookup($this->category)),
            'subcategory' => $this->whenLoaded('subcategory', fn () => $this->lookup($this->subcategory)),
            'specialization' => $this->whenLoaded('specialization', fn () => $this->lookup($this->specialization)),
            'country' => $this->whenLoaded('country', fn () => $this->lookup($this->country, true)),
            'license_type' => $this->whenLoaded('licenseType', fn () => $this->bilingualLookup($this->licenseType)),
            'rights_status' => $this->whenLoaded('rightsStatus', fn () => $this->bilingualLookup($this->rightsStatus)),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
                'order' => $author->pivot->author_order,
            ])->values()),
            'contributors' => $this->whenLoaded('contributors', fn () => $this->contributors->map(fn ($contributor) => [
                'id' => $contributor->id,
                'name' => $contributor->name,
                'role' => $contributor->pivot->role,
                'order' => $contributor->pivot->contributor_order,
            ])->values()),
            'created_by' => $this->whenLoaded('user', fn () => $this->lookup($this->user)),
            'drive' => [
                'file_id' => $this->drive_file_id,
                'folder_id' => $this->drive_folder_id,
                'stored_file_name' => $this->drive_file_name,
                'web_view_link' => $this->drive_url,
                'mime_type' => $this->mime_type,
                'file_extension' => $this->file_extension,
                'file_size' => $this->file_size,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lookup($model, bool $withCode = false): ?array
    {
        if ($model === null) {
            return null;
        }

        return array_filter([
            'id' => $model->id,
            'name' => $model->name,
            'code' => $withCode ? $model->code : null,
        ], fn ($value) => $value !== null);
    }

    /** @return array<string, mixed>|null */
    private function bilingualLookup($model): ?array
    {
        if ($model === null) {
            return null;
        }

        return [
            'id' => $model->id,
            'code' => $model->code,
            'name_ar' => $model->name_ar,
            'name_en' => $model->name_en,
            'name' => $model->name_ar.' - '.$model->name_en,
        ];
    }
}
