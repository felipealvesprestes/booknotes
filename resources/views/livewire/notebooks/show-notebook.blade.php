<div class="mx-auto w-full max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-zinc-900">{{ $notebook->title }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Last updated:') }} {{ $notebook->updated_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <flux:button
            variant="primary"
            :href="route('notebooks.edit', $notebook)"
            wire:navigate>
            {{ __('Edit') }}
        </flux:button>
    </div>

    <div class="rounded-md border border-zinc-200 bg-white p-6">
        <h2 class="text-lg font-medium text-zinc-900">{{ __('Description') }}</h2>
        <p class="mt-2 whitespace-pre-line text-sm text-zinc-600">
            {{ $notebook->description ?? __('No description provided.') }}
        </p>
    </div>

    <div class="flex items-center justify-between">
        <flux:button
            variant="ghost"
            :href="route('notebooks.index')"
            wire:navigate>
            {{ __('Back to notebooks') }}
        </flux:button>

        <x-confirm-dialog
            name="delete-notebook-show-{{ $notebook->id }}"
            :title="__('Delete notebook')"
            :description="__('This action cannot be undone and will be logged.')"
        >
            <x-slot:trigger>
                <flux:button
                    variant="ghost"
                    color="red"
                    type="button"
                >
                    {{ __('Delete notebook') }}
                </flux:button>
            </x-slot:trigger>

            <x-slot:confirm>
                <flux:modal.close>
                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        class="min-w-[110px]"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.close>
            </x-slot:confirm>
        </x-confirm-dialog>
    </div>
</div>
