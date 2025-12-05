<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Note extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

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

    public function getWordCountAttribute(): int
    {
        $text = $this->cleanContent();

        if ($text === '') {
            return 0;
        }

        return count(preg_split('/\\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    public function getCharCountAttribute(): int
    {
        $text = $this->cleanContent();

        if ($text === '') {
            return 0;
        }

        return mb_strlen($text);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = $this->word_count;

        if ($wordCount === 0) {
            return 0;
        }

        return max(1, (int) ceil($wordCount / 200));
    }

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

        $ownerId = $this->getAttribute($this->getUserForeignKey()) ?? Auth::id();

        if (! $ownerId) {
            $this->tags()->detach();

            return;
        }

        $lowered = $normalized->map(fn (string $tag) => mb_strtolower($tag));

        $existing = Tag::query()
            ->withoutGlobalScopes()
            ->where('user_id', $ownerId)
            ->whereIn(DB::raw('LOWER(name)'), $lowered->all())
            ->get()
            ->keyBy(fn (Tag $tag) => mb_strtolower($tag->name));

        $tagIds = [];

        foreach ($normalized as $tagName) {
            $lower = mb_strtolower($tagName);

            if (! $existing->has($lower)) {
                $match = $this->findExistingTagForOwner($ownerId, $tagName, $lower)
                    ?? $this->createTagForOwner($ownerId, $tagName, $lower);

                $existing->put($lower, $match);
            }

            $tagIds[] = $existing->get($lower)->id;
        }

        $this->tags()->sync($tagIds);
    }

    protected function cleanContent(): string
    {
        $content = (string) $this->content;
        $stripped = strip_tags($content);
        $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizedWhitespace = preg_replace('/\\s+/u', ' ', $decoded);

        return trim($normalizedWhitespace);
    }

    protected function findExistingTagForOwner(int $ownerId, string $tagName, string $lower): ?Tag
    {
        return Tag::query()
            ->withoutGlobalScopes()
            ->where('user_id', $ownerId)
            ->where(function ($query) use ($tagName, $lower): void {
                $query->where('name', $tagName)
                    ->orWhereRaw('LOWER(name) = ?', [$lower]);
            })
            ->first();
    }

    protected function createTagForOwner(int $ownerId, string $tagName, string $lower): Tag
    {
        try {
            return Tag::create([
                'name' => $tagName,
                'user_id' => $ownerId,
            ]);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateTagConstraint($exception)) {
                throw $exception;
            }

            $existing = $this->findExistingTagForOwner($ownerId, $tagName, $lower);

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    protected function isDuplicateTagConstraint(QueryException $exception): bool
    {
        $errorCode = (int) ($exception->errorInfo[1] ?? 0);

        return $errorCode === 1062
            && str_contains($exception->getMessage(), 'tags_user_id_name_unique');
    }
}
