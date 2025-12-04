<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAuthenticatedUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use BelongsToAuthenticatedUser;
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_WAITING_USER = 'waiting_user';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'reference',
        'subject',
        'category',
        'status',
        'last_message_at',
        'resolved_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ticket): void {
            if (! $ticket->reference) {
                $ticket->reference = static::generateReference();
            }

            if (! $ticket->status) {
                $ticket->status = self::STATUS_OPEN;
            }
        });
    }

    public static function generateReference(): string
    {
        return 'SUP-' . Str::upper(Str::random(8));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)
            ->orderByDesc('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportTicketMessage::class)->latestOfMany();
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => __('Open'),
            self::STATUS_WAITING_USER => __('Waiting for you'),
            self::STATUS_RESOLVED => __('Resolved'),
            default => ucfirst($this->status),
        };
    }

    /**
     * UI metadata for each status.
     *
     * @return array<string, array<string, string>>
     */
    public static function statusMetadata(): array
    {
        return [
            self::STATUS_OPEN => [
                'label' => __('Waiting for support'),
                'dot' => 'bg-indigo-500',
                'badge' => 'border border-indigo-200 bg-indigo-50 text-indigo-700',
            ],
            self::STATUS_WAITING_USER => [
                'label' => __('Waiting for you'),
                'dot' => 'bg-amber-500',
                'badge' => 'border border-amber-200 bg-amber-50 text-amber-700',
            ],
            self::STATUS_RESOLVED => [
                'label' => __('Resolved'),
                'dot' => 'bg-emerald-500',
                'badge' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
        ];
    }

    public function syncStatusFromMessage(?SupportTicketMessage $latestMessage = null): void
    {
        if ($this->status === self::STATUS_RESOLVED && $this->last_message_at) {
            return;
        }

        $latest = $latestMessage ?? $this->latestMessage()->first();

        if (! $latest) {
            return;
        }

        $expectedStatus = $latest->isFromTeam()
            ? self::STATUS_WAITING_USER
            : self::STATUS_OPEN;

        $updates = [];

        if ($this->status !== self::STATUS_RESOLVED && $this->status !== $expectedStatus) {
            $updates['status'] = $expectedStatus;
            $this->status = $expectedStatus;
        }

        if (! $this->last_message_at || ! $this->last_message_at->equalTo($latest->created_at)) {
            $updates['last_message_at'] = $latest->created_at;
            $this->last_message_at = $latest->created_at;
        }

        if (! empty($updates)) {
            $this->forceFill($updates)->saveQuietly();
        }
    }
}
