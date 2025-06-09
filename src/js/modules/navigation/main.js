// filepath: e:\visualstudio\geotour-theme\src\js\modules\navigation\main.js
export function initializeMainMenu() {
  const hamburgerIcon = document.getElementById('hamburger-icon');
  const fullscreenMenu = document.getElementById('fullscreen-menu');
  const body = document.body;
  // Query for all navigation links and accordion icons
  const allNavLinks = document.querySelectorAll('#fullscreen-menu .menu-item a');
  const accordionIcons = document.querySelectorAll('#fullscreen-menu .has-submenu .accordion-icon');

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

  // --- Accordion (Sub-menu) Functionality via Icon Click ---
  accordionIcons.forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.preventDefault(); // Prevent any default action if icon is, e.g., wrapped in a link
      e.stopPropagation(); // Prevent event bubbling to parent <a> if icon is inside it

      const parentLi = icon.closest('.has-submenu');
      if (!parentLi) return;

      const isCurrentlyOpen = parentLi.classList.contains('submenu-open');

      // Find the parent UL of the clicked item to target siblings at the same level
      const parentUl = parentLi.parentElement;
      if (parentUl) {
        // Close other open submenus AT THE SAME LEVEL only
        const siblingSubmenus = parentUl.querySelectorAll(':scope > .has-submenu.submenu-open');
        siblingSubmenus.forEach(openLi => {
          if (openLi !== parentLi) {
            openLi.classList.remove('submenu-open');
          }
        });
      }

      // Toggle the current one
      if (!isCurrentlyOpen) {
        parentLi.classList.add('submenu-open');
      } else {
        // If it was already open and is clicked again, close it.
        parentLi.classList.remove('submenu-open');
      }
    });
  });

  // --- Handle Clicks on All Navigation Links ---
  allNavLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      // Check if this link is the direct child <a> of a .has-submenu li
      const isParentAccordionLink = link.matches('.has-submenu > a');

      // If the link is the main <a> of a .has-submenu item AND its href is '#',
      // it shouldn't navigate. The icon handles the accordion.
      if (isParentAccordionLink && href === '#') {
        e.preventDefault();
        // The click on the link text itself does nothing for accordion parents with href="#"
      } else {
        // For all other links (actual navigation links, links inside submenus,
        // or an accordion parent with a real URL):
        // If the main menu is open and the link is a real navigating link, close the menu.
        if (body.classList.contains('menu-open')) {
          // Check if it's a link that will cause navigation away from the current view
          // (i.e., not just a fragment identifier for the current page, though those might also warrant closing)
          if (href && href !== '#' && !href.startsWith('#_')) { // Basic check, can be refined
             toggleMenu(); // Close the main menu before navigating
          }
        }
        // Default link behavior (navigation) will proceed.
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
