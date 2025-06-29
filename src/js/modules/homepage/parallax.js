// Homepage Parallax Effect Module
// Efficient parallax effect using CSS transforms and requestAnimationFrame

export function initializeHomepageParallax() {
    // Only run on homepage
    if (!document.body.classList.contains('page-template-page-homepage')) {
        return;
    }

    const heroImage = document.querySelector('.homepage-hero .hero-background-image');
    
    // Only run if the hero image exists on the page
    if (!heroImage) {
        return;
    }

    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        return;
    }

    // Flag to prevent the function from running on every scroll event
    let ticking = false;
    
    // Store the initial position
    let lastScrollY = 0;

    function updateParallax() {
        // Get the vertical scroll position
        const scrollY = window.scrollY;
        
        // Only update if scroll position has changed significantly
        if (Math.abs(scrollY - lastScrollY) < 1) {
            ticking = false;
            return;
        }
        
        lastScrollY = scrollY;
        
        // Get the hero section bounds
        const heroSection = heroImage.closest('.homepage-hero');
        if (!heroSection) {
            ticking = false;
            return;
        }
        
        const heroRect = heroSection.getBoundingClientRect();
        const heroTop = heroRect.top + scrollY;
        const heroHeight = heroRect.height;
        
        // Only apply parallax when hero is in viewport
        if (scrollY < heroTop + heroHeight && scrollY + window.innerHeight > heroTop) {
            // Calculate parallax offset
            // The 0.3 factor controls the speed of the parallax effect
            // Smaller values = slower movement, larger values = faster movement
            const parallaxSpeed = 0.3;
            const yPos = scrollY * parallaxSpeed;
            
            // Apply the transform using GPU-accelerated translate3d
            heroImage.style.transform = `translate3d(0, ${yPos}px, 0)`;
        }
        
        // Reset the flag
        ticking = false;
    }

    // Throttled scroll handler using requestAnimationFrame
    function handleScroll() {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }

    // Add scroll listener
    window.addEventListener('scroll', handleScroll, { passive: true });

    // Handle window resize
    function handleResize() {
        // Reset transform on resize to prevent issues
        if (window.innerWidth <= 768) {
            // Disable on mobile
            heroImage.style.transform = '';
            window.removeEventListener('scroll', handleScroll);
        } else if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            // Re-enable on desktop if not reduced motion
            window.addEventListener('scroll', handleScroll, { passive: true });
        }
    }

    window.addEventListener('resize', handleResize, { passive: true });

    // Initialize on load
    handleScroll();

    console.log('Homepage parallax effect initialized');
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeHomepageParallax);
