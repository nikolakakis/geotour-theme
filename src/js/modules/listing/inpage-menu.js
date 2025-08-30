// Smooth scrolling and active state for listing in-page menu
export function initializeListingInpageMenu() {
    const menu = document.querySelector('.listing-inpage-menu');
    if (!menu) return; // Only run on pages with the menu

    const links = menu.querySelectorAll('.inpage-menu-link');
    const headerOffset = menu.offsetHeight || 60; // Use menu height as offset

    // Smooth scroll with offset
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('[InpageMenu] Clicked:', this.getAttribute('href'));
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    const rect = target.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const top = rect.top + scrollTop - headerOffset - 8; // 8px extra spacing
                    window.scrollTo({
                        top,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Active state on scroll
    const sectionIds = Array.from(links).map(link => link.getAttribute('data-section'));
    const sectionEls = sectionIds.map(id => document.getElementById(id));
    function onScroll() {
        let activeIdx = 0;
        for (let i = 0; i < sectionEls.length; i++) {
            const el = sectionEls[i];
            if (el && el.getBoundingClientRect().top <= headerOffset + 16) {
                activeIdx = i;
            }
        }
        links.forEach((link, idx) => {
            if (idx === activeIdx) link.classList.add('active');
            else link.classList.remove('active');
        });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}
