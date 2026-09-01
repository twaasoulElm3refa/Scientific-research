<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Contributor;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentAuthor;
use App\Models\DocumentContributor;
use App\Models\Specialization;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_relationships_are_available_and_pivots_are_ordered(): void
    {
        $category = Category::create(['name' => 'Science']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Computer Science',
        ]);
        $specialization = Specialization::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Artificial Intelligence',
        ]);
        $country = Country::create(['name' => 'Egypt', 'code' => 'EG']);
        $user = User::factory()->create();
        $document = Document::create([
            'user_id' => $user->id,
            'specialization_id' => $specialization->id,
            'country_id' => $country->id,
            'title' => 'A Search Paper',
            'drive_file_id' => 'drive-file-1',
        ]);
        $firstAuthor = Author::create(['name' => 'First Author', 'country_id' => $country->id]);
        $secondAuthor = Author::create(['name' => 'Second Author', 'country_id' => $country->id]);
        $firstContributor = Contributor::create(['name' => 'First Contributor', 'country_id' => $country->id]);
        $secondContributor = Contributor::create(['name' => 'Second Contributor', 'country_id' => $country->id]);

        $document->authors()->attach($secondAuthor, ['author_order' => 2]);
        $document->authors()->attach($firstAuthor, ['author_order' => 1]);
        $document->contributors()->attach($secondContributor, [
            'role' => 'Editor',
            'contributor_order' => 2,
        ]);
        $document->contributors()->attach($firstContributor, [
            'role' => 'Reviewer',
            'contributor_order' => 1,
        ]);

        $this->assertTrue($category->subcategories->contains($subcategory));
        $this->assertTrue($subcategory->category->is($category));
        $this->assertTrue($subcategory->specializations->contains($specialization));
        $this->assertTrue($specialization->documents->contains($document));
        $this->assertTrue($country->authors->contains($firstAuthor));
        $this->assertTrue($country->contributors->contains($firstContributor));
        $this->assertTrue($country->documents->contains($document));
        $this->assertTrue($user->documents->contains($document));
        $this->assertTrue($document->user->is($user));
        $this->assertTrue($document->specialization->is($specialization));
        $this->assertTrue($document->country->is($country));
        $this->assertSame(
            [$firstAuthor->id, $secondAuthor->id],
            $document->authors->pluck('id')->all()
        );
        $this->assertSame(
            [$firstContributor->id, $secondContributor->id],
            $document->contributors->pluck('id')->all()
        );
        $this->assertSame('Reviewer', $document->contributors->first()->pivot->role);
        $this->assertTrue($firstAuthor->documents->contains($document));
        $this->assertTrue($firstContributor->documents->contains($document));
    }

    public function test_duplicate_document_author_is_rejected(): void
    {
        [$document, $author] = $this->makeDocumentAndAuthor();
        DocumentAuthor::create(['document_id' => $document->id, 'author_id' => $author->id]);

        $this->expectException(QueryException::class);

        DocumentAuthor::create(['document_id' => $document->id, 'author_id' => $author->id]);
    }

    public function test_duplicate_document_contributor_is_rejected(): void
    {
        $document = Document::create(['title' => 'Document', 'drive_file_id' => 'drive-contributor']);
        $contributor = Contributor::create(['name' => 'Contributor']);
        DocumentContributor::create([
            'document_id' => $document->id,
            'contributor_id' => $contributor->id,
        ]);

        $this->expectException(QueryException::class);

        DocumentContributor::create([
            'document_id' => $document->id,
            'contributor_id' => $contributor->id,
        ]);
    }

    public function test_duplicate_drive_file_id_is_rejected(): void
    {
        Document::create(['title' => 'First', 'drive_file_id' => 'same-drive-file']);

        $this->expectException(QueryException::class);

        Document::create(['title' => 'Second', 'drive_file_id' => 'same-drive-file']);
    }

    public function test_expected_foreign_key_indexes_and_unique_constraints_exist(): void
    {
        $indexedColumns = [
            'subcategories' => ['category_id'],
            'specializations' => ['subcategory_id'],
            'authors' => ['country_id'],
            'contributors' => ['country_id'],
            'documents' => ['user_id', 'specialization_id', 'country_id'],
            'document_authors' => ['document_id', 'author_id'],
            'document_contributors' => ['document_id', 'contributor_id'],
        ];

        foreach ($indexedColumns as $table => $columns) {
            $indexes = Schema::getIndexes($table);

            foreach ($columns as $column) {
                $this->assertTrue(
                    collect($indexes)->contains(
                        fn (array $index): bool => ($index['columns'] ?? []) === [$column]
                    ),
                    "Missing index on {$table}.{$column}"
                );
            }
        }

        $uniqueIndexes = [
            'users' => [['email']],
            'categories' => [['slug']],
            'countries' => [['code']],
            'documents' => [['drive_file_id']],
            'document_authors' => [['document_id', 'author_id']],
            'document_contributors' => [['document_id', 'contributor_id']],
        ];

        foreach ($uniqueIndexes as $table => $expectedColumnSets) {
            $indexes = collect(Schema::getIndexes($table));

            foreach ($expectedColumnSets as $columns) {
                $this->assertTrue(
                    $indexes->contains(
                        fn (array $index): bool => ($index['unique'] ?? false)
                            && ($index['columns'] ?? []) === $columns
                    ),
                    'Missing unique constraint on '.$table.'.'.implode(',', $columns)
                );
            }
        }
    }

    public function test_foreign_key_delete_actions_are_enforced(): void
    {
        $category = Category::create(['name' => 'Category']);
        $subcategory = Subcategory::create(['category_id' => $category->id, 'name' => 'Subcategory']);
        $specialization = Specialization::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Specialization',
        ]);
        $country = Country::create(['name' => 'Country']);
        $user = User::factory()->create();
        $document = Document::create([
            'user_id' => $user->id,
            'specialization_id' => $specialization->id,
            'country_id' => $country->id,
            'title' => 'Document',
            'drive_file_id' => 'delete-actions',
        ]);
        $author = Author::create(['name' => 'Author', 'country_id' => $country->id]);
        $contributor = Contributor::create(['name' => 'Contributor', 'country_id' => $country->id]);
        $document->authors()->attach($author);
        $document->contributors()->attach($contributor);

        $country->delete();
        $user->delete();

        $this->assertNull($author->fresh()->country_id);
        $this->assertNull($contributor->fresh()->country_id);
        $this->assertNull($document->fresh()->country_id);
        $this->assertNull($document->fresh()->user_id);

        $document->delete();

        $this->assertDatabaseCount('document_authors', 0);
        $this->assertDatabaseCount('document_contributors', 0);
    }

    /**
     * @return array{Document, Author}
     */
    private function makeDocumentAndAuthor(): array
    {
        return [
            Document::create(['title' => 'Document', 'drive_file_id' => 'drive-author']),
            Author::create(['name' => 'Author']),
        ];
    }
}
