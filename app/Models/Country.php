<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    public function contributors(): HasMany
    {
        return $this->hasMany(Contributor::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
