import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./node_modules/flowbite/**/*.js",
    ],

    safelist: [
        "w-64",
        "w-1/2",
        "rounded-l-lg",
        "rounded-r-lg",
        "bg-gray-200",
        "grid-cols-4",
        "grid-cols-7",
        "h-6",
        "leading-6",
        "h-9",
        "leading-9",
        "shadow-lg",
        "bg-opacity-50",
        "dark:bg-opacity-80",
    ],
    darkMode: "class",

    theme: {
        extend: {
            colors: {
                primary: {
                    50: "#e0f2f1",
                    100: "#b2dfdb",
                    200: "#80cbc4",
                    300: "#4db6ac",
                    400: "#26a69a",
                    500: "#009688",
                    600: "#00897b",
                    700: "#00796b",
                    800: "#00695c",
                    900: "#004d40",
                },
                secondary: {
                    50: "#eceff1",
                    100: "#cfd8dc",
                    200: "#b0bec5",
                    300: "#90a4ae",
                    400: "#78909c",
                    500: "#607d8b",
                    600: "#546e7a",
                    700: "#455a64",
                    800: "#37474f",
                    900: "#263238",
                },
            },
            fontFamily: {
                sans: [
                    "Inter",
                    "ui-sans-serif",
                    "system-ui",
                    "-apple-system",
                    "system-ui",
                    "Segoe UI",
                    "Roboto",
                    "Helvetica Neue",
                    "Arial",
                    "Noto Sans",
                    "sans-serif",
                    "Apple Color Emoji",
                    "Segoe UI Emoji",
                    "Segoe UI Symbol",
                    "Noto Color Emoji",
                ],
                body: [
                    "Inter",
                    "ui-sans-serif",
                    "system-ui",
                    "-apple-system",
                    "system-ui",
                    "Segoe UI",
                    "Roboto",
                    "Helvetica Neue",
                    "Arial",
                    "Noto Sans",
                    "sans-serif",
                    "Apple Color Emoji",
                    "Segoe UI Emoji",
                    "Segoe UI Symbol",
                    "Noto Color Emoji",
                ],
                mono: [
                    "ui-monospace",
                    "SFMono-Regular",
                    "Menlo",
                    "Monaco",
                    "Consolas",
                    "Liberation Mono",
                    "Courier New",
                    "monospace",
                ],
            },
            transitionProperty: {
                width: "width",
            },
            textDecoration: ["active"],
            minWidth: {
                kanban: "28rem",
            },
            spacing: {
                130: "38rem",
                98: "28rem",
            },
        },
    },

    plugins: [
        forms,
        typography,
        require("flowbite/plugin")({
            charts: true,
        }),
    ],
};
