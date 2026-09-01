<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Contributor;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Language;
use App\Models\Magazine;
use App\Models\Source;
use App\Models\Specialization;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDocumentLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_searched_with_twenty_results_per_page(): void
    {
        $admin = $this->admin();
        foreach (range(1, 25) as $number) {
            Category::create(['name' => "Category {$number}", 'is_active' => true]);
        }
        Category::create(['name' => 'الإعلام والاتصال', 'is_active' => true]);

        $this->withToken($admin->createToken('lookup')->plainTextToken)
            ->getJson('/api/admin/documents/lookups/categories?search=الإعلام')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'الإعلام والاتصال');

        $this->withToken($admin->createToken('page-size')->plainTextToken)
            ->getJson('/api/admin/documents/lookups/categories')
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 26);
    }

    public function test_admin_can_create_category_and_equivalent_duplicate_returns_existing_record(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('lookup')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/admin/documents/lookups/categories', [
            'name' => '  العلوم   البحرية  ',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('created', true)
            ->assertJsonPath('data.name', 'العلوم البحرية');

        $this->withToken($token)->postJson('/api/admin/documents/lookups/categories', [
            'name' => 'العلوم البحرية',
        ])->assertOk()
            ->assertJsonPath('created', false)
            ->assertJsonPath('data.id', $created->json('data.id'));

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_subcategory_creation_and_search_are_scoped_to_category(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('lookup')->plainTextToken;
        $media = Category::create(['name' => 'الإعلام والاتصال', 'is_active' => true]);
        $politics = Category::create(['name' => 'العلوم السياسية', 'is_active' => true]);
        Subcategory::create(['category_id' => $politics->id, 'name' => 'الصحافة', 'is_active' => true]);

        $created = $this->withToken($token)->postJson('/api/admin/documents/lookups/subcategories', [
            'name' => 'الصحافة',
            'category_id' => $media->id,
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('data.category_id', $media->id);

        $this->assertDatabaseHas('subcategories', [
            'id' => $created->json('data.id'),
            'category_id' => $media->id,
        ]);

        $this->withToken($token)
            ->getJson("/api/admin/documents/lookups/subcategories?category_id={$media->id}&search=الصحافة")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $created->json('data.id'));
    }

    public function test_specialization_creation_and_search_are_scoped_to_subcategory(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('lookup')->plainTextToken;
        $category = Category::create(['name' => 'الذكاء الاصطناعي', 'is_active' => true]);
        $machineLearning = Subcategory::create(['category_id' => $category->id, 'name' => 'تعلم الآلة', 'is_active' => true]);
        $deepLearning = Subcategory::create(['category_id' => $category->id, 'name' => 'التعلم العميق', 'is_active' => true]);
        Specialization::create(['subcategory_id' => $deepLearning->id, 'name' => 'النماذج التنبؤية', 'is_active' => true]);

        $created = $this->withToken($token)->postJson('/api/admin/documents/lookups/specializations', [
            'name' => 'النماذج التنبؤية',
            'subcategory_id' => $machineLearning->id,
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('data.subcategory_id', $machineLearning->id);

        $this->withToken($token)
            ->getJson("/api/admin/documents/lookups/specializations?subcategory_id={$machineLearning->id}&search=النماذج")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $created->json('data.id'));
    }

    public function test_source_and_magazine_creation_search_and_duplicate_scoping(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('lookup')->plainTextToken;

        $sourceResponse = $this->withToken($token)->postJson('/api/admin/documents/lookups/sources', [
            'name' => 'SAGE Publications',
        ])->assertCreated();
        $sourceId = $sourceResponse->json('data.id');

        $this->withToken($token)->postJson('/api/admin/documents/lookups/sources', [
            'name' => '  sage publications ',
        ])->assertOk()->assertJsonPath('data.id', $sourceId);
        $this->assertDatabaseCount('sources', 1);

        $otherSource = Source::create(['name' => 'Other Publisher', 'is_active' => true]);
        Magazine::create(['source_id' => $otherSource->id, 'name' => 'New Media & Society', 'is_active' => true]);

        $magazineResponse = $this->withToken($token)->postJson('/api/admin/documents/lookups/magazines', [
            'name' => 'New Media & Society',
            'source_id' => $sourceId,
        ])->assertCreated()->assertJsonPath('data.source_id', $sourceId);

        $this->withToken($token)
            ->getJson("/api/admin/documents/lookups/magazines?source_id={$sourceId}&search=new media")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $magazineResponse->json('data.id'));

        $this->assertDatabaseCount('magazines', 2);
    }

    public function test_parent_is_required_for_dependent_lookup_creation(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('lookup')->plainTextToken;

        $this->withToken($token)->postJson('/api/admin/documents/lookups/subcategories', ['name' => 'New'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_id');
        $this->withToken($token)->postJson('/api/admin/documents/lookups/specializations', ['name' => 'New'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('subcategory_id');
        $this->withToken($token)->postJson('/api/admin/documents/lookups/magazines', ['name' => 'New'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source_id');
    }

    public function test_language_country_document_type_author_and_contributor_are_searchable_and_creatable(): void
    {
        $admin = $this->admin();
        $token = $admin->createToken('additional-lookups')->plainTextToken;
        $lookups = [
            'languages' => [Language::class, 'Ancient Language', true],
            'countries' => [Country::class, 'New Country', false],
            'document-types' => [DocumentType::class, 'Intelligence Report', true],
            'authors' => [Author::class, 'Ahmed Mohamed', false],
            'contributors' => [Contributor::class, 'Research Assistant', false],
        ];

        foreach ($lookups as $type => [$modelClass, $name, $hasActiveState]) {
            $created = $this->withToken($token)
                ->postJson("/api/admin/documents/lookups/{$type}", ['name' => "  {$name}  "])
                ->assertCreated()
                ->assertJsonPath('created', true)
                ->assertJsonPath('data.name', $name);

            $this->assertDatabaseHas((new $modelClass)->getTable(), array_filter([
                'id' => $created->json('data.id'),
                'name' => $name,
                'is_active' => $hasActiveState ? true : null,
            ], fn ($value) => $value !== null));

            $this->withToken($token)
                ->getJson("/api/admin/documents/lookups/{$type}?search=".urlencode(strtolower($name)))
                ->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.id', $created->json('data.id'));

            $this->withToken($token)
                ->postJson("/api/admin/documents/lookups/{$type}", ['name' => '  '.strtolower($name).'  '])
                ->assertOk()
                ->assertJsonPath('created', false)
                ->assertJsonPath('data.id', $created->json('data.id'));

            $this->assertSame(1, $modelClass::query()->count());
        }
    }

    public function test_lookup_names_reject_control_characters(): void
    {
        $admin = $this->admin();

        $this->withToken($admin->createToken('lookup')->plainTextToken)
            ->postJson('/api/admin/documents/lookups/authors', ['name' => "Unsafe\0Author"])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->assertDatabaseCount('authors', 0);
    }

    public function test_public_and_non_admin_users_cannot_create_lookups(): void
    {
        $this->postJson('/api/admin/documents/lookups/categories', ['name' => 'Private'])
            ->assertUnauthorized();

        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $token = $user->createToken('lookup')->plainTextToken;

        foreach (['categories', 'languages', 'countries', 'document-types', 'authors', 'contributors'] as $type) {
            $this->withToken($token)
                ->postJson("/api/admin/documents/lookups/{$type}", ['name' => 'Private'])
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('categories', ['name' => 'Private']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }
}
