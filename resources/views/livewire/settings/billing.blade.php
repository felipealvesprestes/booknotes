@php
    $monthlyAmount = number_format(config('services.stripe.monthly_amount', 14.9), 2, ',', '.');
    $planLabel = $user?->subscriptionPlanName();
@endphp

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Assinatura e cobrança')" :subheading="__('Gerencie sua assinatura paga e o período de testes do Booknotes')">
        <div class="space-y-5">
            @if ($statusMessage)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ $statusMessage }}
                </div>
            @endif

            @if (request('checkout') === 'success')
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    {{ __('Checkout concluído com sucesso! Você receberá um e-mail da Stripe assim que o pagamento for confirmado.') }}
                </div>
            @elseif (request('checkout') === 'cancelled')
                <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                    {{ __('O checkout foi cancelado e nenhuma cobrança foi feita.') }}
                </div>
            @endif

            @if (session('billingMessage'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    {{ session('billingMessage') }}
                </div>
            @endif

            @error('billing')
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
                    {{ $message }}
                </div>
            @enderror

            @unless ($priceConfigured)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ __('Configure o STRIPE_PRICE_ID no arquivo .env para habilitar o checkout.') }}
                </div>
            @endunless

            <div class="rounded-3xl border border-neutral-200 bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-neutral-500">{{ __('Plano atual') }}</p>
                        <p class="text-2xl font-semibold text-neutral-900">{{ $planLabel }}</p>
                        <p class="text-sm text-neutral-600">
                            {{ __('R$ :amount / mês • Stripe Billing com impostos automáticos e suporte a cupons.', ['amount' => $monthlyAmount]) }}
                        </p>
                    </div>

                    @if (! $user->hasLifetimeAccess())
                        <div class="text-sm text-neutral-600">
                            @if ($user->onGenericTrial())
                                <p class="font-medium text-amber-600">
                                    {{ __('Período de teste ativo — :dias dias restantes', ['dias' => $trialDaysRemaining]) }}
                                </p>
                                <p>
                                    {{ __('Seu teste termina em :data.', ['data' => optional($user->trial_ends_at)?->translatedFormat('d \\d\\e F')]) }}
                                </p>
                            @else
                                <p class="font-medium text-neutral-700">
                                    {{ __('Período de teste encerrado') }}
                                </p>
                                @if ($user->trial_ends_at)
                                    <p>{{ __('O teste foi finalizado em :data.', ['data' => $user->trial_ends_at->translatedFormat('d \\d\\e F')]) }}</p>
                                    <p>{{ __('Inicie a assinatura para continuar usando o Booknotes.') }}</p>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-6 space-y-4 text-sm text-neutral-700">
                    @if ($user->hasLifetimeAccess())
                        <p class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
                            {{ __('Você possui acesso vitalício concedido pela equipe do Booknotes. Nenhuma cobrança será aplicada e todos os recursos permanecem liberados.') }}
                        </p>
                    @else
                        @if ($subscription && $subscription->active())
                            <p class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
                                {{ __('Sua assinatura está ativa. A Stripe cuidará das renovações mensais automaticamente e você pode cancelar quando quiser.') }}
                            </p>
                        @elseif ($subscription && $subscription->onGracePeriod())
                            <p class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                                {{ __('Assinatura cancelada — acesso garantido até :data.', ['data' => optional($subscription->ends_at)?->translatedFormat('d \\d\\e F')]) }}
                            </p>
                        @elseif ($subscription && $subscription->pastDue())
                            <p class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-900">
                                {{ __('Há um pagamento pendente. Atualize o método de pagamento para evitar a interrupção do serviço.') }}
                            </p>
                        @else
                            <p class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-neutral-800">
                                {{ __('Você ainda não ativou a assinatura. Após o período de testes, o acesso será bloqueado até que o plano mensal seja contratado.') }}
                            </p>
                        @endif
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (! $user->hasLifetimeAccess())
                        @if (! $subscription || ! $subscription->active())
                            <flux:button wire:click="startCheckout" type="button" variant="primary" wire:target="startCheckout" wire:loading.attr="disabled">
                                {{ __('Assinar agora') }}
                            </flux:button>
                        @endif

                        @if ($subscription && $subscription->onGracePeriod())
                            <flux:button wire:click="resumeSubscription" type="button" variant="primary" wire:target="resumeSubscription" wire:loading.attr="disabled">
                                {{ __('Retomar cobrança') }}
                            </flux:button>
                        @elseif ($subscription && $subscription->active())
                            <flux:button wire:click="cancelSubscription" type="button" variant="ghost" wire:target="cancelSubscription" wire:loading.attr="disabled">
                                {{ __('Cancelar assinatura') }}
                            </flux:button>
                        @endif

                        <!-- <flux:button wire:click="openBillingPortal" type="button" variant="ghost" wire:target="openBillingPortal" wire:loading.attr="disabled">
                            {{ __('Gerenciar cartão / notas fiscais') }}
                        </flux:button> -->
                    @endif
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6">
                <flux:heading size="sm">{{ __('Como funciona a cobrança') }}</flux:heading>
                <div class="mt-4 space-y-3 text-sm leading-relaxed text-neutral-700">
                    <p>
                        {{ __('Oferecemos 14 dias de uso gratuito para que você valide se o Booknotes faz sentido no seu estudo. Terminando esse período, pediremos que conclua a assinatura para manter o acesso aos seus cadernos e PDF anotados.') }}
                    </p>
                    <p>
                        {{ __('O plano mensal custa R$ :amount. A Stripe calcula impostos automaticamente (ISS, IOF e tributos locais) e você receberá recibos em português por e-mail a cada cobrança. É possível aplicar cupons promocionais diretamente na tela de pagamento.', ['amount' => $monthlyAmount]) }}
                    </p>
                    <p>
                        {{ __('Você pode cancelar quando quiser. Caso cancele, o acesso permanece liberado até o fim do ciclo já pago e nada mais será cobrado. Os dados ficam preservados para um eventual retorno.') }}
                    </p>
                    <p>
                        {{ __('Em caso de dúvidas sobre cobrança ou necessidade de nota fiscal personalizada, fale conosco pelo suporte dentro do app ou escreva para contato@booknotes.com.br.') }}
                    </p>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
