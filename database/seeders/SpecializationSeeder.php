<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Specialization;
use App\Models\Subcategory;
use App\Support\LookupName;
use Database\Seeders\Concerns\SeedsLookupNames;
use Database\Seeders\Data\AcademicTaxonomy;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        foreach (AcademicTaxonomy::all() as $categoryName => $subcategories) {
            $category = Category::query()
                ->whereRaw('LOWER(name) = ?', [LookupName::comparable($categoryName)])
                ->firstOrFail();

            foreach ($subcategories as $subcategoryName => $specializations) {
                $subcategory = Subcategory::query()
                    ->where('category_id', $category->id)
                    ->whereRaw('LOWER(name) = ?', [LookupName::comparable($subcategoryName)])
                    ->firstOrFail();

                foreach ($specializations as $specializationName) {
                    $this->seedLookup(Specialization::class, $specializationName, [
                        'subcategory_id' => $subcategory->id,
                    ]);
                }
            }
        }
    }
}
