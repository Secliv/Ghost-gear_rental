import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ghost: {
                    950: '#070A0D',
                    900: '#0B0F14',
                    850: '#0F151C',
                    800: '#131B23',
                    750: '#17222D',
                    700: '#1D2A37',
                    600: '#2A3C4E',
                    500: '#48627D',
                    400: '#7E9BB8',
                    300: '#A9BFD4',
                    200: '#D2DFEC',
                    100: '#EBF2F8',
                    accent: '#00FF66',
                    'accent-hover': '#00E65C',
                    'accent-muted': 'rgba(0, 255, 102, 0.12)',
                    'accent-border': 'rgba(0, 255, 102, 0.25)',
                },
            },
        },
    },

    plugins: [forms],
};
