/**
 * Side Rail Ads Module
 * 
 * Handles the display and animation of sticky side rail ads
 * that appear after scrolling past a certain point
 * 
 * @package Geotour_Mobile_First
 */

export function initializeSideRailAds() {
    // === CONFIGURATION ===
    // Set how many pixels to scroll before the ads fade in.
    const scrollTriggerPoint = 600; // Change this value to match your site
    // =====================

    const leftAd = document.querySelector('.side-rail-left');
    const rightAd = document.querySelector('.side-rail-right');

    if (!leftAd || !rightAd) {
        console.log('Side rail ads not found on this page');
        return; // No ads to animate
    }

    console.log('Side rail ads initialized');

    function handleScroll() {
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollY > scrollTriggerPoint) {
            // We've scrolled past the trigger point, fade the ads IN
            leftAd.classList.add('is-visible');
            rightAd.classList.add('is-visible');
        } else {
            // We're back at the top, fade the ads OUT
            leftAd.classList.remove('is-visible');
            rightAd.classList.remove('is-visible');
        }
    }

    // Listen for the 'scroll' event with passive option for better performance
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Run it once on load to set initial state
    handleScroll();
}
