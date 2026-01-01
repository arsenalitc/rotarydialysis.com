<?php
/**
 * Template: Gallery
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rdc-gallery" data-columns="<?php echo esc_attr($columns); ?>">
    <?php foreach ($images as $image): ?>
    <div class="rdc-gallery-item">
        <a href="<?php echo esc_url($image->full_url); ?>" class="rdc-gallery-link" data-lightbox="rdc-gallery-<?php echo esc_attr($store_id); ?>">
            <img src="<?php echo esc_url($image->thumbnail_url); ?>" alt="<?php echo esc_attr($image->title ?: $image->caption); ?>" loading="lazy">
            <?php if ($image->is_featured): ?>
            <span class="rdc-gallery-featured"><?php esc_html_e('Featured', 'rotary-dialysis-core'); ?></span>
            <?php endif; ?>
        </a>
        <?php if ($image->caption): ?>
        <p class="rdc-gallery-caption"><?php echo esc_html($image->caption); ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
