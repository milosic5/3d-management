import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { i18n } from './i18n';

// Updated from the shared "appName" prop (Settings → app name) on every visit.
let appName = import.meta.env.VITE_APP_NAME || '3D PrintShop';

const applySharedProps = (props) => {
    if (props.appName) {
        appName = props.appName;
    }
    if (props.locale) {
        i18n.global.locale.value = props.locale;
    }
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        applySharedProps(props.initialPage.props);
        router.on('navigate', (event) => applySharedProps(event.detail.page.props));

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
