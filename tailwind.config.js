/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{html,js,php}", // شامل تمام فایل‌های HTML، JS و PHP در روت و زیرپوشه‌ها
  ],
  theme: {
    extend: {},
  },
  plugins: [require('daisyui'),],
  daisyui: {
    themes: [
      {
        i8_theme: {
        "primary": "#4f46e5",
        "secondary": "#d1d5db",
        "accent": "#818cf8", 
        "neutral": "#ddd6fe",
        "base-100": "#ffffff",
        "info": "#7dd3fc",
        "success": "#10b981",
        "warning": "#facc15",
        "error": "#ef4444",
        },
      },
    ],
  },
}