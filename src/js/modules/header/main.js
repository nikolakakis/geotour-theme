// Header functionality for the main header
export function initializeHeader() {
    const hamburgerButton = document.getElementById('hamburger-icon');
    const fullscreenMenu = document.getElementById('fullscreen-menu');

    // Function to toggle menu visibility
    const toggleMenu = () => {
        if (hamburgerButton && fullscreenMenu) {
            hamburgerButton.classList.toggle('is-active');
            fullscreenMenu.classList.toggle('is-visible');
            // Toggle body class to prevent scrolling when menu is open
            document.body.classList.toggle('menu-open-no-scroll');
            
            // Update aria attributes for accessibility
            const isOpen = fullscreenMenu.classList.contains('is-visible');
            hamburgerButton.setAttribute('aria-expanded', isOpen.toString());
            fullscreenMenu.setAttribute('aria-hidden', (!isOpen).toString());
        }
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
                        toggleMenu();
                    }
                });
            });
        }
    }

    // Initialize animations for header elements
    const animatedElements = document.querySelectorAll('.animate-on-load');
    animatedElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.2}s`;
    });
}
