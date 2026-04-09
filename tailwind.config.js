import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    safelist: [
        // Colores dinámicos usados en Blade
        { pattern: /bg-(amber|blue|emerald|red|purple|cyan|slate|orange|green|indigo|yellow|pink)-(500|400|600)(\/10|\/20|\/30)?/ },
        { pattern: /text-(amber|blue|emerald|red|purple|cyan|slate|orange|green|indigo|yellow|pink)-(500|400|600)/ },
        { pattern: /border-(amber|blue|emerald|red|purple|cyan|slate|orange|green|indigo|yellow|pink)-(500|400|600)(\/30|\/50)?/ },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans:    ['DM Sans',  ...defaultTheme.fontFamily.sans],
                display: ['Syne',     ...defaultTheme.fontFamily.sans],
                mono:    ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                primary: '#f59e0b',
            },
            animation: {
                'fade-in': 'fadeIn 0.2s ease-out',
                'slide-up': 'slideUp 0.3s ease-out',
            },
        },
    },
    plugins: [],
};