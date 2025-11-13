<x-layouts.auth :title="__('E-mail verificado com sucesso')">
    <div class="flex flex-col items-center gap-6 text-center">
        <div class="flex size-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <div class="space-y-3">
            <flux:heading size="lg">{{ __('E-mail verificado com sucesso') }}</flux:heading>
            <flux:text>
                {{ __('Sua conta está confirmada. Agora você pode acessar todos os cadernos, disciplinas e PDFs do seu hub de estudos.') }}
            </flux:text>
        </div>

        <div class="flex w-full flex-col gap-3">
            <flux:button :href="route('dashboard')" variant="primary" wire:navigate>
                {{ __('Ir para o dashboard') }}
            </flux:button>
            <flux:button :href="route('home')" variant="ghost" wire:navigate>
                {{ __('Voltar para a página inicial') }}
            </flux:button>
        </div>
    </div>
</x-layouts.auth>
