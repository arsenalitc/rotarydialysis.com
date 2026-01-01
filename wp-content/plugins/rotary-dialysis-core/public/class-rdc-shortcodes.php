<?php
/**
 * Shortcodes
 *
 * Registers and handles all plugin shortcodes.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Shortcodes {

    /**
     * Register all shortcodes
     */
    public function register() {
        add_shortcode('rdc_review_form', array($this, 'review_form'));
        add_shortcode('rdc_reviews', array($this, 'reviews_list'));
        add_shortcode('rdc_booking_form', array($this, 'booking_form'));
        add_shortcode('rdc_availability', array($this, 'availability_badge'));
        add_shortcode('rdc_gallery', array($this, 'gallery'));
        add_shortcode('rdc_documents', array($this, 'documents_list'));
        add_shortcode('rdc_center_info', array($this, 'center_info'));
    }

    /**
     * Review submission form
     * [rdc_review_form store_id="1"]
     */
    public function review_form($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
        ), $atts);

        $store_id = absint($atts['store_id']);

        if (!$store_id) {
            return '<p class="rdc-error">' . esc_html__('Please specify a dialysis center.', 'rotary-dialysis-core') . '</p>';
        }

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/review-form.php';
        return ob_get_clean();
    }

    /**
     * Reviews list
     * [rdc_reviews store_id="1" limit="5"]
     */
    public function reviews_list($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
            'limit' => 5,
        ), $atts);

        $store_id = absint($atts['store_id']);
        $limit = absint($atts['limit']);

        if (!$store_id) {
            return '<p class="rdc-error">' . esc_html__('Please specify a dialysis center.', 'rotary-dialysis-core') . '</p>';
        }

        $reviews = RDC_Review_Service::get_reviews($store_id, array('limit' => $limit));
        $stats = RDC_Review_Service::get_stats($store_id);

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/reviews-list.php';
        return ob_get_clean();
    }

    /**
     * Booking form
     * [rdc_booking_form store_id="1"]
     */
    public function booking_form($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
        ), $atts);

        $store_id = absint($atts['store_id']);

        if (!$store_id) {
            return '<p class="rdc-error">' . esc_html__('Please specify a dialysis center.', 'rotary-dialysis-core') . '</p>';
        }

        $store = RDC_ASL_Integration::get_store($store_id);
        $shifts = RDC_Appointment_Service::get_shifts($store_id);
        $advance_days = get_option('rdc_booking_advance_days', 30);

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }

    /**
     * Availability badge
     * [rdc_availability store_id="1"]
     */
    public function availability_badge($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
            'show_count' => 'yes',
        ), $atts);

        $store_id = absint($atts['store_id']);
        $show_count = $atts['show_count'] === 'yes';

        if (!$store_id) {
            return '';
        }

        $availability = RDC_Availability_Service::get_availability($store_id);

        $status_labels = array(
            'available' => __('Available', 'rotary-dialysis-core'),
            'limited' => __('Limited', 'rotary-dialysis-core'),
            'full' => __('Full', 'rotary-dialysis-core'),
            'unknown' => __('Unknown', 'rotary-dialysis-core'),
        );

        $label = $status_labels[$availability['status']] ?? $status_labels['unknown'];

        $output = '<span class="rdc-bed-badge rdc-bed-badge--' . esc_attr($availability['status']) . '">';
        $output .= esc_html($label);
        if ($show_count && $availability['total_beds'] > 0) {
            $output .= ' (' . esc_html($availability['available_beds']) . '/' . esc_html($availability['total_beds']) . ')';
        }
        $output .= '</span>';

        return $output;
    }

    /**
     * Gallery
     * [rdc_gallery store_id="1" limit="8"]
     */
    public function gallery($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
            'limit' => 8,
            'columns' => 4,
        ), $atts);

        $store_id = absint($atts['store_id']);
        $limit = absint($atts['limit']);
        $columns = absint($atts['columns']);

        if (!$store_id) {
            return '';
        }

        $images = RDC_Gallery_Service::get_images($store_id, $limit);

        if (empty($images)) {
            return '';
        }

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/gallery.php';
        return ob_get_clean();
    }

    /**
     * Documents list
     * [rdc_documents store_id="1"]
     */
    public function documents_list($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
        ), $atts);

        $store_id = absint($atts['store_id']);

        global $wpdb;

        // Get documents for specific store OR all stores (store_id = NULL)
        $documents = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_documents
            WHERE store_id IS NULL OR store_id = %d
            ORDER BY is_mandatory DESC, sort_order ASC, document_name ASC",
            $store_id
        ));

        if (empty($documents)) {
            return '<p>' . esc_html__('No documents required.', 'rotary-dialysis-core') . '</p>';
        }

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/documents-list.php';
        return ob_get_clean();
    }

    /**
     * Center info card
     * [rdc_center_info store_id="1"]
     */
    public function center_info($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
            'show_rating' => 'yes',
            'show_availability' => 'yes',
            'show_contact' => 'yes',
        ), $atts);

        $store_id = absint($atts['store_id']);

        if (!$store_id) {
            return '';
        }

        $store = RDC_ASL_Integration::get_enhanced_store($store_id);

        if (!$store) {
            return '';
        }

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/center-info.php';
        return ob_get_clean();
    }
}
