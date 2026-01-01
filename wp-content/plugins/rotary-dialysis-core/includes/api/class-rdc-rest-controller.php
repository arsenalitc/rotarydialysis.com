<?php
/**
 * Base REST Controller
 *
 * Base class for REST API controllers.
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class RDC_REST_Controller extends WP_REST_Controller {

    /**
     * API namespace
     */
    protected $namespace = 'rdc/v1';

    /**
     * Check if request has valid nonce for logged-in users
     */
    protected function verify_nonce($request) {
        if (is_user_logged_in()) {
            $nonce = $request->get_header('X-WP-Nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return new WP_Error(
                    'rest_cookie_invalid_nonce',
                    __('Cookie nonce is invalid', 'rotary-dialysis-core'),
                    array('status' => 403)
                );
            }
        }
        return true;
    }

    /**
     * Check if user can manage center
     */
    protected function can_manage_center($store_id) {
        if (current_user_can('administrator')) {
            return true;
        }

        if (!current_user_can('rdc_manage_center')) {
            return false;
        }

        global $wpdb;
        $user_id = get_current_user_id();

        $is_staff = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}rdc_center_staff
            WHERE store_id = %d AND user_id = %d",
            $store_id,
            $user_id
        ));

        return !empty($is_staff);
    }

    /**
     * Validate store ID
     */
    protected function validate_store_id($store_id) {
        global $wpdb;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}asl_stores WHERE id = %d AND is_disabled = 0",
            $store_id
        ));

        if (!$exists) {
            return new WP_Error(
                'invalid_store',
                __('Invalid dialysis center.', 'rotary-dialysis-core'),
                array('status' => 404)
            );
        }

        return true;
    }

    /**
     * Rate limiting check
     */
    protected function check_rate_limit($action, $identifier, $limit = 10, $window = 60) {
        $transient_key = 'rdc_rate_' . md5($action . $identifier);
        $count = get_transient($transient_key);

        if ($count === false) {
            set_transient($transient_key, 1, $window);
            return true;
        }

        if ($count >= $limit) {
            return new WP_Error(
                'rate_limit_exceeded',
                __('Too many requests. Please try again later.', 'rotary-dialysis-core'),
                array('status' => 429)
            );
        }

        set_transient($transient_key, $count + 1, $window);
        return true;
    }

    /**
     * Success response
     */
    protected function success_response($data, $status = 200) {
        return new WP_REST_Response($data, $status);
    }

    /**
     * Error response
     */
    protected function error_response($message, $code = 'error', $status = 400) {
        return new WP_Error($code, $message, array('status' => $status));
    }
}
