<?php
/**
 * Availability Service
 *
 * Handles bed availability business logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Availability_Service {

    /**
     * Get availability for a store
     */
    public static function get_availability($store_id, $shift = 'all') {
        global $wpdb;

        $availability = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_bed_availability
            WHERE store_id = %d AND shift = %s",
            $store_id,
            $shift
        ));

        if (!$availability) {
            return array(
                'store_id' => $store_id,
                'shift' => $shift,
                'total_beds' => 0,
                'available_beds' => 0,
                'status' => 'unknown',
                'percentage' => 0,
                'updated_at' => null,
            );
        }

        $percentage = $availability->total_beds > 0
            ? ($availability->available_beds / $availability->total_beds) * 100
            : 0;

        $threshold_warning = get_option('rdc_availability_threshold_warning', 30);
        $threshold_critical = get_option('rdc_availability_threshold_critical', 10);

        if ($percentage <= $threshold_critical) {
            $status = 'full';
        } elseif ($percentage <= $threshold_warning) {
            $status = 'limited';
        } else {
            $status = 'available';
        }

        return array(
            'store_id' => $availability->store_id,
            'shift' => $availability->shift,
            'total_beds' => $availability->total_beds,
            'available_beds' => $availability->available_beds,
            'status' => $status,
            'percentage' => round($percentage),
            'updated_at' => $availability->updated_at,
        );
    }

    /**
     * Update availability
     */
    public static function update_availability($store_id, $available_beds, $total_beds = null, $shift = 'all', $user_id = null) {
        global $wpdb;

        // Get current availability
        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_bed_availability
            WHERE store_id = %d AND shift = %s",
            $store_id,
            $shift
        ));

        if ($total_beds === null && $current) {
            $total_beds = $current->total_beds;
        } elseif ($total_beds === null) {
            $total_beds = 0;
        }

        // Validate
        if ($available_beds < 0 || $available_beds > $total_beds) {
            return new WP_Error('invalid_value', __('Available beds must be between 0 and total beds.', 'rotary-dialysis-core'));
        }

        $data = array(
            'store_id' => $store_id,
            'shift' => $shift,
            'total_beds' => $total_beds,
            'available_beds' => $available_beds,
            'updated_by_user_id' => $user_id ?: get_current_user_id(),
            'updated_at' => current_time('mysql'),
        );

        // Log the change
        if ($current) {
            $wpdb->insert(
                $wpdb->prefix . 'rdc_availability_log',
                array(
                    'store_id' => $store_id,
                    'shift' => $shift,
                    'previous_available' => $current->available_beds,
                    'new_available' => $available_beds,
                    'updated_by_user_id' => $user_id ?: get_current_user_id(),
                ),
                array('%d', '%s', '%d', '%d', '%d')
            );

            // Update existing record
            $wpdb->update(
                $wpdb->prefix . 'rdc_bed_availability',
                $data,
                array('store_id' => $store_id, 'shift' => $shift),
                array('%d', '%s', '%d', '%d', '%d', '%s'),
                array('%d', '%s')
            );
        } else {
            // Insert new record
            $wpdb->insert(
                $wpdb->prefix . 'rdc_bed_availability',
                $data,
                array('%d', '%s', '%d', '%d', '%d', '%s')
            );
        }

        return self::get_availability($store_id, $shift);
    }

    /**
     * Get availability for all stores
     */
    public static function get_all_availability($shift = 'all') {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT s.id, s.title, s.lat, s.lng,
                    COALESCE(a.total_beds, 0) as total_beds,
                    COALESCE(a.available_beds, 0) as available_beds,
                    a.updated_at
            FROM {$wpdb->prefix}asl_stores s
            LEFT JOIN {$wpdb->prefix}rdc_bed_availability a ON s.id = a.store_id AND a.shift = %s
            WHERE s.is_disabled = 0
            ORDER BY s.title",
            $shift
        ));

        $threshold_warning = get_option('rdc_availability_threshold_warning', 30);
        $threshold_critical = get_option('rdc_availability_threshold_critical', 10);

        foreach ($results as &$store) {
            $percentage = $store->total_beds > 0
                ? ($store->available_beds / $store->total_beds) * 100
                : 0;

            if ($percentage <= $threshold_critical) {
                $store->status = 'full';
            } elseif ($percentage <= $threshold_warning) {
                $store->status = 'limited';
            } else {
                $store->status = 'available';
            }

            $store->percentage = round($percentage);
        }

        return $results;
    }

    /**
     * Get availability history
     */
    public static function get_history($store_id, $days = 7) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_availability_log
            WHERE store_id = %d AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            ORDER BY created_at DESC",
            $store_id,
            $days
        ));
    }
}
