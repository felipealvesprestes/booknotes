<div class="space-y-6 w-full">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Document library') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Upload study materials, keep them organized, and open any PDF without leaving Booknotes.') }}
            </p>
        </div>
        @if ($selectedPdf)
            <div class="flex items-center gap-3">
                <flux:button
                    variant="ghost"
                    icon="arrow-top-right-on-square"
                    :href="route('pdfs.preview', $selectedPdf)"
                    target="_blank"
                    rel="noopener"
                >
                    {{ __('Open in new tab') }}
                </flux:button>
            </div>
        @endif
    </div>

    <x-auth-session-status :status="session('status')" />

    <div class="grid gap-6 xl:grid-cols-[360px,1fr]">
        <div class="space-y-6">
            <div class="rounded-md border border-zinc-200 bg-white p-6">
                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-zinc-800">{{ __('Select a PDF') }}</label>
                        <label class="mt-2 flex w-full flex-col items-center justify-center rounded-md border border-dashed border-zinc-300 bg-zinc-50 px-4 py-8 text-center hover:border-zinc-400">
                            <input
                                type="file"
                                accept="application/pdf"
                                class="sr-only"
                                wire:model="upload"
                            >
                            <flux:icon.document-text class="h-6 w-6 text-zinc-500" />
                            <p class="mt-2 text-sm text-zinc-700">
                                @if ($upload)
                                    {{ $upload->getClientOriginalName() }}
                                @else
                                    {{ __('Drop your PDF here or click to browse') }}
                                @endif
                            </p>
                            <p class="text-xs text-zinc-500">{{ __('Up to 20MB per file') }}</p>
                        </label>
                        <p wire:loading wire:target="upload" class="mt-2 text-xs text-indigo-600 flex items-center justify-center gap-2">
                            <flux:icon.arrow-path class="h-4 w-4 animate-spin" />
                            {{ __('Uploading...') }}
                        </p>
                        @error('upload')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <flux:input
                            wire:model="title"
                            :label="__('Short title (optional)')"
                            :placeholder="__('Biology summary, Exam 2, etc...')"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-end gap-3">
                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="arrow-up-tray"
                            wire:loading.attr="disabled"
                            wire:target="save,upload"
                        >
                            {{ __('Upload PDF') }}
                        </flux:button>
                        </div>
                        @if ($showUploadSuccess)
                            <div
                                wire:transition.fade.duration.300ms
                                class="flex items-center justify-end gap-2 text-xs font-medium text-emerald-600"
                            >
                                <flux:icon.check-circle class="h-4 w-4" />
                                {{ __('Upload complete! We highlighted it below.') }}
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            <div class="rounded-md border border-zinc-200 bg-white">
                <div class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 px-4 py-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            :placeholder="__('Search PDFs...')"
                            class="w-full sm:w-60"
                            icon="magnifying-glass"
                        />

                        <label class="flex items-center gap-2 text-xs font-medium text-zinc-500">
                            {{ __('Per page') }}
                            <select
                                wire:model.live="perPage"
                                class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs font-medium text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                @foreach ($perPageOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <span class="text-xs font-medium text-zinc-500">
                        {{ trans_choice('pdfs.library.total', $pdfs->total(), ['count' => $pdfs->total()]) }}
                    </span>
                    <p class="text-xs text-zinc-500">
                        {{ __('Click any PDF to preview it instantly in the reader below.') }}
                    </p>
                </div>

                @if ($pdfs->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center text-sm text-zinc-500">
                        <flux:icon.document-chart-bar class="h-8 w-8 text-zinc-300" />
                        <p>{{ __('Your uploads will appear here once you add them.') }}</p>
                    </div>
                @else
                    <ul class="divide-y divide-zinc-100">
                        @foreach ($pdfs as $pdf)
                            <li
                                wire:key="pdf-{{ $pdf->id }}"
                                class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <button
                                    type="button"
                                    wire:click="selectPdf({{ $pdf->id }})"
                                    class="w-full text-left focus:outline-none"
                                >
                                    <div class="flex items-start gap-3">
                                        <span @class([
                                            'inline-flex h-10 w-10 items-center justify-center rounded-lg border text-sm font-semibold',
                                            'border-indigo-200 bg-indigo-50 text-indigo-700' => $selectedPdf && $selectedPdf->id === $pdf->id,
                                            'border-zinc-200 bg-white text-zinc-500' => ! $selectedPdf || $selectedPdf->id !== $pdf->id,
                                        ])>
                                            PDF
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-900">{{ $pdf->title }}</p>
                                            <p class="text-xs text-zinc-500">
                                                {{ $pdf->readable_size }}
                                                &middot;
                                                {{ $pdf->updated_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </button>

                                <div class="flex items-center justify-end gap-2">
                                    <flux:button
                                        size="xs"
                                        variant="ghost"
                                        icon="arrow-top-right-on-square"
                                        :href="route('pdfs.preview', $pdf)"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        {{ __('Open') }}
                                    </flux:button>
                                    <x-confirm-dialog
                                        class="inline-flex"
                                        name="delete-pdf-{{ $pdf->id }}"
                                        :title="__('Delete PDF')"
                                        :description="__('This action removes the file permanently.')"
                                    >
                                        <x-slot:trigger>
                                            <flux:button
                                                size="xs"
                                                variant="ghost"
                                                color="red"
                                                type="button"
                                            >
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </x-slot:trigger>

                                        <x-slot:confirm>
                                            <flux:modal.close>
                                                <flux:button
                                                    type="button"
                                                    variant="danger"
                                                    wire:click="deletePdf({{ $pdf->id }})"
                                                    wire:loading.attr="disabled"
                                                    class="min-w-[90px]"
                                                >
                                                    {{ __('Delete') }}
                                                </flux:button>
                                            </flux:modal.close>
                                        </x-slot:confirm>
                                    </x-confirm-dialog>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="border-t border-zinc-100 px-4 py-3">
                        {{ $pdfs->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div  class="rounded-md border border-zinc-200 bg-white p-6" wire:key="pdf-reader-container-{{ $selectedPdf?->id ?? 'none' }}">
            @if ($selectedPdf)
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Now reading') }}</p>
                        <h2 class="mt-2 text-xl font-semibold text-zinc-900">
                            {{ $selectedPdf->title }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            {{ $selectedPdf->original_name }} &middot; {{ $selectedPdf->readable_size }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-400">
                            {{ __('Last opened:') }}
                            {{ $selectedPdf->last_opened_at ? $selectedPdf->last_opened_at->diffForHumans() : __('Just now') }}
                        </p>
                    </div>

                    <div class="rounded-md border border-zinc-100 bg-zinc-50 p-3 text-xs text-zinc-600">
                        {{ __('Scroll to read the document. Toolbar controls are disabled inside the reader to keep focus on the content.') }}
                    </div>

                    <div class="overflow-hidden rounded-lg border border-zinc-200">
                        <iframe
                            wire:key="reader-{{ $selectedPdf->id }}"
                            src="{{ route('pdfs.preview', $selectedPdf) }}#toolbar=1"
                            class="h-[96vh] w-full"
                            title="{{ __('PDF reader') }}"
                        ></iframe>
                    </div>
                </div>
            @else
                <div class="flex h-full flex-col items-center justify-center gap-3 text-center text-sm text-zinc-500 min-h-[60vh]">
                    <flux:icon.document-magnifying-glass class="h-10 w-10 text-zinc-300" />
                    <p>{{ __('Upload a PDF or pick one from the list to start reading.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
