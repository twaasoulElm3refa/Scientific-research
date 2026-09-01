<?php

namespace Database\Seeders;

use App\Models\Magazine;
use App\Models\Source;
use App\Support\LookupName;
use Database\Seeders\Concerns\SeedsLookupNames;
use Database\Seeders\Data\AcademicPublishingData;
use Illuminate\Database\Seeder;

class MagazineSeeder extends Seeder
{
    use SeedsLookupNames;

    public function run(): void
    {
        foreach (AcademicPublishingData::magazinesBySource() as $sourceName => $magazines) {
            $source = Source::query()
                ->whereRaw('LOWER(name) = ?', [LookupName::comparable($sourceName)])
                ->firstOrFail();

            foreach ($magazines as $magazineName) {
                $existing = Magazine::query()
                    ->whereRaw('LOWER(name) = ?', [LookupName::comparable($magazineName)])
                    ->where(function ($query) use ($source) {
                        $query->whereNull('source_id')->orWhere('source_id', $source->id);
                    })
                    ->first();

                if ($existing) {
                    $existing->forceFill([
                        'name' => LookupName::clean($magazineName),
                        'source_id' => $source->id,
                        'is_active' => true,
                    ])->save();

                    continue;
                }

                $this->seedLookup(Magazine::class, $magazineName, ['source_id' => $source->id]);
            }
        }

        foreach (AcademicPublishingData::independentMagazines() as $magazineName) {
            $this->seedLookup(Magazine::class, $magazineName, ['source_id' => null]);
        }
    }
}
