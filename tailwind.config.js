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

            colors:{
                'primary': {
                    '50': '#eaf3ff',
                    '100': '#d8e9ff',
                    '200': '#b9d5ff',
                    '300': '#8fb7ff',
                    '400': '#628dff',
                    '500': '#3e63ff',
                    '600': '#1d34ff',
                    '700': '#192cf0',
                    '800': '#1224c1',
                    '900': '#192996',
                    '950': '#0f1757',
                },

                'secondary': {
                    '50': '#f6f8f9',
                    '100': '#ebf0f3',
                    '200': '#d3dee4',
                    '300': '#9cb7c3',
                    '400': '#7fa3b1',
                    '500': '#5f8798',
                    '600': '#4b6e7e',
                    '700': '#3e5966',
                    '800': '#364b56',
                    '900': '#30404a',
                },
                'ternary': {
                    '50': '#f8f8f8',
                    '100': '#f1f0ef',
                    '200': '#e7e5e3',
                    '300': '#d4d1cd',
                    '400': '#bab5af',
                    '500': '#a09a93',
                    '600': '#88817a',
                    '700': '#716b64',
                    '800': '#5f5a55',
                    '900': '#524e4a',
                },
            },
        },
    },

    plugins: [forms],
};
