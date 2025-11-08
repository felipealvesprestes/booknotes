<div class="mx-auto w-full max-w-3xl space-y-6">
    <div class="space-y-2">
        <h1 class="text-3xl font-semibold text-zinc-900">{{ $discipline->title }}</h1>
        <p class="text-sm text-zinc-500">
            {{ __('Notebook: :notebook', ['notebook' => $discipline->notebook?->title ?? __('Unknown')]) }}
        </p>
        <p class="text-xs text-zinc-400">
            {{ trans_choice(':count note|:count notes', $notesCount, ['count' => $notesCount]) }}
        </p>
    </div>

    <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-4">
        <div>
            <h2 class="text-sm font-semibold text-zinc-700">{{ __('Description') }}</h2>
            <p class="mt-1 text-sm text-zinc-600 whitespace-pre-line">
                {{ $discipline->description ?? __('No description provided.') }}
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <flux:button
            variant="ghost"
            :href="route('notes.index', $discipline)"
            wire:navigate
        >
            {{ __('View notes') }}
        </flux:button>

        <flux:button
            variant="ghost"
            color="indigo"
            :href="route('disciplines.edit', $discipline)"
            wire:navigate
        >
            {{ __('Edit') }}
        </flux:button>

        <flux:spacer />

        <flux:button
            variant="ghost"
            :href="route('disciplines.index')"
            wire:navigate
        >
            {{ __('Back to disciplines') }}
        </flux:button>
    </div>
</div>
