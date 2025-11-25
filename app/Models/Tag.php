<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'name',
        'user_id',
    ];

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class)->withTimestamps();
    }
}
