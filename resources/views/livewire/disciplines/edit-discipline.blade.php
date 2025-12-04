<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Edit discipline') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Update discipline details or move it to a different notebook.') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button
                variant="ghost"
                :href="route('disciplines.show', $discipline)"
                wire:navigate
            >
                {{ __('View discipline') }}
            </flux:button>

            <flux:button
                variant="ghost"
                :href="route('disciplines.index')"
                wire:navigate
            >
                {{ __('Back to disciplines') }}
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
                        autofocus
                    />
                </div>

                <div class="w-full max-w-lg lg:max-w-xl">
                    <x-select
                        wire:model="notebookId"
                        :label="__('Notebook')"
                        :placeholder="__('Select a notebook')"
                        class="w-full"
                    >
                        @foreach ($notebooks as $notebook)
                            <option
                                value="{{ $notebook->id }}"
                                @selected((string) $notebookId === (string) $notebook->id)
                            >
                                {{ $notebook->title }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <flux:textarea
                        wire:model="description"
                        :label="__('Description')"
                        rows="4"
                    />
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button
                        variant="ghost"
                        :href="route('disciplines.index')"
                        wire:navigate
                    >
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ __('Update discipline') }}
                    </flux:button>
                </div>
            </form>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/70 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-red-700">
                        {{ __('Delete discipline') }}
                    </h2>
                    <p class="mt-1 text-sm text-red-600/80">
                        {{ __('This action cannot be undone and will be logged.') }}
                    </p>
                </div>

                <x-confirm-dialog
                    name="delete-discipline-modal-{{ $discipline->id }}"
                    :title="__('Delete discipline')"
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
