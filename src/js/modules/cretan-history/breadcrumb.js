/**
 * Cretan History Breadcrumb Module
 * Handles animations and interactions for the historical timeline breadcrumb
 */

export class CretanHistoryBreadcrumb {
    constructor(container) {
        this.container = container;
        this.breadcrumbItems = container.querySelectorAll('.breadcrumb-item');
        this.isAnimated = false;
        
        this.init();
    }
    
    init() {
        // Check if animations should be disabled
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (prefersReducedMotion) {
            // Skip animations but still initialize functionality
            this.setupHoverEffects();
            return;
        }
        
        // Set up initial state for animation
        this.setupInitialState();
        
        // Use Intersection Observer to trigger animation when visible
        this.setupIntersectionObserver();
        
        // Setup hover effects
        this.setupHoverEffects();
    }
    
    setupInitialState() {
        // Get the position of the first item (Cretan History)
        const firstItem = this.breadcrumbItems[0];
        if (!firstItem) return;
        
        const firstItemRect = firstItem.getBoundingClientRect();
        const containerRect = this.container.getBoundingClientRect();
        const startPosition = firstItemRect.left - containerRect.left;
        
        // Position all period icons at the first item's position
        this.breadcrumbItems.forEach((item, index) => {
            if (index === 0) return; // Skip the main "Cretan History" item
            
            const periodIcon = item.querySelector('.period-icon');
            if (periodIcon) {
                // Calculate the distance this item needs to travel
                const itemRect = item.getBoundingClientRect();
                const targetPosition = itemRect.left - containerRect.left;
                const translateDistance = startPosition - targetPosition;
                
                // Set initial position and hide
                item.style.transform = `translateX(${translateDistance}px)`;
                item.style.opacity = '0';
                item.style.transition = 'none'; // Prevent flash
                
                // Store the final position for animation
                item.dataset.finalTransform = 'translateX(0px)';
            }
        });
    }
    
    setupIntersectionObserver() {
        const options = {
            threshold: 0.3, // Trigger when 30% visible
            rootMargin: '0px 0px -50px 0px' // Start animation a bit before fully visible
        };
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.isAnimated) {
                    this.animateSlideIn();
                    this.isAnimated = true;
                }
            });
        }, options);
        
        this.observer.observe(this.container);
    }
    
    animateSlideIn() {
        // Add transition property for smooth animation
        this.breadcrumbItems.forEach((item, index) => {
            if (index === 0) return; // Skip main item
            
            item.style.transition = 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease';
        });
        
        // Animate each item with a staggered delay
        this.breadcrumbItems.forEach((item, index) => {
            if (index === 0) return; // Skip the main "Cretan History" item
            
            const delay = index * 80; // 80ms delay between each item
            
            setTimeout(() => {
                item.style.transform = item.dataset.finalTransform || 'translateX(0px)';
                item.style.opacity = '1';
            }, delay);
        });
        
        // Clean up after animation
        setTimeout(() => {
            this.breadcrumbItems.forEach(item => {
                item.style.transition = '';
                item.removeAttribute('data-final-transform');
            });
        }, 1000);
    }
    
    setupHoverEffects() {
        this.breadcrumbItems.forEach(item => {
            const periodIcon = item.querySelector('.period-icon');
            if (!periodIcon) return;
            
            item.addEventListener('mouseenter', () => {
                periodIcon.style.transform = 'scale(1.1)';
            });
            
            item.addEventListener('mouseleave', () => {
                periodIcon.style.transform = 'scale(1)';
            });
        });
    }
    
    // Public method to manually trigger animation (if needed)
    triggerAnimation() {
        if (!this.isAnimated) {
            this.animateSlideIn();
            this.isAnimated = true;
        }
    }
    
    // Cleanup method
    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        // Remove event listeners
        this.breadcrumbItems.forEach(item => {
            item.replaceWith(item.cloneNode(true));
        });
    }
}

/**
 * Initialize all Cretan History breadcrumbs on the page
 */
export function initializeCretanHistoryBreadcrumbs() {
    const breadcrumbs = document.querySelectorAll('#cretan-history-breadcrumb');
    const instances = [];
    
    breadcrumbs.forEach(breadcrumb => {
        const instance = new CretanHistoryBreadcrumb(breadcrumb);
        instances.push(instance);
    });
    
    return instances;
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeCretanHistoryBreadcrumbs();
});
