<?php
/**
 * Admin Dashboard Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get stats
$total_reviews = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rdc_reviews");
$pending_reviews = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rdc_reviews WHERE status = 'pending'");
$total_appointments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rdc_appointments");
$pending_appointments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rdc_appointments WHERE status = 'pending'");

// Get ASL stores count
$total_centers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}asl_stores WHERE is_disabled = 0");
?>

<div class="wrap rdc-dashboard">
    <h1><?php esc_html_e('Rotary Dialysis Dashboard', 'rotary-dialysis-core'); ?></h1>

    <div class="rdc-stats-grid">
        <div class="rdc-stat-card">
            <div class="rdc-stat-icon dashicons dashicons-location"></div>
            <div class="rdc-stat-content">
                <span class="rdc-stat-value"><?php echo esc_html($total_centers ?: 0); ?></span>
                <span class="rdc-stat-label"><?php esc_html_e('Dialysis Centers', 'rotary-dialysis-core'); ?></span>
            </div>
        </div>

        <div class="rdc-stat-card">
            <div class="rdc-stat-icon dashicons dashicons-star-filled"></div>
            <div class="rdc-stat-content">
                <span class="rdc-stat-value"><?php echo esc_html($total_reviews ?: 0); ?></span>
                <span class="rdc-stat-label"><?php esc_html_e('Total Reviews', 'rotary-dialysis-core'); ?></span>
                <?php if ($pending_reviews > 0): ?>
                    <span class="rdc-stat-badge"><?php echo esc_html($pending_reviews); ?> <?php esc_html_e('pending', 'rotary-dialysis-core'); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="rdc-stat-card">
            <div class="rdc-stat-icon dashicons dashicons-calendar-alt"></div>
            <div class="rdc-stat-content">
                <span class="rdc-stat-value"><?php echo esc_html($total_appointments ?: 0); ?></span>
                <span class="rdc-stat-label"><?php esc_html_e('Appointments', 'rotary-dialysis-core'); ?></span>
                <?php if ($pending_appointments > 0): ?>
                    <span class="rdc-stat-badge"><?php echo esc_html($pending_appointments); ?> <?php esc_html_e('pending', 'rotary-dialysis-core'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rdc-dashboard-row">
        <div class="rdc-dashboard-col">
            <div class="rdc-panel">
                <h2><?php esc_html_e('Quick Actions', 'rotary-dialysis-core'); ?></h2>
                <ul class="rdc-quick-actions">
                    <li>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rdc-reviews')); ?>" class="button">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('Moderate Reviews', 'rotary-dialysis-core'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rdc-availability')); ?>" class="button">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php esc_html_e('Update Bed Availability', 'rotary-dialysis-core'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rdc-appointments')); ?>" class="button">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php esc_html_e('View Appointments', 'rotary-dialysis-core'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rdc-settings')); ?>" class="button">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php esc_html_e('Plugin Settings', 'rotary-dialysis-core'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="rdc-dashboard-col">
            <div class="rdc-panel">
                <h2><?php esc_html_e('Recent Activity', 'rotary-dialysis-core'); ?></h2>
                <?php
                $recent_reviews = $wpdb->get_results(
                    "SELECT r.*, s.title as store_name
                    FROM {$wpdb->prefix}rdc_reviews r
                    LEFT JOIN {$wpdb->prefix}asl_stores s ON r.store_id = s.id
                    ORDER BY r.created_at DESC LIMIT 5"
                );

                if ($recent_reviews):
                ?>
                <ul class="rdc-activity-list">
                    <?php foreach ($recent_reviews as $review): ?>
                    <li>
                        <span class="rdc-activity-rating">
                            <?php echo str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating); ?>
                        </span>
                        <span class="rdc-activity-text">
                            <?php echo esc_html($review->reviewer_name); ?>
                            <?php esc_html_e('reviewed', 'rotary-dialysis-core'); ?>
                            <strong><?php echo esc_html($review->store_name); ?></strong>
                        </span>
                        <span class="rdc-activity-time">
                            <?php echo esc_html(human_time_diff(strtotime($review->created_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'rotary-dialysis-core'); ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="rdc-no-activity"><?php esc_html_e('No reviews yet.', 'rotary-dialysis-core'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
