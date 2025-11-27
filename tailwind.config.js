import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'primary-brazuka': '#007537',
                'second-brazuka': '#fddb1b',
                'three-brazuka': '#14628c',
                'primary-hover': '#559a75ff',
                'second-hover': '#b09918ff',
                'three-hover': '#7fb5d2ff',
                'dark-mode': '#33313140',
            },
            fontFamily: {
                league: ['"League Spartan"', 'sans-serif'],
            },
        },
    },
    plugins: [forms],
}
