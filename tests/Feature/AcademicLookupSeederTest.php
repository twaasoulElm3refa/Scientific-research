<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Magazine;
use App\Models\Source;
use App\Models\Specialization;
use App\Models\Subcategory;
use Database\Seeders\CategorySeeder;
use Database\Seeders\Data\AcademicPublishingData;
use Database\Seeders\Data\AcademicTaxonomy;
use Database\Seeders\MagazineSeeder;
use Database\Seeders\SourceSeeder;
use Database\Seeders\SpecializationSeeder;
use Database\Seeders\SubcategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicLookupSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_lookup_seeders_are_comprehensive_and_idempotent(): void
    {
        $seeders = [
            CategorySeeder::class,
            SubcategorySeeder::class,
            SpecializationSeeder::class,
            SourceSeeder::class,
            MagazineSeeder::class,
        ];

        $this->seed($seeders);
        $expected = $this->expectedCounts();

        $this->assertDatabaseCount('categories', $expected['categories']);
        $this->assertDatabaseCount('subcategories', $expected['subcategories']);
        $this->assertDatabaseCount('specializations', $expected['specializations']);
        $this->assertDatabaseCount('sources', $expected['sources']);
        $this->assertDatabaseCount('magazines', $expected['magazines']);

        $this->seed($seeders);

        $this->assertSame($expected['categories'], Category::count());
        $this->assertSame($expected['subcategories'], Subcategory::count());
        $this->assertSame($expected['specializations'], Specialization::count());
        $this->assertSame($expected['sources'], Source::count());
        $this->assertSame($expected['magazines'], Magazine::count());

        $journal = Magazine::query()->where('name', 'Nature Communications')->firstOrFail();
        $this->assertSame('Nature Portfolio', $journal->source->name);
        $this->assertDatabaseHas('specializations', ['name' => 'الإعلام والذكاء الاصطناعي']);
    }

    /** @return array<string, int> */
    private function expectedCounts(): array
    {
        $taxonomy = AcademicTaxonomy::all();
        $subcategoryCount = 0;
        $specializationCount = 0;

        foreach ($taxonomy as $subcategories) {
            $subcategoryCount += count($subcategories);
            foreach ($subcategories as $specializations) {
                $specializationCount += count($specializations);
            }
        }

        $magazineCount = count(AcademicPublishingData::independentMagazines());
        foreach (AcademicPublishingData::magazinesBySource() as $magazines) {
            $magazineCount += count($magazines);
        }

        return [
            'categories' => count($taxonomy),
            'subcategories' => $subcategoryCount,
            'specializations' => $specializationCount,
            'sources' => count(AcademicPublishingData::sources()),
            'magazines' => $magazineCount,
        ];
    }
}
