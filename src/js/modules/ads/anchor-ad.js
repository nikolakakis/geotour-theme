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
    // Check for *unfilled* state only (no localStorage for closed state)
    const isUnfilled = localStorage.getItem('isAdUnfilled') === 'true';
    if (isUnfilled) {
        wrapper.classList.add('is-unfilled');
        // If it's unfilled, don't run any of the close/open logic.
        return; 
    }

    // Anchor ad starts open on every page load (no localStorage check)
    // Add click listener to the button for toggling during current session only
    button.addEventListener('click', () => {
        // Check the *current* state (by checking for the class)
        const shouldClose = !wrapper.classList.contains('is-closed');
        
        // Toggle the classes
        wrapper.classList.toggle('is-closed', shouldClose);
        arrow.classList.toggle('is-closed', shouldClose);
        
        // No localStorage - state is not remembered across pages
    });

    console.log('Anchor ad initialized');
}
