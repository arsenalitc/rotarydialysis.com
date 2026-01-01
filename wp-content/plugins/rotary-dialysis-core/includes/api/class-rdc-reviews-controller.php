<?php
/**
 * Reviews REST Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Reviews_Controller extends RDC_REST_Controller {

    /**
     * Resource name
     */
    protected $rest_base = 'centers/(?P<store_id>[\d]+)/reviews';

    /**
     * Register routes
     */
    public function register_routes() {
        // GET/POST reviews for a center
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'limit' => array(
                        'default' => 10,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'offset' => array(
                        'default' => 0,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'rating' => array(
                        'required' => true,
                        'type' => 'integer',
                        'minimum' => 1,
                        'maximum' => 5,
                    ),
                    'review_text' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'reviewer_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'reviewer_email' => array(
                        'required' => true,
                        'type' => 'string',
                        'format' => 'email',
                    ),
                ),
            ),
        ));

        // Email verification endpoint
        register_rest_route($this->namespace, '/email/verify', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'verify_email'),
            'permission_callback' => '__return_true',
            'args' => array(
                'token' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));

        // Get review stats for a center
        register_rest_route($this->namespace, '/centers/(?P<store_id>[\d]+)/review-stats', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_stats'),
            'permission_callback' => '__return_true',
            'args' => array(
                'store_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }

    /**
     * Get reviews for a center
     */
    public function get_items($request) {
        $store_id = $request->get_param('store_id');

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $reviews = RDC_Review_Service::get_reviews($store_id, array(
            'limit' => $request->get_param('limit'),
            'offset' => $request->get_param('offset'),
        ));

        $stats = RDC_Review_Service::get_stats($store_id);

        return $this->success_response(array(
            'reviews' => $reviews,
            'stats' => $stats,
        ));
    }

    /**
     * Submit a review
     */
    public function create_item($request) {
        $store_id = $request->get_param('store_id');

        // Rate limiting - 5 reviews per hour per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_check = $this->check_rate_limit('review_submit', $ip, 5, 3600);
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $result = RDC_Review_Service::submit_review(array(
            'store_id' => $store_id,
            'rating' => $request->get_param('rating'),
            'review_text' => $request->get_param('review_text'),
            'reviewer_name' => $request->get_param('reviewer_name'),
            'reviewer_email' => $request->get_param('reviewer_email'),
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response($result, 201);
    }

    /**
     * Verify email
     */
    public function verify_email($request) {
        $token = $request->get_param('token');

        $result = RDC_Review_Service::verify_review($token);

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response($result);
    }

    /**
     * Get review stats
     */
    public function get_stats($request) {
        $store_id = $request->get_param('store_id');

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $stats = RDC_Review_Service::get_stats($store_id);

        return $this->success_response($stats);
    }
}
