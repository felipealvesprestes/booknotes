<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create discipline') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Add a new discipline inside one of your notebooks.') }}
            </p>
        </div>

        <flux:button
            variant="ghost"
            :href="route('disciplines.index')"
            wire:navigate
        >
            {{ __('Back to disciplines') }}
        </flux:button>
    </div>

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
                    {{ __('Save discipline') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
