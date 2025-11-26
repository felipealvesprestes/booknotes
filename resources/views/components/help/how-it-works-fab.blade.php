@php
$isDashboard = request()->routeIs('dashboard');
@endphp

@if ($isDashboard)
    <div class="fixed bottom-5 right-4 z-40 sm:bottom-8 sm:right-8">
        <flux:modal.trigger name="how-it-works">
            <button
                type="button"
                class="flex items-center gap-2 rounded-full bg-emerald-400 px-5 py-3 text-sm font-semibold text-white shadow-xl transition hover:bg-emerald-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400 sm:px-6"
                aria-label="{{ __('How the platform works') }}"
            >
                <span
                    aria-hidden="true"
                    class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-300 text-base font-semibold text-white"
                >?</span>
                <span class="hidden sm:inline">{{ __('How it works') }}</span>
            </button>
        </flux:modal.trigger>

        <flux:modal name="how-it-works" focusable class="max-w-xl">
            <div class="space-y-6">
                <div class="space-y-1">
                    <flux:heading size="lg">{{ __('How the platform works') }}</flux:heading>
                    <flux:text>{{ __('See the complete flow in seconds, then get back to studying.') }}</flux:text>
                </div>

                <ol class="space-y-3 text-sm text-zinc-600">
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white">1</span>
                        <div>
                            <p class="font-semibold text-zinc-900">{{ __('Create a notebook') }}</p>
                            <p class="text-xs text-zinc-500">{{ __('Group classes, projects, or exams in one place.') }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white">2</span>
                        <div>
                            <p class="font-semibold text-zinc-900">{{ __('Add disciplines') }}</p>
                            <p class="text-xs text-zinc-500">{{ __('Each discipline keeps its own content and flashcards.') }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-zinc-900 text-xs font-semibold text-white">3</span>
                        <div>
                            <p class="font-semibold text-zinc-900">{{ __('Create notes and mark them as flashcards') }}</p>
                            <p class="text-xs text-zinc-500">{{ __('Promote the important notes into question & answer cards.') }}</p>
                        </div>
                    </li>
                </ol>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                    <p class="text-sm font-semibold text-zinc-900">{{ __('Everything revolves around flashcards') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">
                        {{ __('They power every study mode: flashcards, fill in the blank, multiple choice, and true or false.') }}
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="flex-1 sm:flex-none">
                            {{ __('Not now') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button
                        variant="primary"
                        icon="plus"
                        :href="route('notebooks.create')"
                        wire:navigate
                    >
                        {{ __('Start a notebook') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
@endif
