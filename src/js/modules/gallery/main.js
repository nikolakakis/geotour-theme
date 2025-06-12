// src/js/modules/gallery/main.js
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export function initializeGallery() {
    console.log('Initializing gallery functionality...');
    
    // Initialize Fancybox for all galleries on the page
    initializeFancybox();
    
    // Add any additional gallery enhancements
    enhanceGalleryAccessibility();
}

function initializeFancybox() {
    // For cross-gallery looping, use a single page-wide gallery group
    const pageGalleryId = 'page-gallery-' + Math.random().toString(36).substr(2, 9);
    
    // Update all gallery items to use the same group
    const allGalleryItems = document.querySelectorAll('.geotour-image-grid .grid-item');
    
    allGalleryItems.forEach(item => {
        item.setAttribute('data-fancybox', pageGalleryId);
    });
    
    if (allGalleryItems.length > 0) {
        // Initialize single Fancybox instance for all images on the page
        Fancybox.bind(`[data-fancybox="${pageGalleryId}"]`, {
            // Global Fancybox configuration
            loop: true,
            keyboard: true,
            wheel: false,
            touch: {
                vertical: true,
                momentum: true
            },
            
            // Animation settings
            showClass: "fancybox-fadeIn",
            hideClass: "fancybox-fadeOut",
            
            // Toolbar configuration
            Toolbar: {
                display: {
                    left: ["infobar"],
                    middle: [
                        "zoomIn",
                        "zoomOut",
                        "toggle1to1",
                        "rotateCCW",
                        "rotateCW",
                        "flipX",
                        "flipY",
                    ],
                    right: ["slideshow", "thumbs", "close"],
                },
            },
            
            // Thumbnail configuration
            Thumbs: {
                autoStart: false,
            },
            
            // Image configuration
            Images: {
                zoom: true,
                protected: true
            },
            
            // Custom events
            on: {
                ready: (fancybox) => {
                    const itemCount = fancybox.items ? fancybox.items.length : 0;
                    console.log(`Page gallery ready with ${itemCount} images from all galleries`);
                },
                
                reveal: (fancybox, slide) => {
                    // Add custom class for theme styling
                    if (fancybox.container) {
                        fancybox.container.classList.add('geotour-gallery-lightbox');
                    }
                }
            }
        });
        
        console.log(`Initialized cross-gallery Fancybox with ${allGalleryItems.length} total images`);
    }
}

function enhanceGalleryAccessibility() {
    // Add ARIA labels and keyboard navigation enhancements
    const galleryItems = document.querySelectorAll('.geotour-image-grid .grid-item');
    
    galleryItems.forEach((item, index) => {
        // Add ARIA labels
        item.setAttribute('role', 'button');
        item.setAttribute('aria-label', `Open image ${index + 1} in lightbox`);
        
        // Add keyboard support
        item.setAttribute('tabindex', '0');
        
        // Handle keyboard events
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                item.click();
            }
        });
    });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if there are any galleries on the page
    if (document.querySelector('.geotour-image-grid')) {
        initializeGallery();
    }
});

// Also initialize for dynamically loaded content
export function reinitializeGalleries() {
    // Destroy existing Fancybox instances
    Fancybox.destroy();
    
    // Reinitialize
    if (document.querySelector('.geotour-image-grid')) {
        initializeGallery();
    }
}