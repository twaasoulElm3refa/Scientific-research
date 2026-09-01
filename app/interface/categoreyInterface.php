<?php

namespace App\interface;

interface categoreyInterface
{
    public function allCategories();
    public function createCategorey($request);
    public function showCategory($id);
    public function updateCategory($request,$id);
    public function deleteCategory($id);
}
