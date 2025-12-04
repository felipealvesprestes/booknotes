<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public int $unreadPerPage = 6;

    public int $historyPerPage = 10;

    protected $listeners = [
        'notification-received' => '$refresh',
    ];

    public function paginationView(): string
    {
        return 'livewire.study.pagination';
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        $this->resetPage('unreadPage');
        $this->dispatch('notification-center-updated');
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = $this->findNotification($notificationId);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $this->dispatch('notification-center-updated');
    }

    public function markAsUnread(string $notificationId): void
    {
        $notification = $this->findNotification($notificationId);

        if (! $notification) {
            return;
        }

        $notification->markAsUnread();

        $this->resetPage('unreadPage');

        $this->dispatch('notification-center-updated');
    }

    protected function findNotification(string $notificationId): ?DatabaseNotification
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->notifications()->whereKey($notificationId)->first();
    }

    public function getUnreadNotificationsProperty(): LengthAwarePaginator
    {
        return $this->formatNotifications(
            auth()->user()
                ->notifications()
                ->whereNull('read_at')
                ->latest()
                ->paginate($this->unreadPerPage, ['*'], 'unreadPage')
        );
    }

    public function getReadNotificationsProperty(): LengthAwarePaginator
    {
        return $this->formatNotifications(
            auth()->user()
                ->notifications()
                ->whereNotNull('read_at')
                ->latest()
                ->paginate($this->historyPerPage, ['*'], 'readPage')
        );
    }

    public function render(): View
    {
        return view('livewire.notifications.index', [
            'unreadNotifications' => $this->unreadNotifications,
            'readNotifications' => $this->readNotifications,
        ])->layout('layouts.app', [
            'title' => __('Notifications'),
        ]);
    }

    protected function formatNotifications(LengthAwarePaginator $notifications): LengthAwarePaginator
    {
        $notifications->setCollection(
            $notifications->getCollection()->map(fn (DatabaseNotification $notification) => $this->transformNotification($notification))
        );

        return $notifications;
    }

    protected function transformNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];
        $level = $data['level'] ?? 'info';
        $styles = $this->toneStyles($level);

        $timestamp = $notification->created_at;

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? __('System update'),
            'message' => $data['message'] ?? $data['body'] ?? null,
            'tag' => $data['tag'] ?? $data['category'] ?? $this->labelForLevel($level),
            'level' => $level,
            'icon' => $data['icon'] ?? $styles['icon'],
            'icon_classes' => $styles['icon_classes'],
            'tag_classes' => $styles['tag_classes'],
            'action' => $this->actionData($data),
            'meta' => $this->metaData($data),
            'timestamp' => $timestamp ? $timestamp->translatedFormat('d M Y \\a\\t H:i') : null,
            'ago' => $timestamp ? $timestamp->diffForHumans() : null,
            'read' => $notification->read(),
        ];
    }

    protected function actionData(array $data): ?array
    {
        if (empty($data['action_url']) || empty($data['action_label'])) {
            return null;
        }

        return [
            'label' => $data['action_label'],
            'url' => $data['action_url'],
            'target' => $data['action_target'] ?? '_self',
        ];
    }

    protected function metaData(array $data): array
    {
        $meta = $data['meta'] ?? [];

        if (! is_array($meta)) {
            return [];
        }

        return collect($meta)
            ->map(function ($value, $label) {
                return [
                    'label' => (string) $label,
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                ];
            })
            ->values()
            ->all();
    }

    protected function toneStyles(string $level): array
    {
        $tones = [
            'success' => [
                'icon' => 'check-badge',
                'icon_classes' => 'bg-emerald-50 text-emerald-600',
                'tag_classes' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
            'warning' => [
                'icon' => 'exclamation-triangle',
                'icon_classes' => 'bg-amber-50 text-amber-600',
                'tag_classes' => 'border border-amber-200 bg-amber-50 text-amber-700',
            ],
            'danger' => [
                'icon' => 'exclamation-circle',
                'icon_classes' => 'bg-rose-50 text-rose-600',
                'tag_classes' => 'border border-rose-200 bg-rose-50 text-rose-700',
            ],
            'info' => [
                'icon' => 'bell-alert',
                'icon_classes' => 'bg-indigo-50 text-indigo-600',
                'tag_classes' => 'border border-indigo-200 bg-indigo-50 text-indigo-700',
            ],
        ];

        return $tones[$level] ?? $tones['info'];
    }

    protected function labelForLevel(string $level): string
    {
        return match ($level) {
            'success' => __('Success'),
            'warning' => __('Reminder'),
            'danger' => __('Attention'),
            default => __('Update'),
        };
    }
}
