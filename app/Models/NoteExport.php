<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NoteExport extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'note_count',
        'flashcard_count',
        'status',
        'filters',
        'failure_reason',
        'finished_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest();
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_COMPLETED && $this->file_path;
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Queued'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_COMPLETED => __('Ready'),
            self::STATUS_FAILED => __('Failed'),
            default => Str::title(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'muted',
        };
    }

    public function downloadRoute(): ?string
    {
        return $this->isReady()
            ? route('notes.export.download', $this)
            : null;
    }
}
