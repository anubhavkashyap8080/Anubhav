/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./*.php",
    "./admin/**/*.php",
    "./seller/**/*.php",
    "./includes/**/*.php",
    "./ajax/**/*.php",
    "./payments/**/*.php",
    "./assets/**/*.js",
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
        },
        'brand-green': '#0c5a34',
        'brand-green-dark': '#073c22',
        'brand-green-light': '#15803d',
        'brand-orange': '#e65c00',
        'brand-orange-dark': '#c2410c',
        'brand-orange-light': '#f97316',
        'brand-cream': '#fdfbf7',
        'brand-sand': '#f7f3eb',
        'brand-earth': '#78350f',
        'brand-gold': '#d97706',
        'brand-dark': '#081c15',
        'admin-indigo': '#4338ca',
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
