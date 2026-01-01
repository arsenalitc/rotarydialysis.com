<?php
/**
 * Availability REST Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Availability_Controller extends RDC_REST_Controller {

    /**
     * Resource name
     */
    protected $rest_base = 'centers/(?P<store_id>[\d]+)/availability';

    /**
     * Register routes
     */
    public function register_routes() {
        // GET/PUT availability for a center
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'shift' => array(
                        'default' => 'all',
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'available_beds' => array(
                        'required' => true,
                        'type' => 'integer',
                        'minimum' => 0,
                    ),
                    'total_beds' => array(
                        'type' => 'integer',
                        'minimum' => 0,
                    ),
                    'shift' => array(
                        'default' => 'all',
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
        ));

        // GET all centers availability
        register_rest_route($this->namespace, '/availability', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_items'),
            'permission_callback' => '__return_true',
            'args' => array(
                'shift' => array(
                    'default' => 'all',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));

        // GET availability history
        register_rest_route($this->namespace, '/' . $this->rest_base . '/history', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_history'),
            'permission_callback' => array($this, 'view_history_permissions_check'),
            'args' => array(
                'store_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'days' => array(
                    'default' => 7,
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 30,
                ),
            ),
        ));
    }

    /**
     * Get availability for a center
     */
    public function get_item($request) {
        $store_id = $request->get_param('store_id');
        $shift = $request->get_param('shift');

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $availability = RDC_Availability_Service::get_availability($store_id, $shift);

        return $this->success_response($availability);
    }

    /**
     * Get availability for all centers
     */
    public function get_items($request) {
        $shift = $request->get_param('shift');

        $availability = RDC_Availability_Service::get_all_availability($shift);

        return $this->success_response(array(
            'centers' => $availability,
            'total' => count($availability),
        ));
    }

    /**
     * Update availability
     */
    public function update_item($request) {
        $store_id = $request->get_param('store_id');
        $available_beds = $request->get_param('available_beds');
        $total_beds = $request->get_param('total_beds');
        $shift = $request->get_param('shift');

        $result = RDC_Availability_Service::update_availability(
            $store_id,
            $available_beds,
            $total_beds,
            $shift
        );

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response($result);
    }

    /**
     * Get availability history
     */
    public function get_history($request) {
        $store_id = $request->get_param('store_id');
        $days = $request->get_param('days');

        $history = RDC_Availability_Service::get_history($store_id, $days);

        return $this->success_response(array(
            'history' => $history,
            'total' => count($history),
        ));
    }

    /**
     * Permission check for update
     */
    public function update_item_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id) || current_user_can('rdc_manage_availability');
    }

    /**
     * Permission check for viewing history
     */
    public function view_history_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id) || current_user_can('rdc_manage_availability');
    }
}
