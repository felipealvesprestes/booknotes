@php
if (! isset($scrollTo)) {
$scrollTo = false;
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
? <<<JS
    (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth' , block: 'start' })
    JS
    : '' ;
    @endphp

    @if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            <span>
                @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
                @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-600 transition hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {!! __('pagination.previous') !!}
                </button>
                @endif
            </span>

            <span>
                @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-600 transition hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {!! __('pagination.next') !!}
                </button>
                @else
                <span class="inline-flex items-center rounded-md border border-zinc-200 bg-white px-3 py-2 text-xs font-medium text-zinc-400 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
                @endif
            </span>
        </div>

        <div class="hidden w-full items-center justify-between sm:flex">
            <div>
                <p class="text-xs text-zinc-500">
                    <span>{!! __('Showing') !!}</span>
                    <span class="font-medium text-zinc-700">{{ $paginator->firstItem() }}</span>
                    <span>{!! __('to') !!}</span>
                    <span class="font-medium text-zinc-700">{{ $paginator->lastItem() }}</span>
                    <span>{!! __('of') !!}</span>
                    <span class="font-medium text-zinc-700">{{ $paginator->total() }}</span>
                    <span>{!! __('results') !!}</span>
                </p>
            </div>

            @php
                $totalPages = $paginator->lastPage();
                $currentPage = $paginator->currentPage();
                $window = 1;

                $pageSequence = [];

                if ($totalPages <= 9) {
                    $pageSequence = range(1, $totalPages);
                } else {
                    $pageSequence[] = 1;

                    $start = max(2, $currentPage - $window);
                    $end = min($totalPages - 1, $currentPage + $window);

                    if ($start > 2) {
                        $pageSequence[] = '…';
                    }

                    for ($page = $start; $page <= $end; $page++) {
                        $pageSequence[] = $page;
                    }

                    if ($end < $totalPages - 1) {
                        $pageSequence[] = '…';
                    }

                    $pageSequence[] = $totalPages;
                }
            @endphp

            <div class="inline-flex items-center gap-px rounded-md border border-zinc-200 bg-white px-1 py-1 text-xs text-zinc-600">
                @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-md px-2 py-1 text-zinc-300 cursor-not-allowed" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md px-2 py-1 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="{{ __('pagination.previous') }}">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                @endif

                @foreach ($pageSequence as $entry)
                @if (is_string($entry))
                <span class="inline-flex items-center px-3 py-1 text-zinc-400" aria-hidden="true">…</span>
                @elseif ($entry === $paginator->currentPage())
                <span class="inline-flex items-center rounded-md bg-zinc-900 px-3 py-1 font-semibold text-white" aria-current="page">
                    {{ $entry }}
                </span>
                @else
                <button
                    type="button"
                    wire:click="gotoPage({{ $entry }}, '{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    class="inline-flex items-center rounded-md px-3 py-1 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="{{ __('Go to page :page', ['page' => $entry]) }}">
                    {{ $entry }}
                </button>
                @endif
                @endforeach

                @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md px-2 py-1 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="{{ __('pagination.next') }}">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                @else
                <span class="inline-flex items-center rounded-md px-2 py-1 text-zinc-300 cursor-not-allowed" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                @endif
            </div>
        </div>
    </nav>
    @endif
