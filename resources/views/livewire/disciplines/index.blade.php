<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Disciplines') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Organize the subjects within your notebooks.') }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search disciplines...')"
                class="w-full sm:w-64"
                icon="magnifying-glass"
            />

            <x-select
                wire:model.live="notebookFilter"
                :placeholder="__('All notebooks')"
                class="w-full sm:w-56"
            >
                @foreach ($notebooks as $notebook)
                    <option value="{{ $notebook->id }}">{{ $notebook->title }}</option>
                @endforeach
            </x-select>

            <flux:button
                variant="primary"
                icon="plus"
                :href="route('disciplines.create')"
                wire:navigate
            >
                {{ __('New discipline') }}
            </flux:button>
        </div>
    </div>

    <x-auth-session-status :status="session('status')" class="mb-4" />

    @if ($disciplines->isEmpty())
        <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center">
            <h2 class="text-lg font-medium text-zinc-700">
                {{ __('No disciplines yet') }}
            </h2>
            <p class="mt-2 text-sm text-zinc-500">
                {{ __('Create your first discipline to organize notes inside a notebook.') }}
            </p>
            <flux:button
                class="mt-4"
                variant="primary"
                icon="plus"
                :href="route('disciplines.create')"
                wire:navigate
            >
                {{ __('Create discipline') }}
            </flux:button>
        </div>
    @else
        <p class="sm:hidden mb-2 text-xs text-zinc-600 bg-zinc-50 border border-dashed border-zinc-200 rounded-md px-3 py-2">
            {{ __('Swipe sideways to reveal all options.') }}
        </p>
        <div class="overflow-x-auto rounded-md border border-zinc-200 bg-white">
            <table class="min-w-full w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">{{ __('Title') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('Notebook') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('Updated at') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-sm text-zinc-700">
                    @foreach ($disciplines as $discipline)
                        <tr wire:key="discipline-{{ $discipline->id }}">
                            <td class="px-4 py-3 font-medium">{{ $discipline->title }}</td>
                            <td class="px-4 py-3 text-zinc-500">{{ $discipline->notebook?->title ?? __('Unknown') }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ $discipline->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <flux:button
                                        size="xs"
                                        variant="primary"
                                        icon="plus"
                                        :href="route('notes.create', $discipline)"
                                        wire:navigate
                                    >
                                        {{ __('Create note') }}
                                    </flux:button>
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        :href="route('notes.index', $discipline)"
                                        wire:navigate
                                    >
                                        {{ __('Notes') }}
                                    </flux:button>
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        color="indigo"
                                        :href="route('disciplines.edit', $discipline)"
                                        wire:navigate
                                    >
                                        {{ __('Edit') }}
                                    </flux:button>
                                    <x-confirm-dialog
                                        class="inline-flex"
                                        name="delete-discipline-{{ $discipline->id }}"
                                        :title="__('Delete discipline')"
                                        :description="__('This action cannot be undone and will be logged.')"
                                    >
                                        <x-slot:trigger>
                                            <flux:button
                                                size="xs"
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
                                                    wire:click="deleteDiscipline({{ $discipline->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="min-w-[90px]"
                                                >
                                                    {{ __('Delete') }}
                                                </flux:button>
                                            </flux:modal.close>
                                        </x-slot:confirm>
                                    </x-confirm-dialog>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $disciplines->links() }}
        </div>
    @endif
</div>
