<?php

namespace Tests\Feature;

use App\Contracts\GoogleDrive;
use App\Models\Author;
use App\Models\Category;
use App\Models\Contributor;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Language;
use App\Models\Magazine;
use App\Models\Source;
use App\Models\Specialization;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Fakes\FakeGoogleDrive;
use Tests\TestCase;

class AdminDocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private FakeGoogleDrive $drive;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google_drive.max_file_size_mb', 25);
        $this->drive = new FakeGoogleDrive;
        $this->app->instance(GoogleDrive::class, $this->drive);
    }

    public function test_admin_can_create_a_document_with_relations_and_drive_metadata(): void
    {
        [$admin, $payload, $relations] = $this->validPayload();

        $response = $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Document created successfully.')
            ->assertJsonPath('document.file_name', 'Secure Research Document')
            ->assertJsonPath('document.publish_date', '2026-04')
            ->assertJsonPath('document.source.id', $relations['source']->id)
            ->assertJsonPath('document.authors.0.id', $relations['author']->id)
            ->assertJsonPath('document.contributors.0.role', 'Reviewer');

        $this->assertDatabaseHas('documents', [
            'title' => 'Secure Research Document',
            'source_id' => $relations['source']->id,
            'category_id' => $relations['category']->id,
            'subcategory_id' => $relations['subcategory']->id,
            'drive_file_id' => 'fake-drive-file-id',
            'publication_date' => '2026-04-01 00:00:00',
        ]);
        $this->assertDatabaseHas('document_authors', [
            'author_id' => $relations['author']->id,
            'author_order' => 1,
        ]);
        $this->assertDatabaseHas('document_contributors', [
            'contributor_id' => $relations['contributor']->id,
            'role' => 'Reviewer',
            'contributor_order' => 1,
        ]);
        $this->assertSame(['research.pdf'], $this->drive->uploadedFiles);
    }

    public function test_hierarchy_validation_prevents_upload_and_database_writes(): void
    {
        [$admin, $payload] = $this->validPayload();
        $otherCategory = Category::create(['name' => 'Other Category']);
        $payload['category_id'] = $otherCategory->id;

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('subcategory_id');

        $this->assertDatabaseCount('documents', 0);
        $this->assertSame([], $this->drive->uploadedFiles);
    }

    public function test_magazine_must_belong_to_the_selected_source(): void
    {
        [$admin, $payload] = $this->validPayload();
        $otherSource = Source::create(['name' => 'Other Source']);
        $payload['source_id'] = $otherSource->id;

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('magazine_id');

        $this->assertDatabaseCount('documents', 0);
        $this->assertSame([], $this->drive->uploadedFiles);
    }

    public function test_invalid_file_type_is_rejected_before_drive_upload(): void
    {
        [$admin, $payload] = $this->validPayload();
        $payload['document_file'] = UploadedFile::fake()->createWithContent('malware.php', '<?php echo "bad";');

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('document_file');

        $this->assertDatabaseCount('documents', 0);
        $this->assertSame([], $this->drive->uploadedFiles);
    }

    public function test_document_rejects_unknown_lookup_and_people_ids_before_upload(): void
    {
        [$admin, $payload] = $this->validPayload();
        $payload['language_id'] = 999999;
        $payload['country_id'] = 999999;
        $payload['document_type_id'] = 999999;
        $payload['author_ids'] = [999999];
        $payload['contributors'] = [['id' => 999999, 'role' => 'Reviewer']];

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'language_id',
                'country_id',
                'document_type_id',
                'author_ids.0',
                'contributors.0.id',
            ]);

        $this->assertDatabaseCount('documents', 0);
        $this->assertSame([], $this->drive->uploadedFiles);
    }

    public function test_drive_failure_rolls_back_without_creating_a_document(): void
    {
        [$admin, $payload] = $this->validPayload();
        $this->drive->failUpload = true;

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertStatus(502)
            ->assertExactJson(['message' => 'The document could not be uploaded to Google Drive.']);

        $this->assertDatabaseCount('documents', 0);
        $this->assertSame([], $this->drive->deletedFiles);
    }

    public function test_database_failure_rolls_back_pivots_and_deletes_uploaded_drive_file(): void
    {
        [$admin, $payload] = $this->validPayload();
        Document::create([
            'title' => 'Existing',
            'drive_file_id' => $this->drive->nextFileId,
        ]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertStatus(500)
            ->assertExactJson(['message' => 'The document could not be saved.']);

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_authors', 0);
        $this->assertDatabaseCount('document_contributors', 0);
        $this->assertSame([$this->drive->nextFileId], $this->drive->deletedFiles);
    }

    public function test_non_admin_cannot_access_document_endpoints(): void
    {
        [, $payload] = $this->validPayload();
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertForbidden();

        $this->withToken($user->createToken('test-2')->plainTextToken)
            ->getJson('/api/admin/documents')
            ->assertForbidden();

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_lookup_endpoint_filters_subcategories_and_supports_search(): void
    {
        [$admin, , $relations] = $this->validPayload();
        Subcategory::create([
            'category_id' => Category::create(['name' => 'Other'])->id,
            'name' => 'Hidden Subcategory',
        ]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/documents/lookups/subcategories?category_id='.$relations['category']->id.'&search=primary')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $relations['subcategory']->id)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_document_index_is_paginated_searchable_and_relation_loaded(): void
    {
        [$admin, $payload, $relations] = $this->validPayload();
        $this->withToken($admin->createToken('create')->plainTextToken)
            ->post('/api/admin/documents', $payload, ['Accept' => 'application/json'])
            ->assertCreated();

        $this->withToken($admin->createToken('list')->plainTextToken)
            ->getJson('/api/admin/documents?search=secure&per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.file_name', 'Secure Research Document')
            ->assertJsonPath('data.0.category.id', $relations['category']->id)
            ->assertJsonPath('meta.total', 1);
    }

    /**
     * @return array{User, array<string, mixed>, array<string, mixed>}
     */
    private function validPayload(): array
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        $source = Source::create(['name' => 'University Press']);
        $magazine = Magazine::create(['source_id' => $source->id, 'name' => 'Science Journal']);
        $documentType = DocumentType::create(['name' => 'Research']);
        $language = Language::create(['name' => 'English', 'code' => 'en']);
        $category = Category::create(['name' => 'Science']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Primary Subcategory',
        ]);
        $specialization = Specialization::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Computing',
        ]);
        $country = Country::create(['name' => 'Egypt', 'code' => 'EG']);
        $author = Author::create(['name' => 'Document Author']);
        $contributor = Contributor::create(['name' => 'Document Contributor']);
        $payload = [
            'submission_id' => (string) Str::uuid(),
            'document_file' => UploadedFile::fake()->createWithContent('research.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n"),
            'file_name' => 'Secure Research Document',
            'source_id' => $source->id,
            'magazine_id' => $magazine->id,
            'document_type_id' => $documentType->id,
            'doi' => '10.1234/secure-document',
            'language_id' => $language->id,
            'publish_year' => 2026,
            'publish_date' => '2026-04',
            'pages_number' => 42,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'specialization_id' => $specialization->id,
            'author_ids' => [$author->id],
            'contributors' => [['id' => $contributor->id, 'role' => 'Reviewer']],
            'country_id' => $country->id,
        ];

        return [$admin, $payload, compact(
            'source',
            'magazine',
            'documentType',
            'language',
            'category',
            'subcategory',
            'specialization',
            'country',
            'author',
            'contributor',
        )];
    }
}
