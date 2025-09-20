<!-- Desktop Mega Menu (static HTML from menu.html) -->
<nav class="main-nav desktop-mega-menu">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo" style="display: flex; align-items: center;">
        <?php
            // Inline the SVG for best performance and styling flexibility
            $logo_svg_path = get_template_directory() . '/assets/graphics/logo.svg';
            if (file_exists($logo_svg_path)) {
                echo file_get_contents($logo_svg_path);
            }
        ?>
    </a>
    <ul class="nav-links">
        <li data-menu-target="#geotour-menu"><a href="#">Geotour</a></li>
        <li data-menu-target="#explore-menu"><a href="#">Explore</a></li>
        <li data-menu-target="#listings-menu"><a href="#">Listings</a></li>
        <li><a href="/blog">Blog</a></li>
        <li data-menu-target="#events-menu"><a href="#">Events</a></li>
        <li data-menu-target="#search-menu">
            <a href="#" class="search-menu-link" aria-label="Search">
                <svg class="search-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:block; margin:auto; color: #7f8c9a;"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </a>
        </li>
    </ul>
</nav>
<!-- Mega Menu Panels (copied from menu.html, only for desktop) -->
<!-- Geotour Menu -->
<div id="geotour-menu" class="mega-menu-container">
    <div class="mega-menu-content">
        <div class="mega-menu-column">
            <h3 class="menu-item">About</h3>
            <a href="/about-geotour" class="menu-item">About Geotour</a>
            <a href="/about-geotour/geotour-timeline/" class="menu-item">Geotour timeline</a>
        </div>
        <div class="mega-menu-column">
            <h3 class="menu-item">People</h3>            
            <a href="/people" class="menu-item">People bio</a>
            <a href="/listing/?listing-region=&listing-category=social-teams&search=" class="menu-item">Social Teams</a>
            <a href="/cultural-associations-contacts/" class="menu-item">Cultural Associations</a>
        </div>
        <div class="mega-menu-column">
            <h3 class="menu-item">History</h3>
            <a href="/about-geotour/cretan-history/" class="menu-item">Cretan History</a>
            <a href="/about-geotour/cretan-history/human-presence-in-crete/" class="menu-item">Human Presence in Crete</a>
        </div>
    </div>
</div>
<!-- Explore Menu -->
<div id="explore-menu" class="mega-menu-container">
    <div class="mega-menu-content" style="grid-template-columns: 1fr 1fr 1fr;">
        <div class="mega-menu-column">
            <div class="rhombus-card">
                <a href="https://www.geotour.gr/vt/" class="media-card menu-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/photos/vtour.webp" alt="Kofinas peak in Geotour virtual tour">
                    <div class="media-card-content">
                        <h4>Virtual Tour</h4>
                        <p>A photographic virtual tour for the island  of Crete.</p>
                        <p>Use the virtual tour as an alternative way to navigate to the Geotour content.</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="mega-menu-column">
            <div class="rhombus-card">
                <a href="https://www.geotour.gr/vt/3dmap/" class="media-card menu-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/photos/3d.webp" alt="Kofinas peak in Geotour 3D map">
                    <div class="media-card-content">
                        <h4>3D Map</h4>
                        <p>Another alternative navigation but this time in 3D space, also activate the 3D tours and the Youtube connected videos.</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="mega-menu-column">
            <div class="rhombus-card">
                <a href="/listing" class="media-card menu-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/photos/2dmap.webp" alt="The area of Asterousia in the 2D map">
                    <div class="media-card-content">
                        <h4>2D Map</h4>
                        <p>A convenient but also lightweight map for you navigation.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Listings Menu -->
<div id="listings-menu" class="mega-menu-container">
    <div class="mega-menu-content">
        <div class="mega-menu-column">
            <h3 class="menu-item">Archaeological Sites</h3>
            <a href="/listing/?acffield=minoan" class="menu-item">Minoan period</a>
            <a href="/listing/?acffield=classical" class="menu-item">Archaic & Classical</a>
            <a href="/listing/?acffield=hellenistic" class="menu-item">Hellenistic period</a>
            <a href="/listing/?acffield=roman" class="menu-item">Roman period</a>
        </div>
        <div class="mega-menu-column">
            <h3 class="menu-item">Locations</h3>
            <a href="/listing/?listing-category=villages-en" class="menu-item">Villages</a>
            <a href="/listing/?listing-category=religion-pois-en class="menu-item">Religion</a>
        </div>
        <div class="mega-menu-column" style="grid-column: span 2;">
            <h3 class="menu-item">Featured Sites</h3>
            <div class="thumbnail-gallery">
                <a href="/listing/gortyna-archaeological-site/" class="menu-item">
                    <img src="/wp-content/uploads/2024/09/AppoloPytrhios-640x384.webp" alt="Gortyna ruins">
                    <div class="overlay"><span>Gortyna</span></div>
                </a>
                <a href="/listing/festos/" class="menu-item">
                    <img src="/wp-content/uploads/2024/04/Group-0-DSC_9239_DSC_9242-4-images-Edit-640x240.webp" alt="Phaistos Minoan palace">
                    <div class="overlay"><span>Phaistos</span></div>
                </a>
                <a href="/listing/ancient-eleftherna/" class="menu-item">
                    <img src="/wp-content/uploads/2024/09/eleutherna-640x384.webp" alt="Ancient Eleutherna">
                    <div class="overlay"><span>Ancient Eleutherna</span></div>
                </a>
                <a href="/listing/zakros-minoan-palace/" class="menu-item">
                    <img src="/wp-content/uploads/2024/06/DJI_0084-640x427.webp" alt="Kato Zakros Minoan palace">
                    <div class="overlay"><span>Kato Zakros</span></div>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Events Menu -->
<div id="events-menu" class="mega-menu-container">
    <div class="mega-menu-content">
        <div class="mega-menu-column">
            <h3 class="menu-item">Events Views</h3>
            <a href="/events/month/" class="menu-item">Calendar</a>
            <a href="/events/map/" class="menu-item">Events Map</a>
            <a href="/events/week/" class="menu-item">By week</a>
        </div>
        <div class="mega-menu-column">
            <div class="compact-events-menu-summary">   
                <?php
                     echo do_shortcode('[tribe_events_list limit="5" venue="false" address="false" city="false" region="false" country="false" postal_code="false"]'); ?>
                </div>
                
        </div>
    </div>
</div>
<!-- Search Menu -->



<div id="search-menu" class="mega-menu-container">

    <div class="homepage-search-form mega-menu-content" style="display: flex; justify-content: center; align-items: center; padding: 60px 40px;">
        <form method="get" action="/" class="listing-search-form">
            <!-- Text Search -->
            <div class="search-field search-text">
                <label for="website-search-text" class="screen-reader-text">Search the website</label>
                <input type="search" name="s" id="website-search-text" placeholder="Search articles, people, photos..." aria-label="Search website content">
            </div>
            <button type="submit" class="search-submit" title="Search Website" aria-label="Submit website search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
                </svg>
            </button>
        </form>
    </div>
</div>


