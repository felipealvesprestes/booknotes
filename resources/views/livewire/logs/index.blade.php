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

            <x-select
                wire:model.live="actionFilter"
                :placeholder="__('All actions')"
                class="w-full sm:w-56"
            >
                @foreach ($actionOptions as $option)
                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                @endforeach
            </x-select>
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
        <div class="rounded-md border border-zinc-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                <span class="text-xs font-medium text-zinc-500">
                    {{ trans_choice(':count log|:count logs', $logs->total(), ['count' => $logs->total()]) }}
                </span>

                <div class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                    {{ __('Per page') }}
                    <x-select
                        wire:model.live="perPage"
                        class="w-24"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>

            <div id="logs-list">
                <div class="flow-root">
                    <div class="overflow-x-auto">
                        <div class="inline-block min-w-full py-2 align-middle">
                            <table class="relative min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                            {{ __('Log entry') }}
                                        </th>
                                        <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-gray-900 sm:pr-3 w-44">
                                            {{ __('Timestamp') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach ($logs as $log)
                                        <tr class="even:bg-gray-50">
                                            <td class="py-4 pr-3 pl-4 text-sm text-gray-900 align-top sm:pl-3">
                                                <div class="flex gap-4">
                                                    <div class="mt-1">
                                                        <span class="flex h-10 w-10 items-center justify-center rounded-md {{ $log['icon_classes'] }}">
                                                            <flux:icon :icon="$log['icon']" class="h-5 w-5" />
                                                        </span>
                                                    </div>
                                                    <div class="flex-1 space-y-3">
                                                        <div class="space-y-1.5">
                                                            <p class="text-xs font-medium text-zinc-500">
                                                                {{ $log['label'] }}
                                                            </p>
                                                            <p class="text-sm leading-5 text-zinc-800 line-clamp-2">
                                                                {{ $log['description'] }}
                                                            </p>
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
                                                </div>
                                            </td>
                                            <td class="py-4 pr-4 pl-3 text-right text-xs text-gray-500 sm:pr-3 w-44">
                                                @if ($log['timestamp'])
                                                    <p>{{ $log['timestamp'] }}</p>
                                                @endif
                                                @if ($log['ago'])
                                                    <p>{{ $log['ago'] }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3">
                {{ $logs->links('livewire.study.pagination', ['scrollTo' => '#logs-list']) }}
            </div>
        </div>
    @endif
</div>
