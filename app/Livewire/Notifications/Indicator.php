<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Indicator extends Component
{
    protected $listeners = [
        'notification-center-updated' => '$refresh',
        'notification-received' => '$refresh',
    ];

    public function render(): View
    {
        return view('livewire.notifications.indicator', [
            'hasUnread' => $this->hasUnreadNotifications(),
        ]);
    }

    protected function hasUnreadNotifications(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->unreadNotifications()->exists();
    }
}
