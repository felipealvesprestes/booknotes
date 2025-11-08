<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Activity log') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Review the latest actions across notebooks, disciplines, notes, and flashcard sessions.') }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search logs...')"
                icon="magnifying-glass"
                class="w-full sm:w-64"
            />

            <select
                wire:model.live="actionFilter"
                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-56"
            >
                <option value="">{{ __('All actions') }}</option>
                @foreach ($actionOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($logs->isEmpty())
        <div class="rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-6 py-12 text-center">
            <flux:icon.sparkles class="mx-auto h-10 w-10 text-zinc-300" />
            <h2 class="mt-3 text-lg font-medium text-zinc-700">{{ __('No activity recorded yet') }}</h2>
            <p class="mt-2 text-sm text-zinc-500">
                {{ __('As you create notes, manage disciplines, or study flashcards, your activity will appear here with all the details.') }}
            </p>
        </div>
    @else
        <div class="overflow-hidden rounded-md border border-zinc-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                <span class="text-xs font-medium text-zinc-500">
                    {{ trans_choice(':count log|:count logs', $logs->total(), ['count' => $logs->total()]) }}
                </span>

                <label class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                    {{ __('Per page') }}
                    <select
                        wire:model.live="perPage"
                        class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs font-medium text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div id="logs-list">
                <ul class="divide-y divide-zinc-200">
                    @foreach ($logs as $log)
                        <li class="flex gap-4 px-4 py-5">
                            <div class="mt-1">
                                <span class="flex h-10 w-10 items-center justify-center rounded-md {{ $log['icon_classes'] }}">
                                    <flux:icon :icon="$log['icon']" class="h-5 w-5" />
                                </span>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="space-y-1.5">
                                        <p class="text-xs font-medium text-zinc-500">
                                            {{ $log['label'] }}
                                        </p>
                                        <p class="text-sm leading-5 text-zinc-800 line-clamp-2">
                                            {{ $log['description'] }}
                                        </p>
                                    </div>
                                    <div class="text-right text-xs text-zinc-500">
                                        @if ($log['timestamp'])
                                            <p>{{ $log['timestamp'] }}</p>
                                        @endif
                                        @if ($log['ago'])
                                            <p>{{ $log['ago'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($log['tags']))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($log['tags'] as $tag)
                                            <span class="inline-flex items-center rounded-full border bg-white/80 px-2.5 py-1 text-xs font-medium {{ $tag['classes'] }}">
                                                {{ $tag['text'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($log['meta']))
                                    <div class="flex flex-wrap gap-2 text-xs text-zinc-600">
                                        @foreach ($log['meta'] as $meta)
                                            <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-1">
                                                <span class="text-zinc-500">{{ $meta['label'] }}:</span>
                                                <span class="font-medium text-zinc-700">{{ $meta['value'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if (!empty($log['changes']))
                                    <div class="space-y-2 rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                            {{ __('Changes') }}
                                        </p>
                                        <dl class="space-y-1 text-xs text-zinc-600">
                                            @foreach ($log['changes'] as $change)
                                                <div class="flex flex-wrap items-center gap-1">
                                                    <dt class="font-medium text-zinc-500">{{ $change['label'] }}:</dt>
                                                    <dd class="flex items-center gap-1">
                                                        <span class="text-zinc-500">{{ $change['from'] }}</span>
                                                        <span class="text-zinc-400">→</span>
                                                        <span class="text-zinc-700">{{ $change['to'] }}</span>
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3">
                {{ $logs->links('livewire.study.pagination', ['scrollTo' => '#logs-list']) }}
            </div>
        </div>
    @endif
</div>
