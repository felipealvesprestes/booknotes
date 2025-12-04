<div class="space-y-8 w-full">
    <div class="space-y-1.5">
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('System notifications') }}</h1>
        <p class="text-sm text-zinc-500 max-w-3xl leading-relaxed">
            {{ __('Stay up to date with reminders, alerts, and product updates delivered directly in your study workspace.') }}
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <section class="flex flex-col rounded-md border border-zinc-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">{{ __('Unread notifications') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Prioritize what needs your attention right now.') }}
                    </p>
                </div>
                <flux:button
                    variant="ghost"
                    class="self-start"
                    size="sm"
                    wire:click="markAllAsRead"
                    wire:loading.attr="disabled"
                    wire:target="markAllAsRead"
                    :disabled="$unreadNotifications->isEmpty()"
                >
                    {{ __('Mark all as read') }}
                </flux:button>
            </div>

            <div id="unread-notifications">
                @forelse ($unreadNotifications as $notification)
                    <article
                        wire:key="unread-{{ $notification['id'] }}"
                        class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-start border-b border-zinc-100"
                    >
                        <div class="sm:pt-6">
                            <span class="flex h-12 w-12 items-center justify-center rounded-md {{ $notification['icon_classes'] }}">
                                <flux:icon :icon="$notification['icon']" class="h-6 w-6" />
                            </span>
                        </div>

                        <div class="flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-zinc-500">
                                @if ($notification['timestamp'])
                                    <span>{{ $notification['timestamp'] }}</span>
                                @endif
                                @if ($notification['timestamp'] && $notification['ago'])
                                    <span class="text-zinc-400">•</span>
                                @endif
                                @if ($notification['ago'])
                                    <span>{{ $notification['ago'] }}</span>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide {{ $notification['tag_classes'] }}">
                                        {{ __($notification['tag']) }}
                                    </span>
                                    @if (!empty($notification['meta']))
                                        <div class="flex flex-wrap gap-2 text-[11px] uppercase tracking-wide text-zinc-500">
                                            @foreach ($notification['meta'] as $meta)
                                                <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 px-2 py-0.5 text-[11px] font-medium">
                                                    <span class="text-zinc-400">{{ __($meta['label']) }}:</span>
                                                    <span class="text-zinc-700">{{ __($meta['value']) }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-zinc-900">{{ $notification['title'] }}</h3>
                                    @if ($notification['message'])
                                        <p class="text-sm text-zinc-600 leading-relaxed mt-4">
                                            {!! nl2br(e($notification['message'])) !!}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    class="text-zinc-700 hover:text-zinc-900 bg-zinc-100/80 hover:bg-zinc-200 rounded-md transition-colors"
                                    wire:click="markAsRead('{{ $notification['id'] }}')"
                                    wire:target="markAsRead('{{ $notification['id'] }}')"
                                    wire:loading.attr="disabled"
                                >
                                    {{ __('Mark as read') }}
                                </flux:button>

                                @if ($notification['action'])
                                    <a
                                        href="{{ $notification['action']['url'] }}"
                                        target="{{ $notification['action']['target'] }}"
                                        class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                    >
                                        {{ $notification['action']['label'] }}
                                        <flux:icon.chevron-right class="h-4 w-4" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                            <flux:icon.bell class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-zinc-900">{{ __('You are all caught up') }}</h3>
                        <p class="mt-2 text-sm text-zinc-500">
                            {{ __('We will notify you here as soon as something new happens.') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($unreadNotifications->hasPages())
                <div class="border-t border-zinc-100 px-5 py-4 bg-zinc-50 rounded-b-2xl">
                    {{ $unreadNotifications->links('livewire.study.pagination', ['scrollTo' => '#unread-notifications']) }}
                </div>
            @endif
        </section>

        <section class="flex flex-col rounded-md border border-zinc-200 bg-white">
            <div class="border-b border-zinc-200 px-5 py-5">
                <h2 class="text-lg font-semibold text-zinc-900">{{ __('Notification history') }}</h2>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ __('Visit previous updates whenever you need.') }}
                </p>
            </div>

            <div id="read-notifications">
                @forelse ($readNotifications as $notification)
                    @php($modalName = 'notification-history-' . $notification['id'])
                    <article
                        wire:key="read-{{ $notification['id'] }}"
                        class="flex flex-col gap-4 px-5 py-5 border-b border-zinc-100"
                    >
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-zinc-500">
                                @if ($notification['timestamp'])
                                    <span>{{ $notification['timestamp'] }}</span>
                                @endif
                                @if ($notification['timestamp'] && $notification['ago'])
                                    <span class="text-zinc-400">•</span>
                                @endif
                                @if ($notification['ago'])
                                    <span>{{ $notification['ago'] }}</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs text-zinc-500">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide {{ $notification['tag_classes'] }}">
                                    {{ __($notification['tag']) }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold text-zinc-900">{{ $notification['title'] }}</h3>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <flux:button
                                size="xs"
                                variant="ghost"
                                class="text-zinc-600 hover:text-zinc-900 bg-zinc-100/70 hover:bg-zinc-200 rounded-md transition-colors"
                                wire:click="markAsUnread('{{ $notification['id'] }}')"
                                wire:target="markAsUnread('{{ $notification['id'] }}')"
                                wire:loading.attr="disabled"
                            >
                                {{ __('Mark as unread') }}
                            </flux:button>

                            <flux:modal.trigger name="{{ $modalName }}">
                                <flux:button
                                    size="xs"
                                    variant="ghost"
                                    class="text-indigo-600 hover:text-indigo-500 !bg-transparent hover:!bg-indigo-50 transition-colors"
                                >
                                    {{ __('View notification') }}
                                </flux:button>
                            </flux:modal.trigger>

                            @if ($notification['action'])
                                <a
                                    href="{{ $notification['action']['url'] }}"
                                    target="{{ $notification['action']['target'] }}"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                >
                                    {{ $notification['action']['label'] }}
                                    <flux:icon.arrow-up-right class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </article>

                    <flux:modal name="{{ $modalName }}" focusable class="max-w-2xl">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-zinc-500">
                                @if ($notification['timestamp'])
                                    <span>{{ $notification['timestamp'] }}</span>
                                @endif
                                @if ($notification['timestamp'] && $notification['ago'])
                                    <span class="text-zinc-400">•</span>
                                @endif
                                @if ($notification['ago'])
                                    <span>{{ $notification['ago'] }}</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide {{ $notification['tag_classes'] }}">
                                    {{ __($notification['tag']) }}
                                </span>
                                @if (!empty($notification['meta']))
                                    <div class="flex flex-wrap gap-2 text-[11px] uppercase tracking-wide text-zinc-500">
                                        @foreach ($notification['meta'] as $meta)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 px-2 py-0.5 text-[11px] font-medium">
                                                <span class="text-zinc-400">{{ __($meta['label']) }}:</span>
                                                <span class="text-zinc-700">{{ __($meta['value']) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-lg font-semibold text-zinc-900">
                                    {{ $notification['title'] }}
                                </h3>
                                @if ($notification['message'])
                                    <p class="text-sm text-zinc-600 leading-relaxed">
                                        {!! nl2br(e($notification['message'])) !!}
                                    </p>
                                @endif
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <flux:modal.close>
                                    <flux:button variant="ghost">
                                        {{ __('Close') }}
                                    </flux:button>
                                </flux:modal.close>
                            </div>
                        </div>
                    </flux:modal>
                @empty
                    <div class="px-5 py-10 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white">
                            <flux:icon.document class="h-5 w-5 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-zinc-900">{{ __('No notifications yet') }}</h3>
                        <p class="mt-2 text-sm text-zinc-500">
                            {{ __('As soon as the system has updates to share, they will be archived here.') }}
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($readNotifications->hasPages())
                <div class="border-t border-zinc-100 px-5 py-4 bg-white rounded-b-2xl">
                    {{ $readNotifications->links('livewire.study.pagination', ['scrollTo' => '#read-notifications']) }}
                </div>
            @endif
        </section>
    </div>
</div>
