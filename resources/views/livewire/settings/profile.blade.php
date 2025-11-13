<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
                <flux:input
                    wire:model.live="cep"
                    :label="__('CEP')"
                    type="text"
                    inputmode="numeric"
                    maxlength="9"
                    autocomplete="postal-code"
                />

                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="lookupCep"
                    wire:loading.attr="disabled"
                    wire:target="lookupCep"
                    class="md:justify-self-end"
                >
                    <span wire:loading.remove wire:target="lookupCep">
                        {{ __('Buscar CEP') }}
                    </span>
                    <span wire:loading wire:target="lookupCep">
                        {{ __('Buscando...') }}
                    </span>
                </flux:button>
            </div>

            <flux:input
                wire:model="cpf"
                :label="__('CPF')"
                type="text"
                inputmode="numeric"
                maxlength="14"
                autocomplete="off"
            />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    wire:model="address_street"
                    :label="__('Street')"
                    type="text"
                    autocomplete="street-address"
                />

                <flux:input
                    wire:model="address_number"
                    :label="__('Number')"
                    type="text"
                    autocomplete="address-line2"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    wire:model="address_neighborhood"
                    :label="__('Neighborhood')"
                    type="text"
                    autocomplete="address-level3"
                />

                <flux:input
                    wire:model="address_city"
                    :label="__('City')"
                    type="text"
                    autocomplete="address-level2"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input
                    wire:model="address_state"
                    :label="__('State')"
                    type="text"
                    autocomplete="address-level1"
                />

                <flux:input
                    wire:model="address_country"
                    :label="__('Country')"
                    type="text"
                    readonly
                    autocomplete="country-name"
                />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
