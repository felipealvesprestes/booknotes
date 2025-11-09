<x-layouts.auth.simple :title="$title ?? null">
    @isset($aside)
        <x-slot name="aside">
            {{ $aside }}
        </x-slot>
    @endisset

    {{ $slot }}
</x-layouts.auth.simple>
