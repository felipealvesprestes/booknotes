<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notebook extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
    ];

    public function disciplines(): HasMany
    {
        return $this->hasMany(Discipline::class);
    }
}
