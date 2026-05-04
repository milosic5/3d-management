import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import sr from './locales/sr.json';

const locale = localStorage.getItem('locale') || 'en';

export const i18n = createI18n({
    legacy: false,
    locale: locale,
    fallbackLocale: 'en',
    messages: {
        en,
        sr
    }
});
