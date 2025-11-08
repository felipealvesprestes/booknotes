<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notebook extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'title',
        'description',
    ];

    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }
}
