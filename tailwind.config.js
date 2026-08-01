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
        // XploreSmithers inspired color palette.
        // forest/accent/emerald read from --c-* CSS variables so the admin
        // Theme settings can restyle the site at runtime; the var() fallbacks
        // are the original hand-tuned hexes as RGB triplets, so the design is
        // unchanged until a theme override is emitted (partials/theme-styles).
        'primary': {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: 'rgb(var(--c-forest-600, 44 95 93) / <alpha-value>)',  // Deep Teal - Primary brand (tracks forest-600)
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        'emerald': {
          50: 'rgb(var(--c-emerald-50, 236 253 245) / <alpha-value>)',
          100: 'rgb(var(--c-emerald-100, 209 250 229) / <alpha-value>)',
          200: 'rgb(var(--c-emerald-200, 167 243 208) / <alpha-value>)',
          300: 'rgb(var(--c-emerald-300, 110 231 183) / <alpha-value>)',
          400: 'rgb(var(--c-emerald-400, 74 155 142) / <alpha-value>)',  // Bright Teal
          500: 'rgb(var(--c-emerald-500, 16 185 129) / <alpha-value>)',
          600: 'rgb(var(--c-emerald-600, 5 150 105) / <alpha-value>)',
          700: 'rgb(var(--c-emerald-700, 4 120 87) / <alpha-value>)',
          800: 'rgb(var(--c-emerald-800, 6 95 70) / <alpha-value>)',
          900: 'rgb(var(--c-emerald-900, 6 78 59) / <alpha-value>)',
        },
        'accent': {
          50: 'rgb(var(--c-accent-50, 255 247 237) / <alpha-value>)',
          100: 'rgb(var(--c-accent-100, 255 237 213) / <alpha-value>)',
          200: 'rgb(var(--c-accent-200, 254 215 170) / <alpha-value>)',
          300: 'rgb(var(--c-accent-300, 253 186 116) / <alpha-value>)',
          400: 'rgb(var(--c-accent-400, 251 146 60) / <alpha-value>)',
          500: 'rgb(var(--c-accent-500, 232 123 53) / <alpha-value>)',  // Warm Orange
          600: 'rgb(var(--c-accent-600, 234 88 12) / <alpha-value>)',
          700: 'rgb(var(--c-accent-700, 194 65 12) / <alpha-value>)',
          800: 'rgb(var(--c-accent-800, 154 52 18) / <alpha-value>)',
          900: 'rgb(var(--c-accent-900, 124 45 18) / <alpha-value>)',
        },
        'forest': {
          50: 'rgb(var(--c-forest-50, 245 248 247) / <alpha-value>)',
          100: 'rgb(var(--c-forest-100, 232 240 237) / <alpha-value>)',
          200: 'rgb(var(--c-forest-200, 209 225 219) / <alpha-value>)',
          300: 'rgb(var(--c-forest-300, 168 196 185) / <alpha-value>)',
          400: 'rgb(var(--c-forest-400, 125 165 153) / <alpha-value>)',
          500: 'rgb(var(--c-forest-500, 90 133 121) / <alpha-value>)',
          600: 'rgb(var(--c-forest-600, 44 95 93) / <alpha-value>)',  // Deep forest green
          700: 'rgb(var(--c-forest-700, 35 78 76) / <alpha-value>)',
          800: 'rgb(var(--c-forest-800, 29 64 62) / <alpha-value>)',
          900: 'rgb(var(--c-forest-900, 25 54 52) / <alpha-value>)',
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
        sans: ["var(--font-body, 'Inter')", 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        display: ["var(--font-heading, 'Playfair Display')", 'Georgia', 'serif'],
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-gradient': 'linear-gradient(135deg, rgb(var(--c-emerald-800, 6 95 70)) 0%, rgb(var(--c-emerald-700, 4 120 87)) 25%, rgb(var(--c-emerald-600, 5 150 105)) 50%, rgb(var(--c-accent-500, 232 123 53)) 100%)',
      },
    },
  },
  plugins: [],
}