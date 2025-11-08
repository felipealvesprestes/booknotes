<div class="mx-auto w-full max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create notebook') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Add a new notebook to group your future notes.') }}
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
                    {{ __('Save notebook') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
