@props([
    'id' => null,
    'label' => null,
    'hint' => null,
    'placeholder' => null,
    'containerClass' => null,
    'searchPlaceholder' => null,
    'noResultsText' => null,
])

@php
    $selectId = $id ?? $attributes->get('id');
    $wireModelDirective = $attributes->wire('model');
    $wireModel = $wireModelDirective?->value();

    if (! $selectId && $wireModel) {
        $selectId = 'select-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $wireModel);
    }

    $selectId ??= 'select-' . uniqid();
    $triggerId = $selectId . '-trigger';
    $dropdownId = $selectId . '-dropdown';

    $placeholderText = $placeholder ?? __('Select an option');
    $searchPlaceholder = $searchPlaceholder ?? __('Search...');
    $noResultsText = $noResultsText ?? __('No results found.');

    $wrapperClass = $attributes->get('class');
    $selectAttributes = $attributes->except('class');
    $isDisabled = $attributes->has('disabled');
@endphp

<div
    x-data="{
        open: false,
        search: '',
        placeholder: @js($placeholderText),
        searchPlaceholder: @js($searchPlaceholder),
        noResultsText: @js($noResultsText),
        selectedValue: '',
        selectedLabel: '',
        options: [],
        disabled: @js($isDisabled),
        init() {
            this.refreshOptions();
            this.syncFromSelect();

            if (window.MutationObserver) {
                const observer = new MutationObserver(() => {
                    this.refreshOptions();
                    this.syncFromSelect();
                });

                observer.observe(this.$refs.select, {
                    childList: true,
                    subtree: true,
                });
            }

            this.$watch('open', value => {
                if (value) {
                    this.$nextTick(() => this.$refs.search?.focus());
                } else {
                    this.search = '';
                }
            });
        },
        refreshOptions() {
            this.options = Array.from(this.$refs.select?.options || []).map(option => ({
                value: option.value,
                label: option.textContent.trim(),
                disabled: option.disabled,
            }));
        },
        filteredOptions() {
            if (! this.search.trim()) {
                return this.options;
            }

            const term = this.search.toLowerCase();

            return this.options.filter(option =>
                option.label.toLowerCase().includes(term),
            );
        },
        toggle() {
            if (this.disabled) {
                return;
            }

            this.open = ! this.open;
        },
        close() {
            this.open = false;
        },
        selectOption(option) {
            if (option.disabled) {
                return;
            }

            this.$refs.select.value = option.value;
            this.$refs.select.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.select.dispatchEvent(new Event('change', { bubbles: true }));

            this.syncFromSelect();
            this.close();
        },
        syncFromSelect() {
            this.selectedValue = this.$refs.select?.value ?? '';
            const match = this.options.find(option => option.value === this.selectedValue);
            this.selectedLabel = match ? match.label : '';
        },
    }"
    x-on:keydown.escape.stop="close()"
    @click.away="close()"
    @class([
        'flex flex-col gap-1',
        $containerClass => filled($containerClass),
        $wrapperClass => filled($wrapperClass),
    ])
>
    @if ($label)
        <label for="{{ $triggerId }}" class="text-sm font-medium text-zinc-700">{{ $label }}</label>
    @endif

    <div class="relative">
        <button
            type="button"
            id="{{ $triggerId }}"
            x-on:click="toggle()"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-controls="{{ $dropdownId }}"
            @class([
                'flex w-full items-center justify-between rounded-md border border-zinc-200 bg-white px-3 py-2 text-left text-sm text-zinc-700 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500',
                'opacity-60 cursor-not-allowed' => $isDisabled,
            ])
        >
            <span x-text="selectedLabel || placeholder" class="truncate"></span>

            <span class="relative flex h-4 w-4 items-center justify-center text-zinc-400">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-4 transition-transform"
                    :class="{ 'rotate-180': open }"
                    @if ($wireModel) wire:loading.remove wire:target="{{ $wireModel }}" @endif
                >
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>

                @if ($wireModel)
                    <svg
                        class="absolute size-4 animate-spin text-indigo-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                        wire:loading
                        wire:target="{{ $wireModel }}"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                @endif
            </span>
        </button>

        <div
            x-cloak
            x-show="open"
            x-transition.origin.top.left
            class="absolute z-10 mt-2 w-full rounded-md border border-zinc-200 bg-white shadow-lg"
            role="listbox"
            id="{{ $dropdownId }}"
        >
            <div class="border-b border-zinc-100 p-2">
                <div class="relative">
                    <input
                        x-ref="search"
                        x-model="search"
                        type="search"
                        class="w-full rounded-md border border-zinc-200 bg-white px-3 py-2 pl-9 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :placeholder="searchPlaceholder"
                        autocomplete="off"
                    />
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.5 5.5a7.5 7.5 0 0011.15 11.15z" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="max-h-56 overflow-y-auto py-1">
                <template x-for="option in filteredOptions()" :key="option.value + option.label">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between px-3 py-2 text-sm"
                        :class="{
                            'text-zinc-700 hover:bg-zinc-50': ! option.disabled,
                            'text-zinc-400 cursor-not-allowed': option.disabled,
                            'bg-indigo-50 text-indigo-600 font-medium': option.value === selectedValue && ! option.disabled,
                        }"
                        @click.prevent="selectOption(option)"
                    >
                        <span x-text="option.label" class="truncate"></span>

                        <svg
                            x-show="option.value === selectedValue"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-4 text-indigo-600"
                        >
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.415l-7.169 7.214a1 1 0 01-1.422 0L3.296 9.073a1 1 0 011.422-1.414l3.195 3.22 6.458-6.59a1 1 0 011.333-.014z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </template>

                <p
                    x-show="filteredOptions().length === 0"
                    class="px-3 py-3 text-center text-sm text-zinc-500"
                    x-text="noResultsText"
                ></p>
            </div>
        </div>
    </div>

    <select
        id="{{ $selectId }}"
        x-ref="select"
        x-on:change="syncFromSelect()"
        x-on:input="syncFromSelect()"
        {{ $selectAttributes->merge([
            'class' => 'hidden',
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if ($hint)
        <p class="text-xs text-zinc-500">{{ $hint }}</p>
    @endif
</div>
