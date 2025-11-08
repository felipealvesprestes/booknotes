<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <style>
            @page {
                margin: 42px 36px 48px 36px;
            }

            html {
                background: #ffffff;
            }

            body {
                font-family: "Inter", "Helvetica Neue", Helvetica, Arial;
                color: #1f2937;
                margin: 0;
                padding: 0;
                background: #ffffff;
                font-size: 12px;
                padding-right: 30px;
            }

            .container {
                width: 100%;
                box-sizing: border-box;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 12px;
                padding: 0 8px 12px;
                border-bottom: 1px solid #e5e7eb;
            }

            .header-title {
                font-size: 18px;
                font-weight: 700;
                margin: 0;
                color: #111827;
            }

            .header-subtitle {
                margin-top: 4px;
                font-size: 12px;
                color: #6b7280;
            }

            .summary-card {
                margin: 16px 0 24px;
                padding: 16px 18px;
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                display: flex;
                flex-wrap: wrap;
                gap: 18px;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .summary-item {
                min-width: 25%;
            }

            .summary-label {
                display: block;
                font-size: 11px;
                text-transform: uppercase;
                color: #6b7280;
                letter-spacing: 0.08em;
                margin-bottom: 4px;
            }

            .summary-value {
                font-size: 15px;
                font-weight: 600;
                color: #111827;
            }

            .section-heading {
                font-size: 14px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #374151;
                margin: 28px 0 12px;
            }

            .cards-page {
                margin-bottom: 24px;
            }

            .cards-page-break {
                page-break-after: always;
                break-after: page;
            }

            .cards-grid {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .cards-grid-notes .note-card-wrapper,
            .cards-grid-flashcards .note-card-wrapper {
                width: 100%;
            }

            .note-card-wrapper {
                display: block;
                width: 100%;
                vertical-align: top;
                page-break-inside: avoid;
                break-inside: avoid;
                break-inside: avoid-page;
            }

            .note-card {
                background: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 10px;
                padding: 14px 16px;
                width: 100%;
                page-break-inside: avoid;
                break-inside: avoid;
                break-inside: avoid-page;
                display: flex;
                flex-direction: column;
                flex: 1;
            }

            .note-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 8px 12px;
                margin-bottom: 10px;
            }

            .note-title {
                font-size: 14px;
                font-weight: 600;
                color: #111827;
                margin: 0;
            }

            .badge {
                font-family: "Inter", "Helvetica Neue", Helvetica, Arial;
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 10px;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 999px;
                border: 1px solid rgba(79, 70, 229, 0.15);
                background: rgba(79, 70, 229, 0.06);
                color: #4338ca;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                margin-top: 4px;
            }

            .badge.note {
                border-color: rgba(37, 99, 235, 0.18);
                background: rgba(59, 130, 246, 0.08);
                color: #1d4ed8;
            }

            .badge.flashcard {
                border-color: rgba(16, 185, 129, 0.2);
                background: rgba(16, 185, 129, 0.12);
                color: #047857;
            }

            .meta {
                font-size: 10px;
                color: #6b7280;
                margin-bottom: 10px;
            }

            .meta span + span::before {
                content: "•";
                padding: 0 6px;
                color: #d1d5db;
            }

            .body {
                font-size: 11.5px;
                line-height: 1.5;
                color: #1f2937;
                white-space: pre-wrap;
            }

            .flashcard-block {
                margin-top: 10px;
                border-radius: 8px;
                border: 1px solid #d1fae5;
                background: #ecfdf5;
                padding: 10px 12px;
                font-family: "Inter", "Helvetica Neue", Helvetica, Arial;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .flashcard-block.question {
                border-color: #e0f2fe;
                background: #eff6ff;
            }

            .flashcard-label {
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 6px;
                color: #03543f;
                font-family: "Inter", "Helvetica Neue", Helvetica, Arial;
            }

            .flashcard-block.question .flashcard-label {
                color: #1e40af;
            }

            .flashcard-split {
                display: block;
            }

            .footer {
                position: fixed;
                bottom: -10px;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 10px;
                color: #9ca3af;
                font-family: "Inter", "Helvetica Neue", Helvetica, Arial;
            }

            .empty {
                font-style: italic;
                color: #9ca3af;
            }

            /* Compact density tweaks */
            body.density-compact {
                font-size: 11px;
            }

            body.density-compact .summary-card {
                padding: 14px 16px;
                margin: 12px 0 20px;
                gap: 14px;
            }

            body.density-compact .note-card {
                padding: 12px 13px;
            }

            body.density-compact .cards-grid {
                gap: 12px;
            }

            body.density-compact .body {
                line-height: 1.35;
            }

            /* Flashcard layout density tweaks */
            body.density-flashcard {
                font-size: 11.5px;
            }

            body.density-flashcard .summary-card {
                margin: 14px 0 22px;
            }

            body.density-flashcard .cards-grid {
                gap: 14px;
            }

            body.density-flashcard .note-card {
                padding: 14px;
            }

            body.density-flashcard .flashcard-split {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            body.density-flashcard .flashcard-block {
                margin-top: 0;
                height: 100%;
            }
        </style>
    </head>
    @php
        $layoutDensity = $filters['layoutDensity'] ?? 'detailed';
        $notesCollection = ($notes ?? collect())->values();
        $flashcardCollection = ($flashcards ?? collect())->values();
        $noteChunks = $notesCollection->chunk(4)->values();
        $flashcardChunks = $flashcardCollection->chunk(3)->values();
        $noteChunkCount = $noteChunks->count();
        $flashcardChunkCount = $flashcardChunks->count();
    @endphp
    <body class="density-{{ $layoutDensity }}">
        <div class="container">
            <header class="header">
                <div>
                    <h1 class="header-title">{{ config('app.name', 'Booknotes') }} — {{ __('Notes export') }}</h1>
                    <p class="header-subtitle">
                        {{ __('Prepared for :user on :date', ['user' => $user->name, 'date' => $generatedAt->format('d/m/Y H:i')]) }}
                    </p>
                </div>
                <div class="badge">
                    {{ strtoupper(($filters['layoutOrientation'] ?? 'portrait') === 'landscape' ? __('Landscape') : __('Portrait')) }}
                </div>
            </header>

            @if ($noteChunkCount > 0)
                <h2 class="section-heading">{{ __('Notes') }}</h2>

                @foreach ($noteChunks as $chunkIndex => $noteChunk)
                    @php
                        $noteShouldBreak = $chunkIndex < ($noteChunkCount - 1) || $flashcardChunkCount > 0;
                        $notePageClasses = 'cards-page cards-page-notes' . ($noteShouldBreak ? ' cards-page-break' : '');
                    @endphp

                    <section class="{{ $notePageClasses }}">
                        <div class="cards-grid cards-grid-notes">
                            @foreach ($noteChunk as $noteItem)
                                &nbsp;
                                <div class="note-card-wrapper note-card-wrapper-note">
                                    <article class="note-card note-card-note">
                                        <div class="note-header">
                                            <h3 class="note-title">{{ $noteItem->title }}</h3>
                                            <span class="badge note">{{ __('Note') }}</span>
                                        </div>

                                        <div class="meta">
                                            <span>{{ __('Created') }}: {{ $noteItem->created_at->format('d/m/Y') }}</span>
                                            <span>{{ __('Updated') }}: {{ $noteItem->updated_at->format('d/m/Y H:i') }}</span>
                                            @if ($noteItem->discipline?->title)
                                                <span>{{ $noteItem->discipline->title }}</span>
                                            @endif
                                            @if ($noteItem->discipline?->notebook?->title)
                                                <span>{{ $noteItem->discipline->notebook->title }}</span>
                                            @endif
                                        </div>

                                        @if (($filters['includeNoteBody'] ?? true))
                                            <div class="body">
                                                {!! nl2br(e($noteItem->content ?: __('(empty)'))) !!}
                                            </div>
                                        @else
                                            <p class="body empty">{{ __('Content hidden in export settings.') }}</p>
                                        @endif
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif

            @if ($flashcardChunkCount > 0)
                <h2 class="section-heading">{{ __('Flashcards') }}</h2>

                @foreach ($flashcardChunks as $chunkIndex => $flashcardChunk)
                    @php
                        $flashcardShouldBreak = $chunkIndex < ($flashcardChunkCount - 1);
                        $flashcardPageClasses = 'cards-page cards-page-flashcards' . ($flashcardShouldBreak ? ' cards-page-break' : '');
                    @endphp

                    <section class="{{ $flashcardPageClasses }}">
                        <div class="cards-grid cards-grid-flashcards">
                            @foreach ($flashcardChunk as $flashcard)
                                &nbsp;
                                <div class="note-card-wrapper note-card-wrapper-flashcard">
                                    <article class="note-card note-card-flashcard">
                                        <div class="note-header">
                                            <h3 class="note-title">{{ $flashcard->title }}</h3>
                                            <span class="badge flashcard">{{ __('Flashcard') }}</span>
                                        </div>

                                        <div class="meta">
                                            <span>{{ __('Created') }}: {{ $flashcard->created_at->format('d/m/Y') }}</span>
                                            <span>{{ __('Updated') }}: {{ $flashcard->updated_at->format('d/m/Y H:i') }}</span>
                                            @if ($flashcard->discipline?->title)
                                                <span>{{ $flashcard->discipline->title }}</span>
                                            @endif
                                            @if ($flashcard->discipline?->notebook?->title)
                                                <span>{{ $flashcard->discipline->notebook->title }}</span>
                                            @endif
                                        </div>

                                        <div class="flashcard-split">
                                            <div class="flashcard-block question">
                                                <p class="flashcard-label">{{ __('Flashcard question') }}</p>
                                                <div class="body">{!! nl2br(e($flashcard->flashcard_question ?: $flashcard->title)) !!}</div>
                                            </div>

                                            @if (($filters['includeFlashcardAnswer'] ?? true))
                                                <div class="flashcard-block">
                                                    <p class="flashcard-label">{{ __('Flashcard answer') }}</p>
                                                    <div class="body">{!! nl2br(e($flashcard->flashcard_answer ?: $flashcard->content)) ?: '<span class="empty">'.__('Empty').'</span>' !!}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            @endif
        </div>

        <footer class="footer">
            {{ config('app.name', 'Booknotes') }} — {{ __('Generated on :date', ['date' => $generatedAt->format('d/m/Y H:i')]) }}
        </footer>
    </body>
</html>
