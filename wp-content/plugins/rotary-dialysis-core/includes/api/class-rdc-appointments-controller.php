<?php
/**
 * Appointments REST Controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Appointments_Controller extends RDC_REST_Controller {

    /**
     * Resource name
     */
    protected $rest_base = 'centers/(?P<store_id>[\d]+)/appointments';

    /**
     * Register routes
     */
    public function register_routes() {
        // GET/POST appointments for a center
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
                'args' => array(
                    'store_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'status' => array(
                        'type' => 'string',
                        'enum' => array('pending', 'confirmed', 'cancelled', 'completed'),
                    ),
                    'date' => array(
                        'type' => 'string',
                        'format' => 'date',
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
                    'patient_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'patient_phone' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'patient_email' => array(
                        'type' => 'string',
                        'format' => 'email',
                    ),
                    'preferred_date' => array(
                        'required' => true,
                        'type' => 'string',
                        'format' => 'date',
                    ),
                    'shift_id' => array(
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'message' => array(
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                ),
            ),
        ));

        // GET/PATCH single appointment by confirmation code
        register_rest_route($this->namespace, '/appointments/(?P<code>[A-Z0-9-]+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
            array(
                'methods' => 'PATCH',
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'update_item_permissions_check'),
                'args' => array(
                    'code' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'status' => array(
                        'required' => true,
                        'type' => 'string',
                        'enum' => array('confirmed', 'cancelled', 'completed'),
                    ),
                ),
            ),
        ));

        // GET shifts for a center
        register_rest_route($this->namespace, '/' . $this->rest_base . '/shifts', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_shifts'),
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
     * Get appointments for a center
     */
    public function get_items($request) {
        $store_id = $request->get_param('store_id');

        $appointments = RDC_Appointment_Service::get_store_appointments($store_id, array(
            'status' => $request->get_param('status'),
            'date' => $request->get_param('date'),
        ));

        return $this->success_response(array(
            'appointments' => $appointments,
            'total' => count($appointments),
        ));
    }

    /**
     * Get single appointment by code
     */
    public function get_item($request) {
        $code = $request->get_param('code');

        $appointment = RDC_Appointment_Service::get_by_code($code);

        if (!$appointment) {
            return $this->error_response(
                __('Appointment not found.', 'rotary-dialysis-core'),
                'not_found',
                404
            );
        }

        // Mask sensitive info for public access
        if (!is_user_logged_in()) {
            $appointment->patient_email = self::mask_email($appointment->patient_email);
            $appointment->patient_phone = self::mask_phone($appointment->patient_phone);
        }

        return $this->success_response($appointment);
    }

    /**
     * Create appointment
     */
    public function create_item($request) {
        $store_id = $request->get_param('store_id');

        // Rate limiting - 5 bookings per hour per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rate_check = $this->check_rate_limit('appointment_create', $ip, 5, 3600);
        if (is_wp_error($rate_check)) {
            return $rate_check;
        }

        $valid = $this->validate_store_id($store_id);
        if (is_wp_error($valid)) {
            return $valid;
        }

        $result = RDC_Appointment_Service::create_appointment(array(
            'store_id' => $store_id,
            'patient_name' => $request->get_param('patient_name'),
            'patient_phone' => $request->get_param('patient_phone'),
            'patient_email' => $request->get_param('patient_email'),
            'preferred_date' => $request->get_param('preferred_date'),
            'shift_id' => $request->get_param('shift_id'),
            'message' => $request->get_param('message'),
        ));

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response($result, 201);
    }

    /**
     * Update appointment status
     */
    public function update_item($request) {
        $code = $request->get_param('code');
        $status = $request->get_param('status');

        $appointment = RDC_Appointment_Service::get_by_code($code);

        if (!$appointment) {
            return $this->error_response(
                __('Appointment not found.', 'rotary-dialysis-core'),
                'not_found',
                404
            );
        }

        $result = RDC_Appointment_Service::update_status($appointment->id, $status);

        if (is_wp_error($result)) {
            return $result;
        }

        return $this->success_response(array(
            'success' => true,
            'new_status' => $status,
        ));
    }

    /**
     * Get shifts for a center
     */
    public function get_shifts($request) {
        $store_id = $request->get_param('store_id');

        $shifts = RDC_Appointment_Service::get_shifts($store_id);

        return $this->success_response(array(
            'shifts' => $shifts,
        ));
    }

    /**
     * Permission check for getting items
     */
    public function get_items_permissions_check($request) {
        $store_id = $request->get_param('store_id');
        return $this->can_manage_center($store_id) || current_user_can('rdc_view_appointments');
    }

    /**
     * Permission check for update
     */
    public function update_item_permissions_check($request) {
        return current_user_can('rdc_manage_appointments');
    }

    /**
     * Mask email for privacy
     */
    private static function mask_email($email) {
        if (!$email) {
            return '';
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }
        $name = substr($parts[0], 0, 2) . str_repeat('*', max(0, strlen($parts[0]) - 2));
        return $name . '@' . $parts[1];
    }

    /**
     * Mask phone for privacy
     */
    private static function mask_phone($phone) {
        if (!$phone || strlen($phone) < 4) {
            return '***';
        }
        return str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
    }
}
