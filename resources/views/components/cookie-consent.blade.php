<div
    id="cookie-consent-banner"
    role="region"
    aria-live="polite"
    aria-label="Aviso de privacidade e uso de cookies"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex justify-center px-4 pb-4 transition-all duration-300 ease-out sm:px-6 sm:pb-6"
>
    <div class="pointer-events-auto flex w-full max-w-5xl flex-col gap-4 rounded-2xl border border-neutral-200 bg-white/95 p-5 text-neutral-900 backdrop-blur lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-2 text-sm leading-relaxed text-neutral-600">
            <p class="text-base font-semibold text-neutral-900">Controle sua privacidade</p>
            <p>
                Usamos cookies essenciais para manter sua sessão segura e, com sua permissão, cookies adicionais para entender métricas de uso e melhorar o {{ config('app.name') }} em conformidade com a LGPD. Veja mais detalhes em nossa
                <a href="{{ route('privacy') }}" class="font-semibold text-indigo-600 underline-offset-4 transition hover:text-indigo-500">Política de privacidade</a>.
            </p>
        </div>
        <div class="flex w-full flex-col gap-2 text-sm font-semibold sm:max-w-xs">
            <button
                type="button"
                data-cookie-action="reject"
                class="inline-flex w-full flex-col rounded-xl border border-neutral-200 bg-white/90 px-4 py-3 text-left text-neutral-800 transition hover:border-neutral-300 hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-400"
            >
                <span class="text-base font-semibold text-neutral-900">Manter apenas essenciais</span>
                <span class="text-xs font-normal leading-snug text-neutral-500">Mantém cookies necessários para login, segurança e idioma.</span>
            </button>
            <button
                type="button"
                data-cookie-action="accept"
                class="inline-flex w-full flex-col rounded-xl border border-transparent bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 text-left text-white transition hover:from-indigo-500 hover:to-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500"
            >
                <span class="text-base font-semibold text-white">Permitir todos os cookies</span>
                <span class="text-xs font-normal leading-snug text-indigo-100/80">Inclui também métricas anônimas para aprimorar sua experiência.</span>
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    if (typeof window === 'undefined' || window.__booknotesCookieConsentInitialized) {
        return;
    }

    window.__booknotesCookieConsentInitialized = true;

    const storageKey = 'booknotes-cookie-consent';
    const container = document.getElementById('cookie-consent-banner');

    if (!container) {
        return;
    }

    const readStoredPreference = () => {
        try {
            return JSON.parse(window.localStorage.getItem(storageKey));
        } catch (error) {
            return null;
        }
    };

    const storedPreference = readStoredPreference();

    if (storedPreference && ['accepted', 'essential'].includes(storedPreference.status)) {
        container.remove();
        return;
    }

    const persistPreference = (status) => {
        try {
            window.localStorage.setItem(
                storageKey,
                JSON.stringify({
                    status,
                    updated_at: new Date().toISOString(),
                }),
            );
        } catch (error) {
            // Ignore quota or storage errors to avoid breaking the UI.
        }
    };

    const dismissBanner = () => {
        container.classList.add('translate-y-4', 'opacity-0');
        container.addEventListener(
            'transitionend',
            () => {
                container.remove();
            },
            { once: true },
        );
    };

    container.querySelectorAll('[data-cookie-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.getAttribute('data-cookie-action');
            const status = action === 'accept' ? 'accepted' : 'essential';

            persistPreference(status);
            dismissBanner();
        });
    });
})();
</script>
