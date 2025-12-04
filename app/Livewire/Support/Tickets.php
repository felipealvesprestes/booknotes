<?php

namespace App\Livewire\Support;

use App\Models\SupportTicket;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Tickets extends Component
{
    public string $statusFilter = SupportTicket::STATUS_OPEN;

    public ?int $selectedTicketId = null;

    public string $subject = '';

    public string $category = '';

    public string $message = '';

    public string $replyMessage = '';

    public array $categorySuggestions = [];

    public ?string $flashMessage = null;

    protected $queryString = [
        'statusFilter' => ['except' => SupportTicket::STATUS_OPEN],
        'selectedTicketId' => ['except' => null],
    ];

    public function mount(): void
    {
        $this->categorySuggestions = [
            __('Payments or billing'),
            __('Technical issue'),
            __('Feature request'),
            __('Study workflow question'),
        ];

        $this->statusFilter = $this->normalizeStatus($this->statusFilter);

        $this->ensureActiveStatusMatchesTicket();
    }

    public function updatedStatusFilter($value): void
    {
        $this->statusFilter = $this->normalizeStatus($value);
        $this->selectedTicketId = $this->firstTicketIdForCurrentFilter();
    }

    public function updatedSelectedTicketId($ticketId): void
    {
        if (! $ticketId) {
            $this->selectedTicketId = null;

            return;
        }

        $ticket = SupportTicket::query()->find($ticketId);

        if (! $ticket) {
            $this->selectedTicketId = $this->firstTicketIdForCurrentFilter();
        }
    }

    public function selectTicket(int $ticketId): void
    {
        $ticket = SupportTicket::query()->find($ticketId);

        if (! $ticket) {
            return;
        }

        $this->selectedTicketId = $ticket->id;
    }

    public function createTicket(): void
    {
        $validated = $this->validate([
            'subject' => ['required', 'string', 'min:8', 'max:160'],
            'category' => ['nullable', 'string', 'max:80'],
            'message' => ['required', 'string', 'min:20'],
        ]);

        $ticket = SupportTicket::query()->create([
            'subject' => trim($validated['subject']),
            'category' => $validated['category'] ? trim($validated['category']) : null,
            'status' => SupportTicket::STATUS_OPEN,
            'last_message_at' => now(),
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'author_type' => 'user',
            'message' => trim($validated['message']),
        ]);

        $this->reset(['subject', 'category', 'message']);
        $this->statusFilter = SupportTicket::STATUS_OPEN;
        $this->selectedTicketId = $ticket->id;

        $this->notify(__('Ticket sent successfully. We will keep you updated here.'));
    }

    public function sendReply(): void
    {
        $ticket = $this->selectedTicket;

        if (! $ticket) {
            return;
        }

        $this->validate([
            'replyMessage' => ['required', 'string', 'min:4'],
        ]);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'author_type' => 'user',
            'message' => trim($this->replyMessage),
        ]);

        $this->replyMessage = '';

        $ticket->refresh();
        $ticket->syncStatusFromMessage($ticket->messages()->orderByDesc('id')->first());
        $this->statusFilter = $ticket->status;

        $this->notify(__('Your update was sent to support.'));
    }

    public function closeTicket(): void
    {
        $ticket = $this->selectedTicket;

        if (! $ticket) {
            return;
        }

        $ticket->forceFill([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ])->save();

        $this->statusFilter = SupportTicket::STATUS_RESOLVED;
        $this->selectedTicketId = $ticket->id;

        $this->notify(__('Ticket marked as resolved.'));
    }

    public function reopenTicket(): void
    {
        $ticket = $this->selectedTicket;

        if (! $ticket) {
            return;
        }

        $ticket->forceFill([
            'status' => SupportTicket::STATUS_OPEN,
            'resolved_at' => null,
        ])->save();

        $this->statusFilter = SupportTicket::STATUS_OPEN;
        $this->selectedTicketId = $ticket->id;

        $this->notify(__('Ticket reopened and waiting for support.'));
    }

    public function useCategorySuggestion(string $suggestion): void
    {
        $this->category = $suggestion;
    }

    public function getTicketsProperty(): Collection
    {
        return $this->ticketsQuery()->get();
    }

    public function getStatusCountsProperty(): array
    {
        $counts = SupportTicket::query()
            ->whereHas('messages')
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $totals = [];
        foreach ($this->statusKeys() as $status) {
            $totals[$status] = $counts[$status] ?? 0;
        }
        $totals['all'] = array_sum($counts);

        return $totals;
    }

    public function getStatusTabsProperty(): array
    {
        $base = SupportTicket::statusMetadata();

        $tabs = [];
        $tabs[SupportTicket::STATUS_OPEN] = [
            'label' => $base[SupportTicket::STATUS_OPEN]['label'],
            'description' => __('New or ongoing questions waiting for our reply.'),
        ];
        $tabs[SupportTicket::STATUS_WAITING_USER] = [
            'label' => $base[SupportTicket::STATUS_WAITING_USER]['label'],
            'description' => __('We answered and are waiting for your confirmation.'),
        ];
        $tabs[SupportTicket::STATUS_RESOLVED] = [
            'label' => $base[SupportTicket::STATUS_RESOLVED]['label'],
            'description' => __('Closed tickets stay here for future reference.'),
        ];
        $tabs['all'] = [
            'label' => __('All tickets'),
            'description' => __('View every conversation regardless of status.'),
        ];

        return $tabs;
    }

    public function getSelectedTicketProperty(): ?SupportTicket
    {
        if (! $this->selectedTicketId) {
            return null;
        }

        $ticket = SupportTicket::query()
            ->whereHas('messages')
            ->with(['messages' => function ($query) {
                $query->orderByDesc('id');
            }])
            ->find($this->selectedTicketId);

        if (! $ticket) {
            $this->selectedTicketId = null;

            return null;
        }

        $ticket->syncStatusFromMessage($ticket->messages->first());
        $this->statusFilter = $ticket->status;

        return $ticket;
    }

    protected function ticketsQuery()
    {
        $query = SupportTicket::query()
            ->whereHas('messages')
            ->with('latestMessage')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    protected function firstTicketIdForCurrentFilter(): ?int
    {
        $ticket = $this->ticketsQuery()->first();

        return $ticket?->id;
    }

    protected function ensureActiveStatusMatchesTicket(): void
    {
        if (! $this->selectedTicketId) {
            $ticket = $this->ticketsQuery()->first();

            if ($ticket) {
                $this->selectedTicketId = $ticket->id;
                $this->statusFilter = $ticket->status;
            }

            return;
        }

        $currentTicket = SupportTicket::query()->find($this->selectedTicketId);

        if (! $currentTicket) {
            $ticket = $this->ticketsQuery()->first();
            $this->selectedTicketId = $ticket?->id;
            $this->statusFilter = $ticket?->status ?? SupportTicket::STATUS_OPEN;

            return;
        }

        if ($currentTicket->status !== $this->statusFilter && $this->statusFilter !== 'all') {
            $this->statusFilter = $currentTicket->status;
        }
    }

    protected function statusKeys(): array
    {
        return array_keys(SupportTicket::statusMetadata());
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = null;
    }

    protected function normalizeStatus(?string $status): string
    {
        $allowed = array_merge($this->statusKeys(), ['all']);

        if (! $status || ! in_array($status, $allowed, true)) {
            return SupportTicket::STATUS_OPEN;
        }

        return $status;
    }

    protected function notify(string $message): void
    {
        $this->flashMessage = $message;
    }

    public function render(): View
    {
        $this->synchronizeTicketStatuses();

        return view('livewire.support.tickets', [
            'tickets' => $this->tickets,
            'selectedTicket' => $this->selectedTicket,
            'statusTabs' => $this->statusTabs,
            'statusCounts' => $this->statusCounts,
            'categorySuggestions' => $this->categorySuggestions,
            'statusMeta' => SupportTicket::statusMetadata(),
        ])->layout('layouts.app', [
            'title' => __('Support'),
        ]);
    }

    protected function synchronizeTicketStatuses(): void
    {
        SupportTicket::query()
            ->with('latestMessage')
            ->get()
            ->each(function (SupportTicket $ticket) {
                if ($ticket->latestMessage) {
                    $ticket->syncStatusFromMessage($ticket->latestMessage);
                } else {
                    $ticket->forceFill([
                        'status' => SupportTicket::STATUS_OPEN,
                        'last_message_at' => $ticket->created_at,
                    ])->saveQuietly();
                }
            });
    }
}
