// Hero Parallax Effect Module
// Universal parallax effect for all hero sections (homepage and general)
// Uses requestAnimationFrame for optimal performance

export class HeroParallax {
    constructor() {
        this.heroImages = [];
        this.ticking = false;
        this.isMobile = window.innerWidth <= 768;
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        this.init();
    }
    
    init() {
        // Skip parallax on mobile or if user prefers reduced motion
        if (this.isMobile || this.prefersReducedMotion) {
            return;
        }
        
        // Find all hero background images on the page
        this.heroImages = document.querySelectorAll('.hero-background-image');
        
        if (this.heroImages.length === 0) {
            return;
        }
        
        this.setupEventListeners();
        
        // Initial parallax position
        this.updateParallax();
    }
    
    setupEventListeners() {
        // Scroll event with throttling
        window.addEventListener('scroll', () => {
            if (!this.ticking) {
                requestAnimationFrame(() => this.updateParallax());
                this.ticking = true;
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth <= 768;
            
            // Disable parallax on mobile
            if (this.isMobile) {
                this.heroImages.forEach(image => {
                    image.style.transform = 'none';
                });
            }
        });
        
        // Listen for motion preference changes
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
            this.prefersReducedMotion = e.matches;
            
            if (this.prefersReducedMotion) {
                this.heroImages.forEach(image => {
                    image.style.transform = 'none';
                });
            }
        });
    }
    
    updateParallax() {
        // Skip if mobile or reduced motion
        if (this.isMobile || this.prefersReducedMotion) {
            this.ticking = false;
            return;
        }
        
        const scrolled = window.scrollY;
        
        this.heroImages.forEach(image => {
            const heroSection = image.closest('.homepage-hero, .hero-section');
            
            if (!heroSection) return;
            
            // Skip parallax for homepage hero sections
            if (heroSection.classList.contains('homepage-hero')) {
                return;
            }
            
            const rect = heroSection.getBoundingClientRect();
            const sectionTop = rect.top + scrolled;
            const sectionHeight = rect.height;
            
            // Only apply parallax if the hero section is in viewport or near viewport
            if (rect.bottom > -200 && rect.top < window.innerHeight + 200) {
                // Calculate parallax offset
                // Stronger effect: 0.4 factor instead of 0.3
                const parallaxOffset = (scrolled - sectionTop) * 0.4;
                
                // Apply transform using GPU-accelerated CSS property
                image.style.transform = `translate3d(0, ${parallaxOffset}px, 0)`;
            }
        });
        
        this.ticking = false;
    }
    
    // Public method to disable parallax (useful for specific conditions)
    disable() {
        this.heroImages.forEach(image => {
            image.style.transform = 'none';
        });
        
        window.removeEventListener('scroll', this.updateParallax);
    }
    
    // Public method to enable parallax
    enable() {
        if (!this.isMobile && !this.prefersReducedMotion) {
            this.setupEventListeners();
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new HeroParallax();
});
