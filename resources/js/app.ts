import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { setupPrimeVue } from './config/primevue';
import { initializeTheme } from './composables/useAppearance';
import './composables/useRouter.js'; // Initialize router.route() extension
import ToastNotifications from './components/ToastNotifications.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ 
            render: () => h('div', [
                h(App, props),
                h(ToastNotifications),
            ])
        });
        
        app.use(plugin);
        setupPrimeVue(app);
        // Flowbite components are imported directly, no plugin registration needed
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
