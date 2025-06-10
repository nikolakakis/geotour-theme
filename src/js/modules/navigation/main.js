// filepath: e:\visualstudio\geotour-theme\src\js\modules\navigation\main.js
export function initializeMainMenu() {
  // Query for all navigation links and accordion icons
  const allNavLinks = document.querySelectorAll('#fullscreen-menu .menu-item a');
  const accordionIcons = document.querySelectorAll('#fullscreen-menu .has-submenu .accordion-icon');

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
      }
      // Menu closing upon navigation is handled by src/js/modules/header/main.js
    });
  });

  // Main menu toggle, click outside, and Escape key are handled by src/js/modules/header/main.js
}
