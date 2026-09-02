<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentLookupRequest;
use App\Models\Author;
use App\Models\Category;
use App\Models\Contributor;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Language;
use App\Models\LicenseType;
use App\Models\Magazine;
use App\Models\RightsStatus;
use App\Models\Source;
use App\Models\Specialization;
use App\Models\Subcategory;
use App\Support\LookupName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentLookupController extends Controller
{
    /** @var array<string, class-string<Model>> */
    private const LOOKUPS = [
        'sources' => Source::class,
        'magazines' => Magazine::class,
        'document-types' => DocumentType::class,
        'languages' => Language::class,
        'categories' => Category::class,
        'subcategories' => Subcategory::class,
        'specializations' => Specialization::class,
        'authors' => Author::class,
        'contributors' => Contributor::class,
        'countries' => Country::class,
        'license-types' => LicenseType::class,
        'rights-statuses' => RightsStatus::class,
    ];

    private const CREATABLE_LOOKUPS = [
        'categories',
        'subcategories',
        'specializations',
        'sources',
        'magazines',
        'languages',
        'countries',
        'document-types',
        'authors',
        'contributors',
    ];

    public function index(Request $request, string $type): JsonResponse
    {
        if (! isset(self::LOOKUPS[$type])) {
            return response()->json(['message' => 'Lookup type not found.'], 404);
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'source_id' => ['nullable', 'integer', 'exists:sources,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $modelClass = self::LOOKUPS[$type];
        $query = $modelClass::query();

        if (in_array($type, [
            'sources',
            'magazines',
            'document-types',
            'languages',
            'categories',
            'subcategories',
            'specializations',
        ], true)) {
            $query->where('is_active', true);
        }

        $this->applyParentFilter($query, $type, $filters);

        $isBilingualLookup = in_array($type, ['license-types', 'rights-statuses'], true);

        if (! empty($filters['search'])) {
            $term = '%'.LookupName::comparable($filters['search']).'%';

            if ($isBilingualLookup) {
                $query->where(fn (Builder $query) => $query
                    ->whereRaw('LOWER(code) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_ar) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(name_en) LIKE ?', [$term]));
            } else {
                $query->whereRaw('LOWER(name) LIKE ?', [$term]);
            }
        }

        $paginator = $query
            ->orderBy($isBilingualLookup ? 'name_en' : 'name')
            ->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => collect($paginator->items())->map(fn (Model $item) => $this->serialize($item))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreDocumentLookupRequest $request, string $type): JsonResponse
    {
        if (! in_array($type, self::CREATABLE_LOOKUPS, true)) {
            return response()->json(['message' => 'This lookup type cannot be created here.'], 404);
        }

        $validated = $request->validated();
        $name = LookupName::clean($validated['name']);
        $modelClass = self::LOOKUPS[$type];
        $parent = $this->parentAttributes($type, $validated);

        try {
            [$lookup, $created] = DB::transaction(function () use ($modelClass, $name, $parent, $type) {
                $query = $modelClass::query()->where($parent);
                $existing = $query
                    ->whereRaw('LOWER(name) = ?', [LookupName::comparable($name)])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if (array_key_exists('is_active', $existing->getAttributes()) && ! $existing->is_active) {
                        $existing->update(['is_active' => true]);
                    }

                    return [$existing, false];
                }

                return [$modelClass::create($this->creationAttributes($type, $name, $parent)), true];
            });
        } catch (QueryException $exception) {
            $lookup = $modelClass::query()
                ->where($parent)
                ->whereRaw('LOWER(name) = ?', [LookupName::comparable($name)])
                ->first();

            if (! $lookup) {
                throw $exception;
            }

            $created = false;
        }

        return response()->json([
            'message' => $created ? 'Lookup value created successfully.' : 'The existing lookup value was selected.',
            'created' => $created,
            'data' => $this->serialize($lookup),
        ], $created ? 201 : 200);
    }

    /** @param array<string, mixed> $filters */
    private function applyParentFilter(Builder $query, string $type, array $filters): void
    {
        $parentMap = [
            'subcategories' => 'category_id',
            'specializations' => 'subcategory_id',
            'magazines' => 'source_id',
        ];

        if (! isset($parentMap[$type])) {
            return;
        }

        $foreignKey = $parentMap[$type];

        isset($filters[$foreignKey])
            ? $query->where($foreignKey, $filters[$foreignKey])
            : $query->whereRaw('1 = 0');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, int>
     */
    private function parentAttributes(string $type, array $validated): array
    {
        return match ($type) {
            'subcategories' => ['category_id' => (int) $validated['category_id']],
            'specializations' => ['subcategory_id' => (int) $validated['subcategory_id']],
            'magazines' => ['source_id' => (int) $validated['source_id']],
            default => [],
        };
    }

    /**
     * @param  array<string, int>  $parent
     * @return array<string, mixed>
     */
    private function creationAttributes(string $type, string $name, array $parent): array
    {
        $attributes = $parent + ['name' => $name];

        if (in_array($type, [
            'categories',
            'subcategories',
            'specializations',
            'sources',
            'magazines',
            'languages',
            'document-types',
        ], true)) {
            $attributes['is_active'] = true;
        }

        return $attributes;
    }

    /** @return array<string, mixed> */
    private function serialize(Model $item): array
    {
        $nameAr = $item->getAttribute('name_ar');
        $nameEn = $item->getAttribute('name_en');

        return array_filter([
            'id' => $item->getKey(),
            'name' => $nameAr && $nameEn ? $nameAr.' - '.$nameEn : $item->getAttribute('name'),
            'code' => $item->getAttribute('code'),
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'category_id' => $item->getAttribute('category_id'),
            'subcategory_id' => $item->getAttribute('subcategory_id'),
            'source_id' => $item->getAttribute('source_id'),
        ], fn ($value) => $value !== null);
    }
}
