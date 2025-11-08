<div class="mx-auto w-full max-w-3xl space-y-6">
    <div class="space-y-2">
        <h1 class="text-3xl font-semibold text-zinc-900">{{ $note->title }}</h1>
        <p class="text-sm text-zinc-500">
            {{ __('Discipline: :discipline', ['discipline' => $discipline->title]) }}
        </p>
        <x-auth-session-status :status="session('status')" class="max-w-sm" />
    </div>

    <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-md bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-600">
                {{ $note->is_flashcard ? __('Flashcard') : __('Note') }}
            </span>
            <span class="text-xs text-zinc-400">{{ __('Last updated: :date', ['date' => $note->updated_at->format('d/m/Y H:i')]) }}</span>
        </div>

        <div class="prose max-w-none text-sm text-zinc-700">
            {!! nl2br(e($note->content)) !!}
        </div>

        @if ($note->is_flashcard)
            <div class="rounded-md border border-gray-200 bg-gray-50 p-4 space-y-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700">{{ __('Flashcard question') }}</h2>
                    <p class="mt-1 text-sm text-gray-800">{{ $note->flashcard_question }}</p>
                </div>
            </div>
            <div class="rounded-md border border-green-200 bg-green-50 p-4 space-y-3">
                <div>
                    <h2 class="text-sm font-semibold text-green-700">{{ __('Flashcard answer') }}</h2>
                    <p class="mt-1 text-sm text-green-800 whitespace-pre-line">{{ $note->flashcard_answer }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-3">
        @if ($note->is_flashcard)
            <flux:button
                variant="ghost"
                color="amber"
                wire:click="revertFlashcard"
            >
                {{ __('Mark as note') }}
            </flux:button>
        @else
            <flux:button
                variant="ghost"
                color="green"
                wire:click="convertToFlashcard"
            >
                {{ __('Convert to flashcard') }}
            </flux:button>
        @endif

        <flux:button
            variant="ghost"
            color="indigo"
            :href="route('notes.edit', [$discipline, $note])"
            wire:navigate
        >
            {{ __('Edit') }}
        </flux:button>

        <x-confirm-dialog
            name="delete-note-show-{{ $note->id }}"
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

        <flux:spacer />

        <flux:button
            variant="ghost"
            :href="route('notes.index', $discipline)"
            wire:navigate
        >
            {{ __('Back to notes') }}
        </flux:button>
    </div>
</div>
