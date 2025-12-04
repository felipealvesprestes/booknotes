<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'author_type',
        'author_name',
        'message',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $message): void {
            if ($message->author_type === 'user' && $message->user && ! $message->author_name) {
                $message->author_name = $message->user->name;
            }
        });

        static::created(function (self $message): void {
            $status = $message->isFromTeam()
                ? SupportTicket::STATUS_WAITING_USER
                : SupportTicket::STATUS_OPEN;

            SupportTicket::withoutGlobalScopes()
                ->whereKey($message->support_ticket_id)
                ->update([
                    'last_message_at' => $message->created_at,
                    'status' => $status,
                ]);
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFromTeam(): bool
    {
        return $this->author_type === 'team';
    }

    public function isFromUser(): bool
    {
        return $this->author_type === 'user';
    }
}
