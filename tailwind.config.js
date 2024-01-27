/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.php",
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    darkMode: 'class',
    theme: {
        extend: {},
        screens: {
            'sm': '576px',
            'md': '768px',
            'lg': '992px',
            'xl': '1248px',
            '2xl': '1536px',
        },
    },
    plugins: [],
}
