<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'title',
        'content',
        'is_flashcard',
        'flashcard_question',
        'flashcard_answer',
        'discipline_id',
    ];

    protected $casts = [
        'is_flashcard' => 'bool',
    ];

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }
}
