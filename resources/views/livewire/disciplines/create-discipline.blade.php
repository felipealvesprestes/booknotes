<div class="mx-auto w-full max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create discipline') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Add a new discipline inside one of your notebooks.') }}
        </p>
    </div>

    <div class="rounded-md border border-zinc-200 bg-white p-6">
        <form wire:submit.prevent="save" class="space-y-5">
            <div>
                <flux:input
                    wire:model="title"
                    :label="__('Title')"
                    type="text"
                    maxlength="255"
                    autofocus
                />
            </div>

            <div>
                <flux:select
                    wire:model="notebookId"
                    :label="__('Notebook')"
                    :placeholder="__('Select a notebook')"
                >
                    @foreach ($notebooks as $notebook)
                        <option value="{{ $notebook->id }}">{{ $notebook->title }}</option>
                    @endforeach
                </flux:select>
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
