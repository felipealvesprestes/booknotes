<div class="space-y-6 w-full">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Edit note') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Update the note or adjust flashcard details for :discipline.', ['discipline' => $discipline->title]) }}
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-zinc-500">
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon.book-open class="h-3.5 w-3.5 text-indigo-500" />
                    <span>{{ $note->word_count }} palavras</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon.pencil-square class="h-3.5 w-3.5 text-amber-500" />
                    <span>{{ $note->char_count }} caracteres</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon.clock class="h-3.5 w-3.5 text-sky-500" />
                    <span>{{ $note->reading_time }} min de leitura</span>
                </span>
            </div>
        </div>

        <flux:button
            variant="ghost"
            :href="route('notes.index', $discipline)"
            wire:navigate
        >
            {{ __('Back to notes') }}
        </flux:button>
    </div>

    <div class="space-y-6 w-full">
        <x-auth-session-status :status="session('status')" class="w-full max-w-3xl" />

        @include('livewire.notes.partials.ai-generator')

        <div class="rounded-md border border-zinc-200 bg-white p-6">
        <form wire:submit.prevent="save" class="space-y-5">
            <div class="w-full max-w-lg lg:max-w-xl">
                <flux:input
                    wire:model="title"
                    :label="__('Title')"
                    type="text"
                    maxlength="255"
                />
            </div>

            <div
                x-data="{
                    limit: 1100,
                    content: $wire.entangle('content').live,
                    showModal: false,
                    wasOverLimit: false,
                    init() {
                        this.$watch('content', (value) => {
                            const length = (value ?? '').length;
                            const currentlyOver = length > this.limit;

                            if (currentlyOver && ! this.wasOverLimit) {
                                this.showModal = true;
                            }

                            this.wasOverLimit = currentlyOver;
                        });
                    },
                    closeModal() {
                        this.showModal = false;
                    },
                    get count() {
                        return (this.content ?? '').length;
                    },
                    get isOverLimit() {
                        return this.count > this.limit;
                    },
                }"
                x-on:keydown.escape.window="showModal = false"
            >
                <flux:textarea
                    wire:model.live="content"
                    :label="__('Content')"
                    rows="14"
                />

                <div class="mt-2 flex justify-end text-xs font-medium">
                    <span :class="isOverLimit ? 'text-rose-600' : 'text-zinc-500'">
                        <span x-text="count"></span>
                        / 1100
                    </span>
                </div>

                <div
                    x-cloak
                    x-show="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="note-length-warning-title"
                >
                    <div
                        class="fixed inset-0 bg-zinc-950/25"
                        x-show="showModal"
                        x-transition.opacity
                        x-on:click="closeModal()"
                    ></div>

                    <div
                        class="relative z-10 w-full max-w-sm transform rounded-2xl bg-white p-6 text-center shadow-xl transition-all dark:bg-zinc-900"
                        x-show="showModal"
                        x-transition.scale
                        x-on:click.stop
                    >
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <flux:icon.shield-exclamation class="h-6 w-6" />
                        </div>
                        <div class="mt-4 space-y-2">
                            <h3 id="note-length-warning-title" class="text-base font-semibold text-zinc-900 dark:text-white">
                                {{ __('Prefer short notes') }}
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ __('Shorter notes are easier to review. Consider splitting very long content into smaller entries to keep focus.') }}
                            </p>
                        </div>
                        <div class="mt-6">
                            <button
                                type="button"
                                class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                x-on:click="closeModal()"
                            >
                                {{ __('Got it') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-data="{}">
                <label class="block text-sm font-medium text-zinc-700">
                    {{ __('Tags') }}
                </label>
                <p class="mt-1 text-xs text-zinc-500">
                    {{ __('Type tags separated by commas and press Enter to create them. Use backspace to edit the input.') }}
                </p>
                <div class="mt-2">
                    <flux:input
                        wire:model.defer="tagInput"
                        :placeholder="__('Type tags separated by commas, then press Enter')"
                        type="text"
                        x-on:keydown.enter.prevent="$wire.addTagsFromInput($event.target.value)"
                        x-on:blur="$wire.addTagsFromInput($event.target.value)"
                    />
                </div>
                @if (! empty($tags))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <span class="inline-flex items-center gap-1 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                {{ $tag }}
                                <button
                                    type="button"
                                    class="text-indigo-500 hover:text-indigo-700"
                                    wire:click="removeTag(@js($tag))"
                                    aria-label="{{ __('Remove tag') }}"
                                >
                                    ×
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div
                class="rounded-md border border-zinc-200 bg-zinc-50 p-4"
                x-data="{ open: @js($isFlashcard) }"
                x-effect="open = $wire.isFlashcard"
            >
                <label class="flex items-center gap-3 text-sm font-medium text-zinc-700">
                    <input
                        type="checkbox"
                        wire:model.live="isFlashcard"
                        x-on:change="open = $event.target.checked"
                        class="size-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    {{ __('Convert to flashcard') }}
                </label>
                <p class="mt-1 text-xs text-zinc-500">
                    {{ __('When enabled, the note becomes a flashcard with a question and answer.') }}
                </p>

                <div
                    class="mt-4 space-y-4"
                    x-show="open"
                    x-transition
                    x-cloak
                >
                    <div>
                        <flux:input
                            wire:model="flashcardQuestion"
                            :label="__('Flashcard question')"
                            type="text"
                            maxlength="255"
                        />
                    </div>

                    <div>
                        <flux:textarea
                            wire:model="flashcardAnswer"
                            :label="__('Flashcard answer')"
                            rows="8"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <flux:button
                    variant="ghost"
                    :href="route('notes.show', [$discipline, $note])"
                    wire:navigate
                >
                    {{ __('View note') }}
                </flux:button>

                <div class="flex items-center gap-3">
                    <flux:button
                        variant="ghost"
                        :href="route('notes.index', $discipline)"
                        wire:navigate
                    >
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ __('Update note') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>

    <div class="rounded-md border border-red-200 bg-red-50/70 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-red-700">
                    {{ __('Delete note') }}
                </h2>
                <p class="mt-1 text-sm text-red-600/80">
                    {{ __('This action cannot be undone and will be logged.') }}
                </p>
            </div>

            <x-confirm-dialog
                name="delete-note-modal-{{ $note->id }}"
                :title="__('Delete note')"
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
