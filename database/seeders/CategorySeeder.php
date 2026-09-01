<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\SeedsLookupNames;
use Database\Seeders\Data\AcademicTaxonomy;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        foreach (array_keys(AcademicTaxonomy::all()) as $categoryName) {
            $this->seedLookup(Category::class, $categoryName);
        }
    }
}
