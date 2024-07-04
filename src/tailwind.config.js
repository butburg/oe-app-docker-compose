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
                'main-color': {
                    DEFAULT: '#00D7FF',
                    50: '#B8F4FF',
                    100: '#A3F1FF',
                    200: '#7AEAFF',
                    300: '#52E4FF',
                    400: '#29DDFF',
                    500: '#00D7FF',
                    600: '#00A8C7',
                    700: '#00788F',
                    800: '#004957',
                    900: '#001A1F',
                    950: '#000203'
                },
                'brown-red': { DEFAULT: '#FF7300' }, // complementary red FF7300
                
                
                'clear-red': { DEFAULT: '#FF4700' },
                'orange': { DEFAULT: '#FF9D00' },
                'cappuccino': { DEFAULT: '#806740' },
                'main-green': { DEFAULT: '#00FF98' },
                'main-orange': { DEFAULT: '#FFB100' },
                'main-red': { DEFAULT: '#FF7300' },
                'main-accent': { DEFAULT: '#3999AA' },
                'gpt-red-comp': { DEFAULT: '#FF0054' },
                'gpt-green': { DEFAULT: '#00FFAB' },
                'gpt-red': { DEFAULT: '#FF5E00' },

                // Navigation section
                'nav-bg': '#001A1F',          // Dark background for the nav
                'nav-text': '#A3F1FF',        // Light text color for nav links
                'nav-text-hover': '#B8F4FF',  // Lighter text on hover
                'nav-underline-hover': '#00D7FF',  // Underline color on hover
                'yellow-text': '#FFB100',
                // Title bar
                'title-bg': '#004957',        // Dark background for title bar
                'title-text': '#00D7FF',      // Main color for title text
                'title-btn-bg': '#00A8C7',    // Button background in title bar
                'title-btn-bg-hover': '#00788F', // Button background on hover

                // Main content
                'body-bg': '#001A1F',         // Body background color
                'content-bg': '#004957',      // Background color for content tiles
                'content-text': '#A3F1FF',    // Text color for content
                'image-border': 'transparent', // No border for images
                'input-bg': '#001A1F',        // Input field background
                'input-text': '#A3F1FF',      // Input field text color
                'comment-bg': '#00303F',      // Background for comment bubbles
                'comment-text': '#A3F1FF',    // Text color for comment bubbles

                // Buttons
                'btn-primary-bg': '#00FFAB',  // Primary button background
                'btn-primary-bg-hover': '#00C784', // Primary button background on hover
                'btn-primary-text': '#001A1F', // Primary button text color
                'btn-secondary-bg': '#FF0054', // Secondary button background (e.g., abort)
                'btn-secondary-bg-hover': '#C70041', // Secondary button background on hover
                'btn-secondary-text': '#001A1F', // Secondary button text color

                // Links
                'link-text': '#A3F1FF',       // Text color for links
                'link-text-hover': '#B8F4FF', // Text color on hover for links
            },
        },
    },

    plugins: [forms],
};
/**
 * 
 * 
 */