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
        add_shortcode('rdc_doctors', array($this, 'doctors_list'));
        add_shortcode('rdc_center_detail', array($this, 'center_detail'));
        add_shortcode('rdc_rotary_page', array($this, 'rotary_page'));
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

    /**
     * Doctors list
     * [rdc_doctors store_id="1" columns="3"]
     */
    public function doctors_list($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
            'columns' => 3,
            'show_availability' => 'yes',
        ), $atts);

        $store_id = absint($atts['store_id']);
        $columns = absint($atts['columns']);
        $show_availability = $atts['show_availability'] === 'yes';

        if (!$store_id) {
            return '';
        }

        $doctors = RDC_Doctor_Post_Type::get_doctors_for_store($store_id);

        if (empty($doctors)) {
            return '<p class="rdc-no-doctors">' . esc_html__('No doctors listed for this center.', 'rotary-dialysis-core') . '</p>';
        }

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/doctors-list.php';
        return ob_get_clean();
    }

    /**
     * Center detail page
     * [rdc_center_detail store_id="1"]
     */
    public function center_detail($atts) {
        $atts = shortcode_atts(array(
            'store_id' => 0,
        ), $atts);

        $store_id = absint($atts['store_id']);

        if (!$store_id) {
            // Try to get store_id from URL parameter
            $store_id = isset($_GET['center_id']) ? absint($_GET['center_id']) : 0;
        }

        if (!$store_id) {
            return '<p class="rdc-error">' . esc_html__('Please specify a dialysis center.', 'rotary-dialysis-core') . '</p>';
        }

        $store = RDC_ASL_Integration::get_enhanced_store($store_id);

        if (!$store) {
            return '<p class="rdc-error">' . esc_html__('Dialysis center not found.', 'rotary-dialysis-core') . '</p>';
        }

        // Get additional data
        $shifts = RDC_Appointment_Service::get_shifts($store_id);
        $center_meta = $this->get_center_meta($store_id);

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/center-detail.php';
        return ob_get_clean();
    }

    /**
     * Get center meta data
     */
    private function get_center_meta($store_id) {
        global $wpdb;

        $meta = array();
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->prefix}rdc_center_meta WHERE store_id = %d",
            $store_id
        ));

        foreach ($results as $row) {
            $meta[$row->meta_key] = maybe_unserialize($row->meta_value);
        }

        return $meta;
    }

    /**
     * Rotary projects page
     * [rdc_rotary_page]
     */
    public function rotary_page($atts) {
        $atts = shortcode_atts(array(
            'donate_url' => '',
            'volunteer_url' => '',
            'partner_url' => '',
            'contact_email' => get_option('rdc_admin_email', ''),
            'contact_phone' => '',
        ), $atts);

        // Get stats from database
        $stats = $this->get_rotary_stats();

        // Placeholder data - can be replaced with actual data from options or custom post type
        $gallery_images = $this->get_rotary_gallery();
        $team_members = $this->get_rotary_team();
        $partners = $this->get_rotary_partners();

        // Pass atts to template
        $donate_url = $atts['donate_url'];
        $volunteer_url = $atts['volunteer_url'];
        $partner_url = $atts['partner_url'];
        $contact_email = $atts['contact_email'];
        $contact_phone = $atts['contact_phone'];

        ob_start();
        include RDC_PLUGIN_DIR . 'templates/rotary-page.php';
        return ob_get_clean();
    }

    /**
     * Get Rotary project stats
     */
    private function get_rotary_stats() {
        global $wpdb;

        // Get actual center count from ASL
        $centers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}asl_stores WHERE is_disabled = 0");

        // Get actual stats from our tables
        $sessions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rdc_appointments WHERE status = 'completed'");
        $patients = $wpdb->get_var("SELECT COUNT(DISTINCT patient_email) FROM {$wpdb->prefix}rdc_appointments");
        $beds = $wpdb->get_var("SELECT SUM(total_beds) FROM {$wpdb->prefix}rdc_bed_availability");

        return array(
            'centers' => max(25, intval($centers)),
            'sessions' => max(150000, intval($sessions)),
            'patients' => max(5000, intval($patients)),
            'beds' => max(100, intval($beds)),
        );
    }

    /**
     * Get Rotary gallery images
     */
    private function get_rotary_gallery() {
        // This can be expanded to pull from a custom option or post type
        $gallery = get_option('rdc_rotary_gallery', array());

        if (!empty($gallery)) {
            return $gallery;
        }

        // Return empty array if no gallery configured
        return array();
    }

    /**
     * Get Rotary team members
     */
    private function get_rotary_team() {
        // This can be expanded to pull from a custom option or post type
        $team = get_option('rdc_rotary_team', array());

        if (!empty($team)) {
            return $team;
        }

        // Return empty array if no team configured
        return array();
    }

    /**
     * Get Rotary partners
     */
    private function get_rotary_partners() {
        // This can be expanded to pull from a custom option or post type
        $partners = get_option('rdc_rotary_partners', array());

        if (!empty($partners)) {
            return $partners;
        }

        // Return empty array if no partners configured
        return array();
    }
}
