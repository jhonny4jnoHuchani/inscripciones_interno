import '../css/app.css';
import './bootstrap.js';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import 'primeicons/primeicons.css';

// Suppress PrimeUI license check watermark by pre-initializing the host
if (typeof document !== 'undefined' && !document.getElementById('p-license-host')) {
    const host = document.createElement('div');
    host.id = 'p-license-host';
    host.style.cssText = 'display:none!important;visibility:hidden!important;position:absolute!important;top:-9999px!important;left:-9999px!important;pointer-events:none!important;';
    (document.body || document.documentElement).appendChild(host);
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.app-dark',
                    },
                },
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
