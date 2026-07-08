import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'neu-bg': '#f0f2f5',
                'sunber': '#FFD13B',
            },
            boxShadow: {
                'neu': '8px 8px 16px #d1d5db, -8px -8px 16px #ffffff',
                'neu-inner': 'inset 8px 8px 16px #d1d5db, inset -8px -8px 16px #ffffff',
                'neu-sm': '4px 4px 8px #d1d5db, -4px -4px 8px #ffffff',
                'neu-inner-sm': 'inset 4px 4px 8px #d1d5db, inset -4px -4px 8px #ffffff',
                'neu-pressed': 'inset 6px 6px 12px #d1d5db, inset -6px -6px 12px #ffffff',
            }
        },
    },

    plugins: [forms],
};
