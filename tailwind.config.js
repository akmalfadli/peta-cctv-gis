const path = require('path');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    path.join(__dirname, "Views/**/*.blade.php"),
  ],
  theme: {
    extend: {
      colors: {
        ops: {
          bg: '#f8fafc',          // Muted light gray
          panel: '#ffffff',       // Pure white panels
          border: '#e2e8f0',      // Clean slate-200 borders
          textMain: '#1e293b',    // Slate-800 text
          textMuted: '#64748b',   // Slate-500 muted labels
          primary: '#1e3a8a',     // Muted Navy Blue (government operational)
          primaryHover: '#172554',
          success: '#2e7d32',     // Muted Green (Online)
          danger: '#c62828',      // Muted Red (Offline)
          inactive: '#757575',    // Muted Gray
          hoverbg: '#f1f5f9'
        }
      }
    },
  },
  plugins: [],
}
