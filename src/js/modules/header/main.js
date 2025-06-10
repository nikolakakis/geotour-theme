// Header functionality for the main header
export function initializeHeader() {
    const hamburgerButton = document.getElementById('hamburger-icon');
    const fullscreenMenu = document.getElementById('fullscreen-menu');
    const header = document.querySelector('.main-header');

    // Function to toggle menu visibility
    const toggleMenu = () => {
        if (hamburgerButton && fullscreenMenu) {
            hamburgerButton.classList.toggle('is-active');
            fullscreenMenu.classList.toggle('is-visible');
            // Toggle body class to prevent scrolling AND control menu visibility via SCSS
            document.body.classList.toggle('menu-open'); // Changed from menu-open-no-scroll
            
            // Update aria attributes for accessibility
            const isOpen = fullscreenMenu.classList.contains('is-visible');
            hamburgerButton.setAttribute('aria-expanded', isOpen.toString());
            fullscreenMenu.setAttribute('aria-hidden', (!isOpen).toString());
        }
    };

    // Function to close menu and reset hamburger state
    const closeMenu = () => {
        if (hamburgerButton && fullscreenMenu) {
            hamburgerButton.classList.remove('is-active');
            fullscreenMenu.classList.remove('is-visible');
            document.body.classList.remove('menu-open'); // Changed from menu-open-no-scroll
            
            hamburgerButton.setAttribute('aria-expanded', 'false');
            fullscreenMenu.setAttribute('aria-hidden', 'true');
        }
    };    // Scroll functionality for header height animation (only on larger screens)
    let lastScrollTop = 0;
    const handleScroll = () => {
        // Only apply scroll effects on screens 768px and wider
        if (window.innerWidth < 768) {
            return;
        }
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (header) {
            if (scrollTop > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
        
        lastScrollTop = scrollTop;
    };

    // Event listener for the hamburger button
    if (hamburgerButton) {
        hamburgerButton.addEventListener('click', toggleMenu);
        
        // Close menu when clicking on menu links
        const menuLinks = fullscreenMenu?.querySelectorAll('a');
        if (menuLinks) {
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (fullscreenMenu.classList.contains('is-visible')) {
                        closeMenu();
                    }
                });
            });
        }
    }

    // Close menu when clicking outside of it
    if (fullscreenMenu) {
        fullscreenMenu.addEventListener('click', (e) => {
            // Close menu if clicking on the overlay (not on menu content)
            if (e.target === fullscreenMenu) {
                closeMenu();
            }
        });

        // Close menu on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && fullscreenMenu.classList.contains('is-visible')) {
                closeMenu();
            }
        });
    }    // Add scroll event listener
    window.addEventListener('scroll', handleScroll, { passive: true });

    // Handle window resize to manage scroll effects
    window.addEventListener('resize', () => {
        // Remove scrolled class on smaller screens
        if (window.innerWidth < 768 && header && header.classList.contains('scrolled')) {
            header.classList.remove('scrolled');
        }
    }, { passive: true });

    // Initialize animations for header elements
    const animatedElements = document.querySelectorAll('.animate-on-load');
    animatedElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.2}s`;
    });
}
