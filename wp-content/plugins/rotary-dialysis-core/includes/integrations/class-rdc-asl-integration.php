<?php
/**
 * Agile Store Locator Integration
 *
 * Integrates with ASL plugin to enhance store listings.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_ASL_Integration {

    /**
     * Initialize integration
     */
    public static function init() {
        // Add custom fields to ASL store popup
        add_filter('asl_popup_template', array(__CLASS__, 'customize_popup_template'));

        // Add rating badge to store cards
        add_filter('asl_store_card_template', array(__CLASS__, 'add_rating_to_card'));

        // Add availability badge via JavaScript
        add_action('wp_footer', array(__CLASS__, 'inject_availability_js'));
    }

    /**
     * Get store by ID
     */
    public static function get_store($store_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}asl_stores WHERE id = %d",
            $store_id
        ));
    }

    /**
     * Get all active stores
     */
    public static function get_all_stores() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}asl_stores WHERE is_disabled = 0 ORDER BY title"
        );
    }

    /**
     * Customize popup template
     */
    public static function customize_popup_template($template) {
        // Add rating and availability placeholders
        $rating_html = '<div class="rdc-store-rating" data-store-id="{{id}}"></div>';
        $availability_html = '<div class="rdc-store-availability" data-store-id="{{id}}"></div>';

        // Insert before closing popup content
        $template = str_replace('</div><!-- .asl-popup-content -->', $rating_html . $availability_html . '</div><!-- .asl-popup-content -->', $template);

        return $template;
    }

    /**
     * Add rating to store card
     */
    public static function add_rating_to_card($template) {
        // Add rating placeholder to card
        $rating_html = '<div class="rdc-card-rating" data-store-id="{{id}}"></div>';

        // Insert after store title
        $template = str_replace('{{title}}</h3>', '{{title}}</h3>' . $rating_html, $template);

        return $template;
    }

    /**
     * Inject availability JavaScript
     */
    public static function inject_availability_js() {
        // Only on pages with ASL
        if (!self::is_asl_page()) {
            return;
        }
        ?>
        <script>
        (function($) {
            'use strict';

            // Fetch and display availability for all stores
            function loadAvailability() {
                $.get('<?php echo esc_url(rest_url('rdc/v1/availability')); ?>', function(response) {
                    if (response && response.centers) {
                        response.centers.forEach(function(center) {
                            var $badge = $('[data-store-id="' + center.id + '"].rdc-store-availability, [data-store-id="' + center.id + '"] .rdc-store-availability');
                            if ($badge.length) {
                                var statusClass = 'rdc-bed-badge--' + center.status;
                                var label = center.status === 'full' ? '<?php echo esc_js(__('Full', 'rotary-dialysis-core')); ?>' :
                                            center.status === 'limited' ? '<?php echo esc_js(__('Limited Beds', 'rotary-dialysis-core')); ?>' :
                                            '<?php echo esc_js(__('Available', 'rotary-dialysis-core')); ?>';
                                $badge.html('<span class="rdc-bed-badge ' + statusClass + '">' + label + ' (' + center.available_beds + '/' + center.total_beds + ')</span>');
                            }
                        });
                    }
                });
            }

            // Fetch and display ratings
            function loadRatings() {
                $('[data-store-id].rdc-card-rating, [data-store-id].rdc-store-rating').each(function() {
                    var $el = $(this);
                    var storeId = $el.data('store-id');
                    if (!storeId) return;

                    $.get('<?php echo esc_url(rest_url('rdc/v1/centers/')); ?>' + storeId + '/review-stats', function(response) {
                        if (response && response.total_reviews > 0) {
                            var stars = '';
                            for (var i = 1; i <= 5; i++) {
                                stars += i <= Math.round(response.average_rating) ? '★' : '☆';
                            }
                            $el.html('<span class="rdc-rating-stars">' + stars + '</span><span class="rdc-rating-count">(' + response.total_reviews + ')</span>');
                        }
                    });
                });
            }

            $(document).ready(function() {
                loadAvailability();
                loadRatings();

                // Reload on ASL store list update
                $(document).on('asl_stores_loaded', function() {
                    loadAvailability();
                    loadRatings();
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    /**
     * Check if current page has ASL
     */
    private static function is_asl_page() {
        global $post;

        if (!$post) {
            return false;
        }

        // Check for ASL shortcode
        if (has_shortcode($post->post_content, 'ASL_STORELOCATOR')) {
            return true;
        }

        // Check for specific pages
        $asl_pages = array('home', 'dialysis-centers', 'find-center');
        if (in_array($post->post_name, $asl_pages)) {
            return true;
        }

        return false;
    }

    /**
     * Get enhanced store data
     */
    public static function get_enhanced_store($store_id) {
        $store = self::get_store($store_id);

        if (!$store) {
            return null;
        }

        // Add rating stats
        $store->rating_stats = RDC_Review_Service::get_stats($store_id);

        // Add availability
        $store->availability = RDC_Availability_Service::get_availability($store_id);

        // Add gallery
        $store->gallery = RDC_Gallery_Service::get_images($store_id, 5);

        // Add featured image
        $store->featured_image = RDC_Gallery_Service::get_featured_image($store_id);

        return $store;
    }
}

// Initialize integration
add_action('init', array('RDC_ASL_Integration', 'init'));
