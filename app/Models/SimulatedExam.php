<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulatedExam extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'scope_type',
        'notebook_id',
        'discipline_id',
        'question_count',
        'answered_count',
        'correct_count',
        'incorrect_count',
        'score',
        'duration_seconds',
        'status',
        'questions',
        'metadata',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function getAccuracyAttribute(): int
    {
        if ($this->question_count === 0) {
            return 0;
        }

        return (int) round(($this->correct_count / max(1, $this->question_count)) * 100);
    }
}
