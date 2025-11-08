<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Language')" :subheading="__('Choose your interface language')">
        <form wire:submit="updateLanguage" class="my-6 w-full space-y-6">
            <flux:text muted>
                {{ __('Select the language used across Booknotes.') }}
            </flux:text>

            <flux:select
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
            </flux:select>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="language-updated">
                    {{ __('Language preference updated.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
