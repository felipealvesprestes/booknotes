<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardSession extends Model
{
    use BelongsToAuthenticatedUser;

    protected $fillable = [
        'status',
        'total_cards',
        'current_index',
        'correct_count',
        'incorrect_count',
        'accuracy',
        'note_ids',
        'studied_at',
        'completed_at',
        'discipline_id',
    ];

    protected $casts = [
        'note_ids' => 'array',
        'studied_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function accuracyPercentage(): Attribute
    {
        return Attribute::get(fn () => $this->accuracy ?? 0);
    }

    public function totalReviewed(): Attribute
    {
        return Attribute::get(fn () => $this->correct_count + $this->incorrect_count);
    }

    public function hasPendingCards(): bool
    {
        $queueSize = is_array($this->note_ids) ? count($this->note_ids) : 0;

        return $this->status === 'active' && $queueSize > $this->current_index;
    }

    public function currentNoteId(): ?int
    {
        $noteIds = $this->note_ids ?? [];

        return $noteIds[$this->current_index] ?? null;
    }

    public function recordAnswer(bool $isCorrect): void
    {
        $noteIds = $this->note_ids ?? [];
        $currentIndex = $this->current_index ?? 0;

        if (! isset($noteIds[$currentIndex])) {
            return;
        }

        $currentNoteId = $noteIds[$currentIndex];

        if ($isCorrect) {
            $this->correct_count++;
        } else {
            $this->incorrect_count++;
            $noteIds[] = $currentNoteId;
        }

        $this->current_index++;
        $this->note_ids = array_values($noteIds);

        $answered = max(1, $this->correct_count + $this->incorrect_count);
        $this->accuracy = (int) round(($this->correct_count / $answered) * 100);

        if ($this->current_index >= count($this->note_ids ?? [])) {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $this->save();
    }
}
