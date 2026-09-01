<?php

namespace App\Repository;

use App\interface\BooksInterface;
use App\Models\books;
use Illuminate\Support\Facades\Auth;

class bookRepository implements BooksInterface
{
    public function allBooks()
    {
        return books::all();
    }

    public function singleBook($id)
    {
        return books::findOrFail($id);
    }

    public function createBook($request)
    {
        return books::create([
            'title' => $request['title'],
            'image' => $request['image'],
            'description' => $request['description'],
            'author' => $request['author'],
            'price' => $request['price'],
            'categories_id' => 1,
            'user_id' => Auth::user()->id,
        ]);
    }

    public function updateBook($request, $id)
    {
        return books::findOrFail($id)->update($request->all());
    }

    public function deleteBook($id)
    {
        return books::findOrFail($id)->delete();
    }

}
