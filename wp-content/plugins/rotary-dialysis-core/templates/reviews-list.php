<?php
/**
 * Template: Reviews List
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rdc-reviews-wrapper">
    <?php if ($stats && $stats->total_reviews > 0): ?>
    <div class="rdc-reviews-summary">
        <div class="rdc-rating-large">
            <span class="rdc-rating-value"><?php echo esc_html(number_format($stats->average_rating, 1)); ?></span>
            <span class="rdc-rating-stars">
                <?php
                $full_stars = floor($stats->average_rating);
                $half_star = ($stats->average_rating - $full_stars) >= 0.5;
                echo str_repeat('★', $full_stars);
                if ($half_star) echo '★';
                echo str_repeat('☆', 5 - $full_stars - ($half_star ? 1 : 0));
                ?>
            </span>
        </div>
        <p class="rdc-reviews-count">
            <?php printf(
                esc_html(_n('%d review', '%d reviews', $stats->total_reviews, 'rotary-dialysis-core')),
                $stats->total_reviews
            ); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($reviews)): ?>
    <div class="rdc-reviews-list">
        <?php foreach ($reviews as $review): ?>
        <div class="rdc-review-card">
            <div class="rdc-review-header">
                <span class="rdc-review-rating">
                    <?php echo str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating); ?>
                </span>
                <span class="rdc-review-date">
                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review->created_at))); ?>
                </span>
            </div>
            <?php if ($review->review_text): ?>
            <p class="rdc-review-text"><?php echo esc_html($review->review_text); ?></p>
            <?php endif; ?>
            <div class="rdc-review-footer">
                <span class="rdc-review-author">
                    <?php echo esc_html($review->reviewer_name); ?>
                    <?php if ($review->is_verified): ?>
                    <span class="rdc-verified-badge" title="<?php esc_attr_e('Verified', 'rotary-dialysis-core'); ?>">✓</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="rdc-no-reviews"><?php esc_html_e('No reviews yet. Be the first to review!', 'rotary-dialysis-core'); ?></p>
    <?php endif; ?>
</div>
