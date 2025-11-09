<div class="w-full space-y-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Export to PDF') }}</h1>
            <p class="mt-2 text-sm text-zinc-500">
                {{ __('Bundle your notes and flashcards into a polished PDF. Choose what to include, how to organize it, and we will prepare the file for download.') }}
            </p>
        </div>

        <div class="flex flex-col items-start gap-3 sm:items-end">
            @php
                $scopeSelectionMissing = ($scope === 'notebook' && ! $selectedNotebook)
                    || ($scope === 'discipline' && ! $selectedDiscipline);
            @endphp
            <flux:button
                variant="primary"
                icon="arrow-down-tray"
                wire:click="export"
                :disabled="$summary['total'] === 0 || $scopeSelectionMissing"
            >
                {{ __('Generate PDF') }}
            </flux:button>

            <p class="text-xs text-zinc-400">
                {{ __('A download link will be available once the export is ready.') }}
            </p>
        </div>
    </div>

    <x-auth-session-status :status="session('status')" class="max-w-sm" />

    @if ($focusedExport)
        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-800 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <p @class([
                    'font-semibold text-indigo-900',
                    'flex items-center gap-2' => ! $focusedExport->isReady()
                        && $focusedExport->status !== \App\Models\NoteExport::STATUS_FAILED,
                ])>
                    @if ($focusedExport->isReady())
                        {{ __('Your export is ready to download.') }}
                    @elseif ($focusedExport->status === \App\Models\NoteExport::STATUS_FAILED)
                        {{ __('The export failed to generate.') }}
                    @else
                        <flux:icon.loading variant="mini" class="h-4 w-4 animate-spin text-indigo-500" />
                        {{ __('Export queued. We will refresh this list automatically.') }}
                    @endif
                </p>

                @if ($focusedExport->status === \App\Models\NoteExport::STATUS_FAILED && $focusedExport->failure_reason)
                    <p class="text-xs text-indigo-700">
                        {{ $focusedExport->failure_reason }}
                    </p>
                @elseif ($focusedExport->isProcessing())
                    <p class="text-xs text-indigo-700">
                        {{ __('Keep this window open—we poll every few seconds and will show the download link as soon as it is ready.') }}
                    </p>
                @endif
            </div>

            @if ($focusedExport->isReady())
                <flux:button
                    variant="primary"
                    icon="arrow-down-tray"
                    :href="$focusedExport->downloadRoute()"
                    target="_blank"
                    rel="noopener"
                >
                    {{ __('Download PDF') }}
                </flux:button>
            @endif
        </div>
    @endif

    @error('export')
        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $message }}
        </div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">{{ __('Export scope') }}</h2>
                    <p class="text-sm text-zinc-500">
                        {{ __('Pick which part of your workspace should be exported. You can focus on a single discipline or ship everything at once.') }}
                    </p>
                </div>

                <fieldset class="grid gap-3 sm:grid-cols-3">
                    @php
                        $scopeCards = [
                            [
                                'value' => 'all',
                                'title' => __('Entire workspace'),
                                'description' => __('Combine every notebook, discipline, note, and flashcard.'),
                            ],
                            [
                                'value' => 'notebook',
                                'title' => __('Specific notebook'),
                                'description' => __('Focus on a single notebook and its disciplines.'),
                            ],
                            [
                                'value' => 'discipline',
                                'title' => __('Single discipline'),
                                'description' => __('Export just the notes under one discipline.'),
                            ],
                        ];
                    @endphp

                    @foreach ($scopeCards as $card)
                        <label
                            @class([
                                'group relative flex cursor-pointer flex-col rounded-lg border px-4 py-3 text-sm transition-all hover:border-indigo-300',
                                'border-indigo-500 bg-indigo-50 text-indigo-900' => $scope === $card['value'],
                                'border-zinc-200 text-zinc-600 hover:bg-zinc-50' => $scope !== $card['value'],
                            ])
                        >
                            <input
                                type="radio"
                                name="export_scope"
                                value="{{ $card['value'] }}"
                                class="hidden"
                                wire:model.live="scope"
                            />
                            <span class="text-sm font-semibold">{{ $card['title'] }}</span>
                            <span class="mt-2 text-xs leading-relaxed text-inherit">
                                {{ $card['description'] }}
                            </span>
                        </label>
                    @endforeach
                </fieldset>

                @if ($scope === 'notebook')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Notebook') }}</label>
                            <select
                                wire:model.live="selectedNotebook"
                                class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select a notebook') }}</option>
                                @foreach ($notebooks as $notebook)
                                    @php
                                        $totalNotes = $notebook->disciplines->sum('notes_count');
                                        $flashcards = $notebook->disciplines->sum('flashcard_count');
                                    @endphp
                                    <option value="{{ $notebook->id }}">
                                        {{ $notebook->title }} · {{ trans_choice('{0} no notes|{1} :count note|[2,*] :count notes', $totalNotes, ['count' => $totalNotes]) }}
                                        @if ($flashcards > 0)
                                            — {{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $flashcards, ['count' => $flashcards]) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-md border border-dashed border-zinc-200 bg-zinc-50 p-4 text-xs text-zinc-600 sm:col-span-2">
                            {{ __('Need to export multiple notebooks? Generate one PDF per notebook so each file stays focused and easy to navigate.') }}
                        </div>
                    </div>
                @elseif ($scope === 'discipline')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Discipline') }}</label>
                            <select
                                wire:model.live="selectedDiscipline"
                                class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">{{ __('Select a discipline') }}</option>
                                @foreach ($disciplines as $discipline)
                                    <option value="{{ $discipline->id }}">
                                        {{ $discipline->title }}
                                        @if ($discipline->notebook)
                                            — {{ $discipline->notebook->title }}
                                        @endif
                                        · {{ trans_choice('{0} no notes|{1} :count note|[2,*] :count notes', $discipline->notes_count, ['count' => $discipline->notes_count]) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">{{ __('Filters & organization') }}</h2>
                    <p class="text-sm text-zinc-500">{{ __('Decide which content enters the PDF and how it should be grouped.') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Content type') }}</label>
                        <select
                            wire:model.live="noteType"
                            class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="all">{{ __('Notes and flashcards') }}</option>
                            <option value="notes">{{ __('Notes only') }}</option>
                            <option value="flashcards">{{ __('Flashcards only') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Group sections by') }}</label>
                        <select
                            wire:model.live="layoutGrouping"
                            class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="discipline">{{ __('Discipline') }}</option>
                            <option value="notebook">{{ __('Notebook') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Page orientation') }}</label>
                        <select
                            wire:model.live="layoutOrientation"
                            class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="portrait">{{ __('Portrait (recommended)') }}</option>
                            <option value="landscape">{{ __('Landscape') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Layout density') }}</label>
                        <select
                            wire:model.live="layoutDensity"
                            class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="detailed">{{ __('Detailed (full content)') }}</option>
                            <option value="compact">{{ __('Compact (shorter spacing)') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-4">
                    <label class="flex items-start gap-3 text-sm text-zinc-600">
                        <input
                            type="checkbox"
                            wire:model.live="includeNoteBody"
                            class="mt-1 h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>
                            <span class="font-medium text-zinc-900">{{ __('Include full note body') }}</span>
                            <span class="block text-xs text-zinc-500">
                                {{ __('When disabled we only include the note title and synopsis.') }}
                            </span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 text-sm text-zinc-600">
                        <input
                            type="checkbox"
                            wire:model.live="includeFlashcardAnswer"
                            class="mt-1 h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        <span>
                            <span class="font-medium text-zinc-900">{{ __('Show flashcard answer blocks') }}</span>
                            <span class="block text-xs text-zinc-500">
                                {{ __('Useful if you want a printable study deck. Disable to generate question-only sheets.') }}
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">{{ __('Preview snapshot') }}</h2>
                    <p class="text-sm text-zinc-500">
                        {{ __('Here is a quick look at how your PDF will be structured. Final formatting adapts to the density and orientation you selected.') }}
                    </p>
                </div>

                @php
                    $density = $filters['layoutDensity'] ?? 'detailed';
                @endphp

                @if ($previewGroups->flatten()->isEmpty())
                    <div class="rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-6 py-10 text-center text-sm text-zinc-500">
                        <flux:icon.folder-open class="mx-auto h-10 w-10 text-zinc-300" />
                        <p class="mt-3 font-medium text-zinc-700">{{ __('No notes available for this selection') }}</p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ __('Adjust the scope or filters to include at least one note or flashcard.') }}
                        </p>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach ($previewGroups as $groupTitle => $notes)
                            <section class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-zinc-800">{{ $groupTitle }}</h3>
                                    <span class="text-[11px] uppercase tracking-wide text-zinc-400">
                                        {{ trans_choice('{1} :count item|[2,*] :count items', $notes->count(), ['count' => $notes->count()]) }}
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    @foreach ($notes as $note)
                                        <article @class([
                                            'rounded-md border border-zinc-200 bg-white',
                                            'p-4' => $density !== 'compact',
                                            'p-3' => $density === 'compact',
                                        ])>
                                            <header class="flex flex-wrap items-center gap-2">
                                                <span class="text-sm font-semibold text-zinc-900">{{ $note->title }}</span>
                                                <span class="inline-flex items-center gap-1 rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-[11px] font-medium text-zinc-600">
                                                    {{ $note->discipline?->title ?? __('Discipline removed') }}
                                                    @if ($note->discipline?->notebook)
                                                        <span class="text-zinc-300">•</span>
                                                        {{ $note->discipline->notebook->title }}
                                                    @endif
                                                </span>
                                                <span class="mt-1 inline-flex items-center gap-1 rounded-full border {{ $note->is_flashcard ? 'border-green-200 bg-green-50 text-green-600' : 'border-indigo-200 bg-indigo-50 text-indigo-600' }} px-2 py-0.5 text-[11px] font-medium">
                                                    {{ $note->is_flashcard ? __('Flashcard') : __('Note') }}
                                                </span>
                                            </header>

                                            <div class="mt-3 space-y-3 text-xs leading-relaxed text-zinc-600">
                                                <div class="flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-wide text-zinc-400">
                                                    <span>{{ __('Updated at :date', ['date' => $note->updated_at->format('d/m/Y H:i')]) }}</span>
                                                    @if ($note->discipline?->title)
                                                        <span>{{ $note->discipline->title }}</span>
                                                    @endif
                                                    @if ($note->discipline?->notebook?->title)
                                                        <span>{{ $note->discipline->notebook->title }}</span>
                                                    @endif
                                                </div>

                                                @if ($note->is_flashcard)
                                                    <div class="space-y-3">
                                                        <div class="rounded-md border border-zinc-200 bg-zinc-50 p-3">
                                                            <p class="text-[11px] font-semibold uppercase tracking-wide text-indigo-500">
                                                                {{ __('Question') }}
                                                            </p>
                                                            <p class="mt-2 whitespace-pre-line text-sm text-zinc-700">
                                                                {{ \Illuminate\Support\Str::limit($note->flashcard_question ?? $note->title, 200) }}
                                                            </p>
                                                        </div>

                                                        @if (($filters['includeFlashcardAnswer'] ?? true))
                                                            <div class="rounded-md border border-green-200 bg-green-50 p-3">
                                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-green-600">
                                                                    {{ __('Answer') }}
                                                                </p>
                                                                <p class="mt-2 whitespace-pre-line text-sm text-green-700">
                                                                    {{ \Illuminate\Support\Str::limit($note->flashcard_answer ?? $note->content, 260) }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    @if (($filters['includeNoteBody'] ?? true))
                                                        <p class="whitespace-pre-line text-sm text-zinc-700">
                                                            {{ \Illuminate\Support\Str::limit($note->content, 320) }}
                                                        </p>
                                                    @else
                                                        <p class="italic text-zinc-500">
                                                            {{ __('Content hidden. Only titles will appear in the PDF.') }}
                                                        </p>
                                                    @endif
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    @if ($summary['total'] > $previewGroups->flatten()->count())
                        <p class="text-xs text-zinc-500">
                            {{ __('Only a subset is shown here. The PDF will include all :count selected items.', ['count' => $summary['total']]) }}
                        </p>
                    @endif
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Summary') }}</h2>
                        <p class="text-xs text-zinc-500">{{ __('Review the selection before exporting.') }}</p>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-600">
                        {{ trans_choice('[0,0] :count items|{1} :count item|[2,*] :count items', $summary['total'], ['count' => $summary['total']]) }}
                    </span>
                </div>

                <dl class="grid gap-3">
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-zinc-500">{{ __('Notes') }}</dt>
                        <dd class="font-semibold text-zinc-900">{{ $summary['noteCount'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-zinc-500">{{ __('Flashcards') }}</dt>
                        <dd class="font-semibold text-zinc-900">{{ $summary['flashcardCount'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-zinc-500">{{ __('Disciplines') }}</dt>
                        <dd class="font-semibold text-zinc-900">{{ $summary['disciplines'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <dt class="text-zinc-500">{{ __('Notebooks') }}</dt>
                        <dd class="font-semibold text-zinc-900">{{ $summary['notebooks'] }}</dd>
                    </div>
                </dl>

                <div class="rounded-md border border-dashed border-zinc-200 bg-zinc-50 p-4 text-xs text-zinc-600">
                    {{ __('Tip: generate separate PDFs when sharing with classmates so each file keeps a clear focus.') }}
                </div>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-4" wire:poll.5s>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('Recent downloads') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('Latest exports stay available for 24 hours.') }}
                        </p>
                    </div>
                    <flux:icon.clipboard-document-list class="h-6 w-6 text-zinc-300" />
                </div>

                @if ($recentExports->isEmpty())
                    <div class="rounded-md border border-dashed border-zinc-200 bg-zinc-50 px-4 py-6 text-center text-xs text-zinc-500">
                        {{ __('No downloads yet. Generate your first PDF to see it here.') }}
                    </div>
                @else
                    <ul class="space-y-4">
                        @foreach ($recentExports as $export)
                            @php
                                $statusClasses = match ($export->status) {
                                    \App\Models\NoteExport::STATUS_COMPLETED => 'border-green-200 bg-green-50 text-green-700',
                                    \App\Models\NoteExport::STATUS_FAILED => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-zinc-200 bg-zinc-50 text-zinc-600',
                                };
                            @endphp
                            <li class="rounded-md border border-zinc-200 bg-zinc-50 p-4" wire:key="export-{{ $export->id }}">
                                <div class="flex flex-col gap-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-900">{{ $export->file_name }}</p>
                                            <p class="text-xs text-zinc-500">
                                                {{ $export->created_at->format('d/m/Y H:i') }}
                                                ·
                                                @switch($export->status)
                                                    @case(\App\Models\NoteExport::STATUS_COMPLETED)
                                                        {{ __('Ready') }}
                                                        @break
                                                    @case(\App\Models\NoteExport::STATUS_FAILED)
                                                        {{ __('Failed') }}
                                                        @break
                                                    @case(\App\Models\NoteExport::STATUS_PROCESSING)
                                                        {{ __('Processing') }}
                                                        @break
                                                    @default
                                                        {{ __('Queued') }}
                                                @endswitch
                                            </p>
                                        </div>

                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                            {{ $export->statusLabel() }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-500">
                                        <span>{{ trans_choice('{1} :count note|[2,*] :count notes', $export->note_count, ['count' => $export->note_count]) }}</span>
                                        <span>·</span>
                                        <span>{{ trans_choice('{1} :count flashcard|[2,*] :count flashcards', $export->flashcard_count, ['count' => $export->flashcard_count]) }}</span>

                                        @if ($export->finished_at)
                                            <span>·</span>
                                            <span>{{ __('Ready at :time', ['time' => $export->finished_at->format('d/m/Y H:i')]) }}</span>
                                        @endif
                                    </div>

                                    @if ($export->failure_reason && $export->status === \App\Models\NoteExport::STATUS_FAILED)
                                        <p class="text-xs text-rose-600">
                                            {{ $export->failure_reason }}
                                        </p>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="arrow-down-tray"
                                            :href="$export->isReady() ? $export->downloadRoute() : null"
                                            :disabled="! $export->isReady()"
                                        >
                                            {{ __('Download') }}
                                        </flux:button>

                                        @if (! $export->isReady())
                                            <span class="text-[11px] text-zinc-400">
                                                {{ __('Still generating…') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-md border border-indigo-200 bg-indigo-50 p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <flux:icon.clipboard-document-check class="h-8 w-8 text-indigo-400" />
                    <div>
                        <h3 class="text-sm font-semibold text-indigo-900">{{ __('Recommended layout') }}</h3>
                        <p class="text-xs text-indigo-600">
                            {{ __('Use “Detailed” for the richest spacing, or toggle “Compact” when you need slimmer printouts. Landscape works best for long flashcard answers.') }}
                        </p>
                    </div>
                </div>

                <ul class="space-y-3 text-xs text-indigo-700">
                    <li>• {{ __('Detailed keeps comfortable spacing for reading or PDF sharing.') }}</li>
                    <li>• {{ __('Compact tightens padding while preserving all metadata.') }}</li>
                    <li>• {{ __('Landscape is optional—try it when flashcard answers are lengthy.') }}</li>
                </ul>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white p-5 text-xs text-zinc-600">
                <h3 class="text-sm font-semibold text-zinc-900">{{ __('What happens next?') }}</h3>
                <ol class="mt-3 space-y-2">
                    <li>1. {{ __('We queue the export respecting your selections and layout preferences.') }}</li>
                    <li>2. {{ __('A background job builds the PDF and stores it securely for 24 hours.') }}</li>
                    <li>3. {{ __('You receive an in-app notification with the download link.') }}</li>
                </ol>
            </div>
        </aside>
    </div>
</div>
