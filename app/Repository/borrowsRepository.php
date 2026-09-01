<?php

namespace App\Repository;

use App\interface\borrowsInterface;
use App\Models\Borrows;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class borrowsRepository implements borrowsInterface
{
    public function allBorrows()
    {
        return Borrows::paginate(10);
    }

    public function singleBorrow($id)
    {
        return Borrows::findOrFail($id);
    }

    public function store($id)
    {
        return Borrows::create(
            [
                'user_id' => Auth::user()->id,
                'books_id' => $id,
                'back_date' => Carbon::now()->addDays(14)
            ]
        );
    }

    public function destroy($id)
    {
        return Borrows::find($id)->delete();
    }

}
