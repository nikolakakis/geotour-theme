/**
 * Anchor Ad Module
 * 
 * Handles the display and toggle functionality of the sticky anchor ad
 * at the bottom of the page
 * 
 * @package Geotour_Mobile_First
 */

export function initializeAnchorAd() {
    const wrapper = document.getElementById('anchor-ad-wrapper');
    
    // Exit if anchor ad wrapper doesn't exist
    if (!wrapper) {
        return;
    }

    const button = document.getElementById('toggle-btn');
    const arrow = document.getElementById('toggle-arrow');

    // --- Logic to handle unfilled state ---
    // 1. Check for *unfilled* state first.
    const isUnfilled = localStorage.getItem('isAdUnfilled') === 'true';
    if (isUnfilled) {
        wrapper.classList.add('is-unfilled');
        // If it's unfilled, don't run any of the close/open logic.
        return; 
    }

    // 2. Check for *closed* state
    const isClosed = localStorage.getItem('isAdAnchorClosed') === 'true';

    if (isClosed) {
        // Apply closed state immediately without animation
        
        // Add 'no-transition' utility class
        wrapper.classList.add('no-transition');
        button.classList.add('no-transition'); 
        arrow.classList.add('no-transition');
        
        // Apply the closed styles
        wrapper.classList.add('is-closed');
        arrow.classList.add('is-closed');
        
        // Force a browser reflow.
        wrapper.offsetHeight; 
        
        // Remove 'no-transition' class so animations work on the *next* click
        wrapper.classList.remove('no-transition');
        button.classList.remove('no-transition'); 
        arrow.classList.remove('no-transition');
    }

    // 3. Add click listener to the button
    button.addEventListener('click', () => {
        // Check the *current* state (by checking for the class)
        const shouldClose = !wrapper.classList.contains('is-closed');
        
        // Toggle the classes
        wrapper.classList.toggle('is-closed', shouldClose);
        arrow.classList.toggle('is-closed', shouldClose);
        
        // 4. Save the new state to localStorage
        localStorage.setItem('isAdAnchorClosed', shouldClose);
    });

    console.log('Anchor ad initialized');
}
