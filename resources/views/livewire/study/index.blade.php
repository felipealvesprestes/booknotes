<div class="space-y-8">
    @unless ($focusMode)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Study hub') }}</h1>
                <p class="text-sm text-zinc-500">
                    {{ __('Review your flashcards with spaced practice, track accuracy, and revisit past sessions whenever you need.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <select
                    wire:model.live="disciplineFilter"
                    class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:w-60">
                    <option value="">{{ __('All disciplines') }}</option>
                    @foreach ($disciplines as $discipline)
                    <option value="{{ $discipline->id }}">{{ $discipline->title }}</option>
                    @endforeach
                </select>

                <flux:button variant="primary" wire:click="startSession" icon="play">
                    {{ __('Start / resume') }}
                </flux:button>

                <flux:button variant="ghost" color="amber" wire:click="restartSession" icon="arrow-path">
                    {{ __('Restart') }}
                </flux:button>
            </div>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if ($errors->has('session'))
        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first('session') }}
        </div>
        @endif

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-md border border-zinc-200 bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Studied today') }}</dt>
                <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $todaySummary['reviewed'] }}</dd>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Cards reviewed across your open sessions.') }}</p>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-green-600">{{ __('Correct answers') }}</dt>
                <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $todaySummary['correct'] }}</dd>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Well done! These cards are sticking.') }}</p>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-amber-600">{{ __('Needs review') }}</dt>
                <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $todaySummary['incorrect'] }}</dd>
                <p class="mt-1 text-xs text-zinc-500">{{ __('We will show these again so you can reinforce them.') }}</p>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-4">
                <dt class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Accuracy') }}</dt>
                <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $todaySummary['accuracy'] }}%</dd>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Aim for steady progress—consistency beats perfection.') }}</p>
            </div>
        </div>
    @endunless

    <div @class([
        'grid gap-6 lg:grid-cols-[2fr_1fr]' => ! $focusMode,
        'flex justify-center' => $focusMode,
    ])>
        <div @class([
            'space-y-6' => ! $focusMode,
            'w-full max-w-3xl' => $focusMode,
        ])>
            <div @class([
                'rounded-md border border-zinc-200 bg-white p-6',
                'mx-auto w-full max-w-3xl shadow-lg' => $focusMode,
                'min-h-[70vh]' => $focusMode,
            ])>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Active session') }}</h2>
                        <p class="text-sm text-zinc-500">
                            {{ $session ? __('Keep answering cards until you finish this queue. Incorrect answers are pushed to the end for spaced review.') : __('Start a session to review your flashcards. We prepare a shuffled deck based on your selection.') }}
                        </p>
                        @if ($session)
                        <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">
                            {{ $session->discipline?->title ?? __('All disciplines') }}
                        </span>
                        @endif
                    </div>
                    @if ($session && $session->hasPendingCards())
                    <flux:button
                        variant="ghost"
                        @class([
                            'self-start whitespace-nowrap text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40 focus-visible:ring-offset-1',
                            '!text-blue-500' => ! $focusMode,
                            '!text-red-500' => $focusMode,
                        ])
                        wire:click="toggleFocusMode"
                    >
                        {{ $focusMode ? __('Exit focus mode') : __('Focus mode') }}
                    </flux:button>
                    @endif
                </div>

                @if ($session)
                @php
                $queueCount = is_array($session->note_ids) ? count($session->note_ids) : 0;
                $position = min($session->current_index + 1, max($queueCount, 1));
                @endphp

                <div class="mt-6">
                    <div class="flex items-center justify-between text-xs font-medium text-zinc-500">
                        <span>{{ __('Progress') }}</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="mt-2 h-3 rounded-full bg-zinc-100">
                        <div
                            class="h-3 rounded-full bg-indigo-500 transition-all duration-500"
                            style="width: {{ $progressPercent }}%;"
                            aria-valuenow="{{ $progressPercent }}"
                            aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500">
                        {{ __('Card :current of :total in today\'s queue', ['current' => $position > $queueCount ? $queueCount : $position, 'total' => $queueCount]) }}
                    </p>
                </div>

                <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 p-6" data-study-card>
                    @if ($currentCard)
                    <div class="space-y-4">
                        <p class="text-xs uppercase tracking-wide text-zinc-900 font-bold">{{ __('Question') }}</p>
                        <h3 class="text-lg font-semibold text-zinc-900">
                            {{ $currentCard->flashcard_question ?: $currentCard->title }}
                        </h3>
                        @if ($currentCard->flashcard_question && $currentCard->title !== $currentCard->flashcard_question)
                        <p class="mt-1 text-xs text-zinc-500">{{ $currentCard->title }}</p>
                        @endif

                        <div class="rounded-md border border-zinc-200 bg-white px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('Answer') }}</p>

                            @if ($showAnswer)
                            <div class="mt-2 text-sm text-zinc-700 whitespace-pre-wrap">
                                {{ $currentCard->flashcard_answer ?: strip_tags($currentCard->content) }}
                            </div>
                            @else
                            <p class="mt-2 text-sm text-zinc-500">{{ __('Ready when you are. Click to reveal the answer and self-grade your recall.') }}</p>

                            <flux:button class="mt-3" variant="primary" wire:click="revealAnswer" icon="eye">
                                {{ __('Reveal answer') }}
                            </flux:button>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                            <flux:button
                                class="flex-1"
                                variant="ghost"
                                color="red"
                                wire:click="markIncorrect"
                                icon="arrow-uturn-left"
                                :disabled="! $showAnswer">
                                {{ __('I need to review') }}
                            </flux:button>

                            <flux:button
                                class="flex-1"
                                variant="primary"
                                wire:click="markCorrect"
                                icon="check"
                                :disabled="! $showAnswer">
                                {{ __('I got it right') }}
                            </flux:button>
                        </div>
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center gap-3 py-10 text-center text-sm text-zinc-500">
                        <flux:icon.sparkles class="h-10 w-10 text-zinc-300" />
                        <p>{{ __('You finished every card in this session. Start a new round or explore your history below.') }}</p>
                    </div>
                    @endif
                </div>

                <dl class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-md border border-zinc-200 bg-zinc-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Reviewed') }}</dt>
                        <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $session->correct_count + $session->incorrect_count }}</dd>
                    </div>
                    <div class="rounded-md border border-zinc-200 bg-zinc-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-green-600">{{ __('Correct') }}</dt>
                        <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $session->correct_count }}</dd>
                    </div>
                    <div class="rounded-md border border-zinc-200 bg-zinc-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-amber-600">{{ __('To revisit') }}</dt>
                        <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $session->incorrect_count }}</dd>
                    </div>
                </dl>
                @else
                <div class="mt-6 flex flex-col items-center gap-3 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center">
                    <flux:icon.bolt class="h-10 w-10 text-indigo-300" />
                    <h3 class="text-lg font-semibold text-zinc-800">{{ __('No session running') }}</h3>
                    <p class="text-sm text-zinc-500">
                        {{ __('Choose a discipline (or keep all) and click “Start / resume” to begin your study routine. We keep track of your progress every day.') }}
                    </p>
                    <flux:button variant="primary" wire:click="startSession" icon="play">
                        {{ __('Begin studying now') }}
                    </flux:button>
                </div>
                @endif
            </div>

            @unless ($focusMode)
            <div class="rounded-md border border-zinc-200 bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Today\'s sessions') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('Review the queues you have started today and jump back in when needed.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-zinc-500">
                            {{ $todaySessions->total() }} {{ __('sessions') }}
                        </span>

                        <label class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                            {{ __('Per page') }}
                            <select
                                wire:model.live="todaySessionsPerPage"
                                class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs font-medium text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                            </select>
                        </label>
                    </div>
                </div>

                @if ($todaySessions->isEmpty())
                    <div class="flex flex-col items-center gap-3 py-8 text-center text-sm text-zinc-500">
                        <flux:icon.calendar class="h-8 w-8 text-zinc-300" />
                        <p>{{ __('No sessions recorded today yet. Start one above and we will display it here for quick access.') }}</p>
                    </div>
                @else
                    <ul class="mt-4 space-y-4">
                        @foreach ($todaySessions as $todaySession)
                            @php
                                $totalQueue = is_array($todaySession->note_ids) ? count($todaySession->note_ids) : 0;
                                $hasPending = $todaySession->hasPendingCards();
                            @endphp
                            <li class="rounded-md border border-zinc-100 bg-zinc-50 p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">
                                            {{ $todaySession->discipline?->title ?? __('All disciplines') }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ __('Correct :correct • Incorrect :incorrect', ['correct' => $todaySession->correct_count, 'incorrect' => $todaySession->incorrect_count]) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full border px-3 py-1 text-xs font-medium {{ $hasPending ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                            {{ $hasPending ? __('In progress') : __('Completed') }}
                                        </span>
                                        @if ($hasPending)
                                            <flux:button variant="ghost" size="xs" wire:click="resumeSession({{ $todaySession->id }})" icon="play">
                                                {{ __('Resume') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-zinc-500">
                                    <span>{{ __('Queue') }}: {{ $todaySession->current_index }} / {{ $totalQueue }}</span>
                                    <span>{{ __('Accuracy') }}: {{ $todaySession->accuracy }}%</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($todaySessions->hasPages())
                        <div class="mt-4">
                            {{ $todaySessions->links('livewire.study.pagination', ['scrollTo' => false]) }}
                        </div>
                    @endif
                @endif
            </div>
            @endunless
        </div>

        @unless ($focusMode)
        <div
            class="relative self-start rounded-md border border-zinc-200 bg-white"
            x-data="{
                atEnd: true,
                init() {
                    const el = this.$refs.dailyHistoryScroll;
                    if (! el) return;

                    const update = () => {
                        this.atEnd = el.scrollTop + el.clientHeight >= el.scrollHeight - 4;
                    };

                    update();
                    el.addEventListener('scroll', update);
                },
            }"
        >
            {{-- Ajuste a classe max-h abaixo para experimentar diferentes alturas neste histórico. --}}
            <div class="max-h-[680px] overflow-y-auto" x-ref="dailyHistoryScroll">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-zinc-900">{{ __('Daily history') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ __('Each row groups all sessions from the same day, so you can revisit your streak and accuracy evolution.') }}
                    </p>

                @if ($dailyHistory->isEmpty())
                <div class="mt-6 flex flex-col items-center gap-3 py-10 text-center text-sm text-zinc-500">
                    <flux:icon.chart-bar class="h-8 w-8 text-zinc-300" />
                    <p>{{ __('As you complete sessions your daily statistics will appear here.') }}</p>
                </div>
                @else
                <div class="mt-4 space-y-4">
                    @foreach ($dailyHistory as $day)
                    <div class="rounded-md border border-zinc-100 bg-zinc-50 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">
                                    {{ $day['date']->translatedFormat('d M Y') }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ __('Sessions: :count • Accuracy: :accuracy%', ['count' => $day['sessions']->count(), 'accuracy' => $day['accuracy']]) }}
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-indigo-600">{{ $day['reviewed'] }} {{ __('cards') }}</span>
                        </div>

                        <div class="mt-3 h-2 rounded-full bg-zinc-200">
                            <div
                                class="h-2 rounded-full bg-indigo-500"
                                style="width: {{ $day['accuracy'] }}%;"
                                aria-valuenow="{{ $day['accuracy'] }}"
                                aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>

                        <div class="mt-4">
                            <ul class="space-y-2 text-xs text-zinc-500">
                                @foreach ($day['sessions'] as $historySession)
                                <li class="flex flex-col gap-1">
                                    <span class="truncate text-sm font-medium text-zinc-700">{{ $historySession->discipline?->title ?? __('All disciplines') }}</span>
                                    <span>{{ $historySession->correct_count }}/{{ $historySession->correct_count + $historySession->incorrect_count }} • {{ $historySession->accuracy }}%</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div
                class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-white to-transparent"
                x-show="! atEnd"
            ></div>
            <div
                class="pointer-events-none absolute inset-x-0 bottom-5 flex justify-center"
                x-show="! atEnd"
                x-transition.opacity
            >
                <span class="inline-flex items-center gap-1 rounded-full bg-white/95 px-2 py-1 text-[11px] font-medium text-zinc-500 shadow-sm">
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75V13l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 111.06-1.06L9.25 13V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Scroll for more') }}
                </span>
            </div>
        </div>
        @endunless
    </div>
</div>
