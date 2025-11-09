<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($title = 'Política de Privacidade — ' . config('app.name'))
        @include('partials.head')
    </head>
    <body class="bg-neutral-50 text-neutral-900 antialiased">
        <div class="relative isolate overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-96 bg-gradient-to-b from-indigo-100 via-white to-transparent"></div>

            <header class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-8">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-neutral-900" wire:navigate>
                    {{ config('app.name') }}
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-4 py-2 text-sm font-semibold text-neutral-700 transition hover:border-neutral-300 hover:text-neutral-900" wire:navigate>
                    <span class="hidden sm:inline">Voltar para o site</span>
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            fill-rule="evenodd"
                            d="M7.22 4.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L10.44 10 7.22 6.78a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </a>
            </header>

            <main class="mx-auto max-w-4xl px-6 pb-20">
                <section class="relative overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-white via-white to-indigo-50/70 p-8">
                    <div class="pointer-events-none absolute inset-0 -z-10 opacity-70 [mask-image:linear-gradient(180deg,black,transparent)]">
                        <div class="absolute -top-20 right-10 size-72 rounded-full bg-indigo-200/40 blur-3xl"></div>
                        <div class="absolute -bottom-16 left-16 size-80 rounded-full bg-sky-200/40 blur-3xl"></div>
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-indigo-500">Política de privacidade</p>
                    <h1 class="mt-4 text-3xl font-semibold text-neutral-900 sm:text-4xl">Transparência no uso dos seus dados</h1>
                    <p class="mt-4 text-base leading-relaxed text-neutral-600">
                        Esta política descreve como o {{ config('app.name') }} coleta, utiliza, armazena e protege informações pessoais em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD).
                        Ao utilizar nossas interfaces públicas e autenticadas, você concorda com os termos descritos abaixo.
                    </p>
                    <p class="mt-6 text-sm text-neutral-500">Última atualização: {{ now()->format('d/m/Y') }}</p>
                </section>

                <section class="mt-12 grid gap-6">
                    <article class="rounded-2xl border border-neutral-200 bg-white px-6 py-6">
                        <h2 class="text-xl font-semibold text-neutral-900">Dados que coletamos</h2>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Coletamos apenas informações necessárias para oferecer a plataforma:
                        </p>
                        <ul class="mt-4 space-y-3 text-sm text-neutral-600">
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Dados de identificação (nome, e-mail e foto opcional) fornecidos por você durante cadastro ou autenticação.
                            </li>
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Registros de atividades associados ao uso de notebooks, disciplinas, notas, flashcards e uploads de PDF.
                            </li>
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Informações técnicas limitadas (IP, navegador, dispositivo) necessárias para segurança, prevenção a fraudes e geração de logs.
                            </li>
                        </ul>
                    </article>

                    <article class="rounded-2xl border border-neutral-200 bg-white px-6 py-6">
                        <h2 class="text-xl font-semibold text-neutral-900">Finalidades e bases legais</h2>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Processamos dados com base no cumprimento de contrato, obrigação legal e legítimo interesse, sempre observando os princípios da LGPD:
                        </p>
                        <ul class="mt-4 grid gap-3 text-sm text-neutral-600 sm:grid-cols-2">
                            <li class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-4">
                                <p class="font-semibold text-indigo-900">Autenticação e sessões</p>
                                <p class="mt-1 text-xs text-indigo-900/70">Mantemos sua conta ativa e consistente em todos os dispositivos.</p>
                            </li>
                            <li class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-4">
                                <p class="font-semibold text-indigo-900">Segurança e logs</p>
                                <p class="mt-1 text-xs text-indigo-900/70">Registramos ações críticas para prevenir fraudes e cumprir obrigações legais.</p>
                            </li>
                            <li class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-4 sm:col-span-2">
                                <p class="font-semibold text-indigo-900">Comunicação e suporte</p>
                                <p class="mt-1 text-xs text-indigo-900/70">Utilizamos dados para avisos de produto, respostas de suporte e melhorias baseadas em feedback.</p>
                            </li>
                        </ul>
                    </article>

                    <article class="rounded-2xl border border-neutral-200 bg-white px-6 py-6">
                        <h2 class="text-xl font-semibold text-neutral-900">Cookies e tecnologias similares</h2>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Utilizamos cookies essenciais para autenticação, segurança, preferência de idioma e balanceamento de carga. Com sua permissão, armazenamos cookies adicionais para métricas agregadas que nos ajudam a evoluir a plataforma.
                            Você pode gerenciar o consentimento diretamente no banner exibido em nossa landing page e telas de autenticação.
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-neutral-600">
                            Logs técnicos são retidos pelo tempo mínimo necessário para auditoria e podem ser anonimizados para análises estatísticas.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-neutral-200 bg-white px-6 py-6">
                        <h2 class="text-xl font-semibold text-neutral-900">Direitos do titular</h2>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Você pode, a qualquer momento:
                        </p>
                        <ul class="mt-4 space-y-3 text-sm text-neutral-600">
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Solicitar confirmação de tratamento, acesso, correção ou portabilidade dos dados.
                            </li>
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Requerer anonimização, bloqueio ou eliminação de dados desnecessários ou tratados em desconformidade.
                            </li>
                            <li class="flex gap-3 rounded-xl bg-neutral-50 px-4 py-3">
                                <span class="text-indigo-500">•</span>
                                Revogar consentimentos para cookies opcionais e contestar decisões automatizadas, quando aplicável.
                            </li>
                        </ul>
                        <p class="mt-4 text-sm leading-relaxed text-neutral-600">
                            Para exercer seus direitos, basta entrar em contato utilizando os canais informados abaixo. Responderemos às solicitações dentro de prazos razoáveis, conforme previsto na LGPD.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-neutral-200 bg-white px-6 py-6">
                        <h2 class="text-xl font-semibold text-neutral-900">Armazenamento, segurança e contato</h2>
                        <p class="mt-3 text-sm leading-relaxed text-neutral-600">
                            Os dados são armazenados em provedores que atendem padrões internacionais de segurança e criptografia. Acesso interno é controlado por autenticação forte e registros de atividade.
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-neutral-600">
                            Para dúvidas, solicitações ou incidentes relacionados à privacidade, envie mensagem para
                            <a href="mailto:{{ config('mail.from.address', 'contato@booknotes.com.br') }}" class="font-semibold text-indigo-600 underline-offset-4 hover:text-indigo-500">
                                {{ config('mail.from.address', 'contato@booknotes.com.br') }}
                            </a>.
                        </p>
                    </article>
                </section>

                <section class="mt-12 rounded-3xl border border-indigo-200 bg-indigo-50 px-8 py-6 text-indigo-900">
                    <h2 class="text-xl font-semibold text-indigo-950">Atualizações desta política</h2>
                    <p class="mt-3 text-sm leading-relaxed text-indigo-900/80">
                        Podemos revisar esta política para refletir melhorias no produto ou requisitos legais. Sempre indicaremos a data da última atualização e notificaremos usuários autenticados em caso de mudanças significativas.
                    </p>
                </section>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
