<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">
                {{ __('Notes - :discipline', ['discipline' => $discipline->title]) }}
            </h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Capture your thoughts and promote them to flashcards when ready to study.') }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search notes...')"
                class="w-full sm:w-64"
                icon="magnifying-glass"
            />

            <x-select
                wire:model.live="flashcardFilter"
                class="w-full sm:w-48"
            >
                <option value="all">{{ __('All types') }}</option>
                <option value="notes">{{ __('Notes only') }}</option>
                <option value="flashcards">{{ __('Flashcards only') }}</option>
            </x-select>

            <flux:button
                variant="primary"
                icon="plus"
                :href="route('notes.create', $discipline)"
                wire:navigate
            >
                {{ __('New note') }}
            </flux:button>
        </div>
    </div>

    <x-auth-session-status :status="session('status')" class="mb-4" />

    @if ($availableTags->isNotEmpty())
        <div class="rounded-md border border-zinc-200 bg-white px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-medium text-zinc-500">
                <span>{{ __('Filter by tags') }}</span>
                @if (! empty($selectedTags))
                    <button
                        type="button"
                        class="text-indigo-600 hover:text-indigo-700 hover:underline"
                        wire:click="clearTagFilter"
                    >
                        {{ __('Clear') }}
                    </button>
                @endif
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($availableTags as $tag)
                    @php
                        $active = in_array($tag->id, $selectedTags, true);
                    @endphp
                    <button
                        type="button"
                        wire:click="toggleTagFilter({{ $tag->id }})"
                        wire:key="tag-filter-{{ $tag->id }}"
                        @class([
                            'inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-medium transition',
                            'border-emerald-200 bg-emerald-50 text-emerald-700' => $active,
                            'border-zinc-200 bg-zinc-100 text-zinc-600 hover:border-zinc-300' => ! $active,
                        ])
                    >
                        @if ($active)
                            <flux:icon.check class="h-3.5 w-3.5" />
                        @else
                            <flux:icon.tag class="h-3.5 w-3.5 text-zinc-400" />
                        @endif
                        <span>{{ $tag->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($notes->isEmpty())
        <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center">
            <h2 class="text-lg font-medium text-zinc-700">
                {{ __('No notes yet') }}
            </h2>
            <p class="mt-2 text-sm text-zinc-500">
                {{ __('Start by creating a note or convert one into a flashcard to revise later.') }}
            </p>
            <flux:button
                class="mt-4"
                variant="primary"
                icon="plus"
                :href="route('notes.create', $discipline)"
                wire:navigate
            >
                {{ __('Create note') }}
            </flux:button>
        </div>
    @else
        <p class="sm:hidden mb-2 text-xs text-zinc-600 bg-zinc-50 border border-dashed border-zinc-200 rounded-md px-3 py-2">
            {{ __('Swipe sideways to reveal all options.') }}
        </p>
        <div class="overflow-x-auto rounded-md border border-zinc-200 bg-white" id="notes-list">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                <span class="text-xs font-medium text-zinc-500">
                    {{ $notes->total() }} {{ __('notes') }}
                </span>

                <div class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                    {{ __('Per page') }}
                    <x-select
                        wire:model.live="perPage"
                        class="w-24"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>

            <table class="min-w-full w-full divide-y divide-zinc-200">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">{{ __('Title') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('Type') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('Updated at') }}</th>
                        <th scope="col" class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-sm text-zinc-700">
                    @foreach ($notes as $note)
                        <tr wire:key="note-{{ $note->id }}">
                            <td class="px-4 py-3">
                                <div class="mt-1 mb-2 flex-wrap items-center gap-3 text-[11px] text-zinc-500 hidden sm:flex">
                                    <span class="inline-flex items-center gap-1.5">
                                        <flux:icon.book-open class="h-3.5 w-3.5 text-indigo-500" />
                                        <span>{{ $note->word_count }} palavras</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <flux:icon.clock class="h-3.5 w-3.5 text-sky-500" />
                                        <span>{{ $note->reading_time }} min</span>
                                    </span>
                                </div>
                                <div class="font-medium text-zinc-900">{{ $note->title }}</div>
                                <p class="mt-1 mb-4 text-xs text-zinc-500 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($note->content), 140) }}</p>
                                @if ($note->tags->isNotEmpty())
                                    <div class="mt-2 flex-wrap gap-2 hidden sm:flex">
                                        @foreach ($note->tags as $tag)
                                            <span class="inline-flex items-center gap-1 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-[11px] text-indigo-700">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if ($note->is_flashcard)
                                    <span class="inline-flex items-center gap-1 rounded-md border border-green-200 bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ __('Flashcard') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md border border-zinc-200 bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600">
                                        {{ __('Note') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ $note->updated_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($note->is_flashcard)
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            color="amber"
                                            wire:click="revertFlashcard({{ $note->id }})"
                                        >
                                            {{ __('Mark as note') }}
                                        </flux:button>
                                    @else
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            color="green"
                                            wire:click="convertToFlashcard({{ $note->id }})"
                                        >
                                            {{ __('Convert to flashcard') }}
                                        </flux:button>
                                    @endif

                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        :href="route('notes.show', [$discipline, $note])"
                                        wire:navigate
                                    >
                                        {{ __('View') }}
                                    </flux:button>

                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        color="indigo"
                                        :href="route('notes.edit', [$discipline, $note])"
                                        wire:navigate
                                    >
                                        {{ __('Edit') }}
                                    </flux:button>

                                    <x-confirm-dialog
                                        class="inline-flex"
                                        name="delete-discipline-note-{{ $note->id }}"
                                        :title="__('Delete note')"
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
                                                    wire:click="deleteNote({{ $note->id }})"
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
            {{ $notes->links('livewire.study.pagination', ['scrollTo' => '#notes-list']) }}
        </div>
    @endif

    <div>
        <flux:button
            variant="ghost"
            :href="route('disciplines.index')"
            wire:navigate
        >
            {{ __('Back to disciplines') }}
        </flux:button>
    </div>
</div>
