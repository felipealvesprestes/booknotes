<div
    class="space-y-6"
    wire:keydown.enter.prevent="submitCurrentStep"
    x-data="{
        step: @entangle('step').live,
        focusCurrent() {
            this.$nextTick(() => {
                const targets = {
                    1: this.$refs.notebookInput,
                    2: this.$refs.disciplineInput,
                    3: this.$refs.topicInput,
                };

                const el = targets[this.step] || null;

                if (el) {
                    el.focus();
                }
            });
        },
    }"
    x-init="focusCurrent()"
    x-effect="focusCurrent()"
    x-on:wizard-step-updated.window="step = $event.detail.step; focusCurrent()"
>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Create your first study') }}</h1>
                <p class="text-sm text-zinc-500">
                    {{ __('It takes under 2 minutes. We guide you from notebook to AI flashcards so you can start right away.') }}
                </p>
            </div>

            <flux:button
                variant="ghost"
                :href="route('dashboard')"
                wire:navigate>
                {{ __('Back to dashboard') }}
            </flux:button>
        </div>

        @if ($generatedFlashcards > 0 && ! $isGenerating)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900 flex flex-col gap-1">
            <span class="font-semibold text-emerald-800">
                {{ trans_choice('ai_flashcards.generated', $generatedFlashcards, ['count' => $generatedFlashcards, 'discipline' => $discipline?->title ?? $disciplineTitle]) }}
            </span>
            <span class="text-xs text-emerald-800">{{ __('Choose how you want to study next.') }}</span>
        </div>
        @endif

        <div class="rounded-md border border-zinc-200 bg-white shadow-zinc-100">
            <div class="border-b border-zinc-100 p-3">
                <nav aria-label="{{ __('Progress') }}" class="overflow-hidden">
                    <ol class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($steps as $number => $wizardStep)
                        @php
                        $status = $number < $step ? 'complete' : ($number===$step ? 'current' : 'upcoming' );
                            @endphp
                            <li class="relative">
                            <div @class([ 'flex h-full flex-col gap-2 rounded-md border px-3 py-3 transition' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> $status === 'current',
                                'border-zinc-200 bg-white text-zinc-700' => $status === 'upcoming',
                                'border-emerald-200 bg-emerald-50 text-emerald-900' => $status === 'complete',
                                ])>
                                <div class="flex items-center gap-2">
                                    <span @class([ 'flex h-8 w-8 items-center justify-center rounded-full border text-sm font-semibold' , 'border-indigo-500 bg-white text-indigo-600'=> $status === 'current',
                                        'border-emerald-500 bg-emerald-500 text-white' => $status === 'complete',
                                        'border-zinc-200 bg-white text-zinc-500' => $status === 'upcoming',
                                        ])>
                                        @if ($status === 'complete')
                                        <flux:icon.check class="h-4 w-4" />
                                        @else
                                        {{ sprintf('%02d', $number) }}
                                        @endif
                                    </span>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">{{ $wizardStep['label'] }}</p>
                                        <p class="text-xs text-inherit">{{ $wizardStep['description'] }}</p>
                                    </div>
                                </div>
                            </div>
                            </li>
                            @endforeach
                    </ol>
                </nav>
            </div>

            <div class="space-y-6 p-4 sm:p-6">
                @if ($isGenerating)
                <div class="flex items-center gap-2 rounded-lg border border-indigo-100 bg-indigo-50/80 px-4 py-3 text-sm text-indigo-900">
                    <flux:icon.loading class="h-4 w-4 animate-spin" />
                    <span>{{ __('Generating your content...') }}</span>
                </div>
                @endif

                @if ($generationError)
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                    {{ $generationError }}
                </div>
                @endif

                @if ($step === 1)
                <div class="space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('How do you want to organize this study?') }}</h2>
                        <p class="text-sm text-zinc-500">
                            {{ __('Name the notebook that will hold this study. You can edit this later.') }}
                        </p>
                    </div>

                        <div class="space-y-2">
                            <flux:input
                                x-ref="notebookInput"
                                wire:model.defer="notebookTitle"
                                :label="__('Notebook name')"
                                type="text"
                                maxlength="255"
                                autocomplete="off"
                                :placeholder="__('E.g. Exam prep, Semester review, Bar exam...')" />
                            <p class="text-xs text-zinc-500">{{ __('You can edit this later.') }}</p>
                        </div>
                    </div>
                @endif

                @if ($step === 2)
                <div class="space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Which discipline do you want to study?') }}</h2>
                        <p class="text-sm text-zinc-500">
                            {{ __('Add the discipline inside this notebook to keep related flashcards together.') }}
                        </p>
                    </div>

                        <div class="space-y-2">
                            <flux:input
                                x-ref="disciplineInput"
                                wire:model.defer="disciplineTitle"
                                :label="__('Discipline name')"
                                type="text"
                                maxlength="255"
                                autocomplete="off"
                                :placeholder="__('E.g. Anatomy basics, Contract law, Front-end foundations...')" />
                        </div>
                    </div>
                @endif

                @if ($step === 3)
                <div class="space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('What do you want to study now?') }}</h2>
                        <p class="text-sm text-zinc-500">
                            {{ __('Add the topic and what you want the AI to prioritize so we can generate better flashcards.') }}
                        </p>
                    </div>

                        <div class="space-y-3">
                            <flux:input
                                x-ref="topicInput"
                                wire:model.defer="aiTopic"
                                :label="__('Topic')"
                                :placeholder="__('E.g. Digestive system, Reconstruction era, PHP OOP...')" />

                            <flux:textarea
                                wire:model.defer="aiDescription"
                                :label="__('What do you want to learn about this topic?')"
                            rows="4"
                            :placeholder="__('Key concepts, definitions, examples, exceptions...')" />

                        <p class="text-xs text-zinc-500">{{ __('The AI uses this to generate your content.') }}</p>
                    </div>
                </div>
                @endif

                @if ($step === 4)
                <div class="space-y-4">
                    <div class="space-y-1">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('How many flashcards do you want to generate?') }}</h2>
                        <p class="text-sm text-zinc-500">
                            {{ __('We recommend starting with a small batch to test the flow. You can adjust this later.') }}
                        </p>
                    </div>

                    <fieldset class="grid gap-3 sm:grid-cols-3">
                        @foreach ($aiQuantityOptions as $option)
                        @php
                        $isActive = (int) $aiQuantity === (int) $option;
                        @endphp
                        <label
                            @class([ 'relative flex cursor-pointer flex-col rounded-lg border px-4 py-3 text-sm transition-all' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> $isActive,
                            'border-zinc-200 text-zinc-600 hover:border-indigo-300 hover:bg-zinc-50' => ! $isActive,
                            ])
                            >
                            <input
                                type="radio"
                                class="sr-only"
                                name="aiQuantity"
                                value="{{ $option }}"
                                wire:model.live="aiQuantity" />
                            <span class="text-sm font-semibold">
                                {{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $option, ['count' => $option]) }}
                            </span>
                            <span class="mt-2 text-xs leading-relaxed text-inherit">
                                {{ __('Quick to review and easy to retry if you want to tweak the focus.') }}
                            </span>
                        </label>
                        @endforeach
                    </fieldset>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500">
                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-3 py-1 font-medium text-zinc-700">
                            <flux:icon.sparkles class="h-4 w-4" />
                            {{ __('The AI uses this to generate your content.') }}
                        </span>
                        <span class="text-zinc-500">
                            {{ trans_choice('ai_flashcards.remaining_today', $aiRemainingToday, ['count' => $aiRemainingToday]) }}
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <div class="border-t border-zinc-100 bg-white/95 p-4 backdrop-blur sm:p-6 sm:backdrop-blur-0 sm:bg-transparent sm:border-t sm:border-zinc-100 sm:static sticky bottom-0">
                <div class="flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        {{ __('Step :current of :total', ['current' => $step, 'total' => count($steps)]) }}
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:gap-2">
                        <flux:button
                            variant="ghost"
                            wire:click="previousStep"
                            :disabled="$step === 1 || $isGenerating">
                            {{ __('Back') }}
                        </flux:button>

                        @if ($step < 4)
                            <flux:button
                            variant="primary"
                            wire:click="nextStep"
                            wire:loading.attr="disabled"
                            wire:target="nextStep">
                            {{ __('Next') }}
                            </flux:button>
                            @else
                            <flux:button
                                variant="primary"
                                wire:click="submit"
                                wire:loading.attr="disabled"
                                wire:target="submit">
                                <div class="flex items-center justify-center gap-2">
                                    <flux:icon.loading
                                        class="h-4 w-4"
                                        wire:loading
                                        wire:target="submit" />
                                    <span wire:loading.remove wire:target="submit">
                                        {{ __('Generate content and start studying') }}
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        {{ __('Generating your content...') }}
                                    </span>
                                </div>
                            </flux:button>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <flux:modal
        name="study-mode-picker"
        wire:model="showStudyModeModal"
        focusable
        class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Choose how you want to study') }}</flux:heading>
                <p class="text-sm text-zinc-500">
                    {{ __('We will open the mode with your new discipline. You can change the mode later.') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($studyModes as $mode)
                @php
                $active = $selectedStudyMode === $mode['value'];
                @endphp
                <label
                    @class([ 'flex cursor-pointer flex-col rounded-lg border px-4 py-3 text-sm transition-all' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> $active,
                    'border-zinc-200 text-zinc-600 hover:border-indigo-300 hover:bg-zinc-50' => ! $active,
                    ])
                    >
                    <input
                        type="radio"
                        value="{{ $mode['value'] }}"
                        class="sr-only"
                        wire:model.live="selectedStudyMode" />
                    <span class="flex items-center justify-between">
                        <span class="text-sm font-semibold">{{ $mode['title'] }}</span>
                        @if ($active)
                        <flux:icon.check class="h-4 w-4 text-indigo-600" />
                        @endif
                    </span>
                    <span class="mt-2 text-xs leading-relaxed text-inherit">
                        {{ $mode['description'] }}
                    </span>
                </label>
                @endforeach
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
                        {{ __('Back') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    wire:click="startStudy">
                    {{ __('Begin studying now') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
