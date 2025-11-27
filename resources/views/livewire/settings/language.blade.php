<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Language')" :subheading="__('Choose your interface language')">
        @if (session('language_reload_notice'))
            <flux:callout
                class="mb-4"
                variant="success"
                icon="check-circle"
                heading="{{ session('language_reload_notice') }}"
            />
        @endif

        <form wire:submit="updateLanguage" class="my-6 w-full space-y-6">
            <x-select
                wire:model="locale"
                :label="__('Preferred language')"
                :placeholder="__('Select a language')"
            >
                @foreach ($locales as $code => $details)
                    <option value="{{ $code }}">
                        {{ __($details['label']) }}
                        @if (! empty($details['native_label']))
                            ({{ $details['native_label'] }})
                        @endif
                    </option>
                @endforeach
            </x-select>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>
            </div>
        </form>
    </x-settings.layout>
</section>
