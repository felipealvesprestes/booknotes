@php
$scopeLabels = [
'notebook' => __('Notebook'),
'discipline' => __('Discipline'),
];

$questionCountOptions = [10, 30, 50];
$answeredDisplay = $examFinished ? $answeredCount : $answeredSelections;
@endphp

<div
    class="space-y-8"
    x-data="{
        examStarted: @entangle('examStarted').live,
        examFinished: @entangle('examFinished').live,
        scrollToQuestions() {
            this.scrollToRef('questionBlock');
        },
        scrollToResults() {
            this.scrollToRef('resultsBlock');
        },
        scrollToRef(refName) {
            this.$nextTick(() => {
                const target = this.$refs[refName];
                const offset = 24; // px de margem superior

                if (target) {
                    const top = window.pageYOffset + target.getBoundingClientRect().top - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        },
        ensureSidebarState(collapsed) {
            const sidebar = document.querySelector('[data-flux-sidebar]');

            if (! sidebar) {
                return;
            }

            const onDesktop = sidebar.hasAttribute('data-flux-sidebar-on-desktop');
            const isCollapsed = onDesktop
                ? sidebar.hasAttribute('data-flux-sidebar-collapsed-desktop')
                : sidebar.hasAttribute('data-flux-sidebar-collapsed-mobile');

            if (isCollapsed === collapsed) {
                return;
            }

            document.dispatchEvent(new CustomEvent('flux-sidebar-toggle'));
        },
        init() {
            if (this.examStarted) {
                this.$nextTick(() => this.scrollToQuestions());
            }

            this.$watch('examStarted', (started) => {
                if (started) {
                    this.ensureSidebarState(true);
                }

                if (started) {
                    this.scrollToQuestions();
                }
            });

            this.$watch('examFinished', (finished) => {
                if (finished) {
                    this.ensureSidebarState(false);
                }
            });
        }
    }"
    x-on:scroll-to-simulated-questions.window="scrollToQuestions()"
    x-on:scroll-to-simulated-results.window="scrollToResults()">
    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Simulated test') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Create a mock exam with your flashcards, keep an eye on the timer, and store every result for future review.') }}
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr] lg:items-start">
        <section class="space-y-6 rounded-lg border border-zinc-200 bg-white/95 p-6">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Questions selected') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900">
                        {{ $totalQuestions > 0 ? $totalQuestions : $questionCount }}
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ __('Goal for this mock exam.') }}
                    </p>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Answered so far') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900">{{ $answeredDisplay }}</p>
                    <p class="text-xs text-zinc-500">
                        {{ __('Keep a steady rhythm to finish on time.') }}
                    </p>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('Accuracy') }}</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900">
                        {{ $examFinished && $totalQuestions > 0 ? ($score . '%') : '—' }}
                    </p>
                    <p class="text-xs text-zinc-500">
                        {{ $examFinished ? __('Calculated from your current session.') : __('Grades are calculated only after you submit the exam.') }}
                    </p>
                </div>
            </div>

            <div
                class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 p-4"
                x-ref="timerCard"
                x-data="{
                    hidden: @entangle('timerHidden'),
                    startedAtIso: @entangle('startedAt'),
                    completedAtIso: @entangle('completedAt'),
                    serverDuration: @entangle('durationSeconds'),
                    display: '00:00:00',
                    intervalId: null,
                    startTimestamp: null,
                    completedTimestamp: null,
                    lockedSeconds: null,
                    timerInView: true,
                    floatingDismissed: false,
                    floatingVisible: false,
                    visibilityObserver: null,
                    init() {
                        this.$watch('startedAtIso', () => this.sync());
                        this.$watch('completedAtIso', () => this.sync());
                        this.$watch('serverDuration', () => this.sync());
                        this.$watch('hidden', () => this.onVisibilityPreferenceChange());
                        this.observeTimerPosition();
                        this.sync();
                    },
                    observeTimerPosition() {
                        this.$nextTick(() => {
                            const hasObserver = 'IntersectionObserver' in window;
                            const element = this.$refs.timerCard;

                            if (! element) {
                                return;
                            }

                            if (! hasObserver) {
                                const updatePosition = () => {
                                    const rect = element.getBoundingClientRect();
                                    this.timerInView = rect.bottom > 0 && rect.top < window.innerHeight;
                                    if (this.timerInView) {
                                        this.floatingDismissed = false;
                                    }
                                    this.evaluateFloating();
                                };
                                window.addEventListener('scroll', updatePosition, { passive: true });
                                updatePosition();
                                return;
                            }

                            this.visibilityObserver = new IntersectionObserver((entries) => {
                                entries.forEach((entry) => {
                                    this.timerInView = entry.isIntersecting;
                                    if (entry.isIntersecting) {
                                        this.floatingDismissed = false;
                                    }
                                    this.evaluateFloating();
                                });
                            }, { threshold: 0.4 });

                            this.visibilityObserver.observe(element);
                        });
                    },
                    onVisibilityPreferenceChange() {
                        if (this.hidden) {
                            this.floatingVisible = false;
                        } else {
                            this.floatingDismissed = false;
                            this.evaluateFloating();
                        }
                    },
                    dismissFloating() {
                        this.floatingDismissed = true;
                        this.floatingVisible = false;
                    },
                    evaluateFloating() {
                        const hasStarted = this.startTimestamp !== null;
                        this.floatingVisible = ! this.hidden
                            && ! this.timerInView
                            && ! this.floatingDismissed
                            && hasStarted;
                    },
                    sync() {
                        this.startTimestamp = this.startedAtIso
                            ? Math.floor(Date.parse(this.startedAtIso) / 1000)
                            : null;

                        this.completedTimestamp = this.completedAtIso
                            ? Math.floor(Date.parse(this.completedAtIso) / 1000)
                            : null;

                        this.lockedSeconds = this.serverDuration === null || this.serverDuration === undefined
                            ? null
                            : Number(this.serverDuration);

                        if (this.intervalId) {
                            clearInterval(this.intervalId);
                            this.intervalId = null;
                        }

                        this.refresh();
                        this.evaluateFloating();

                        if (this.startTimestamp && this.lockedSeconds === null) {
                            this.intervalId = setInterval(() => this.refresh(), 1000);
                        }
                    },
                    refresh() {
                        if (! this.startTimestamp) {
                            this.display = '00:00:00';

                            return;
                        }

                        const now = Math.floor(Date.now() / 1000);
                        let seconds = this.lockedSeconds ?? Math.max(0, (this.completedTimestamp ?? now) - this.startTimestamp);

                        if (seconds < 0) {
                            seconds = 0;
                        }

                        const hours = Math.floor(seconds / 3600);
                        const minutes = Math.floor((seconds % 3600) / 60);
                        const secs = seconds % 60;

                        this.display = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    }
                }">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Timer') }}</p>
                        <p
                            class="text-2xl font-semibold text-zinc-900"
                            x-show="!hidden"
                            x-text="display"
                            x-cloak></p>
                        <p
                            class="text-base font-medium text-zinc-600"
                            x-show="hidden"
                            x-cloak>
                            {{ __('Timer hidden') }}
                        </p>
                        @if (! $examStarted)
                        <p class="text-xs text-zinc-500">{{ __('Start the simulation to activate the timer.') }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs text-zinc-500"
                            x-text="hidden ? @js(__('Timer hidden')) : @js(__('Timer visible'))">
                            {{ $timerHidden ? __('Timer hidden') : __('Timer visible') }}
                        </span>
                        <flux:button size="sm" variant="ghost" color="zinc" wire:click="toggleTimerVisibility">
                            {{ $timerHidden ? __('Show timer') : __('Hide timer') }}
                        </flux:button>
                    </div>
                </div>

                <div
                    class="fixed top-3 right-6 z-30 flex items-center gap-2 rounded-full border border-zinc-200 bg-white/95 px-4 py-2 text-xs font-semibold text-zinc-900 shadow-lg shadow-zinc-900/10 backdrop-blur"
                    x-show="floatingVisible"
                    x-transition.opacity
                    x-cloak>
                    <flux:icon.clock class="h-4 w-4 text-zinc-500" />
                    <span class="w-[96px] text-center text-sm font-mono" x-text="display"></span>
                    <button
                        type="button"
                        class="rounded-full p-1 text-zinc-400 transition hover:text-zinc-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/60"
                        aria-label="{{ __('Hide timer') }}"
                        x-on:click="dismissFloating">
                        <flux:icon.x-mark class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <section class="space-y-4 rounded-lg border border-zinc-200 bg-white/80 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Scope') }}</p>
                        <p class="text-sm text-zinc-500">{{ __('Choose the source of your questions.') }}</p>
                    </div>

                    <div class="flex rounded-full border border-zinc-200 bg-white p-1 text-sm font-medium text-zinc-500">
                        @foreach ($scopeLabels as $type => $label)
                        @php
                        $isActiveScope = $scopeType === $type;
                        @endphp
                        <button
                            type="button"
                            wire:click="$set('scopeType', '{{ $type }}')"
                            @class([ 'flex-1 rounded-full px-4 py-1 transition' , 'bg-indigo-600 text-white shadow-sm'=> $isActiveScope,
                            'hover:text-zinc-900' => ! $isActiveScope,
                            ])
                            >
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @if ($scopeType === 'notebook')
                    <div class="text-sm font-medium text-zinc-700">
                        <span>{{ __('Notebook') }}</span>
                        <x-select
                            wire:model.live="notebookId"
                            :placeholder="__('Select a notebook')"
                            class="mt-1 w-full">
                            @foreach ($notebooks as $notebook)
                            <option value="{{ $notebook->id }}">{{ $notebook->title }}</option>
                            @endforeach
                        </x-select>
                        @error('notebookId')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    @if ($scopeType === 'discipline')
                    <div class="text-sm font-medium text-zinc-700">
                        <span>{{ __('Discipline') }}</span>
                        <x-select
                            wire:model.live="disciplineId"
                            :placeholder="__('Select a discipline')"
                            class="mt-1 w-full">
                            @foreach ($disciplines as $discipline)
                            <option value="{{ $discipline->id }}">
                                {{ $discipline->title }}
                                @if ($discipline->notebook?->title)
                                — {{ $discipline->notebook->title }}
                                @endif
                            </option>
                            @endforeach
                        </x-select>
                        @error('disciplineId')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <label class="text-sm font-medium text-zinc-700">
                        <span>{{ __('Questions per exam') }}</span>
                        <div class="mt-1 grid grid-cols-3 gap-2">
                            @foreach ($questionCountOptions as $countOption)
                            @php
                            $isActive = (int) $questionCount === $countOption;
                            @endphp
                            <button
                                type="button"
                                wire:click="$set('questionCount', {{ $countOption }})"
                                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                                @class([ 'relative flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/60 focus-visible:ring-offset-1' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> $isActive,
                                'border-zinc-200 text-zinc-600 hover:border-indigo-300 hover:bg-zinc-50' => ! $isActive,
                                ])
                                >
                                <span>{{ $countOption }}</span>
                            </button>
                            @endforeach
                        </div>
                    </label>

                    <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Flashcards available') }}</p>
                        <p class="mt-2 text-lg font-semibold text-zinc-900">
                            {{ $availableFlashcards }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            @if ($availableFlashcards === 0)
                            {{ __('No flashcards found for this scope yet.') }}
                            @else
                            {{ __('We will shuffle them to generate the mock exam.') }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-zinc-500">
                        {{ __('Make sure you have enough flashcards with detailed answers to form convincing alternatives.') }}
                    </div>
                    <flux:button variant="primary" icon="play" wire:click="startExam" x-on:click="ensureSidebarState(true)">
                        {{ $examStarted ? __('Restart simulation') : __('Start simulation') }}
                    </flux:button>
                </div>

                @error('exam')
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $message }}
                </div>
                @enderror
            </section>
        </section>

        <aside class="space-y-4 rounded-lg border border-zinc-200 bg-white/90 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Latest simulations') }}</p>
                    <p class="text-sm text-zinc-500">{{ __('Track how each attempt performed.') }}</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse ($recentAttempts as $attempt)
                @php
                $scopeTypeLabel = $scopeLabels[$attempt->scope_type] ?? __('Scope');
                $scopeTitle = $attempt->metadata['scope_label'] ?? $attempt->notebook?->title ?? $attempt->discipline?->title ?? '—';
                $scopeSubtitle = $attempt->metadata['scope_sub_label'] ?? $attempt->discipline?->notebook?->title;
                $attemptScore = $attempt->score;
                $durationSeconds = $attempt->duration_seconds ?? 0;
                $durationHours = intdiv($durationSeconds, 3600);
                $durationMinutes = intdiv($durationSeconds % 3600, 60);
                $durationSecs = $durationSeconds % 60;
                $durationLabel = $durationHours > 0
                ? sprintf('%02d:%02d:%02d', $durationHours, $durationMinutes, $durationSecs)
                : sprintf('%02d:%02d', $durationMinutes, $durationSecs);
                @endphp
                <article class="space-y-3 rounded-lg border border-zinc-200 bg-white/80 p-4" wire:key="attempt-{{ $attempt->id }}">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ $scopeTitle }}</p>
                            @if ($scopeSubtitle)
                            <p class="text-xs text-zinc-500">{{ $scopeSubtitle }}</p>
                            @endif
                        </div>
                        <span class="text-xl font-semibold text-indigo-600">{{ $attemptScore }}%</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                        <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 font-semibold text-zinc-600">
                            {{ $scopeTypeLabel }}
                        </span>
                        <span>•</span>
                        <span>
                            {{ __('Questions') }}: {{ $attempt->question_count }}
                        </span>
                        <span>•</span>
                        <span>{{ __('Time') }}: {{ $durationLabel }}</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs text-zinc-500">
                            <span>{{ __('Score') }}</span>
                            <span>{{ $attemptScore }}%</span>
                        </div>
                        <div class="mt-1 h-2 rounded-full bg-zinc-100">
                            <div class="h-2 rounded-full bg-indigo-500" style="width: {{ min(100, max(0, $attemptScore)) }}%;"></div>
                        </div>
                    </div>
                </article>
                @empty
                <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-4 py-6 text-center text-sm text-zinc-500">
                    <p class="font-medium text-zinc-700">{{ __('Your simulation history will appear here.') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('Run your first mock exam to unlock insights.') }}</p>
                </div>
                @endforelse
            </div>
        </aside>
    </div>

    @if ($examStarted)
    <section
        x-ref="questionBlock"
        class="space-y-5 rounded-lg border border-zinc-200 bg-white/90 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Progress') }}</p>
                <p class="text-sm font-semibold text-zinc-900">
                    {{ __('Answered :answered of :total', ['answered' => $answeredDisplay, 'total' => $totalQuestions]) }}
                </p>
                <p class="text-xs text-zinc-500">
                    {{ __('All questions are listed below. Select an option for each one before submitting.') }}
                </p>
                <p class="text-xs text-zinc-500">
                    {{ __('Grades are calculated only after you submit the exam.') }}
                </p>
            </div>

            @if ($activeScope)
            <!-- <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-600">
                {{ $scopeLabels[$activeScope['type']] ?? __('Scope') }}:
                <span class="text-zinc-900">{{ $activeScope['label'] }}</span>
            </span> -->
            @endif
        </div>

        <div class="h-2 rounded-full bg-zinc-100">
            <div class="h-2 rounded-full bg-indigo-500 transition-all duration-500" style="width: {{ $progressPercent }}%;"></div>
        </div>

        <div class="space-y-4 border-t border-dashed border-zinc-200 pt-2" data-question-list>
            @foreach ($questions as $index => $question)
            @php
            $selectedKey = $question['selected_key'] ?? null;
            $correctKey = $question['correct_key'] ?? null;
            $isCorrect = $question['is_correct'] ?? null;
            $isUnanswered = $examFinished && $selectedKey === null;
            @endphp
            <!-- rounded-lg border border-zinc-200 bg-white/80  -->
            <article class="space-y-3 p-4" wire:key="question-{{ $question['note_id'] ?? $index }}-{{ $index }}">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            {{ __('Question') }} {{ $question['order'] }}
                        </p>
                        <h3 class="text-base font-semibold text-zinc-900">{{ $question['question'] }}</h3>
                        @if (! empty($question['discipline_title']))
                        <span class="text-xs text-zinc-500">
                            {{ $question['discipline_title'] }}
                            @if ($question['notebook_title'])
                            — {{ $question['notebook_title'] }}
                            @endif
                        </span>
                        @endif
                    </div>

                    @if ($examFinished)
                    <span @class([ 'inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-semibold' , 'border-emerald-200 bg-emerald-50 text-emerald-700'=> $isCorrect,
                        'border-rose-200 bg-rose-50 text-rose-700' => ! $isCorrect && ! $isUnanswered,
                        'border-amber-200 bg-amber-50 text-amber-700' => $isUnanswered,
                        ])>
                        @if ($isUnanswered)
                        <flux:icon.question-mark-circle class="h-4 w-4" />
                        {{ __('Not answered') }}
                        @elseif ($isCorrect)
                        <flux:icon.check class="h-4 w-4" />
                        {{ __('Correct') }}
                        @else
                        <flux:icon.x-mark class="h-4 w-4" />
                        {{ __('Incorrect') }}
                        @endif
                    </span>
                    @endif
                </div>

                <div class="space-y-2">
                    @foreach ($question['options'] as $option)
                    @php
                    $optionKey = $option['key'];
                    $isSelected = $selectedKey === $optionKey;
                    $isCorrectOption = $optionKey === $correctKey;
                    @endphp
                    <button
                        type="button"
                        wire:click="selectOption({{ $index }}, '{{ $optionKey }}')"
                        wire:key="question-{{ $index }}-option-{{ $optionKey }}"
                        wire:loading.attr="disabled"
                        wire:target="selectOption({{ $index }}, '{{ $optionKey }}')"
                        @disabled($examFinished)
                        @class([ 'relative flex w-full items-start gap-3 rounded-lg border px-4 py-3 text-left text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> ! $examFinished && $isSelected,
                        'border-zinc-200 text-zinc-700 hover:border-indigo-300 hover:bg-white' => ! $examFinished && ! $isSelected,
                        'border-emerald-400 bg-emerald-50 text-emerald-900' => $examFinished && $isCorrectOption,
                        'border-rose-300 bg-rose-50 text-rose-900' => $examFinished && $isSelected && ! $isCorrectOption,
                        'border-zinc-100 text-zinc-500 opacity-70 pointer-events-none' => $examFinished && ! $isCorrectOption && ! $isSelected,
                        ])
                        >
                        <div
                            wire:loading.flex
                            wire:target="selectOption({{ $index }}, '{{ $optionKey }}')"
                            class="absolute inset-0 hidden items-center justify-center rounded-lg bg-white/80">
                            <flux:icon.arrow-path class="h-4 w-4 animate-spin text-indigo-600" />
                        </div>

                        <span class="text-xs font-semibold uppercase tracking-wide">{{ $optionKey }}</span>
                        <span class="flex-1">{{ $option['text'] }}</span>
                        @if ($examFinished && $isCorrectOption)
                        <flux:icon.check class="h-4 w-4 text-emerald-600" />
                        @endif
                    </button>
                    @endforeach
                </div>

                @if ($examFinished)
                <div class="text-xs text-zinc-600">
                    <span class="font-semibold text-zinc-900">{{ __('Correct answer') }}:</span>
                    <span>{{ $question['answer'] }}</span>
                </div>
                @endif
            </article>
            @endforeach
        </div>

        <div class="flex flex-col gap-2 border-t border-dashed border-zinc-200 pt-4 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $examFinished ? __('Results saved') : __('Need to wrap up this attempt?') }}</p>
            @if (! $examFinished)
            <flux:button variant="primary" icon="flag" wire:click="finishExam">
                {{ __('Submit exam') }}
            </flux:button>
            @endif
        </div>
    </section>
    @else
    <div class="flex flex-col items-center gap-3 rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-6 py-10 text-center text-sm text-zinc-500">
        <flux:icon.sparkles class="h-10 w-10 text-zinc-300" />
        <p class="text-sm font-medium text-zinc-700">
            {{ __('Define the scope, pick how many questions you want, and we will assemble a mock exam on the spot.') }}
        </p>
        <p class="text-xs text-zinc-500">
            {{ __('Every attempt is saved so you can compare performance later.') }}
        </p>
    </div>
    @endif

    @if ($examFinished)
    <div
        x-ref="resultsBlock"
        class="space-y-4 rounded-xl border border-indigo-200 bg-indigo-50 p-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Results saved') }}</p>
            <p class="mt-2 text-4xl font-semibold text-indigo-900">{{ $score }}%</p>
            <p class="text-sm text-indigo-800">{{ __('Final score for this simulation.') }}</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-indigo-100 bg-white/70 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Answered questions') }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900">{{ $answeredCount }}</p>
            </div>
            <div class="rounded-lg border border-indigo-100 bg-white/70 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Correct answers') }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900">{{ $correctCount }}</p>
            </div>
            <div class="rounded-lg border border-indigo-100 bg-white/70 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Accuracy') }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-900">{{ $score }}%</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-indigo-800">
                {{ __('This attempt is stored in your history. Start another simulation to stay sharp.') }}
            </p>
            <flux:button variant="primary" icon="play" wire:click="startExam" x-on:click="ensureSidebarState(true)">
                {{ __('Start another simulation') }}
            </flux:button>
        </div>
    </div>
    @endif
</div>
