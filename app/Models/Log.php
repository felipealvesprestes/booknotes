<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'action',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
