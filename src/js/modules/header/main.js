document.addEventListener('DOMContentLoaded', function () {
    const hamburgerButton = document.getElementById('hamburger-icon');
    const fullscreenMenu = document.getElementById('fullscreen-menu');

    // Function to toggle menu visibility
    const toggleMenu = () => {
        if (hamburgerButton && fullscreenMenu) {
            hamburgerButton.classList.toggle('is-active');
            fullscreenMenu.classList.toggle('is-visible');
            // Toggle body class to prevent scrolling when menu is open
            document.body.classList.toggle('menu-open-no-scroll'); 
        }
    };

    // Event listener for the hamburger button
    if (hamburgerButton) {
        hamburgerButton.addEventListener('click', toggleMenu);
    }
});
