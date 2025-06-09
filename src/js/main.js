// src/js/main.js
import '../scss/main.scss';
import { initializeMainMenu } from './modules/navigation/main.js';
import { initializeHeader } from './modules/header/main.js';

// Your other JavaScript code goes here
console.log('Geotour Mobile First theme loaded.');

document.addEventListener('DOMContentLoaded', function() {
  initializeMainMenu();
  initializeHeader();
});