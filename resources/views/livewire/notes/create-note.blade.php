<div class="mx-auto w-full max-w-3xl space-y-6">
    <flux:modal
        name="notes-onboarding"
        wire:model="showNotesOnboardingModal"
        focusable
        class="max-w-2xl"
    >
        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="flex h-14 w-14 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                    <flux:icon.sparkles class="h-7 w-7" />
                </div>

                <div class="space-y-2">
                    <flux:heading size="lg">{{ __('Sua base de conhecimento começa aqui') }}</flux:heading>
                    <flux:text>
                        {{ __('É aqui onde tudo se inicia: é registrando notas que o Booknotes monta sua base de conhecimento, cria flashcards e impulsiona seus estudos.') }}
                    </flux:text>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                    <div class="flex items-start gap-3">
                        <div class="rounded-full bg-white/90 p-2 text-indigo-600">
                            <flux:icon.book-open class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-indigo-900">{{ __('Notas são fundamentais ') }}</p>
                            <p class="mt-1 text-xs text-indigo-800/80">
                                {{ __('Construa notas ricas em detalhes sobre tudo que você está estudando. Isso ajuda a absorver o conteúdo.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <div class="flex items-start gap-3">
                        <div class="rounded-full bg-white/90 p-2 text-emerald-600">
                            <flux:icon.bolt class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ __('Base para revisões inteligentes') }}</p>
                            <p class="mt-1 text-xs text-emerald-800/80">
                                {{ __('Cada nota pode virar flashcard com um clique. Flashcards alimentam simulados e todos os modos de estudo.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-zinc-200 bg-white/80 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ __('Passo 1') }}</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-900">{{ __('Contextualize') }}</p>
                    <p class="mt-1 text-xs text-zinc-600">
                        {{ __('Use títulos claros e mantenha cada nota vinculada ao caderno e disciplina certos.') }}
                    </p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white/80 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ __('Passo 2') }}</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-900">{{ __('Organize ideias') }}</p>
                    <p class="mt-1 text-xs text-zinc-600">
                        {{ __('Misture texto e tags para que o sistema entenda seu raciocínio completo.') }}
                    </p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white/80 p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ __('Passo 3') }}</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-900">{{ __('Marque como flashcard') }}</p>
                    <p class="mt-1 text-xs text-zinc-600">
                        {{ __('Notas viram flashcards e você revisa com simulados e modos de estudos.') }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50/80 p-5 text-sm text-zinc-700">
                {{ __('Quanto antes você começar a criar flashcards, mais rápido o Booknotes monta sua base de conhecimento e você já pode começar a estudar.') }}
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-1 sm:flex-none">
                        {{ __('Ler depois') }}
                    </flux:button>
                </flux:modal.close>

                <flux:modal.close>
                    <flux:button
                        variant="primary"
                        icon="sparkles"
                        class="flex-1 sm:flex-none"
                        type="button"
                    >
                        {{ __('Começar minha primeira nota') }}
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <div>
        <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create note') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ __('Write a note for :discipline and optionally prepare it as a flashcard.', ['discipline' => $discipline->title]) }}
        </p>
    </div>

    <div class="space-y-6">
        @include('livewire.notes.partials.ai-generator')

        <div class="rounded-md border border-zinc-200 bg-white p-6">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-900">
                <div class="flex items-start gap-3">
                    <flux:icon.check-circle class="mt-0.5 h-5 w-5 text-emerald-500" />
                    <div>
                        <p class="text-sm font-semibold leading-snug">{{ session('status') }}</p>
                        <p class="mt-1 text-xs text-emerald-800">
                            {{ __('You can add another note right away or jump back to the list.') }}
                        </p>
                    </div>
                </div>
                </div>
            @endif

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
                    <span
                        :class="isOverLimit ? 'text-rose-600' : 'text-zinc-500'"
                    >
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

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancel"
                >
                    {{ __('Cancel') }}
                </flux:button>

                <div class="flex items-center gap-3">
                    <flux:button
                        variant="ghost"
                        :href="route('notes.index', $discipline)"
                        wire:navigate
                    >
                        {{ __('Back to notes list') }}
                    </flux:button>

                    <flux:button variant="primary" type="submit">
                        {{ __('Save note') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</div>
