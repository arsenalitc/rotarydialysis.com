<?php
/**
 * Appointment Service
 *
 * Handles appointment booking business logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Appointment_Service {

    /**
     * Create a new appointment
     */
    public static function create_appointment($data) {
        global $wpdb;

        $store_id = absint($data['store_id']);
        $patient_name = sanitize_text_field($data['patient_name']);
        $patient_phone = sanitize_text_field($data['patient_phone']);
        $patient_email = isset($data['patient_email']) ? sanitize_email($data['patient_email']) : '';
        $preferred_date = sanitize_text_field($data['preferred_date']);
        $message = isset($data['message']) ? sanitize_textarea_field($data['message']) : '';
        $shift_id = isset($data['shift_id']) ? absint($data['shift_id']) : null;

        // Validate required fields
        if (!$store_id || !$patient_name || !$patient_phone || !$preferred_date) {
            return new WP_Error('missing_fields', __('Please fill in all required fields.', 'rotary-dialysis-core'));
        }

        // Validate date
        $date = DateTime::createFromFormat('Y-m-d', $preferred_date);
        if (!$date) {
            return new WP_Error('invalid_date', __('Please provide a valid date.', 'rotary-dialysis-core'));
        }

        // Check date is in the future
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        if ($date < $today) {
            return new WP_Error('past_date', __('Please select a future date.', 'rotary-dialysis-core'));
        }

        // Check date is within booking window
        $advance_days = get_option('rdc_booking_advance_days', 30);
        $max_date = new DateTime();
        $max_date->modify("+{$advance_days} days");
        if ($date > $max_date) {
            return new WP_Error('date_too_far', sprintf(
                __('Appointments can only be booked up to %d days in advance.', 'rotary-dialysis-core'),
                $advance_days
            ));
        }

        // Verify store exists
        $store = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}asl_stores WHERE id = %d AND is_disabled = 0",
            $store_id
        ));

        if (!$store) {
            return new WP_Error('invalid_store', __('Invalid dialysis center.', 'rotary-dialysis-core'));
        }

        // Check for duplicate appointment
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}rdc_appointments
            WHERE store_id = %d AND patient_phone = %s AND preferred_date = %s
            AND status NOT IN ('cancelled', 'completed')",
            $store_id,
            $patient_phone,
            $preferred_date
        ));

        if ($existing) {
            return new WP_Error('duplicate', __('You already have an appointment for this date at this center.', 'rotary-dialysis-core'));
        }

        // Generate confirmation code
        $confirmation_code = self::generate_confirmation_code($preferred_date);

        // Insert appointment
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'rdc_appointments',
            array(
                'store_id' => $store_id,
                'shift_id' => $shift_id,
                'confirmation_code' => $confirmation_code,
                'patient_name' => $patient_name,
                'patient_phone' => $patient_phone,
                'patient_email' => $patient_email,
                'preferred_date' => $preferred_date,
                'message' => $message,
                'status' => 'pending',
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (!$inserted) {
            return new WP_Error('db_error', __('Failed to create appointment.', 'rotary-dialysis-core'));
        }

        $appointment_id = $wpdb->insert_id;
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_appointments WHERE id = %d",
            $appointment_id
        ));

        // Send confirmation email
        RDC_Email_Service::send_appointment_confirmation($appointment);

        return array(
            'success' => true,
            'appointment_id' => $appointment_id,
            'confirmation_code' => $confirmation_code,
            'message' => __('Your appointment request has been submitted. You will receive a confirmation email shortly.', 'rotary-dialysis-core'),
        );
    }

    /**
     * Get appointment by confirmation code
     */
    public static function get_by_code($confirmation_code) {
        global $wpdb;

        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, s.title as store_name, s.phone as store_phone, s.street as store_address
            FROM {$wpdb->prefix}rdc_appointments a
            LEFT JOIN {$wpdb->prefix}asl_stores s ON a.store_id = s.id
            WHERE a.confirmation_code = %s",
            $confirmation_code
        ));

        return $appointment;
    }

    /**
     * Get appointments for a store
     */
    public static function get_store_appointments($store_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'status' => null,
            'date' => null,
            'limit' => 50,
            'offset' => 0,
        );

        $args = wp_parse_args($args, $defaults);

        $where = array("a.store_id = %d");
        $params = array($store_id);

        if ($args['status']) {
            $where[] = "a.status = %s";
            $params[] = $args['status'];
        }

        if ($args['date']) {
            $where[] = "a.preferred_date = %s";
            $params[] = $args['date'];
        }

        $where_clause = implode(' AND ', $where);
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, sh.shift_name
            FROM {$wpdb->prefix}rdc_appointments a
            LEFT JOIN {$wpdb->prefix}rdc_shifts sh ON a.shift_id = sh.id
            WHERE $where_clause
            ORDER BY a.preferred_date DESC, a.created_at DESC
            LIMIT %d OFFSET %d",
            ...$params
        ));
    }

    /**
     * Update appointment status
     */
    public static function update_status($appointment_id, $status) {
        global $wpdb;

        $valid_statuses = array('pending', 'confirmed', 'cancelled', 'completed');

        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', __('Invalid status.', 'rotary-dialysis-core'));
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'rdc_appointments',
            array('status' => $status, 'updated_at' => current_time('mysql')),
            array('id' => $appointment_id),
            array('%s', '%s'),
            array('%d')
        );

        if ($updated === false) {
            return new WP_Error('db_error', __('Failed to update appointment.', 'rotary-dialysis-core'));
        }

        // Send notification
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_appointments WHERE id = %d",
            $appointment_id
        ));

        if ($appointment && $appointment->patient_email) {
            RDC_Email_Service::send_appointment_status_update($appointment, $status);
        }

        return true;
    }

    /**
     * Get shifts for a store
     */
    public static function get_shifts($store_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_shifts
            WHERE store_id = %d AND is_active = 1
            ORDER BY start_time ASC",
            $store_id
        ));
    }

    /**
     * Generate confirmation code
     */
    private static function generate_confirmation_code($date) {
        $date_part = date('Ymd', strtotime($date));
        $random_part = strtoupper(wp_generate_password(4, false));
        return "RDC-{$date_part}-{$random_part}";
    }
}
