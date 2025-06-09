// src/js/main.js
import '../scss/main.scss';
import { initializeMainMenu } from './modules/navigation/main.js';
import { initializeHeader } from './modules/header/main.js';
import { initializeHero } from './modules/hero/main.js';
import { initializeAllMaps } from './modules/maps/main.js'; // Import map initializer

// Your other JavaScript code goes here
console.log('Geotour Mobile First theme loaded.');

document.addEventListener('DOMContentLoaded', function() {
  initializeMainMenu();
  initializeHeader();
  initializeHero();
  initializeAllMaps(); // Initialize maps after DOM is ready
});