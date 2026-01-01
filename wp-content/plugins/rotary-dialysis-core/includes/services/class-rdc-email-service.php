<?php
/**
 * Email Service
 *
 * Handles all email-related functionality.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Email_Service {

    /**
     * Send review verification email
     */
    public static function send_review_verification($review_id, $email, $name) {
        global $wpdb;

        // Generate token
        $token = wp_generate_password(32, false);
        $token_hash = hash('sha256', $token);
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Store token
        $wpdb->insert(
            $wpdb->prefix . 'rdc_email_tokens',
            array(
                'email' => $email,
                'token_hash' => $token_hash,
                'purpose' => 'review',
                'reference_id' => $review_id,
                'expires_at' => $expires_at,
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );

        // Build verification URL
        $verify_url = add_query_arg(array(
            'rdc_action' => 'verify_review',
            'token' => $token,
        ), home_url());

        // Email subject
        $subject = __('Verify your review - Rotary Dialysis Centers', 'rotary-dialysis-core');

        // Email body
        $message = sprintf(
            __("Hello %s,\n\nThank you for submitting a review for our dialysis center.\n\nPlease click the link below to verify your email and confirm your review:\n\n%s\n\nThis link will expire in 24 hours.\n\nIf you did not submit this review, please ignore this email.\n\nBest regards,\nRotary Dialysis Centers Team", 'rotary-dialysis-core'),
            $name,
            $verify_url
        );

        // Send email
        return wp_mail($email, $subject, $message);
    }

    /**
     * Send appointment confirmation email
     */
    public static function send_appointment_confirmation($appointment) {
        global $wpdb;

        $store = $wpdb->get_row($wpdb->prepare(
            "SELECT title, phone, email FROM {$wpdb->prefix}asl_stores WHERE id = %d",
            $appointment->store_id
        ));

        if (!$store) {
            return false;
        }

        // To patient
        $subject = sprintf(
            __('Appointment Confirmation - %s', 'rotary-dialysis-core'),
            $appointment->confirmation_code
        );

        $message = sprintf(
            __("Hello %s,\n\nYour appointment request has been received.\n\nConfirmation Code: %s\nCenter: %s\nPreferred Date: %s\n\nWe will contact you to confirm your appointment.\n\nCenter Contact: %s\n\nBest regards,\nRotary Dialysis Centers", 'rotary-dialysis-core'),
            $appointment->patient_name,
            $appointment->confirmation_code,
            $store->title,
            date_i18n(get_option('date_format'), strtotime($appointment->preferred_date)),
            $store->phone
        );

        wp_mail($appointment->patient_email, $subject, $message);

        // To center
        $admin_email = get_option('rdc_admin_email');
        $center_emails = array($admin_email);
        if ($store->email) {
            $center_emails[] = $store->email;
        }

        $admin_subject = sprintf(
            __('New Appointment Request - %s', 'rotary-dialysis-core'),
            $appointment->confirmation_code
        );

        $admin_message = sprintf(
            __("New appointment request received:\n\nConfirmation Code: %s\nPatient: %s\nPhone: %s\nEmail: %s\nPreferred Date: %s\n\nPlease log in to the admin panel to manage this appointment.", 'rotary-dialysis-core'),
            $appointment->confirmation_code,
            $appointment->patient_name,
            $appointment->patient_phone,
            $appointment->patient_email,
            date_i18n(get_option('date_format'), strtotime($appointment->preferred_date))
        );

        return wp_mail($center_emails, $admin_subject, $admin_message);
    }

    /**
     * Send appointment status update email
     */
    public static function send_appointment_status_update($appointment, $new_status) {
        global $wpdb;

        $store = $wpdb->get_row($wpdb->prepare(
            "SELECT title, phone FROM {$wpdb->prefix}asl_stores WHERE id = %d",
            $appointment->store_id
        ));

        $status_messages = array(
            'confirmed' => __('Your appointment has been confirmed!', 'rotary-dialysis-core'),
            'cancelled' => __('Your appointment has been cancelled.', 'rotary-dialysis-core'),
            'completed' => __('Your appointment has been marked as completed. Thank you for visiting!', 'rotary-dialysis-core'),
        );

        $subject = sprintf(
            __('Appointment Update - %s', 'rotary-dialysis-core'),
            $appointment->confirmation_code
        );

        $message = sprintf(
            __("Hello %s,\n\n%s\n\nConfirmation Code: %s\nCenter: %s\nDate: %s\n\nIf you have any questions, please contact the center at %s.\n\nBest regards,\nRotary Dialysis Centers", 'rotary-dialysis-core'),
            $appointment->patient_name,
            $status_messages[$new_status] ?? __('Your appointment status has been updated.', 'rotary-dialysis-core'),
            $appointment->confirmation_code,
            $store->title,
            date_i18n(get_option('date_format'), strtotime($appointment->preferred_date)),
            $store->phone
        );

        return wp_mail($appointment->patient_email, $subject, $message);
    }
}
