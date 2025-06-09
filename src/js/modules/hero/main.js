// Hero section functionality
export function initializeHero() {
    // Smooth scroll for CTA button
    const heroCta = document.querySelector('.hero-cta-button');
    if (heroCta && heroCta.getAttribute('href') === '#content') {
        heroCta.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.querySelector('#primary') || document.querySelector('#main');
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }

    // Smooth scroll for scroll indicator
    const scrollIndicator = document.querySelector('.hero-scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            const target = document.querySelector('#primary') || document.querySelector('#main');
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }

    // Parallax effect for hero background (on larger screens)
    const heroSection = document.querySelector('.hero-section');
    if (heroSection && window.innerWidth >= 1024) {
        let ticking = false;
        
        const updateParallax = () => {
            const scrollTop = window.pageYOffset;
            const rate = scrollTop * -0.5;
            heroSection.style.transform = `translateY(${rate}px)`;
            ticking = false;
        };

        const requestParallax = () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestParallax, { passive: true });
    }
}
