<section class="mt-10 space-y-6">
    <div class="rounded-md border border-red-200 bg-red-50/70 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-red-700">
                    {{ __('Delete account') }}
                </h2>
                <p class="mt-1 text-sm text-red-600/80">
                    {{ __('Delete your account and all of its resources.') }}
                </p>
            </div>

            <flux:modal.trigger name="confirm-user-deletion">
                <flux:button
                    variant="ghost"
                    color="red"
                    type="button"
                    x-data
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                >
                    {{ __('Delete account') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Delete account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
