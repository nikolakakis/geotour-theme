import { defineConfig } from 'vite';
import legacy from '@vitejs/plugin-legacy';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig(({ mode }) => ({
  // Project root directory (where index.html is located).
  root: './',
  // Base public path when serving in development.
  base: mode === 'development' ? '/' : './',

  plugins: [
    // Enables live reloading on changes to PHP files.
    liveReload(['**/*.php']),

    // Provides support for legacy browsers.
    legacy({
      targets: ['defaults', 'not IE 11'],
    }),
  ],

  // CSS-specific configuration
  css: {
    // This is the key! It enables sourcemaps for CSS during development.
    // Now, your browser's dev tools will point to the original .scss file and line number.
    devSourcemap: true,
    
    // Preprocessor options for SCSS
    preprocessorOptions: {
      scss: {
        // Generate sourcemaps for SCSS files
        sourceMap: true,
        // Add any global SCSS variables/mixins if needed
        // additionalData: `@import "./src/scss/variables.scss";`
      }
    }
  },

  build: {
    // Output directory for build assets.
    outDir: 'build',
    // The public-facing directory of the output.
    assetsDir: 'assets',

    // Sourcemaps for the production build (good to keep).
    sourcemap: true,

    // Generate a manifest file for PHP integration.
    manifest: true,

    // Define entry points.
    rollupOptions: {
      input: {
        main: './src/js/main.js', // Your main JavaScript file
        'listings-list': './src/js/listings-list.js', // Listings list shortcode
        // You can add more entry points here if needed, e.g., for an admin script.
        // admin: './src/js/admin.js',
      },
    },
  },

  // Remove the server config - let WordPress/Flywheel handle serving
  // Vite will only be used for building and CSS sourcemaps
}));
