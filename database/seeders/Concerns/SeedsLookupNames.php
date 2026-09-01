<?php

namespace Database\Seeders\Concerns;

use App\Support\LookupName;
use Illuminate\Database\Eloquent\Model;

trait SeedsLookupNames
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $scope
     */
    protected function seedLookup(string $modelClass, string $name, array $scope = []): Model
    {
        $name = LookupName::clean($name);
        $lookup = $modelClass::query()
            ->where($scope)
            ->whereRaw('LOWER(name) = ?', [LookupName::comparable($name)])
            ->first();

        if ($lookup) {
            $lookup->forceFill($scope + ['name' => $name, 'is_active' => true])->save();

            return $lookup;
        }

        return $modelClass::query()->create($scope + ['name' => $name, 'is_active' => true]);
    }
}
