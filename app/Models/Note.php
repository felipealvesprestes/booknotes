<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Syncs the note tags, creating missing tags for the owner.
     */
    public function syncTags(array $tagNames): void
    {
        $normalized = collect($tagNames)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique(fn ($tag) => mb_strtolower($tag))
            ->values();

        if ($normalized->isEmpty()) {
            $this->tags()->detach();

            return;
        }

        $user = Auth::user();

        $existing = Tag::query()
            ->ownedBy($user)
            ->whereIn('name', $normalized)
            ->get()
            ->keyBy(fn (Tag $tag) => mb_strtolower($tag->name));

        $tagIds = $normalized->map(function (string $tagName) use ($existing, $user): int {
            $lower = mb_strtolower($tagName);

            if ($existing->has($lower)) {
                return $existing->get($lower)->id;
            }

            $created = Tag::create([
                'name' => $tagName,
                'user_id' => $user?->id,
            ]);

            return $created->id;
        });

        $this->tags()->sync($tagIds->all());
    }
}
