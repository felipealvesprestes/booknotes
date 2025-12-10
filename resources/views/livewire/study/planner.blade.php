@php
$modeMeta = [
'flashcards' => ['icon' => 'bolt', 'label' => __('planner.modes.flashcards')],
'true_false' => ['icon' => 'adjustments-horizontal', 'label' => __('planner.modes.true_false')],
'fill_blank' => ['icon' => 'pencil-square', 'label' => __('planner.modes.fill_blank')],
'multiple_choice' => ['icon' => 'bars-3-bottom-left', 'label' => __('planner.modes.multiple_choice')],
'simulated' => ['icon' => 'clipboard-document-check', 'label' => __('planner.modes.simulated')],
];

$statusClasses = [
'pending' => 'bg-amber-100 text-amber-800',
'completed' => 'bg-emerald-100 text-emerald-700',
'cancelled' => 'bg-zinc-100 text-zinc-500',
];
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('planner.heading') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('planner.description') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:button
                variant="ghost"
                color="zinc"
                icon="trash"
                wire:click="confirmReset">
                {{ __('planner.actions.reset') }}
            </flux:button>
        </div>
    </div>

    <flux:modal name="confirm-reset" wire:model="showResetConfirm" class="max-w-md">
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-zinc-900">{{ __('planner.actions.reset') }}</h3>
            <p class="text-sm text-zinc-600">
                {{ __('planner.labels.reset_warning') }}
            </p>
            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" color="zinc">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" color="rose" icon="trash" wire:click="resetPlanTasks">
                    {{ __('planner.actions.reset') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('planner.stats.pending_today') }}</dt>
            <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($planSummary['today_pending']) }}</dd>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <dt class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('planner.stats.completed_today') }}</dt>
            <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($planSummary['today_completed']) }}</dd>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <dt class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('planner.stats.disciplines') }}</dt>
            <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($planSummary['disciplines']) }}</dd>
        </div>

        <div class="rounded-md border border-zinc-200 bg-white p-5">
            <dt class="text-xs font-semibold uppercase tracking-wide text-amber-500">{{ __('planner.stats.weekly_sessions') }}</dt>
            <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ number_format($planSummary['weekly_sessions']) }}</dd>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr] px-2 lg:px-0">
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 space-y-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900">{{ __('planner.labels.today_compact') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __('planner.today_description') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($todayTasks as $task)
                    <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 p-4">
                        <div class="flex flex-col gap-3">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    @php
                                    $mode = $modeMeta[$task->study_mode] ?? null;
                                    @endphp
                                    @if ($mode)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-white/70 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        <flux:icon :icon="$mode['icon']" class="h-4 w-4" />
                                        {{ $mode['label'] }}
                                    </span>
                                    @endif

                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClasses[$task->status] ?? 'bg-zinc-100 text-zinc-500' }}">
                                        {{ __('planner.statuses.' . $task->status) }}
                                    </span>

                                    @if ($task->is_overdue)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                        {{ __('planner.labels.overdue') }}
                                    </span>
                                    @endif
                                </div>

                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-zinc-900 w-full">
                                        {{ $task->discipline?->title ?? __('planner.labels.any_discipline') }}
                                    </h3>
                                    <p class="text-sm text-zinc-500">
                                        {{ trans_choice('planner.units.' . $task->unit_label, $task->quantity, ['count' => $task->quantity]) }}
                                    </p>
                                    <p class="text-xs text-zinc-400">
                                        {{ __('planner.labels.scheduled_for', ['date' => $task->scheduled_for?->translatedFormat('d/m')]) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-dashed border-zinc-200">
                                @if ($task->status === \App\Models\StudyPlanTask::STATUS_PENDING)
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    icon="check"
                                    wire:click="markTaskCompleted({{ $task->id }})">
                                    {{ __('planner.actions.complete') }}
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    color="zinc"
                                    icon="x-mark"
                                    wire:click="cancelTask({{ $task->id }})">
                                    {{ __('planner.actions.cancel') }}
                                </flux:button>
                                @elseif ($task->status === \App\Models\StudyPlanTask::STATUS_COMPLETED)
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    color="zinc"
                                    icon="arrow-path"
                                    wire:click="reopenTask({{ $task->id }})">
                                    {{ __('planner.actions.reopen') }}
                                </flux:button>
                                @else
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    color="zinc"
                                    icon="check"
                                    wire:click="reopenTask({{ $task->id }})">
                                    {{ __('planner.actions.restore') }}
                                </flux:button>
                                @endif

                                @if ($task->actionUrl())
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    color="indigo"
                                    icon="play"
                                    :href="$task->actionUrl()"
                                    wire:navigate>
                                    {{ __('planner.actions.start') }}
                                </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center gap-3 rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-6 py-10 text-center">
                        <flux:icon.sparkles class="h-10 w-10 text-zinc-300" />
                        <p class="text-sm font-medium text-zinc-700">
                            {{ __('planner.today_empty_title') }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            {{ __('planner.today_empty_description') }}
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('planner.upcoming_label') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __('planner.upcoming_description') }}</p>
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($groupedUpcomingTasks as $dateKey => $tasks)
                    @php
                    $date = \Illuminate\Support\Carbon::parse($dateKey);
                    @endphp
                    <div class="rounded-lg border border-zinc-100 bg-zinc-50/80 p-4 space-y-3">
                        <div class="flex items-center justify-between text-sm text-zinc-600">
                            <span class="font-semibold text-zinc-800">{{ $date->translatedFormat('l, d \\d\\e F') }}</span>
                            <span>{{ trans_choice('planner.upcoming_count', $tasks->count(), ['count' => $tasks->count()]) }}</span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($tasks as $task)
                            <div class="flex flex-col gap-2 rounded-md border border-white/60 bg-white/70 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-zinc-800">
                                        {{ $task->discipline?->title ?? __('planner.labels.any_discipline') }}
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        {{ __('planner.labels.mode_prefix') }}
                                        {{ $modeMeta[$task->study_mode]['label'] ?? $task->study_mode }}
                                    </p>
                                </div>
                                <div class="text-xs font-semibold text-zinc-600">
                                    {{ trans_choice('planner.units.' . $task->unit_label, $task->quantity, ['count' => $task->quantity]) }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-6 py-8 text-center">
                        <p class="text-sm font-medium text-zinc-700">
                            {{ __('planner.upcoming_empty_title') }}
                        </p>
                        <p class="text-xs text-zinc-500">
                            {{ __('planner.upcoming_empty_description') }}
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 space-y-6 self-start">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">{{ __('planner.plan_title') }}</h2>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ __('planner.plan_description') }}
                </p>
            </div>

            <form wire:submit.prevent="savePlan" class="space-y-5">
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-zinc-800">{{ __('planner.form.study_days') }}</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ([3, 5, 7] as $dayOption)
                        @php
                        $isActive = (int) $planForm['study_days_per_week'] === $dayOption;
                        @endphp
                        <button
                            type="button"
                            wire:click="$set('planForm.study_days_per_week', {{ $dayOption }})"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                            @class([ 'relative flex items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/60 focus-visible:ring-offset-1' , 'border-indigo-500 bg-indigo-50 text-indigo-900'=> $isActive,
                            'border-zinc-200 text-zinc-600 hover:border-indigo-300 hover:bg-zinc-50' => ! $isActive,
                            ])
                            >
                            <span>{{ $dayOption }}</span>
                            <span class="text-xs font-normal text-zinc-500">{{ __('planner.labels.days_suffix') }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-zinc-800">{{ __('planner.form.disciplines') }}</h3>
                        <span class="text-xs text-zinc-500">{{ __('planner.form.selected_count', ['count' => count($planForm['selected_disciplines'])]) }}</span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($availableDisciplines as $discipline)
                        <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-4 space-y-3">
                            <label class="flex items-center justify-between text-sm font-medium text-zinc-800">
                                <span class="max-w-[85%] sm:truncate">{{ $discipline->title }}</span>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                                    value="{{ $discipline->id }}"
                                    wire:model="planForm.selected_disciplines">
                            </label>
                        </div>
                        @empty
                        <div class="rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-6 py-8 text-center">
                            <p class="text-sm font-medium text-zinc-700">
                                {{ __('planner.form.empty_disciplines_title') }}
                            </p>
                            <p class="text-xs text-zinc-500">
                                {{ __('planner.form.empty_disciplines_description') }}
                            </p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <flux:button type="submit" variant="primary" icon="sparkles">
                        {{ __('planner.actions.save_plan') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>