<?php
/**
 * Template: Review Form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rdc-review-form-wrapper" data-store-id="<?php echo esc_attr($store_id); ?>">
    <h3><?php esc_html_e('Write a Review', 'rotary-dialysis-core'); ?></h3>

    <form class="rdc-review-form" method="post">
        <div class="rdc-form-row">
            <label><?php esc_html_e('Your Rating', 'rotary-dialysis-core'); ?> <span class="required">*</span></label>
            <div class="rdc-star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="rating-<?php echo $store_id; ?>-<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                <label for="rating-<?php echo $store_id; ?>-<?php echo $i; ?>" title="<?php echo esc_attr($i); ?> stars">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="rdc-form-row">
            <label for="rdc-reviewer-name-<?php echo $store_id; ?>">
                <?php esc_html_e('Your Name', 'rotary-dialysis-core'); ?> <span class="required">*</span>
            </label>
            <input type="text" id="rdc-reviewer-name-<?php echo $store_id; ?>" name="reviewer_name" required>
        </div>

        <div class="rdc-form-row">
            <label for="rdc-reviewer-email-<?php echo $store_id; ?>">
                <?php esc_html_e('Your Email', 'rotary-dialysis-core'); ?> <span class="required">*</span>
            </label>
            <input type="email" id="rdc-reviewer-email-<?php echo $store_id; ?>" name="reviewer_email" required>
            <small><?php esc_html_e('We will send a verification link to this email.', 'rotary-dialysis-core'); ?></small>
        </div>

        <div class="rdc-form-row">
            <label for="rdc-review-text-<?php echo $store_id; ?>">
                <?php esc_html_e('Your Review', 'rotary-dialysis-core'); ?>
            </label>
            <textarea id="rdc-review-text-<?php echo $store_id; ?>" name="review_text" rows="4" placeholder="<?php esc_attr_e('Share your experience...', 'rotary-dialysis-core'); ?>"></textarea>
        </div>

        <div class="rdc-form-row">
            <button type="submit" class="rdc-button rdc-button--primary">
                <?php esc_html_e('Submit Review', 'rotary-dialysis-core'); ?>
            </button>
        </div>

        <div class="rdc-form-message" style="display: none;"></div>
    </form>
</div>
