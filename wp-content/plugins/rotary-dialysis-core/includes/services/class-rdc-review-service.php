<?php
/**
 * Review Service
 *
 * Handles review-related business logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Review_Service {

    /**
     * Submit a new review
     */
    public static function submit_review($data) {
        global $wpdb;

        $store_id = absint($data['store_id']);
        $rating = absint($data['rating']);
        $review_text = sanitize_textarea_field($data['review_text']);
        $reviewer_name = sanitize_text_field($data['reviewer_name']);
        $reviewer_email = sanitize_email($data['reviewer_email']);

        // Validation
        if (!$store_id || $rating < 1 || $rating > 5) {
            return new WP_Error('invalid_data', __('Invalid review data.', 'rotary-dialysis-core'));
        }

        if (!is_email($reviewer_email)) {
            return new WP_Error('invalid_email', __('Please provide a valid email address.', 'rotary-dialysis-core'));
        }

        // Check if store exists
        $store = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}asl_stores WHERE id = %d AND is_disabled = 0",
            $store_id
        ));

        if (!$store) {
            return new WP_Error('invalid_store', __('Invalid dialysis center.', 'rotary-dialysis-core'));
        }

        // Check for duplicate review
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}rdc_reviews WHERE store_id = %d AND reviewer_email = %s",
            $store_id,
            $reviewer_email
        ));

        if ($existing) {
            return new WP_Error('duplicate', __('You have already submitted a review for this center.', 'rotary-dialysis-core'));
        }

        // Determine initial status
        $moderation_enabled = get_option('rdc_review_moderation_enabled', true);
        $status = $moderation_enabled ? 'pending' : 'approved';

        // Insert review
        $inserted = $wpdb->insert(
            $wpdb->prefix . 'rdc_reviews',
            array(
                'store_id' => $store_id,
                'rating' => $rating,
                'review_text' => $review_text,
                'reviewer_name' => $reviewer_name,
                'reviewer_email' => $reviewer_email,
                'is_verified' => 0,
                'status' => $status,
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if (!$inserted) {
            return new WP_Error('db_error', __('Failed to save review.', 'rotary-dialysis-core'));
        }

        $review_id = $wpdb->insert_id;

        // Send verification email
        RDC_Email_Service::send_review_verification($review_id, $reviewer_email, $reviewer_name);

        // Update stats if auto-approved
        if ($status === 'approved') {
            self::update_stats($store_id);
        }

        return array(
            'success' => true,
            'review_id' => $review_id,
            'status' => $status,
            'message' => $moderation_enabled
                ? __('Thank you! Your review has been submitted and is pending approval. Please check your email to verify your review.', 'rotary-dialysis-core')
                : __('Thank you for your review!', 'rotary-dialysis-core'),
        );
    }

    /**
     * Verify a review via email token
     */
    public static function verify_review($token) {
        global $wpdb;

        $token_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_email_tokens
            WHERE token_hash = %s AND purpose = 'review' AND is_used = 0 AND expires_at > NOW()",
            hash('sha256', $token)
        ));

        if (!$token_record) {
            return new WP_Error('invalid_token', __('Invalid or expired verification link.', 'rotary-dialysis-core'));
        }

        // Mark token as used
        $wpdb->update(
            $wpdb->prefix . 'rdc_email_tokens',
            array('is_used' => 1),
            array('id' => $token_record->id),
            array('%d'),
            array('%d')
        );

        // Mark review as verified
        $wpdb->update(
            $wpdb->prefix . 'rdc_reviews',
            array('is_verified' => 1),
            array('id' => $token_record->reference_id),
            array('%d'),
            array('%d')
        );

        return array(
            'success' => true,
            'message' => __('Your email has been verified. Thank you!', 'rotary-dialysis-core'),
        );
    }

    /**
     * Get reviews for a store
     */
    public static function get_reviews($store_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'status' => 'approved',
            'limit' => 10,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT id, rating, review_text, reviewer_name, is_verified, created_at
            FROM {$wpdb->prefix}rdc_reviews
            WHERE store_id = %d AND status = %s
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d OFFSET %d",
            $store_id,
            $args['status'],
            $args['limit'],
            $args['offset']
        ));

        // Mask reviewer names
        foreach ($reviews as &$review) {
            $review->reviewer_name = self::mask_name($review->reviewer_name);
        }

        return $reviews;
    }

    /**
     * Update review stats for a store
     */
    public static function update_stats($store_id) {
        global $wpdb;

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                AVG(rating) as average_rating,
                COUNT(*) as total_reviews,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1_count,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2_count,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3_count,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4_count,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5_count
            FROM {$wpdb->prefix}rdc_reviews
            WHERE store_id = %d AND status = 'approved'",
            $store_id
        ));

        if (!$stats) {
            return;
        }

        $wpdb->replace(
            $wpdb->prefix . 'rdc_review_stats',
            array(
                'store_id' => $store_id,
                'average_rating' => round($stats->average_rating, 2),
                'total_reviews' => $stats->total_reviews,
                'rating_1_count' => $stats->rating_1_count,
                'rating_2_count' => $stats->rating_2_count,
                'rating_3_count' => $stats->rating_3_count,
                'rating_4_count' => $stats->rating_4_count,
                'rating_5_count' => $stats->rating_5_count,
            ),
            array('%d', '%s', '%d', '%d', '%d', '%d', '%d', '%d')
        );
    }

    /**
     * Get review stats for a store
     */
    public static function get_stats($store_id) {
        global $wpdb;

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_review_stats WHERE store_id = %d",
            $store_id
        ));

        if (!$stats) {
            return array(
                'average_rating' => 0,
                'total_reviews' => 0,
            );
        }

        return $stats;
    }

    /**
     * Mask a name for privacy
     */
    private static function mask_name($name) {
        $parts = explode(' ', $name);
        $masked = array();

        foreach ($parts as $part) {
            if (strlen($part) <= 2) {
                $masked[] = $part;
            } else {
                $masked[] = substr($part, 0, 1) . str_repeat('*', strlen($part) - 1);
            }
        }

        return implode(' ', $masked);
    }
}
