<?php

namespace App\Http\Controllers\api\admin;

use App\Exceptions\DocumentCreationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentCreationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'source_id' => ['nullable', 'integer', 'exists:sources,id'],
            'magazine_id' => ['nullable', 'integer', 'exists:magazines,id'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'sort' => ['nullable', 'in:title,publication_date,publication_year,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $query = Document::query()->with([
            'user:id,name',
            'source:id,name',
            'magazine:id,name',
            'documentType:id,name',
            'languageRecord:id,name,code',
            'category:id,name',
            'subcategory:id,category_id,name',
            'specialization:id,subcategory_id,name',
            'country:id,name,code',
            'authors:id,name',
        ]);

        if (! empty($filters['search'])) {
            $term = '%'.mb_strtolower($filters['search']).'%';
            $query->where(function (Builder $query) use ($term) {
                $query->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(doi) LIKE ?', [$term])
                    ->orWhereHas('authors', fn (Builder $authors) => $authors->whereRaw('LOWER(name) LIKE ?', [$term]));
            });
        }

        foreach ([
            'source_id',
            'magazine_id',
            'document_type_id',
            'language_id',
            'category_id',
            'subcategory_id',
            'country_id',
        ] as $filter) {
            if (isset($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $documents = $query
            ->orderBy($filters['sort'] ?? 'created_at', $filters['direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return DocumentResource::collection($documents);
    }

    public function store(
        StoreDocumentRequest $request,
        DocumentCreationService $documentCreationService,
    ): JsonResponse {
        try {
            $document = $documentCreationService->create(
                $request->validated(),
                $request->file('document_file'),
                $request->user(),
            );

            return response()->json([
                'message' => 'Document created successfully.',
                'document' => new DocumentResource($document),
            ], 201);
        } catch (DocumentCreationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->httpStatus);
        }
    }
}
