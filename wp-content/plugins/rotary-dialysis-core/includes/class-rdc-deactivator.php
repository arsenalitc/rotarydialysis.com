<?php
/**
 * Plugin Deactivator
 *
 * Handles cleanup on plugin deactivation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled events if any
        wp_clear_scheduled_hook('rdc_daily_cleanup');
        wp_clear_scheduled_hook('rdc_send_appointment_reminders');

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
