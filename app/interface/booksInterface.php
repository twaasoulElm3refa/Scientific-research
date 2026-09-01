<?php

namespace App\interface;

interface BooksInterface
{
    public function allBooks();
    public function createBook($request);
    public function singleBook($id);
    public function updateBook($request,$id);
    public function deleteBook($id);
}
