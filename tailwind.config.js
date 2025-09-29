/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // XploreSmithers inspired color palette
        'primary': {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#2C5F5D',  // Deep Teal - Primary brand
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        'emerald': {
          50: '#ecfdf5',
          100: '#d1fae5',
          200: '#a7f3d0',
          300: '#6ee7b7',
          400: '#4A9B8E',  // Bright Teal
          500: '#10b981',
          600: '#059669',
          700: '#047857',
          800: '#065f46',
          900: '#064e3b',
        },
        'accent': {
          50: '#fff7ed',
          100: '#ffedd5',
          200: '#fed7aa',
          300: '#fdba74',
          400: '#fb923c',
          500: '#E87B35',  // Warm Orange
          600: '#ea580c',
          700: '#c2410c',
          800: '#9a3412',
          900: '#7c2d12',
        },
        'forest': {
          50: '#f5f8f7',
          100: '#e8f0ed',
          200: '#d1e1db',
          300: '#a8c4b9',
          400: '#7da599',
          500: '#5a8579',
          600: '#2C5F5D',  // Deep forest green
          700: '#234e4c',
          800: '#1d403e',
          900: '#193634',
        },
        'sand': {
          50: '#fdfcfa',
          100: '#F5F1E8',  // Warm Beige/Cream
          200: '#ebe4d5',
          300: '#ddd1b8',
          400: '#cbb896',
          500: '#b39f76',
          600: '#8B6F47',  // Earthy Brown
          700: '#705937',
          800: '#5c4a2f',
          900: '#4d3f29',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['Playfair Display', 'Georgia', 'serif'],
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-gradient': 'linear-gradient(135deg, #065f46 0%, #047857 25%, #059669 50%, #E87B35 100%)',
      },
    },
  },
  plugins: [],
}