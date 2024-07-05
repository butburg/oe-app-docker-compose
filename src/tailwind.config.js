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
                sans: ['"Nanum Myeongjo"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'c-text': 'hsl(var(--text))',
                'c-background': 'hsl(var(--background))',
                'c-primary': 'hsl(var(--primary))',
                'c-secondary': 'hsl(var(--secondary))',
                'c-accent': 'hsl(var(--accent))',
            },

        },
    },

    plugins: [forms],

};