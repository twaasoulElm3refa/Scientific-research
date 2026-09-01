<?php

namespace App\Repository;

use App\Models\categories;
use App\interface\categoreyInterface;

class CategoreyRepository implements categoreyInterface
{
    public function allCategories()
    {
        return categories::all();
    }

    public function showCategory($id)
    {
        return categories::with('book')->findOrFail($id);
    }

    public function createCategorey($request)
    {
        return categories::create($request->all());
    }

    public function updateCategory($request, $id)
    {
        return categories::findOrFail($id)->update($request->all());
    }

    public function deleteCategory($id)
    {
        return categories::find($id)->delete();
    }
}
