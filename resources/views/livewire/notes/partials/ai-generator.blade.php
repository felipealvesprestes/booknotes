@php
    $aiLimitReached = $aiRemainingToday < $aiMinimumQuantity;
    $quantityRange = implode(', ', $aiQuantityOptions);
    $maxQuantity = ! empty($aiQuantityOptions) ? max($aiQuantityOptions) : 20;
    $remainingAfterRequest = max(0, $aiRemainingToday - $aiQuantity);
@endphp

<div class="rounded-md border border-indigo-100 bg-indigo-50/80 p-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="rounded-full bg-white/80 p-2 text-indigo-600">
                <flux:icon.sparkles class="h-5 w-5" />
            </div>

            <div class="space-y-1">
                <p class="text-sm font-semibold text-indigo-900">{{ __('Generate flashcards with AI') }}</p>
                <p class="text-xs font-semibold text-indigo-800/80 mb-2">
                    {{ __('Let Booknotes create flashcards for this discipline in seconds.') }}
                </p>
                <div class="text-xs text-indigo-800/80">
                    <span>
                        {{ trans_choice('ai_flashcards.used_today', $aiUsedToday, ['count' => $aiUsedToday]) }}
                    </span>
                    <span>
                        {{ trans_choice('ai_flashcards.remaining_today', $aiRemainingToday, ['count' => $aiRemainingToday]) }}
                    </span>
                </div>
            </div>
        </div>

        <flux:button
            type="button"
            icon="sparkles"
            variant="primary"
            wire:click="openAiFlashcardsModal"
            :disabled="$aiRemainingToday <= 0"
        >
            {{ __('Generate flashcards with AI') }}
        </flux:button>
    </div>
</div>

<flux:modal
    name="ai-flashcards"
    wire:model="showAiFlashcardsModal"
    focusable
    class="max-w-2xl"
>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Generate flashcards with AI') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Fill the topic, focus, and quantity, then confirm before calling the AI.') }}
            </p>
        </div>

        @if ($aiErrorMessage)
            <div class="rounded-lg border border-rose-200 bg-rose-50/80 p-3 text-sm text-rose-900">
                {{ $aiErrorMessage }}
            </div>
        @endif
        @if ($aiStatusMessage && ! $aiErrorMessage)
            <div class="space-y-5">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-3 text-sm text-emerald-900">
                    {{ $aiStatusMessage }}
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('AI request summary') }}
                    </p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Discipline') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $discipline->title }}</p>
                        </div>

                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Topic') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $aiTopic }}</p>
                        </div>

                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/90 p-3 sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Study focus (optional)') }}</p>
                            <p class="mt-1 text-sm font-medium text-zinc-900">
                                {{ $aiDescription ? $aiDescription : __('No description provided.') }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Number of flashcards to generate') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">
                                {{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $aiQuantity, ['count' => $aiQuantity]) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Remaining after this generation') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">
                                {{ trans_choice('ai_flashcards.remaining_after', $remainingAfterRequest, ['count' => $remainingAfterRequest]) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/80 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                {{ __('Remaining after this generation') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-indigo-900">
                                {{ trans_choice('ai_flashcards.remaining_after', $remainingAfterRequest, ['count' => $remainingAfterRequest]) }}
                            </p>
                            <p class="text-xs text-indigo-800">
                                {{ __('Daily limit: :count flashcards', ['count' => $aiDailyLimit]) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-indigo-700">
                            <flux:icon.sparkles class="h-4 w-4" />
                            {{ trans_choice('ai_flashcards.batch_size', $aiQuantity, ['count' => $aiQuantity]) }}
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="primary">
                        {{ __('Close') }}
                    </flux:button>
                </flux:modal.close>
            </div>
        @elseif ($aiGeneratorStep === 'confirm')
            <div class="space-y-5">
                <p class="text-sm text-zinc-600">
                    {{ __('Review the details below before confirming the generation.') }}
                </p>

                <div class="rounded-md border border-zinc-200 bg-white/80 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('AI request summary') }}
                    </p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-md border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Discipline') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $discipline->title }}</p>
                        </div>

                        <div class="rounded-md border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Topic') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $aiTopic }}</p>
                        </div>

                        <div class="rounded-md border border-zinc-100 bg-zinc-50/90 p-3 sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Study focus (optional)') }}</p>
                            <p class="mt-1 text-sm font-medium text-zinc-900">
                                {{ $aiDescription ? $aiDescription : __('No description provided.') }}
                            </p>
                        </div>

                        <div class="rounded-md border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Number of flashcards to generate') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">
                                {{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $aiQuantity, ['count' => $aiQuantity]) }}
                            </p>
                        </div>

                        <div class="rounded-md border border-zinc-100 bg-zinc-50/90 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Remaining after this generation') }}</p>
                            <p class="mt-1 text-sm font-semibold text-zinc-900">
                                {{ trans_choice('ai_flashcards.remaining_after', $remainingAfterRequest, ['count' => $remainingAfterRequest]) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-indigo-100 bg-indigo-50/80 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                                {{ __('Remaining after this generation') }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-indigo-900">
                                {{ trans_choice('ai_flashcards.remaining_after', $remainingAfterRequest, ['count' => $remainingAfterRequest]) }}
                            </p>
                            <p class="text-xs text-indigo-800">
                                {{ __('Daily limit: :count flashcards', ['count' => $aiDailyLimit]) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-indigo-700">
                            <flux:icon.sparkles class="h-4 w-4" />
                            {{ trans_choice('ai_flashcards.batch_size', $aiQuantity, ['count' => $aiQuantity]) }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <flux:button
                        type="button"
                        variant="ghost"
                        wire:click="backToAiFlashcardsForm"
                        wire:loading.attr="disabled"
                        wire:target="generateAiFlashcards"
                    >
                        {{ __('Back and edit') }}
                    </flux:button>

                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="generateAiFlashcards"
                        wire:loading.attr="disabled"
                        wire:target="generateAiFlashcards"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <flux:icon.loading
                                class="h-4 w-4"
                                wire:loading
                                wire:target="generateAiFlashcards"
                            />

                            <span wire:loading.remove wire:target="generateAiFlashcards">
                                {{ __('Confirm and generate') }}
                            </span>

                            <span wire:loading wire:target="generateAiFlashcards">
                                {{ __('Generating flashcards with AI...') }}
                            </span>
                        </div>
                    </flux:button>
                </div>
            </div>
        @else
            <form wire:submit.prevent="submitAiFlashcardsForm" class="space-y-4">
                <div>
                    <flux:input
                        wire:model.defer="aiTopic"
                        :label="__('Topic')"
                        :placeholder="__('E.g. Digestive system, Reconstruction era, PHP OOP...')"
                    />
                </div>

                <div>
                    <flux:textarea
                        wire:model.defer="aiDescription"
                        :label="__('Study focus (optional)')"
                        rows="4"
                        :placeholder="__('Describe what you want to emphasize so the AI stays on track.')"
                    />
                </div>

                <div>
                    <x-select
                        wire:model.defer="aiQuantity"
                        :label="__('Number of flashcards to generate')"
                        class="mt-2 w-full"
                    >
                        @foreach ($aiQuantityOptions as $option)
                            <option value="{{ $option }}">
                                {{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $option, ['count' => $option]) }}
                            </option>
                        @endforeach
                    </x-select>
                    <p class="mt-1 text-xs text-zinc-500">
                        {{ __('Choose one of these options: :options flashcards.', ['options' => $quantityRange]) }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-zinc-100 bg-white/70 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            {{ __('ai_flashcards.used_title') }}
                        </p>
                        <p class="mt-1 text-xl font-semibold text-zinc-900">
                            {{ $aiUsedToday }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            {{ trans_choice('ai_flashcards.used_today', $aiUsedToday, ['count' => $aiUsedToday]) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-zinc-100 bg-white/70 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            {{ __('ai_flashcards.remaining_title') }}
                        </p>
                        <p class="mt-1 text-xl font-semibold text-zinc-900">
                            {{ $aiRemainingToday }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            {{ trans_choice('ai_flashcards.remaining_today', $aiRemainingToday, ['count' => $aiRemainingToday]) }}
                        </p>
                    </div>
                </div>

                @if ($aiLimitReached)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                        {{ __('You need at least :count flashcards available to run a request. Try again tomorrow.', ['count' => $aiMinimumQuantity]) }}
                    </div>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">
                            {{ __('Cancel') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="primary" :disabled="$aiLimitReached">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            </form>
        @endif
    </div>
</flux:modal>
