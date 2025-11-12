/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.php",
    "./admin/**/*.php",
    "./index.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'blue': {
          600: '#2563eb',
          700: '#1d4ed8',
        }
      }
    },
  },
  plugins: [],
}
