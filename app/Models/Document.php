<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'publication_date' => 'date',
            'total_pages' => 'integer',
            'file_size' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function magazine(): BelongsTo
    {
        return $this->belongsTo(Magazine::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function languageRecord(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'document_authors')
            ->withPivot('author_order')
            ->orderByPivot('author_order');
    }

    public function contributors(): BelongsToMany
    {
        return $this->belongsToMany(Contributor::class, 'document_contributors')
            ->withPivot('role', 'contributor_order')
            ->orderByPivot('contributor_order');
    }
}
