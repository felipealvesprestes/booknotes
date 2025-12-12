@php
    $minQuantity = $quantityOptions[0] ?? 0;
    $limitReached = $remainingToday < $minQuantity;
    $uploadMb = number_format($maxUploadKilobytes / 1024, 1);
    $topErrorMessage = $errorMessage
        ? trim(preg_replace(
            ['/(Divida o PDF com Smallpdf.*)$/i', '/(Split the PDF with Smallpdf.*)$/i'],
            '',
            strip_tags($errorMessage),
        ))
        : null;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('pdf_flashcards.title') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('pdf_flashcards.description') }}
            </p>
        </div>

        <div class="flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50/80 px-3 py-1.5 text-xs font-semibold text-indigo-700">
            <flux:icon.sparkles class="h-4 w-4" />
            {{ trans_choice('ai_flashcards.remaining_after', $remainingToday, ['count' => $remainingToday]) }}
        </div>
    </div>

    @if ($topErrorMessage)
        <div class="rounded-md border border-rose-200 bg-rose-50/80 p-4 text-rose-900">
            <div class="flex items-start gap-3">
                <flux:icon.shield-exclamation class="h-5 w-5 text-rose-500" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold">{{ $topErrorMessage }}</p>
                    <p class="text-xs text-rose-700">{{ __('pdf_flashcards.errors.try_again', ['max' => $maxPages]) }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($statusMessage)
        <div class="rounded-md border border-emerald-200 bg-emerald-50/80 p-4 text-emerald-900">
            <div class="flex items-start gap-3">
                <flux:icon.check-circle class="h-5 w-5 text-emerald-600" />
                <div class="space-y-1">
                    <p class="text-sm font-semibold leading-snug">{{ $statusMessage }}</p>
                    @if ($processedPages > 0)
                        <p class="text-xs text-emerald-700">
                            {{ trans_choice('pdf_flashcards.status.pages_used', $processedPages, ['count' => $processedPages]) }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($disciplines->isEmpty())
        <div class="rounded-md border border-dashed border-zinc-200 bg-white p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-zinc-900">{{ __('pdf_flashcards.empty.title') }}</p>
                    <p class="text-xs text-zinc-500">{{ __('pdf_flashcards.empty.description') }}</p>
                </div>
                <flux:button
                    icon="book-open"
                    :href="route('disciplines.create')"
                    wire:navigate
                    variant="primary"
                >
                    {{ __('Create discipline') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[1.6fr,1fr]">
            <div class="rounded-md border border-zinc-200 bg-white p-6 space-y-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">{{ __('pdf_flashcards.form.title') }}</h2>
                        <p class="text-xs text-zinc-500">
                            {{ __('pdf_flashcards.form.subtitle', ['max' => $maxPages]) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                        <flux:icon.document class="h-4 w-4" />
                        {{ __('pdf_flashcards.form.limit_badge', ['max' => $maxPages]) }}
                        <flux:tooltip
                            position="bottom"
                            align="start"
                            interactive
                            :content="new \Illuminate\Support\HtmlString(__('pdf_flashcards.form.limit_help'))"
                        >
                            <button
                                type="button"
                                class="inline-flex h-6 w-6 items-center justify-center rounded-full text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-1"
                            >
                                <flux:icon.information-circle class="h-4 w-4" />
                                <span class="sr-only">{{ __('pdf_flashcards.form.limit_help_label') }}</span>
                            </button>
                        </flux:tooltip>
                    </div>
                </div>

                <form wire:submit.prevent="generateFromPdf" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-800">{{ __('pdf_flashcards.form.pdf_label') }}</label>
                        <label class="mt-1 flex w-full flex-col items-center justify-center rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-4 py-8 text-center hover:border-zinc-400">
                            <input
                                type="file"
                                accept="application/pdf"
                                class="sr-only"
                                wire:model="pdfUpload"
                            >
                            <flux:icon.document-text class="h-6 w-6 text-zinc-500" />
                            <p class="mt-2 text-sm text-zinc-700">
                                @if ($pdfUpload)
                                    {{ $pdfUpload->getClientOriginalName() }}
                                @else
                                    {{ __('pdf_flashcards.form.pdf_placeholder') }}
                                @endif
                            </p>
                            <p class="text-xs text-zinc-500">
                                {{ __('pdf_flashcards.form.page_limit', ['max' => $maxPages]) }}
                                &middot;
                                {{ __('pdf_flashcards.form.size_limit', ['size' => $uploadMb]) }}
                            </p>
                        </label>
                        <p wire:loading wire:target="pdfUpload" class="mt-2 flex items-center gap-2 text-xs font-medium text-indigo-600">
                            <flux:icon.arrow-path class="h-4 w-4 animate-spin" />
                            {{ __('Uploading...') }}
                        </p>
                        @error('pdfUpload')
                            <p class="text-sm text-rose-600">{!! $message !!}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-select
                                wire:model="disciplineId"
                                :label="__('pdf_flashcards.form.discipline_label')"
                                class="w-full"
                            >
                                @foreach ($disciplines as $discipline)
                                    <option value="{{ $discipline->id }}">
                                        {{ $discipline->title }}
                                    </option>
                                @endforeach
                            </x-select>
                            @error('disciplineId')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-select
                                wire:model="pdfQuantity"
                                :label="__('pdf_flashcards.form.quantity_label')"
                                class="w-full"
                            >
                                @foreach ($quantityOptions as $option)
                                    <option value="{{ $option }}">
                                        {{ trans_choice('pdf_flashcards.form.quantity_option', $option, ['count' => $option]) }}
                                    </option>
                                @endforeach
                            </x-select>
                            @error('pdfQuantity')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                {{ __('pdf_flashcards.usage.used') }}
                            </p>
                            <p class="mt-1 text-xl font-semibold text-zinc-900">{{ $usedToday }}</p>
                            <p class="text-[11px] text-zinc-500">
                                {{ trans_choice('ai_flashcards.used_today', $usedToday, ['count' => $usedToday]) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                {{ __('pdf_flashcards.usage.remaining') }}
                            </p>
                            <p class="mt-1 text-xl font-semibold text-zinc-900">{{ $remainingToday }}</p>
                            <p class="text-[11px] text-zinc-500">
                                {{ trans_choice('ai_flashcards.remaining_today', $remainingToday, ['count' => $remainingToday]) }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-zinc-100 bg-zinc-50/70 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                {{ __('pdf_flashcards.usage.total') }}
                            </p>
                            <p class="mt-1 text-xl font-semibold text-zinc-900">{{ $dailyLimit }}</p>
                            <p class="text-[11px] text-zinc-500">
                                {{ __('Daily limit: :count flashcards', ['count' => $dailyLimit]) }}
                            </p>
                        </div>
                    </div>

                    @if ($limitReached)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            {{ __('pdf_flashcards.form.limit_warning', ['min' => $minQuantity]) }}
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-zinc-500">
                            {{ __('pdf_flashcards.form.helper', ['chars' => number_format($maxCharacters)]) }}
                        </p>

                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="sparkles"
                            :disabled="$limitReached"
                            wire:loading.attr="disabled"
                            wire:target="generateFromPdf,pdfUpload"
                        >
                            <div class="flex items-center gap-2">
                                <flux:icon.loading
                                    class="h-4 w-4"
                                    wire:loading
                                    wire:target="generateFromPdf"
                                />
                                <span wire:loading.remove wire:target="generateFromPdf">
                                    {{ __('pdf_flashcards.form.submit') }}
                                </span>
                                <span wire:loading wire:target="generateFromPdf">
                                    {{ __('Generating flashcards with AI...') }}
                                </span>
                            </div>
                        </flux:button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                <div class="rounded-md border border-indigo-100 bg-indigo-50/70 p-5 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-indigo-900">
                        <flux:icon.shield-check class="h-5 w-5 text-indigo-700" />
                        {{ __('pdf_flashcards.helpers.privacy') }}
                    </div>
                    <p class="text-xs text-indigo-800">
                        {{ __('pdf_flashcards.form.pdf_hint') }}
                    </p>
                </div>

                <div class="rounded-md border border-zinc-200 bg-white/80 p-5 space-y-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-zinc-900">
                        <flux:icon.bolt class="h-5 w-5 text-amber-500" />
                        {{ __('pdf_flashcards.helpers.performance') }}
                    </div>
                    <ul class="list-disc space-y-2 pl-4 text-xs text-zinc-600">
                        <li>{{ __('pdf_flashcards.form.page_limit', ['max' => $maxPages]) }}</li>
                        <li>{{ __('pdf_flashcards.form.size_limit', ['size' => $uploadMb]) }}</li>
                        <li>{{ __('pdf_flashcards.form.helper', ['chars' => number_format($maxCharacters)]) }}</li>
                    </ul>
                </div>

                <div class="rounded-md border border-zinc-200 bg-white/80 p-5 space-y-2">
                    <p class="text-sm font-semibold text-zinc-900">{{ __('pdf_flashcards.form.steps_title') }}</p>
                    <ol class="space-y-2 text-xs text-zinc-600">
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center flex-shrink-0 rounded-full bg-indigo-100 text-[11px] font-semibold leading-none text-indigo-700">1</span>
                            <span>{{ __('pdf_flashcards.form.step_upload') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center flex-shrink-0 rounded-full bg-indigo-100 text-[11px] font-semibold leading-none text-indigo-700">2</span>
                            <span>{{ __('pdf_flashcards.form.step_select') }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center flex-shrink-0 rounded-full bg-indigo-100 text-[11px] font-semibold leading-none text-indigo-700">3</span>
                            <span>{{ __('pdf_flashcards.form.step_generate') }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    @endif
</div>
