<div class="mx-auto w-full max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create note') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Write a note for :discipline and optionally prepare it as a flashcard.', ['discipline' => $discipline->title]) }}
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
                <flux:textarea
                    wire:model="content"
                    :label="__('Content')"
                    rows="14"
                />
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
                    {{ __('Flashcards require a question and an answer, ideal for spaced repetition later on.') }}
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

            <div class="flex items-center justify-end gap-3">
                <flux:button
                    variant="ghost"
                    :href="route('notes.index', $discipline)"
                    wire:navigate
                >
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button variant="primary" type="submit">
                    {{ __('Save note') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>
