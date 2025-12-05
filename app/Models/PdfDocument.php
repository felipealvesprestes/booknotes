<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class PdfDocument extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    protected $fillable = [
        'title',
        'original_name',
        'path',
        'size',
        'last_opened_at',
    ];

    protected $casts = [
        'last_opened_at' => 'datetime',
    ];

    /**
     * Human readable file size helper.
     */
    public function getReadableSizeAttribute(): string
    {
        return Number::fileSize($this->size ?? 0);
    }
}
