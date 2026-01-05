import type { App } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';

/**
 * Configure PrimeVue with Aura theme
 * This configuration can be customized with different theme options
 */
export function setupPrimeVue(app: App) {
    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                darkModeSelector: '.dark',
                cssLayer: false, // Set to true if you want to use CSS layers
            },
        },
        ripple: true, // Enable ripple effect
    });
    
    // Register ToastService for useToast composable
    app.use(ToastService);
}

/**
 * PrimeVue theme configuration options
 * You can customize colors, spacing, etc. here
 */
export const primeVueConfig = {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: '.dark',
            cssLayer: false,
        },
    },
    ripple: true,
};

