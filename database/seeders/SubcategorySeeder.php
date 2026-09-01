<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use App\Support\LookupName;
use Database\Seeders\Concerns\SeedsLookupNames;
use Database\Seeders\Data\AcademicTaxonomy;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        foreach (AcademicTaxonomy::all() as $categoryName => $subcategories) {
            $category = Category::query()
                ->whereRaw('LOWER(name) = ?', [LookupName::comparable($categoryName)])
                ->firstOrFail();

            foreach (array_keys($subcategories) as $subcategoryName) {
                $this->seedLookup(Subcategory::class, $subcategoryName, [
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
