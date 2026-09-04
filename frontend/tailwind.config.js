/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'royal-blue': '#0955ac', // Exact color from logo
        brand: {
          50: '#eef6ff',
          100: '#d9ebff',
          200: '#bce0ff',
          300: '#8acaff',
          400: '#52aaff',
          500: '#2b84ea',
          600: '#0955ac', // Core Brand
          700: '#07458c',
          800: '#063a75',
          900: '#053164',
        },
        blue: {
          50: '#eef6ff',
          100: '#d9ebff',
          200: '#bce0ff',
          300: '#8acaff',
          400: '#52aaff',
          500: '#2b84ea',
          600: '#0955ac', // Matched to logo
          700: '#07458c',
          800: '#063a75',
          900: '#053164',
        },
        'glass-panel': 'rgba(255, 255, 255, 0.1)',
        'glass-border': 'rgba(255, 255, 255, 0.2)',
      },
      fontFamily: {
        outfit: ['Outfit', 'sans-serif'],
        serif: ['"Playfair Display"', 'serif'],
      },
    },
  },
  plugins: [],
}
