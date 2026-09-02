<?php

namespace Database\Seeders\Concerns;

use App\Support\LookupName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait SeedsLookupNames
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $scope
     */
    protected function seedLookup(string $modelClass, string $name, array $scope = []): Model
    {
        $name = LookupName::clean($name);
        $query = $modelClass::query()->where($scope);
        $lookup = (clone $query)
            ->whereRaw('LOWER(name) = ?', [LookupName::comparable($name)])
            ->first();

        if (! $lookup && Str::contains($name, ' - ')) {
            $originalName = LookupName::clean(Str::before($name, ' - '));
            $lookup = (clone $query)
                ->whereRaw('LOWER(name) = ?', [LookupName::comparable($originalName)])
                ->first();
        }

        if ($lookup) {
            $lookup->forceFill($scope + ['name' => $name, 'is_active' => true])->save();

            return $lookup;
        }

        return $modelClass::query()->create($scope + ['name' => $name, 'is_active' => true]);
    }
}
