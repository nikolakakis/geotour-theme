import { defineConfig } from 'vite';
import legacy from '@vitejs/plugin-legacy';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig({
  // Project root directory (where index.html is located).
  root: './', 
  // Base public path when serving in development.
  base: './',

  plugins: [
    // Enables live reloading on changes to PHP files.
    liveReload(['**/*.php']),
    
    // Provides support for legacy browsers.
    legacy({
      targets: ['defaults', 'not IE 11'],
    }),
  ],

  build: {
    // Output directory for build assets.
    outDir: 'build', 
    // The public-facing directory of the output.
    assetsDir: 'assets',

    // Sourcemaps for debugging.
    sourcemap: true,
    
    // Generate a manifest file for PHP integration.
    manifest: true,

    // Define entry points.
    rollupOptions: {
      input: {
        main: './src/js/main.js', // Your main JavaScript file
        // You can add more entry points here if needed, e.g., for an admin script.
        // admin: './src/js/admin.js', 
      },
    },
  },

  server: {
    // Configure server for HMR.
    strictPort: true,
    port: 5173, // Default Vite port

    // Serve assets from the theme directory.
    hmr: {
      protocol: 'ws',
      host: 'localhost',
    },
  },
});