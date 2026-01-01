<?php
/**
 * Template: Center Info Card
 */

if (!defined('ABSPATH')) {
    exit;
}

$show_rating = $atts['show_rating'] === 'yes';
$show_availability = $atts['show_availability'] === 'yes';
$show_contact = $atts['show_contact'] === 'yes';
?>

<div class="rdc-center-card">
    <?php if ($store->featured_image): ?>
    <div class="rdc-center-image">
        <img src="<?php echo esc_url($store->featured_image->full_url); ?>" alt="<?php echo esc_attr($store->title); ?>">
    </div>
    <?php endif; ?>

    <div class="rdc-center-content">
        <h3 class="rdc-center-title"><?php echo esc_html($store->title); ?></h3>

        <?php if ($show_rating && $store->rating_stats && $store->rating_stats->total_reviews > 0): ?>
        <div class="rdc-center-rating">
            <span class="rdc-rating-stars">
                <?php
                $rating = $store->rating_stats->average_rating;
                echo str_repeat('★', round($rating)) . str_repeat('☆', 5 - round($rating));
                ?>
            </span>
            <span class="rdc-rating-text">
                <?php echo esc_html(number_format($rating, 1)); ?>
                (<?php echo esc_html($store->rating_stats->total_reviews); ?>)
            </span>
        </div>
        <?php endif; ?>

        <?php if ($show_availability && $store->availability): ?>
        <div class="rdc-center-availability">
            <span class="rdc-bed-badge rdc-bed-badge--<?php echo esc_attr($store->availability['status']); ?>">
                <?php
                $status_labels = array(
                    'available' => __('Available', 'rotary-dialysis-core'),
                    'limited' => __('Limited', 'rotary-dialysis-core'),
                    'full' => __('Full', 'rotary-dialysis-core'),
                );
                echo esc_html($status_labels[$store->availability['status']] ?? __('Unknown', 'rotary-dialysis-core'));
                ?>
            </span>
            <?php if ($store->availability['total_beds'] > 0): ?>
            <span class="rdc-bed-count">
                <?php echo esc_html($store->availability['available_beds']); ?>/<?php echo esc_html($store->availability['total_beds']); ?>
                <?php esc_html_e('beds', 'rotary-dialysis-core'); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="rdc-center-address">
            <span class="dashicons dashicons-location"></span>
            <?php echo esc_html($store->street); ?>
            <?php if ($store->city): ?>, <?php echo esc_html($store->city); ?><?php endif; ?>
        </p>

        <?php if ($show_contact && $store->phone): ?>
        <p class="rdc-center-phone">
            <span class="dashicons dashicons-phone"></span>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $store->phone)); ?>">
                <?php echo esc_html($store->phone); ?>
            </a>
        </p>
        <?php endif; ?>

        <div class="rdc-center-actions">
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($store->lat); ?>,<?php echo esc_attr($store->lng); ?>" target="_blank" class="rdc-button rdc-button--secondary">
                <?php esc_html_e('Get Directions', 'rotary-dialysis-core'); ?>
            </a>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $store->phone)); ?>" class="rdc-button rdc-button--primary">
                <?php esc_html_e('Call Now', 'rotary-dialysis-core'); ?>
            </a>
        </div>
    </div>
</div>
