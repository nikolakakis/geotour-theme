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
    const adTopOffset = 100; // Must match the 'top: 100px' in your CSS
    const adHeight = 600;    // The height of your ad unit
    // =====================

    // Find all the key elements on the page
    const leftAd = document.querySelector('.side-rail-left');
    const rightAd = document.querySelector('.side-rail-right');
    const content = document.getElementById('main-content');
    const footer = document.querySelector('footer');

    // If any element is missing, do nothing.
    if (!leftAd || !rightAd || !content || !footer) {
        console.log('Side rail ads: Missing a required element (ad, content, or footer).');
        return;
    }

    console.log('Side rail ads initialized');

    function handleScroll() {
        // Get the scroll position
        const scrollY = window.pageYOffset || document.documentElement.scrollTop;

        // 1. Calculate START point
        //    We start when the ad's 'top: 100px' is aligned with the
        //    top of the main content area.
        const triggerPointStart = content.offsetTop - adTopOffset;

        // 2. Calculate END point
        //    We stop when the BOTTOM of the ad (scroll position + offset + height)
        //    is about to hit the TOP of the footer.
        const triggerPointEnd = footer.offsetTop - adHeight - adTopOffset - 20; // 20px buffer

        // 3. The Logic
        //    Check if we are *between* the start and end points.
        if (scrollY > triggerPointStart && scrollY < triggerPointEnd) {
            // We are inside the content, fade the ads IN
            leftAd.classList.add('is-visible');
            rightAd.classList.add('is-visible');
        } else {
            // We are above the content or overlapping the footer, fade OUT
            leftAd.classList.remove('is-visible');
            rightAd.classList.remove('is-visible');
        }
    }

    // Listen for scroll and resize (in case window size changes)
    window.addEventListener('scroll', handleScroll, { passive: true });
    window.addEventListener('resize', handleScroll, { passive: true });
    
    // Run once on load to set initial state
    handleScroll();
}
