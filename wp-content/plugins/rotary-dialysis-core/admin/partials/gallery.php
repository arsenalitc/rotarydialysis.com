<?php
/**
 * Admin Gallery Management Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get all stores for the dropdown
$stores = RDC_ASL_Integration::get_all_stores();

// Get selected store
$selected_store_id = isset($_GET['store_id']) ? absint($_GET['store_id']) : 0;

// Get images for selected store
$images = array();
if ($selected_store_id) {
    $images = RDC_Gallery_Service::get_images($selected_store_id, 100);
}
?>

<div class="wrap rdc-admin-page rdc-gallery-page">
    <h1><?php esc_html_e('Gallery Management', 'rotary-dialysis-core'); ?></h1>

    <div class="rdc-gallery-header">
        <form method="get" action="">
            <input type="hidden" name="page" value="rdc-gallery">
            <label for="store_id"><?php esc_html_e('Select Dialysis Center:', 'rotary-dialysis-core'); ?></label>
            <select name="store_id" id="store_id" class="rdc-store-select">
                <option value=""><?php esc_html_e('-- Choose a Center --', 'rotary-dialysis-core'); ?></option>
                <?php foreach ($stores as $store): ?>
                <option value="<?php echo esc_attr($store->id); ?>" <?php selected($selected_store_id, $store->id); ?>>
                    <?php echo esc_html($store->title); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button"><?php esc_html_e('View Gallery', 'rotary-dialysis-core'); ?></button>
        </form>
    </div>

    <?php if ($selected_store_id): ?>
    <div class="rdc-gallery-toolbar">
        <button type="button" class="button button-primary rdc-upload-images" data-store-id="<?php echo esc_attr($selected_store_id); ?>">
            <span class="dashicons dashicons-upload"></span>
            <?php esc_html_e('Add Images', 'rotary-dialysis-core'); ?>
        </button>
        <span class="rdc-gallery-count">
            <?php printf(
                esc_html(_n('%d image', '%d images', count($images), 'rotary-dialysis-core')),
                count($images)
            ); ?>
        </span>
    </div>

    <div class="rdc-gallery-grid" id="rdc-gallery-grid" data-store-id="<?php echo esc_attr($selected_store_id); ?>">
        <?php if (empty($images)): ?>
        <div class="rdc-gallery-empty">
            <span class="dashicons dashicons-format-gallery"></span>
            <p><?php esc_html_e('No images in this gallery yet.', 'rotary-dialysis-core'); ?></p>
            <p><?php esc_html_e('Click "Add Images" to upload photos of this center.', 'rotary-dialysis-core'); ?></p>
        </div>
        <?php else: ?>
            <?php foreach ($images as $image): ?>
            <div class="rdc-gallery-item <?php echo $image->is_featured ? 'rdc-gallery-item--featured' : ''; ?>"
                 data-id="<?php echo esc_attr($image->id); ?>"
                 data-attachment-id="<?php echo esc_attr($image->attachment_id); ?>">
                <div class="rdc-gallery-item-image">
                    <img src="<?php echo esc_url($image->thumbnail_url); ?>" alt="<?php echo esc_attr($image->title); ?>">
                    <?php if ($image->is_featured): ?>
                    <span class="rdc-gallery-featured-badge"><?php esc_html_e('Featured', 'rotary-dialysis-core'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="rdc-gallery-item-info">
                    <input type="text" class="rdc-gallery-title" value="<?php echo esc_attr($image->title); ?>"
                           placeholder="<?php esc_attr_e('Image title...', 'rotary-dialysis-core'); ?>">
                </div>
                <div class="rdc-gallery-item-actions">
                    <button type="button" class="button rdc-set-featured" title="<?php esc_attr_e('Set as featured', 'rotary-dialysis-core'); ?>">
                        <span class="dashicons dashicons-star-<?php echo $image->is_featured ? 'filled' : 'empty'; ?>"></span>
                    </button>
                    <button type="button" class="button rdc-move-up" title="<?php esc_attr_e('Move up', 'rotary-dialysis-core'); ?>">
                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                    </button>
                    <button type="button" class="button rdc-move-down" title="<?php esc_attr_e('Move down', 'rotary-dialysis-core'); ?>">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <button type="button" class="button rdc-delete-image" title="<?php esc_attr_e('Delete', 'rotary-dialysis-core'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="rdc-gallery-help">
        <h3><?php esc_html_e('Gallery Tips', 'rotary-dialysis-core'); ?></h3>
        <ul>
            <li><?php esc_html_e('Upload high-quality photos showing the dialysis center facilities.', 'rotary-dialysis-core'); ?></li>
            <li><?php esc_html_e('Set one image as "Featured" to use as the main image for this center.', 'rotary-dialysis-core'); ?></li>
            <li><?php esc_html_e('Drag and drop to reorder images, or use the arrow buttons.', 'rotary-dialysis-core'); ?></li>
            <li><?php esc_html_e('Recommended image size: 1200x800 pixels or larger.', 'rotary-dialysis-core'); ?></li>
        </ul>
    </div>

    <?php else: ?>
    <div class="rdc-gallery-notice">
        <span class="dashicons dashicons-info"></span>
        <p><?php esc_html_e('Please select a dialysis center to manage its gallery.', 'rotary-dialysis-core'); ?></p>
    </div>
    <?php endif; ?>
</div>
