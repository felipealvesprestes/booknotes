<div class="space-y-8 w-full">
    <header class="space-y-2">
        <h1 class="text-2xl font-semibold text-zinc-900">
            {{ __('Support center') }}
        </h1>
        <p class="text-sm text-zinc-500 max-w-3xl leading-relaxed">
            {{ __('If something is not working or you have a question, open a ticket and we will help you step by step.') }}
        </p>

        <div class="mt-2 inline-flex flex-wrap items-center gap-3 rounded-full border border-zinc-200 bg-zinc-50 px-4 py-2 text-xs text-zinc-600">
            <span class="font-semibold uppercase tracking-wide text-zinc-500">
                {{ __('How it works') }}:
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full border border-zinc-300 text-[11px] font-semibold text-zinc-600">1</span>
                {{ __('You send a ticket') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full border border-zinc-300 text-[11px] font-semibold text-zinc-600">2</span>
                {{ __('We answer with clear instructions') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span class="flex h-5 w-5 items-center justify-center rounded-full border border-zinc-300 text-[11px] font-semibold text-zinc-600">3</span>
                {{ __('You follow everything here') }}
            </span>
        </div>
    </header>

    @if ($flashMessage)
    <div class="flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
        <flux:icon.check-badge class="h-5 w-5 text-emerald-500" />
        <p class="flex-1 leading-relaxed">{{ $flashMessage }}</p>
        <button
            type="button"
            wire:click="dismissFlash"
            class="text-emerald-700 hover:text-emerald-900"
            aria-label="{{ __('Dismiss') }}"
        >
            <flux:icon.x-mark class="h-4 w-4" />
        </button>
    </div>
    @endif

    <section class="rounded-md border border-zinc-200 bg-white">
        <div class="border-b border-zinc-200 px-5 py-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">
                {{ __('Step 1') }}
            </p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">
                {{ __('Open a new support ticket') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Tell us what is happening so we can answer you with the next steps.') }}
            </p>
        </div>

        <form wire:submit.prevent="createTicket" class="px-5 py-5 space-y-4 md:space-y-0 md:grid md:grid-cols-[minmax(0,0.65fr)_minmax(0,0.35fr)] md:gap-4">
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-zinc-800" for="subject">
                    {{ __('Subject') }}
                </label>
                <flux:input
                    id="subject"
                    wire:model.defer="subject"
                    :placeholder="__('Example: Billing doubt about plan upgrade')"
                    class="w-full" />
                @error('subject')
                <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label class="text-sm font-medium text-zinc-800" for="category">
                    {{ __('Topic (optional)') }}
                </label>
                <x-select
                    id="category"
                    wire:model.defer="category"
                    :placeholder="__('Select a topic (optional)')"
                    class="w-full">
                    @foreach ($categorySuggestions as $suggestion)
                    <option value="{{ $suggestion }}">{{ $suggestion }}</option>
                    @endforeach
                </x-select>
                @error('category')
                <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="text-sm font-medium text-zinc-800" for="message">
                    {{ __('Message') }}
                </label>
                <flux:textarea
                    id="message"
                    wire:model.defer="message"
                    rows="5"
                    :placeholder="__('Explain what you tried, what you expected, and, if possible, mention notebook, discipline or note names.')" />
                @error('message')
                <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-zinc-100 pt-4">
                <flux:button
                    type="submit"
                    variant="primary"
                    wire:target="createTicket"
                    wire:loading.attr="disabled">
                    {{ __('Submit ticket') }}
                </flux:button>
                <p class="text-xs text-zinc-500">
                    {{ __('Average first reply time: less than one business day.') }}
                </p>
            </div>
        </form>
    </section>

    <section class="grid gap-6 items-start xl:grid-cols-[340px_minmax(0,1fr)]">
        <section class="rounded-md border border-zinc-200 bg-white flex flex-col h-full">
            <div class="border-b border-zinc-200 px-5 py-4 space-y-1">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">
                    {{ __('Step 2') }}
                </p>
                <h2 class="text-lg font-semibold text-zinc-900">
                    {{ __('Your tickets') }}
                </h2>
                <p class="text-sm text-zinc-500">
                    {{ __('Filter by status and select a ticket to see the details.') }}
                </p>
            </div>

            <div class="grid gap-3 border-b border-zinc-100 px-5 py-4">
                @foreach ($statusTabs as $status => $tab)
                    @php
                        $isActive = $statusFilter === $status;
                    @endphp
                    <label
                        @class([
                            'group relative flex cursor-pointer flex-col rounded-lg border px-4 py-3 text-sm transition-all hover:border-indigo-300',
                            'border-indigo-500 bg-indigo-50 text-indigo-900 shadow-[0_1px_0_rgba(79,70,229,0.15)]' => $isActive,
                            'border-zinc-200 text-zinc-600 hover:bg-zinc-50' => ! $isActive,
                        ])
                    >
                        <input
                            type="radio"
                            name="ticket_status_filter"
                            value="{{ $status }}"
                            class="hidden"
                            wire:model.live="statusFilter"
                        />
                        <div class="flex items-center justify-between">
                            <div class="{{ $isActive ? 'text-indigo-900' : 'text-zinc-800' }}">
                                <p class="text-sm font-semibold">{{ $tab['label'] }}</p>
                                <p class="mt-1 text-xs {{ $isActive ? 'text-indigo-900' : 'text-zinc-500' }}">
                                    {{ $tab['description'] }}
                                </p>
                            </div>
                            <span class="inline-flex h-6 min-w-[28px] items-center justify-center rounded-full border border-zinc-200 bg-white text-xs font-semibold text-zinc-600">
                                {{ $statusCounts[$status] ?? 0 }}
                            </span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div
                id="support-ticket-list"
                class="divide-y divide-zinc-100 flex-1 overflow-y-auto max-h-[420px]">
                @forelse ($tickets as $ticket)
                <button
                    type="button"
                    wire:click="selectTicket({{ $ticket->id }})"
                    wire:key="ticket-{{ $ticket->id }}"
                    @class([ 'w-full text-left px-5 py-4 space-y-2 transition' , 'bg-indigo-50'=> $selectedTicket && $selectedTicket->id === $ticket->id,
                    'hover:bg-zinc-50' => ! $selectedTicket || $selectedTicket->id !== $ticket->id,
                    ])
                    >
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-zinc-900">
                                {{ $ticket->subject }}
                            </p>
                            <span class="text-xs font-medium text-zinc-400">
                                #{{ $ticket->reference }}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit(optional($ticket->latestMessage)->message, 120) }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-medium text-zinc-500">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 {{ $statusMeta[$ticket->status]['badge'] ?? 'border border-zinc-200 text-zinc-600' }}">
                            <span class="h-2 w-2 rounded-full {{ $statusMeta[$ticket->status]['dot'] ?? 'bg-zinc-400' }}"></span>
                            {{ $ticket->statusLabel() }}
                        </span>
                        @if ($ticket->category)
                        <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-0.5 text-zinc-600">
                            {{ $ticket->category }}
                        </span>
                        @endif
                        @if ($ticket->last_message_at)
                        <span>{{ $ticket->last_message_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </button>
                @empty
                <div class="px-5 py-10 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400">
                        <flux:icon.inbox class="h-6 w-6" />
                    </div>
                    <h3 class="mt-3 text-base font-semibold text-zinc-800">
                        {{ __('No tickets yet') }}
                    </h3>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ __('Send your first ticket above and it will appear in this list.') }}
                    </p>
                </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-md border border-zinc-200 bg-white flex flex-col h-full" wire:key="ticket-detail">
            @if ($selectedTicket)
            <div class="border-b border-zinc-200 px-5 py-4">
                <div class="flex flex-wrap items-start gap-3">
                    <div class="space-y-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">
                            {{ __('Step 3') }}
                        </p>
                        <h2 class="text-lg font-semibold text-zinc-900">
                            {{ $selectedTicket->subject }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 pt-2 text-xs text-zinc-500">
                            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 font-medium {{ $statusMeta[$selectedTicket->status]['badge'] ?? 'border border-zinc-200 text-zinc-600' }}">
                                <span class="h-2 w-2 rounded-full {{ $statusMeta[$selectedTicket->status]['dot'] ?? 'bg-zinc-400' }}"></span>
                                {{ $selectedTicket->statusLabel() }}
                            </span>
                            @if ($selectedTicket->category)
                            <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 font-medium text-zinc-600">
                                {{ $selectedTicket->category }}
                            </span>
                            @endif
                            <span>
                                {{ __('Updated :date', ['date' => optional($selectedTicket->last_message_at)->diffForHumans()]) }}
                            </span>
                        </div>
                    </div>

                    <flux:spacer />

                    <div class="flex flex-wrap gap-2">
                        @if ($selectedTicket->status === \App\Models\SupportTicket::STATUS_RESOLVED)
                        <flux:button variant="ghost" size="sm" wire:click="reopenTicket" wire:loading.attr="disabled">
                            {{ __('Reopen ticket') }}
                        </flux:button>
                        @else
                        <flux:button variant="ghost" size="sm" wire:click="closeTicket" wire:loading.attr="disabled">
                            {{ __('Mark as resolved') }}
                        </flux:button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-4 px-5 py-5 overflow-y-auto max-h-[420px]">
                @foreach ($selectedTicket->messages as $message)
                <article class="rounded-md border border-zinc-200 bg-white px-4 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-zinc-900">
                            {{ $message->isFromTeam() ? ($message->author_name ?? __('Booknotes team')) : __('You') }}
                        </p>
                        @if ($message->isFromTeam())
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">
                            {{ __('Team') }}
                        </span>
                        @endif
                        <span class="text-xs text-zinc-500">
                            {{ $message->created_at->translatedFormat('d M Y \a\t H:i') }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-700 whitespace-pre-line">
                        {{ $message->message }}
                    </p>
                </article>
                @endforeach

                @if ($selectedTicket->status === \App\Models\SupportTicket::STATUS_RESOLVED)
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ __('This ticket is resolved. Reopen it if you want to add another update.') }}
                </div>
                @endif
            </div>

            @if ($selectedTicket->status !== \App\Models\SupportTicket::STATUS_RESOLVED)
            <div class="border-t border-zinc-200 bg-zinc-50 px-5 py-4">
                <form wire:submit.prevent="sendReply" class="space-y-3">
                    <label class="text-sm font-semibold text-zinc-700" for="reply-message">
                        {{ __('Reply to support') }}
                    </label>
                    <flux:textarea
                        id="reply-message"
                        wire:model.defer="replyMessage"
                        rows="4"
                        :placeholder="__('Add more context, screenshots, or confirm that everything is fixed.')" />
                    @error('replyMessage')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="flex flex-wrap items-center gap-3">
                        <flux:button
                            type="submit"
                            variant="primary"
                            wire:target="sendReply"
                            wire:loading.attr="disabled">
                            {{ __('Send reply') }}
                        </flux:button>
                        <p class="text-xs text-zinc-500">
                            {{ __('We will notify you as soon as the team sends a new answer.') }}
                        </p>
                    </div>
                </form>
            </div>
            @endif
            @else
            <div class="flex h-full flex-col items-center justify-center gap-3 px-5 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400">
                    <flux:icon.chat-bubble-left-right class="h-7 w-7" />
                </div>
                <h2 class="text-lg font-semibold text-zinc-800">
                    {{ __('Select a ticket to see the conversation') }}
                </h2>
                <p class="text-sm text-zinc-500">
                    {{ __('After opening a ticket, it will appear on the left. Select it to read and reply here.') }}
                </p>
            </div>
            @endif
        </section>
    </section>
</div>
