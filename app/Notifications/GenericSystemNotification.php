<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GenericSystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $tag = 'Onboarding',
        protected array $meta = [],
        protected string $level = 'info',
        protected ?string $actionUrl = null,
        protected ?string $actionLabel = null,
        protected ?string $icon = null,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tag' => $this->tag,
            'meta' => $this->meta,
            'level' => $this->level,
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl ?: null,
            'action_label' => $this->actionLabel ?: null,
            'icon' => $this->icon,
        ];
    }
}
