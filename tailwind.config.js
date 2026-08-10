import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Brand palette
 * Red (primary):      #ed1b2e
 * Soft / "grey":      #ff8da1
 * Emerald (support):  #9be198
 * Orange (support):   #ff5c39
 * Turquoise (support):#78d5e1
 */
const brand = {
    red: {
        50: '#fef2f3',
        100: '#fde6e8',
        200: '#fbc4ca',
        300: '#f6919c',
        400: '#f14f61',
        500: '#ed1b2e',
        600: '#d41224',
        700: '#b10f1e',
        800: '#92111d',
        900: '#7a1420',
        DEFAULT: '#ed1b2e',
    },
    soft: {
        50: '#fff5f7',
        100: '#ffe8ed',
        200: '#ffd0da',
        300: '#ff8da1',
        400: '#ff7a92',
        500: '#ff8da1',
        600: '#e86b82',
        700: '#c44f66',
        800: '#a43f54',
        900: '#8a3748',
        DEFAULT: '#ff8da1',
    },
    emerald: {
        50: '#f3fbf3',
        100: '#e4f7e3',
        200: '#c8efc6',
        300: '#9be198',
        400: '#7ad478',
        500: '#9be198',
        600: '#4faf4d',
        700: '#3f8c3e',
        800: '#356f35',
        900: '#2d5b2d',
        DEFAULT: '#9be198',
    },
    orange: {
        50: '#fff5f2',
        100: '#ffe8e1',
        200: '#ffcbbd',
        300: '#ffa08a',
        400: '#ff7a5c',
        500: '#ff5c39',
        600: '#ef4a28',
        700: '#c93a1d',
        800: '#a6331d',
        900: '#892f1e',
        DEFAULT: '#ff5c39',
    },
    turquoise: {
        50: '#f0fafb',
        100: '#d9f3f6',
        200: '#b5e8ee',
        300: '#78d5e1',
        400: '#4fc5d5',
        500: '#78d5e1',
        600: '#2aa8bb',
        700: '#248798',
        800: '#226d7c',
        900: '#215b68',
        DEFAULT: '#78d5e1',
    },
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand,
                // Remap common utility families so existing UI picks up the brand.
                indigo: brand.red,
                emerald: brand.emerald,
                amber: brand.orange,
                cyan: brand.turquoise,
                rose: brand.soft,
            },
        },
    },

    plugins: [forms],
};
