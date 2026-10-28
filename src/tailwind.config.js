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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'c-text': 'rgb(var(--text) / <alpha-value>)',
                'c-background': 'rgb(var(--background) / <alpha-value>)',
                'c-surface': 'rgb(var(--surface) / <alpha-value>)',
                'c-surface-raised': 'rgb(var(--surface-raised) / <alpha-value>)',
                'c-line': 'rgb(var(--line) / <alpha-value>)',
                'c-muted': 'rgb(var(--muted) / <alpha-value>)',
                'c-quiet': 'rgb(var(--quiet) / <alpha-value>)',
                'c-primary': 'rgb(var(--primary) / <alpha-value>)',
                'c-secondary': 'rgb(var(--secondary) / <alpha-value>)',
                'c-accent': 'rgb(var(--accent) / <alpha-value>)',
            },
            borderWidth: {
                DEFAULT: 'thin', // use 'thin' instead 1px since chrome likes this more
            },

        },
    },

    plugins: [forms],

};