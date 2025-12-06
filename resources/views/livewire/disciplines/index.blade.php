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
        <div>
            <div class="flow-root">
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <table class="relative min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                        {{ __('Title') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        {{ __('Notebook') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        {{ __('Updated at') }}
                                    </th>
                                    <th scope="col" class="py-3.5 pr-4 pl-3 sm:pr-3">
                                        <span class="sr-only">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($disciplines as $discipline)
                                    <tr class="even:bg-gray-50" wire:key="discipline-{{ $discipline->id }}">
                                        <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                                            {{ $discipline->title }}
                                        </td>
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {{ $discipline->notebook?->title ?? __('Unknown') }}
                                        </td>
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                            {{ $discipline->updated_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
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
                </div>
            </div>
        </div>

        <div>
            {{ $disciplines->links() }}
        </div>
    @endif
</div>
