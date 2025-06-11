// Hero section functionality
export function initializeHero() {
    console.log('Initializing hero functionality...');
    
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

    // Initialize scroll indicator functionality
    initializeScrollIndicator();
}

function initializeScrollIndicator() {
    const scrollIndicator = document.querySelector('.hero-scroll-indicator');
    const scrollArrow = document.querySelector('.scroll-arrow');
    
    if (scrollIndicator || scrollArrow) {
        // Add click event to both the indicator and arrow
        [scrollIndicator, scrollArrow].forEach(element => {
            if (element) {
                element.addEventListener('click', handleScrollClick);
                element.style.cursor = 'pointer'; // Ensure pointer cursor
            }
        });
    }
}

function handleScrollClick(event) {
    event.preventDefault();
    
    // Calculate the target scroll position
    const heroSection = document.querySelector('.hero-section, .listing-hero-section');
    
    if (heroSection) {
        const heroHeight = heroSection.offsetHeight;
        const headerHeight = document.querySelector('.main-header')?.offsetHeight || 0;
        
        // Scroll to just below the hero section
        const targetPosition = heroHeight - headerHeight;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
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
