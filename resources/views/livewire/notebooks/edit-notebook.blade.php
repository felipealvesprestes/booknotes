<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Edit notebook') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Update notebook details and keep your logs in sync.') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button
                variant="ghost"
                :href="route('notebooks.show', $notebook)"
                wire:navigate>
                {{ __('View notebook') }}
            </flux:button>

            <flux:button
                variant="ghost"
                :href="route('notebooks.index')"
                wire:navigate>
                {{ __('Back to notebooks') }}
            </flux:button>
        </div>
    </div>

    <div class="space-y-6 w-full">
        <x-auth-session-status :status="session('status')" class="w-full" />

        <div class="rounded-md border border-zinc-200 bg-white p-6">
            <form wire:submit.prevent="save" class="space-y-5">
                <div class="w-full max-w-lg lg:max-w-xl">
                    <flux:input
                        wire:model="title"
                        :label="__('Title')"
                        type="text"
                        maxlength="255"
                        autofocus />
                </div>

                <div>
                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        rows="4" />
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button
                        variant="ghost"
                        :href="route('notebooks.index')"
                        wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ __('Update notebook') }}
                    </flux:button>
                </div>
            </form>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/70 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-red-700">
                        {{ __('Delete notebook') }}
                    </h2>
                    <p class="mt-1 text-sm text-red-600/80">
                        {{ __('This action cannot be undone and will be logged.') }}
                    </p>
                </div>

                <x-confirm-dialog
                    name="delete-notebook-modal-{{ $notebook->id }}"
                    :title="__('Delete notebook')"
                    :description="__('This action cannot be undone and will be logged.')"
                >
                    <x-slot:trigger>
                        <flux:button
                            variant="ghost"
                            color="red"
                            type="button"
                        >
                            {{ __('Delete') }}
                        </flux:button>
                    </x-slot:trigger>

                    <x-slot:confirm>
                        <flux:modal.close>
                            <flux:button
                                type="button"
                                variant="danger"
                                wire:click="delete"
                                wire:loading.attr="disabled"
                                class="min-w-[100px]"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </flux:modal.close>
                    </x-slot:confirm>
                </x-confirm-dialog>
            </div>
        </div>
    </div>
</div>
