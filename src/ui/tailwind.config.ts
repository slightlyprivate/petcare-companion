/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          primary: '#1F2D3D',
          accent: {
            DEFAULT: '#147E7E',
            700: '#0F6666',
          },
          secondary: {
            DEFAULT: '#F6E8D5',
            200: '#e9dcc9',
          },
          muted: '#C8F4F9',
          fg: '#6B7C93',
          bg: '#F9FAFB',
          danger: {
            DEFAULT: '#D32F2F',
            700: '#b72828',
          },
        },
      },
    },
  },
};
