// filepath: e:\visualstudio\geotour-theme\src\js\modules\navigation\main.js
export function initializeMainMenu() {
  const hamburgerIcon = document.getElementById('hamburger-icon');
  const fullscreenMenu = document.getElementById('fullscreen-menu');
  const body = document.body;
  // Query for links inside the WordPress menu structure, excluding accordion triggers
  const menuLinks = document.querySelectorAll('#fullscreen-menu .menu-item:not(.has-submenu) > a, #fullscreen-menu .sub-menu .menu-item a');
  const accordionTriggers = document.querySelectorAll('#fullscreen-menu .has-submenu > a');

  if (!hamburgerIcon || !fullscreenMenu) {
    console.warn('Hamburger icon or fullscreen menu element not found.');
    return;
  }

  // --- Main Menu Toggle Function ---
  const toggleMenu = () => {
    const isMenuOpen = body.classList.contains('menu-open');
    body.classList.toggle('menu-open');
    hamburgerIcon.setAttribute('aria-expanded', String(!isMenuOpen));
    fullscreenMenu.setAttribute('aria-hidden', String(isMenuOpen));
  };

  // --- Event Listeners ---
  hamburgerIcon.addEventListener('click', (e) => {
    e.stopPropagation(); // Prevent event bubbling
    toggleMenu();
  });

  // --- Accordion (Sub-menu) Functionality ---
  accordionTriggers.forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault(); // Prevent link from navigating
      const parentLi = trigger.closest('.has-submenu'); // Get the parent LI
      if (parentLi) {
        parentLi.classList.toggle('submenu-open');
        // Optional: Close other open submenus
        // document.querySelectorAll('#fullscreen-menu .has-submenu.submenu-open').forEach(li => {
        //   if (li !== parentLi) {
        //     li.classList.remove('submenu-open');
        //   }
        // });
      }
    });
  });

  // Close main menu when a standard link (not an accordion trigger) is clicked
  menuLinks.forEach(link => {
    link.addEventListener('click', () => {
      if (body.classList.contains('menu-open')) {
        toggleMenu();
      }
    });
  });

  // Close menu by clicking outside (on the overlay itself)
  fullscreenMenu.addEventListener('click', (e) => {
    if (e.target === fullscreenMenu) {
      toggleMenu();
    }
  });

  // Close menu with the 'Escape' key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && body.classList.contains('menu-open')) {
      toggleMenu();
    }
  });
}
