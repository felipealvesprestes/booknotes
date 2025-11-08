@props([
    'name',
    'title' => __('Are you sure?'),
    'description' => null,
    'confirmText' => __('Confirm'),
    'cancelText' => __('Cancel'),
    'modalClass' => 'max-w-md',
    'align' => 'center',
])

<div {{ $attributes->class('inline-flex') }}>
    @isset($trigger)
        <flux:modal.trigger name="{{ $name }}">
            {{ $trigger }}
        </flux:modal.trigger>
    @endisset

    <flux:modal
        name="{{ $name }}"
        focusable
        :align="$align"
        class="{{ $modalClass }}"
    >
        <div class="space-y-4 text-sm text-zinc-600">
            <div class="space-y-1.5">
                <flux:heading size="lg">{{ $title }}</flux:heading>

                @if ($description)
                    <flux:text>{{ $description }}</flux:text>
                @endif
            </div>

            @if (trim($slot))
                <div class="rounded-md border border-zinc-200 bg-zinc-50 px-4 py-3 text-xs text-zinc-500">
                    {{ $slot }}
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <flux:modal.close>
                <flux:button type="button" variant="filled">
                    {{ $cancelText }}
                </flux:button>
            </flux:modal.close>

            @isset($confirm)
                {{ $confirm }}
            @else
                <flux:button type="button" variant="danger">
                    {{ $confirmText }}
                </flux:button>
            @endisset
        </div>
    </flux:modal>
</div>
