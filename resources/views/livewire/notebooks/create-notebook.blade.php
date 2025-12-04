<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create notebook') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Add a new notebook to group your future notes.') }}
            </p>
        </div>

        <flux:button
            variant="ghost"
            :href="route('notebooks.index')"
            wire:navigate>
            {{ __('Back to notebooks') }}
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
