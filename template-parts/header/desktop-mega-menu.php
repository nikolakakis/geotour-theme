<!-- Desktop Mega Menu (static HTML from menu.html) -->
<nav class="main-nav desktop-mega-menu">
    <div class="nav-logo">Geotour Crete</div>
    <ul class="nav-links">
        <li data-menu-target="#geotour-menu"><a href="#">Geotour</a></li>
        <li data-menu-target="#explore-menu"><a href="#">Explore</a></li>
        <li data-menu-target="#listings-menu"><a href="#">Listings</a></li>
        <li><a href="#">Blog</a></li>
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
            <a href="/people" class="menu-item">People</a>
            <a href="/listing/" class="menu-item">Social Teams</a>
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
                <a href="#" class="media-card menu-item">
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
                <a href="#" class="media-card menu-item">
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
                <a href="#" class="media-card menu-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/photos/2dmap.webp" alt="The area of Asterousia in the 2D map">
                    <div class="media-card-content">
                        <h4>2D Map</h4>
                        <p>A more traditional but also lightweight map for you navigation.</p>
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
            <a href="#" class="menu-item">Minoan period</a>
            <a href="#" class="menu-item">Archaic & Classical</a>
            <a href="#" class="menu-item">Hellenistic period</a>
        </div>
        <div class="mega-menu-column">
            <h3 class="menu-item">Locations</h3>
            <a href="#" class="menu-item">Villages</a>
            <a href="#" class="menu-item">Religion</a>
        </div>
        <div class="mega-menu-column" style="grid-column: span 2;">
            <h3 class="menu-item">Featured Sites</h3>
            <div class="thumbnail-gallery">
                <a href="#" class="menu-item">
                    <img src="https://images.unsplash.com/photo-1629285483773-63a23349910e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDV8fGtub3Nzb3N8ZW58MHx8fHwxNjc5ODU5NTI4&ixlib=rb-4.0.3&q=80&w=1080" alt="Knossos Palace">
                    <div class="overlay"><span>Knossos Palace</span></div>
                </a>
                <a href="#" class="menu-item">
                    <img src="https://images.unsplash.com/photo-1614561331093-4e67f259b68a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDJ8fHBoYWlzdG9zfGVufDB8fHx8MTY3OTg1OTU1Nw&ixlib=rb-4.0.3&q=80&w=1080" alt="Phaistos Disc">
                    <div class="overlay"><span>Phaistos</span></div>
                </a>
                <a href="#" class="menu-item">
                    <img src="https://images.unsplash.com/photo-1582370146430-c3c2b875defa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDEwfHxjcmV0ZSUyMHJ1aW5zfGVufDB8fHx8MTY3OTg1OTU5NA&ixlib=rb-4.0.3&q=80&w=1080" alt="Ancient Ruins">
                    <div class="overlay"><span>Gortyna</span></div>
                </a>
                <a href="#" class="menu-item">
                    <img src="https://images.unsplash.com/photo-1590184432078-55d645f34133?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDE3fHxjcmV0ZSUyMGJlYWNofGVufDB8fHx8MTY3OTg1OTQzMw&ixlib=rb-4.0.3&q=80&w=1080" alt="Coastal view">
                    <div class="overlay"><span>Zakros</span></div>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Events Menu -->
<div id="events-menu" class="mega-menu-container">
    <div class="mega-menu-content">
        <div class="mega-menu-column">
            <h3 class="menu-item">Upcoming</h3>
            <a href="#" class="menu-item">Festivals</a>
            <a href="#" class="menu-item">Conferences</a>
            <a href="#" class="menu-item">Local Holidays</a>
        </div>
        <div class="mega-menu-column">
            <a href="#" class="media-card menu-item">
                <img src="https://images.unsplash.com/photo-1519700345399-65084910a5fe?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=MnwzNjUyOXwwfDF8c2VhcmNofDEyfHxjcmV0ZSUyMGZlc3RpdmFsfGVufDB8fHx8MTY3OTg2MDA5NA&ixlib=rb-4.0.3&q=80&w=1080" alt="Cretan Festival">
                <div class="media-card-content">
                    <h4>Summer Music Festival</h4>
                    <p>Join us for a celebration of traditional Cretan music and culture under the stars.</p>
                </div>
            </a>
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


