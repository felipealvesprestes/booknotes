const COOKIE_PREFERENCE_NAME = 'booknotes_cookie_preferences';
const ONE_YEAR_IN_SECONDS = 60 * 60 * 24 * 365;

const getCookieValue = (name) =>
    document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .find((cookie) => cookie.startsWith(`${name}=`))
        ?.split('=')[1] ?? null;

const setCookiePreference = (value) => {
    document.cookie = `${COOKIE_PREFERENCE_NAME}=${value}; path=/; max-age=${ONE_YEAR_IN_SECONDS}; SameSite=Lax`;
    document.documentElement.dataset.cookiePreference = value;
};

const registerCookiePreferenceChange = (value) => {
    window.dispatchEvent(
        new CustomEvent('cookie-preference-changed', {
            detail: { value },
        }),
    );
};

const initCookieBanner = () => {
    const banner = document.querySelector('[data-cookie-banner]');
    if (!banner) {
        return;
    }

    const storedPreference = getCookieValue(COOKIE_PREFERENCE_NAME);

    if (storedPreference) {
        setCookiePreference(storedPreference);
        banner.remove();

        return;
    }

    banner.hidden = false;
    banner.classList.remove('hidden');

    banner.querySelectorAll('[data-cookie-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const preference = button.dataset.cookieAction === 'all' ? 'all' : 'necessary';

            setCookiePreference(preference);
            registerCookiePreferenceChange(preference);
            banner.remove();
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCookieBanner, { once: true });
} else {
    initCookieBanner();
}
