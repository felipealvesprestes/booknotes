@php
    $aiLimitReached = $aiRemainingToday < $aiMinimumQuantity;
    $quantityRange = implode(', ', $aiQuantityOptions);
    $maxQuantity = ! empty($aiQuantityOptions) ? max($aiQuantityOptions) : 20;
    $remainingAfterRequest = max(0, $aiRemainingToday - $aiQuantity);
@endphp

<div class="rounded-md border border-indigo-100 bg-indigo-50/80 p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="rounded-full bg-white/80 p-2 text-indigo-600">
                <flux:icon.sparkles class="h-5 w-5" />
            </div>

            <div>
                <p class="text-sm font-semibold text-indigo-900">{{ __('Generate flashcards with AI') }}</p>
                <p class="mt-1 text-xs text-indigo-800/80">
                    {{ __('Let Booknotes create flashcards for this discipline in seconds.') }}
                </p>
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

    <p class="mt-2 text-xs font-medium text-indigo-700">
        {{ __('Daily limit: :count flashcards', ['count' => $aiDailyLimit]) }}
    </p>
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

        @if ($aiStatusMessage)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-3 text-sm text-emerald-900">
                {{ $aiStatusMessage }}
            </div>
        @endif

        @if ($aiErrorMessage)
            <div class="rounded-lg border border-rose-200 bg-rose-50/80 p-3 text-sm text-rose-900">
                {{ $aiErrorMessage }}
            </div>
        @endif

        @if ($aiGeneratorStep === 'confirm')
            <div class="space-y-4">
                <p class="text-sm text-zinc-600">
                    {{ __('Review the details below before confirming the generation.') }}
                </p>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Discipline') }}</dt>
                        <dd class="text-right font-medium text-zinc-900">{{ $discipline->title }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Topic') }}</dt>
                        <dd class="text-right font-medium text-zinc-900">{{ $aiTopic }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Study focus (optional)') }}</dt>
                        <dd class="text-right font-medium text-zinc-900">
                            {{ $aiDescription ? $aiDescription : __('No description provided.') }}
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Number of flashcards to generate') }}</dt>
                        <dd class="text-right font-medium text-zinc-900">{{ $aiQuantity }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <dt class="text-zinc-500">{{ __('Remaining after this generation') }}</dt>
                        <dd class="text-right font-medium text-zinc-900">
                            {{ trans_choice('ai_flashcards.remaining_after', $remainingAfterRequest, ['count' => $remainingAfterRequest]) }}
                        </dd>
                    </div>
                </dl>

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
                        <span wire:loading.remove wire:target="generateAiFlashcards">
                            {{ __('Confirm and generate') }}
                        </span>
                        <span wire:loading wire:target="generateAiFlashcards">
                            {{ __('Generating flashcards with AI...') }}
                        </span>
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

                <div class="rounded-lg border border-zinc-200 bg-zinc-50/70 p-3 text-sm text-zinc-700">
                    <p>{{ trans_choice('ai_flashcards.used_today', $aiUsedToday, ['count' => $aiUsedToday]) }}</p>
                    <p class="mt-1">{{ trans_choice('ai_flashcards.remaining_today', $aiRemainingToday, ['count' => $aiRemainingToday]) }}</p>
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
