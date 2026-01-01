<?php
/**
 * Public-facing functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Public {

    /**
     * Enqueue public styles
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'rdc-public',
            RDC_PLUGIN_URL . 'public/css/rdc-public.css',
            array(),
            RDC_VERSION
        );
    }

    /**
     * Enqueue public scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'rdc-public',
            RDC_PLUGIN_URL . 'public/js/rdc-public.js',
            array('jquery'),
            RDC_VERSION,
            true
        );

        wp_localize_script('rdc-public', 'rdcPublic', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('rdc/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => array(
                'submitting' => __('Submitting...', 'rotary-dialysis-core'),
                'success' => __('Submitted successfully!', 'rotary-dialysis-core'),
                'error' => __('An error occurred. Please try again.', 'rotary-dialysis-core'),
                'required' => __('This field is required.', 'rotary-dialysis-core'),
                'invalidEmail' => __('Please enter a valid email address.', 'rotary-dialysis-core'),
                'invalidPhone' => __('Please enter a valid phone number.', 'rotary-dialysis-core'),
            ),
        ));
    }
}
