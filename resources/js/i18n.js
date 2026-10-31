import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import sr from './locales/sr.json';

/**
 * The starting locale comes from the server ("locale" Inertia prop: the user's session choice,
 * else the default_locale setting) and is applied in app.js.
 */
export const i18n = createI18n({
    legacy: false,
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en,
        sr
    }
});
