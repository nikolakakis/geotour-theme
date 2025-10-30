<?php
/**
 * Template part for displaying anchor ad at the bottom of the page
 * Only displayed if 'listing-gam-anchor-bottom' is in the adtype field
 *
 * @package Geotour_Mobile_First
 */

// Get the ACF field value
$adtype = function_exists('get_field') ? get_field('adtype') : array();

if (!is_array($adtype)) {
    $adtype = empty($adtype) ? array() : array($adtype);
}

// Only proceed if listing-gam-anchor-bottom is selected
if (!in_array('listing-gam-anchor-bottom', $adtype)) {
    return;
}
?>

<!-- Anchor Ad Container -->
<div id="anchor-ad-wrapper" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1150; display: flex; justify-content: center;">
    <!-- Ad Content Wrapper -->
    <div style="position: relative; background-color: white; border-radius: 0.5rem 0.5rem 0 0; box-shadow: 0 -4px 10px -5px rgba(0,0,0,0.1);">
        
        <!-- Toggle Button -->
        <button id="toggle-btn" title="Toggle Ad" style="position: absolute; top: -2rem; left: 50%; transform: translateX(-50%); height: 2rem; width: 3rem; padding: 0.25rem; display: flex; align-items: center; justify-content: center; background-color: white; border-radius: 0.5rem 0.5rem 0 0; box-shadow: 0 -2px 5px -2px rgba(0,0,0,0.1); border: none; cursor: pointer;">
            <svg id="toggle-arrow" xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: #4b5563;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        
        <!-- Ad Slot -->
        <div id="ad-slot">
            <?php if ( ! wp_is_mobile() ) : // Desktop Anchor Ad ?>
            <!-- /23317265155/Geotour_tour_Anchor_Ad_desktop -->
            <div id='div-gpt-ad-1761765099446-0' style='min-width: 728px; min-height: 90px;'>
              <script>
                googletag.cmd.push(function() { googletag.display('div-gpt-ad-1761765099446-0'); });
              </script>
            </div>
            <?php else : // Mobile Anchor Ad ?>
            <!-- /23317265155/Geotour_tour_Anchor_Ad_mobile -->
            <div id='div-gpt-ad-1761765177186-0' style='min-width: 320px; min-height: 50px;'>
              <script>
                googletag.cmd.push(function() { googletag.display('div-gpt-ad-1761765177186-0'); });
              </script>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Hide desktop anchor ad on screens < 1000px to prevent overflow */
    @media (max-width: 999px) {
        #anchor-ad-wrapper {
            display: none !important;
        }
    }
    
    #toggle-btn:hover {
        background-color: #f3f4f6;
    }
    
    #toggle-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px #3b82f6;
    }
</style>

<script>
    // This script handles the unfilled ad detection for both desktop and mobile anchor ads
    googletag.cmd.push(function() {
        <?php if ( ! wp_is_mobile() ) : ?>
        const adSlotElementId = "div-gpt-ad-1761765099446-0"; // Desktop
        <?php else : ?>
        const adSlotElementId = "div-gpt-ad-1761765177186-0"; // Mobile
        <?php endif; ?>

        // Get the slot by element ID
        const anchorSlot = googletag.pubads().getSlots().find(slot => {
            return slot.getSlotElementId() === adSlotElementId;
        });

        if (anchorSlot) {
            // Add Event Listener for unfilled detection
            googletag.pubads().addEventListener('slotRenderEnded', function(event) {
                // Check if the event is for our anchor slot
                if (event.slot === anchorSlot) {
                    const wrapper = document.getElementById('anchor-ad-wrapper');
                    
                    if (event.isEmpty) {
                        // Ad slot is empty (unfilled bid)
                        console.log("Anchor ad slot is empty. Hiding container.");
                        wrapper.classList.add('is-unfilled');
                        localStorage.setItem('isAdUnfilled', 'true');
                    } else {
                        // Ad slot was filled successfully
                        console.log("Anchor ad slot was filled.");
                        // Ensure it's not marked as unfilled from a previous page view
                        wrapper.classList.remove('is-unfilled');
                        localStorage.removeItem('isAdUnfilled');
                    }
                }
            });
        }
    });
</script>
