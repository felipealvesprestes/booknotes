@props([
    'toast' => session('toast'),
])

@php
    $toast = $toast ?? session('toast');

    if ($toast instanceof \Illuminate\Contracts\Support\Arrayable) {
        $toast = $toast->toArray();
    }

    if (is_string($toast)) {
        $toast = ['message' => $toast];
    }

    $toast = is_array($toast) ? $toast : null;
    $duration = isset($toast['duration']) ? max(0, (int) $toast['duration']) : 6000;
    $variant = $toast['variant'] ?? 'info';

    $variants = [
        'success' => [
            'container' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'accent' => 'bg-emerald-100 text-emerald-700',
            'body' => 'text-emerald-800',
            'icon' => 'check-circle',
        ],
        'warning' => [
            'container' => 'border-amber-200 bg-amber-50 text-amber-900',
            'accent' => 'bg-amber-100 text-amber-700',
            'body' => 'text-amber-800',
            'icon' => 'shield-exclamation',
        ],
        'danger' => [
            'container' => 'border-rose-200 bg-rose-50 text-rose-900',
            'accent' => 'bg-rose-100 text-rose-700',
            'body' => 'text-rose-800',
            'icon' => 'x-circle',
        ],
        'info' => [
            'container' => 'border-zinc-200 bg-white text-zinc-900',
            'accent' => 'bg-zinc-100 text-zinc-700',
            'body' => 'text-zinc-600',
            'icon' => 'information-circle',
        ],
    ];

    $styles = $variants[$variant] ?? $variants['info'];
@endphp

@if ($toast)
    <div
        x-data="{ open: true }"
        x-init="() => { const timeout = {{ $duration }}; if (timeout > 0) { setTimeout(() => open = false, timeout); } }"
        x-show="open"
        x-transition:enter="duration-200 ease-out"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="duration-150 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed inset-x-4 bottom-4 z-50 flex justify-center sm:inset-auto sm:right-6 sm:bottom-6"
    >
        <div
            role="status"
            aria-live="polite"
            class="flex w-full max-w-md items-start gap-3 rounded-2xl border px-4 py-3 text-sm shadow-2xl shadow-black/10 backdrop-blur {{ $styles['container'] }}"
        >
            <span class="flex size-10 items-center justify-center rounded-2xl {{ $styles['accent'] }}">
                <flux:icon :icon="$styles['icon']" variant="outline" class="size-5" />
            </span>

            <div class="flex-1 space-y-1">
                @if ($toast['title'] ?? false)
                    <p class="text-sm font-semibold leading-tight">
                        {{ $toast['title'] }}
                    </p>
                @endif

                @if ($toast['message'] ?? false)
                    <p class="text-sm leading-relaxed {{ $styles['body'] }}">
                        {{ $toast['message'] }}
                    </p>
                @endif
            </div>

            <button
                type="button"
                class="rounded-full p-1 text-sm leading-none text-black/40 transition hover:bg-black/5 hover:text-black/80 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-black/20"
                @click="open = false"
                aria-label="{{ __('Fechar notificação') }}"
            >
                <flux:icon icon="x-mark" variant="outline" class="size-4" />
            </button>
        </div>
    </div>
@endif
