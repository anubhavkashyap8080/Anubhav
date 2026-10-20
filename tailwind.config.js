/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          green: '#0c5a34',
          'green-dark': '#073c22',
          'green-light': '#15803d',
          'green-50': '#f0fdf4',
          'green-100': '#dcfce7',
          orange: '#e65c00',
          'orange-dark': '#c2410c',
          'orange-light': '#f97316',
          'orange-50': '#fff7ed',
          cream: '#fdfbf7',
          sand: '#f7f3eb',
          earth: '#78350f',
          gold: '#d97706',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
        serif: ['Merriweather', 'Georgia', 'serif'],
        display: ['Poppins', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
