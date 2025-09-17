/**
 * Initializes the desktop mega menu functionality.
 * This includes hover effects to show/hide menus and GSAP animations.
 */
export function initializeDesktopMegaMenu() {
    const header = document.querySelector('.main-header');
    if (!header) return; // Exit if the main header isn't found

    // More specific selector to target only the desktop menu links
    const navLinks = header.querySelectorAll('.desktop-mega-menu .nav-links > li[data-menu-target]');
    if (navLinks.length === 0) return; // Exit if no mega menu links are found

    let activeMenu = null;
    let leaveTimeout;

    // This function shows a menu
    const showMenu = (menu) => {
        if (activeMenu) {
            activeMenu.classList.remove('visible');
            const activeLink = document.querySelector('.nav-links > li.active');
            if (activeLink) activeLink.classList.remove('active');
        }

        menu.classList.add('visible');
        const link = document.querySelector(`li[data-menu-target="#${menu.id}"]`);
        if (link) link.classList.add('active');

        activeMenu = menu;

        // Animate items with GSAP if it's available
        if (typeof gsap !== 'undefined') {
            gsap.fromTo(menu.querySelectorAll('.menu-item'),
                { opacity: 0, y: 15 },
                { opacity: 1, y: 0, duration: 0.4, stagger: 0.06, ease: 'power2.out' }
            );
        }
    };

    // This function hides the currently active menu
    const hideMenu = () => {
        if (activeMenu) {
            activeMenu.classList.remove('visible');
            const link = document.querySelector(`li[data-menu-target="#${activeMenu.id}"]`);
            if (link) link.classList.remove('active');
            activeMenu = null;
        }
    };

    navLinks.forEach(link => {
        const targetId = link.getAttribute('data-menu-target');
        const menu = document.querySelector(targetId);

        if (menu) {
            link.addEventListener('mouseenter', () => {
                clearTimeout(leaveTimeout); // Cancel any pending hide actions
                showMenu(menu);
            });
        }
    });

    // Add a mouseleave listener to the entire header
    header.addEventListener('mouseleave', () => {
        // Use a timeout to delay hiding, allowing for small mouse movements
        leaveTimeout = setTimeout(() => {
            hideMenu();
        }, 200);
    });

    // If the mouse re-enters the header, cancel the hide action
    header.addEventListener('mouseenter', () => {
        clearTimeout(leaveTimeout);
    });
}
