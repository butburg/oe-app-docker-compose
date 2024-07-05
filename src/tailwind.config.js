import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/views/*/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                serif: ['"Nanum Myeongjo"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'c-text': 'rgb(var(--text) / <alpha-value>)',
                'c-background': 'rgb(var(--background) / <alpha-value>)',
                'c-primary': 'rgb(var(--primary) / <alpha-value>)',
                'c-secondary': 'rgb(var(--secondary) / <alpha-value>)',
                'c-accent': 'rgb(var(--accent) / <alpha-value>)',
            },

        },
    },

    plugins: [forms],

};