<div>
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
                icon="book-open"
                :href="route('notes.library')"
                wire:navigate>
                {{ __('Browse all notes') }}
            </flux:button>

            <flux:button
                variant="ghost"
                icon="plus"
                :href="route('notebooks.create')"
                wire:navigate>
                {{ __('New notebook') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Notebooks') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($metrics['notebooks']) }}</dd>
                </div>
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <span class="rounded-md bg-zinc-100 p-2 text-zinc-500">
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
                <div class="mt-6 overflow-hidden rounded-md border border-zinc-200">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm text-zinc-700">
                        <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">{{ __('Discipline') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Notes') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Flashcards') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach ($notesByDiscipline as $discipline)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-900">{{ $discipline->title }}</div>
                                    <p class="text-xs text-zinc-500">
                                        {{ __('Notebook: :notebook', ['notebook' => $discipline->notebook?->title ?? __('—')]) }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">{{ number_format($discipline->notes_count) }}</td>
                                <td class="px-4 py-3">{{ number_format($discipline->flashcards_count) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
                    <p>{{ __('Create disciplines and start adding material to unlock these insights.') }}</p>
                </div>
                @endif
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-6">
                <div class="flex items-center justify-between gap-3">
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
                <ul class="mt-6 space-y-4">
                    @foreach ($recentNotes as $note)
                    <li class="rounded-md border border-zinc-200 bg-zinc-50 p-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <a
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                href="{{ route('notes.show', [$note->discipline, $note]) }}"
                                wire:navigate>
                                {{ $note->title }}
                            </a>
                            <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-white px-2 py-0.5 text-xs font-medium text-zinc-600">
                                {{ $note->is_flashcard ? __('Flashcard') : __('Note') }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">
                            {{ $note->discipline?->title ?? __('No discipline') }}
                            • {{ $note->updated_at?->diffForHumans() }}
                        </p>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="mt-6 rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-6 py-12 text-center text-sm text-zinc-500">
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
