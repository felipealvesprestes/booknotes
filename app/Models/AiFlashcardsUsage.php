<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;

class AiFlashcardsUsage extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'date',
        'generated_count',
    ];

    protected $casts = [
        'date' => 'date',
        'generated_count' => 'int',
    ];
}
