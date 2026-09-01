<?php

namespace Database\Seeders;

use App\Models\Source;
use Database\Seeders\Concerns\SeedsLookupNames;
use Database\Seeders\Data\AcademicPublishingData;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        foreach (AcademicPublishingData::sources() as $sourceName) {
            $this->seedLookup(Source::class, $sourceName);
        }
    }
}
