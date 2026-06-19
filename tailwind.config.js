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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#0058be',
                'on-primary': '#ffffff',
                'primary-container': '#2170e4',
                'on-primary-container': '#fefcff',
                surface: '#f9f9ff',
                'surface-container': '#ecedf7',
                'surface-container-low': '#f2f3fd',
                'surface-container-lowest': '#ffffff',
                'surface-container-high': '#e6e7f2',
                'surface-container-highest': '#e1e2ec',
                'on-surface': '#191b23',
                'on-surface-variant': '#424754',
                outline: '#727785',
                'outline-variant': '#c2c6d6',
            },
        },
    },

    plugins: [forms],
};
