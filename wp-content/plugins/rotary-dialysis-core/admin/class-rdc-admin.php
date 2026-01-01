<?php
/**
 * Admin functionality
 *
 * Handles all admin-related functionality including settings pages.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Admin {

    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Rotary Dialysis', 'rotary-dialysis-core'),
            __('Rotary Dialysis', 'rotary-dialysis-core'),
            'manage_options',
            'rotary-dialysis',
            array($this, 'render_dashboard_page'),
            'dashicons-heart',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Dashboard', 'rotary-dialysis-core'),
            __('Dashboard', 'rotary-dialysis-core'),
            'manage_options',
            'rotary-dialysis',
            array($this, 'render_dashboard_page')
        );

        // Reviews submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Reviews', 'rotary-dialysis-core'),
            __('Reviews', 'rotary-dialysis-core'),
            'rdc_moderate_reviews',
            'rdc-reviews',
            array($this, 'render_reviews_page')
        );

        // Bed Availability submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Bed Availability', 'rotary-dialysis-core'),
            __('Bed Availability', 'rotary-dialysis-core'),
            'rdc_manage_availability',
            'rdc-availability',
            array($this, 'render_availability_page')
        );

        // Appointments submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Appointments', 'rotary-dialysis-core'),
            __('Appointments', 'rotary-dialysis-core'),
            'rdc_view_appointments',
            'rdc-appointments',
            array($this, 'render_appointments_page')
        );

        // Documents submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Documents', 'rotary-dialysis-core'),
            __('Documents', 'rotary-dialysis-core'),
            'manage_options',
            'rdc-documents',
            array($this, 'render_documents_page')
        );

        // Settings submenu
        add_submenu_page(
            'rotary-dialysis',
            __('Settings', 'rotary-dialysis-core'),
            __('Settings', 'rotary-dialysis-core'),
            'rdc_manage_settings',
            'rdc-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Tawk.to settings section
        add_settings_section(
            'rdc_tawkto_section',
            __('Tawk.to Live Chat', 'rotary-dialysis-core'),
            array($this, 'render_tawkto_section'),
            'rdc-settings'
        );

        register_setting('rdc_settings', 'rdc_tawkto_property_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rdc_settings', 'rdc_tawkto_widget_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        register_setting('rdc_settings', 'rdc_tawkto_enabled', array(
            'type' => 'boolean',
            'default' => true,
        ));

        add_settings_field(
            'rdc_tawkto_enabled',
            __('Enable Tawk.to', 'rotary-dialysis-core'),
            array($this, 'render_checkbox_field'),
            'rdc-settings',
            'rdc_tawkto_section',
            array('name' => 'rdc_tawkto_enabled', 'label' => __('Enable live chat widget', 'rotary-dialysis-core'))
        );

        add_settings_field(
            'rdc_tawkto_property_id',
            __('Property ID', 'rotary-dialysis-core'),
            array($this, 'render_text_field'),
            'rdc-settings',
            'rdc_tawkto_section',
            array('name' => 'rdc_tawkto_property_id', 'placeholder' => 'e.g., 5a1b2c3d4e5f6g7h8i9j0k')
        );

        add_settings_field(
            'rdc_tawkto_widget_id',
            __('Widget ID', 'rotary-dialysis-core'),
            array($this, 'render_text_field'),
            'rdc-settings',
            'rdc_tawkto_section',
            array('name' => 'rdc_tawkto_widget_id', 'placeholder' => 'e.g., default')
        );

        // General settings section
        add_settings_section(
            'rdc_general_section',
            __('General Settings', 'rotary-dialysis-core'),
            array($this, 'render_general_section'),
            'rdc-settings'
        );

        register_setting('rdc_settings', 'rdc_admin_email', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
        ));

        register_setting('rdc_settings', 'rdc_review_moderation_enabled', array(
            'type' => 'boolean',
            'default' => true,
        ));

        register_setting('rdc_settings', 'rdc_booking_advance_days', array(
            'type' => 'integer',
            'default' => 30,
            'sanitize_callback' => 'absint',
        ));

        add_settings_field(
            'rdc_admin_email',
            __('Admin Email', 'rotary-dialysis-core'),
            array($this, 'render_text_field'),
            'rdc-settings',
            'rdc_general_section',
            array('name' => 'rdc_admin_email', 'type' => 'email')
        );

        add_settings_field(
            'rdc_review_moderation_enabled',
            __('Review Moderation', 'rotary-dialysis-core'),
            array($this, 'render_checkbox_field'),
            'rdc-settings',
            'rdc_general_section',
            array('name' => 'rdc_review_moderation_enabled', 'label' => __('Require approval for new reviews', 'rotary-dialysis-core'))
        );

        add_settings_field(
            'rdc_booking_advance_days',
            __('Booking Advance Days', 'rotary-dialysis-core'),
            array($this, 'render_number_field'),
            'rdc-settings',
            'rdc_general_section',
            array('name' => 'rdc_booking_advance_days', 'min' => 1, 'max' => 90)
        );

        // Availability thresholds section
        add_settings_section(
            'rdc_availability_section',
            __('Availability Thresholds', 'rotary-dialysis-core'),
            array($this, 'render_availability_section'),
            'rdc-settings'
        );

        register_setting('rdc_settings', 'rdc_availability_threshold_warning', array(
            'type' => 'integer',
            'default' => 30,
            'sanitize_callback' => 'absint',
        ));

        register_setting('rdc_settings', 'rdc_availability_threshold_critical', array(
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => 'absint',
        ));

        add_settings_field(
            'rdc_availability_threshold_warning',
            __('Warning Threshold (%)', 'rotary-dialysis-core'),
            array($this, 'render_number_field'),
            'rdc-settings',
            'rdc_availability_section',
            array('name' => 'rdc_availability_threshold_warning', 'min' => 1, 'max' => 100)
        );

        add_settings_field(
            'rdc_availability_threshold_critical',
            __('Critical Threshold (%)', 'rotary-dialysis-core'),
            array($this, 'render_number_field'),
            'rdc-settings',
            'rdc_availability_section',
            array('name' => 'rdc_availability_threshold_critical', 'min' => 0, 'max' => 100)
        );
    }

    /**
     * Render section descriptions
     */
    public function render_tawkto_section() {
        echo '<p>' . esc_html__('Configure Tawk.to live chat widget. Get your Property ID and Widget ID from your Tawk.to dashboard.', 'rotary-dialysis-core') . '</p>';
        echo '<p><a href="https://www.tawk.to" target="_blank">' . esc_html__('Create a free Tawk.to account', 'rotary-dialysis-core') . '</a></p>';
    }

    public function render_general_section() {
        echo '<p>' . esc_html__('General plugin settings.', 'rotary-dialysis-core') . '</p>';
    }

    public function render_availability_section() {
        echo '<p>' . esc_html__('Set thresholds for bed availability status indicators.', 'rotary-dialysis-core') . '</p>';
    }

    /**
     * Render form fields
     */
    public function render_text_field($args) {
        $value = get_option($args['name'], '');
        $type = isset($args['type']) ? $args['type'] : 'text';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        printf(
            '<input type="%s" id="%s" name="%s" value="%s" placeholder="%s" class="regular-text" />',
            esc_attr($type),
            esc_attr($args['name']),
            esc_attr($args['name']),
            esc_attr($value),
            esc_attr($placeholder)
        );
    }

    public function render_number_field($args) {
        $value = get_option($args['name'], '');
        $min = isset($args['min']) ? $args['min'] : 0;
        $max = isset($args['max']) ? $args['max'] : 100;
        printf(
            '<input type="number" id="%s" name="%s" value="%s" min="%d" max="%d" class="small-text" />',
            esc_attr($args['name']),
            esc_attr($args['name']),
            esc_attr($value),
            $min,
            $max
        );
    }

    public function render_checkbox_field($args) {
        $value = get_option($args['name'], false);
        $label = isset($args['label']) ? $args['label'] : '';
        printf(
            '<label><input type="checkbox" id="%s" name="%s" value="1" %s /> %s</label>',
            esc_attr($args['name']),
            esc_attr($args['name']),
            checked($value, '1', false),
            esc_html($label)
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_styles($hook) {
        if (strpos($hook, 'rotary-dialysis') === false && strpos($hook, 'rdc-') === false) {
            return;
        }

        wp_enqueue_style(
            'rdc-admin',
            RDC_PLUGIN_URL . 'admin/css/rdc-admin.css',
            array(),
            RDC_VERSION
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'rotary-dialysis') === false && strpos($hook, 'rdc-') === false) {
            return;
        }

        wp_enqueue_script(
            'rdc-admin',
            RDC_PLUGIN_URL . 'admin/js/rdc-admin.js',
            array('jquery'),
            RDC_VERSION,
            true
        );

        wp_localize_script('rdc-admin', 'rdcAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('rdc/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => array(
                'confirmDelete' => __('Are you sure you want to delete this?', 'rotary-dialysis-core'),
                'saving' => __('Saving...', 'rotary-dialysis-core'),
                'saved' => __('Saved!', 'rotary-dialysis-core'),
                'error' => __('An error occurred.', 'rotary-dialysis-core'),
            ),
        ));
    }

    /**
     * Render admin pages
     */
    public function render_dashboard_page() {
        include RDC_PLUGIN_DIR . 'admin/partials/dashboard.php';
    }

    public function render_reviews_page() {
        include RDC_PLUGIN_DIR . 'admin/partials/reviews.php';
    }

    public function render_availability_page() {
        include RDC_PLUGIN_DIR . 'admin/partials/availability.php';
    }

    public function render_appointments_page() {
        include RDC_PLUGIN_DIR . 'admin/partials/appointments.php';
    }

    public function render_documents_page() {
        include RDC_PLUGIN_DIR . 'admin/partials/documents.php';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rdc_settings');
                do_settings_sections('rdc-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
