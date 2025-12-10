<div>
    @php
        $showOnboardingHero = $metrics['notebooks'] === 0 || $metrics['disciplines'] === 0 || $metrics['notes'] === 0;
    @endphp

    @if (! $showOnboardingHero)
        <flux:modal
            name="complete-profile"
            wire:model="showProfileCompletionModal"
            class="max-w-lg">
            <div class="space-y-6">
                <div class="flex gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <flux:icon.shield-exclamation class="h-6 w-6" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <flux:heading size="lg">{{ __('Complete your profile') }}</flux:heading>
                        <flux:text>
                            {{ __('We still need a few details to finish your registration. Please keep your billing information up to date.') }}
                        </flux:text>
                    </div>
                </div>

                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-900">{{ __('Missing information') }}</p>

                    @if ($missingProfileFields !== [])
                        <ul class="mt-3 list-disc space-y-1 ps-4 text-sm text-emerald-900">
                            @foreach ($missingProfileFields as $fieldLabel)
                                <li>{{ $fieldLabel }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <p class="mt-3 text-xs text-emerald-800">
                        {{ __('This data is required for invoices and receipts, and only needs to be completed once.') }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="flex-1 sm:flex-none">
                            {{ __('Remind me later') }}
                        </flux:button>
                    </flux:modal.close>

                    <flux:button
                        variant="primary"
                        icon="cog-6-tooth"
                        :href="route('profile.edit')"
                        wire:navigate
                    >
                        {{ __('Update profile') }}
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Welcome back') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Here is a quick overview of your study hub. Use these insights to decide where to focus next.') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button
                variant="primary"
                icon="plus"
                :href="route('notebooks.create')"
                wire:navigate>
                {{ __('New notebook') }}
            </flux:button>
        </div>
    </div>

    @if ($plannerTodayPending->count() > 0)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ __('planner.today_label') }}</p>
                    <h3 class="text-base font-semibold text-amber-900">
                        {{ __('planner.reminder.title', ['count' => $plannerTodayPending->count()]) }}
                    </h3>
                    <p class="text-sm text-amber-800">
                        {{ __('planner.reminder.subtitle') }}
                    </p>
                </div>

                <flux:button
                    variant="primary"
                    color="amber"
                    icon="clipboard-document-check"
                    :href="route('study.planner')"
                    wire:navigate>
                    {{ __('planner.reminder.cta') }}
                </flux:button>
            </div>

            <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($plannerTodayPending->take(3) as $task)
                    <div class="flex items-start justify-between rounded-lg border border-white/40 bg-white/70 px-3 py-2">
                        <div class="flex flex-col gap-0.5">
                            <p class="text-sm font-semibold text-amber-900 truncate">
                                {{ $task->discipline?->title ?? __('planner.labels.any_discipline') }}
                            </p>
                            <p class="text-xs text-amber-800">
                                {{ __('planner.modes.' . $task->study_mode) }} · {{ trans_choice('planner.units.' . $task->unit_label, $task->quantity, ['count' => $task->quantity]) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($showOnboardingHero)
        @php
            $onboardingSteps = [
                [
                    'title' => __('Create notebook'),
                    'description' => __('Create your first notebook to start organizing your notes.'),
                ],
                [
                    'title' => __('Add disciplines'),
                    'description' => __('Create your first discipline to organize notes inside a notebook.'),
                ],
                [
                    'title' => __('Capture notes and flashcards'),
                    'description' => __('Capture your thoughts and promote them to flashcards when ready to study.'),
                ],
                [
                    'title' => __('Start a study session'),
                    'description' => __('Open the Practice menu, pick a study mode, and start reviewing right away.'),
                ],
            ];
        @endphp

        <div class="bg-white">
            <div class="mx-auto w-full">
                <div class="relative isolate overflow-hidden bg-gradient-to-br from-indigo-900 via-zinc-900 to-slate-900 px-6 py-16 text-center text-white rounded-xl sm:px-16">
                    <div class="mx-auto max-w-4xl">
                        <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Build your knowledge base inside Booknotes') }}</h2>
                        <p class="mt-4 text-base text-zinc-200 sm:text-lg">
                            {{ __('Create notebooks, disciplines, and your first note to unlock the full experience. Follow the steps below to activate your personalized study hub.') }}
                        </p>
                    </div>

                    <div class="mt-10 grid gap-4 text-left sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($onboardingSteps as $index => $step)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-left shadow-lg shadow-black/10">
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-200">{{ __('Step :number', ['number' => $index + 1]) }}</p>
                                <h3 class="mt-2 text-lg font-semibold text-white">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm text-zinc-200">{{ $step['description'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12 flex flex-wrap items-center justify-center gap-3">
                        <a
                            href="{{ route('notebooks.create') }}"
                            wire:navigate
                            class="rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-zinc-900 shadow-sm transition hover:bg-zinc-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            {{ __('Create notebook') }}
                        </a>
                        <span class="text-sm text-zinc-300">{{ __('Start with your notebook to unlock the next steps.') }}</span>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Notebooks') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($metrics['notebooks']) }}</dd>
                </div>
                <span class="rounded-md bg-indigo-50 p-2 text-indigo-600">
                    <flux:icon.book-open class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Group disciplines and reduce cognitive load by keeping related notes together.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Disciplines') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($metrics['disciplines']) }}</dd>
                </div>
                <span class="rounded-md bg-sky-50 p-2 text-sky-600">
                    <flux:icon.layout-grid class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Every discipline keeps its own flashcards and notes for faster recall.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Notes') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($metrics['notes']) }}</dd>
                </div>
                <span class="rounded-md bg-amber-50 p-2 text-amber-600">
                    <flux:icon.book-open-text class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Capture ideas quickly, then refine them or promote to flashcards when ready.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Flashcards') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($metrics['flashcards']) }}</dd>
                </div>
                <span class="rounded-md bg-violet-50 p-2 text-violet-600">
                    <flux:icon.sparkles class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Your active memory bank. Convert more notes to reinforce long term learning.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Active sessions') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($activeSessions) }}</dd>
                </div>
                <span class="rounded-md bg-cyan-50 p-2 text-cyan-600">
                    <flux:icon.play class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Pick up exactly where you stopped and avoid resetting your spaced repetition flow.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Completed sessions') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($completedSessions) }}</dd>
                </div>
                <span class="rounded-md bg-emerald-50 p-2 text-emerald-600">
                    <flux:icon.check class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Shows consistency. Celebrate progress and review detailed stats in the study area.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Cards reviewed (30 days)') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($reviewed30d) }}</dd>
                </div>
                <span class="rounded-md bg-rose-50 p-2 text-rose-600">
                    <flux:icon.chart-bar class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Regular reviews keep knowledge fresh. Aim for a steady weekly cadence.') }}
            </p>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Accuracy (30 days)') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $reviewed30d > 0 ? $accuracy30d . '%' : __('—') }}</dd>
                </div>
                <span class="rounded-md bg-fuchsia-50 p-2 text-fuchsia-600">
                    <flux:icon.chart-bar-square class="h-5 w-5" />
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-500">
                {{ __('Keep tracking quality over quantity: accuracy highlights what needs another pass.') }}
            </p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-md border border-zinc-200 bg-white p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Disciplines overview') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('See where your notes and flashcards are concentrated to balance your study load.') }}
                        </p>
                    </div>
                    <span class="rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">
                        {{ __('Top 5 disciplines') }}
                    </span>
                </div>

                @if ($notesByDiscipline->isNotEmpty())
                <p class="sm:hidden mb-2 mt-6 text-xs text-zinc-600 bg-zinc-50 border border-dashed border-zinc-200 rounded-md px-3 py-2">
                    {{ __('Swipe sideways to reveal all options.') }}
                </p>
                <div class="mt-4 sm:mt-6">
                    <div class="flow-root">
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full py-2 align-middle">
                                <table class="relative min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-3">
                                                {{ __('Discipline') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                <span class="sm:hidden">{{ __('Notes dashboard') }}</span>
                                                <span class="hidden sm:inline">{{ __('Notes') }}</span>
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                {{ __('Flashcards') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach ($notesByDiscipline as $discipline)
                                            <tr class="even:bg-gray-50">
                                                <td class="py-4 pr-3 pl-4 text-sm text-gray-900 align-top sm:pl-3">
                                                    <div class="font-medium text-gray-900">{{ $discipline->title }}</div>
                                                    <p class="text-xs text-gray-500">
                                                        {{ __('Notebook: :notebook', ['notebook' => $discipline->notebook?->title ?? __('—')]) }}
                                                    </p>
                                                </td>
                                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                                    {{ number_format($discipline->notes_count) }}
                                                </td>
                                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                                                    {{ number_format($discipline->flashcards_count) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                    <p>{{ __('Create disciplines and start adding material to unlock these insights.') }}</p>
                </div>
                @endif
            </div>

            <div class="rounded-md border border-zinc-200 bg-white">
                <div class="flex items-center justify-between gap-3 p-6">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Recent notes') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('Quick shortcuts to the latest material you captured or converted.') }}
                        </p>
                    </div>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('notes.library')"
                        wire:navigate>
                        {{ __('View library') }}
                    </flux:button>
                </div>

                @if ($recentNotes->isNotEmpty())
                <ul role="list" class="divide-y divide-zinc-100">
                    @foreach ($recentNotes as $note)
                    @php
                        $badgeClasses = $note->is_flashcard
                            ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                            : 'border-zinc-200 bg-zinc-50 text-zinc-600';
                        $iconWrapperClasses = $note->is_flashcard
                            ? 'bg-emerald-50 text-emerald-600'
                            : 'bg-indigo-50 text-indigo-600';
                    @endphp
                    <li>
                        <a
                            class="relative flex flex-col gap-4 px-4 py-4 transition hover:bg-zinc-50 sm:flex-row sm:items-center sm:justify-between sm:px-6 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                            href="{{ route('notes.show', [$note->discipline, $note]) }}"
                            wire:navigate>
                        <div class="flex min-w-0 gap-4">
                            <span class="flex h-12 w-12 flex-none items-center justify-center rounded-full {{ $iconWrapperClasses }}">
                                @if ($note->is_flashcard)
                                    <flux:icon.sparkles class="h-5 w-5" />
                                @else
                                    <flux:icon.book-open-text class="h-5 w-5" />
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-zinc-900">
                                    {{ $note->title }}
                                </p>
                                <p class="mt-2 text-xs text-zinc-500">
                                    {{ $note->discipline?->title ?? __('No discipline') }}
                                    • {{ $note->updated_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3">
                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-medium {{ $badgeClasses }}">
                                {{ $note->is_flashcard ? __('Flashcard') : __('Note') }}
                            </span>
                            <flux:icon.chevron-right class="h-5 w-5 text-zinc-400" />
                        </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                    <p>{{ __('Start by creating a note or converting one into a flashcard to populate this list.') }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-md border border-zinc-200 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Last study sessions') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('Jump back into a queue or review how accuracy evolved over the past two weeks.') }}
                        </p>
                    </div>
                    @if ($lastStudy)
                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 leading-tight">
                        <span>{{ __('Last studied :time', ['time' => $lastStudy->diffForHumans()]) }}</span>
                    </span>
                    @endif
                </div>

                @if ($dailyHistory->isNotEmpty())
                <ul class="mt-6 space-y-3">
                    @foreach ($dailyHistory as $day)
                    <li class="rounded-md border border-zinc-200 p-4">
                        <div class="flex items-center justify-between text-xs font-medium text-zinc-600">
                            <span>{{ $day['date']->translatedFormat('d M') }}</span>
                            <span>{{ number_format($day['reviewed']) }} {{ __('cards') }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-zinc-100">
                            <div
                                class="h-2 rounded-full bg-indigo-500 transition-all"
                                style="width: {{ max(0, min(100, $day['accuracy'])) }}%;">
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">
                            {{ __('Accuracy :accuracy%', ['accuracy' => $day['accuracy']]) }}
                        </p>
                    </li>
                    @endforeach
                </ul>
                @if ($recentSessions->isNotEmpty())
                <div class="mt-6 space-y-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Latest queues') }}</h3>
                    <ul class="space-y-3">
                        @foreach ($recentSessions as $session)
                        @php
                            $isCompleted = $session->status === 'completed';
                            $statusLabel = $isCompleted ? __('Completed') : __('In progress');
                            $statusBadgeClasses = $isCompleted
                                ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                                : 'border-amber-100 bg-amber-50 text-amber-700';
                            $statusDotClasses = $isCompleted ? 'bg-emerald-500' : 'bg-amber-500';
                        @endphp
                        <li class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm shadow-zinc-100/60">
                            <p class="text-sm font-semibold text-zinc-900">
                                {{ $session->discipline?->title ?? __('All disciplines') }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $statusBadgeClasses }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDotClasses }}"></span>
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-zinc-400">•</span>
                                <span>{{ $session->studied_at?->diffForHumans() }}</span>
                            </div>
                            @if (! $isCompleted)
                            <div class="mt-4">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="arrow-top-right-on-square"
                                    :href="route('study.flashcards', ['session' => $session->id])"
                                    wire:navigate>
                                    {{ __('Resume') }}
                                </flux:button>
                            </div>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @else
                <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                    <p>{{ __('Study sessions will appear here after you start reviewing your flashcards.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
