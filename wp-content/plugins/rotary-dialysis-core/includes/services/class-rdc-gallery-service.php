<?php
/**
 * Gallery Service
 *
 * Handles gallery-related business logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Gallery_Service {

    /**
     * Get gallery images for a store
     */
    public static function get_images($store_id, $limit = 20) {
        global $wpdb;

        $images = $wpdb->get_results($wpdb->prepare(
            "SELECT g.*, p.guid as image_url
            FROM {$wpdb->prefix}rdc_gallery_images g
            LEFT JOIN {$wpdb->posts} p ON g.attachment_id = p.ID
            WHERE g.store_id = %d
            ORDER BY g.is_featured DESC, g.sort_order ASC
            LIMIT %d",
            $store_id,
            $limit
        ));

        foreach ($images as &$image) {
            $image->thumbnail_url = wp_get_attachment_image_url($image->attachment_id, 'medium');
            $image->full_url = wp_get_attachment_image_url($image->attachment_id, 'full');
        }

        return $images;
    }

    /**
     * Add image to gallery
     */
    public static function add_image($store_id, $attachment_id, $data = array()) {
        global $wpdb;

        // Verify attachment exists
        if (!wp_attachment_is_image($attachment_id)) {
            return new WP_Error('invalid_attachment', __('Invalid image attachment.', 'rotary-dialysis-core'));
        }

        // Verify store exists
        $store = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}asl_stores WHERE id = %d",
            $store_id
        ));

        if (!$store) {
            return new WP_Error('invalid_store', __('Invalid dialysis center.', 'rotary-dialysis-core'));
        }

        // Get max sort order
        $max_order = $wpdb->get_var($wpdb->prepare(
            "SELECT MAX(sort_order) FROM {$wpdb->prefix}rdc_gallery_images WHERE store_id = %d",
            $store_id
        ));

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'rdc_gallery_images',
            array(
                'store_id' => $store_id,
                'attachment_id' => $attachment_id,
                'title' => isset($data['title']) ? sanitize_text_field($data['title']) : '',
                'caption' => isset($data['caption']) ? sanitize_textarea_field($data['caption']) : '',
                'sort_order' => ($max_order ?? 0) + 1,
                'is_featured' => isset($data['is_featured']) ? 1 : 0,
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d')
        );

        if (!$inserted) {
            return new WP_Error('db_error', __('Failed to add image.', 'rotary-dialysis-core'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update gallery image
     */
    public static function update_image($image_id, $data) {
        global $wpdb;

        $update_data = array();
        $format = array();

        if (isset($data['title'])) {
            $update_data['title'] = sanitize_text_field($data['title']);
            $format[] = '%s';
        }

        if (isset($data['caption'])) {
            $update_data['caption'] = sanitize_textarea_field($data['caption']);
            $format[] = '%s';
        }

        if (isset($data['sort_order'])) {
            $update_data['sort_order'] = absint($data['sort_order']);
            $format[] = '%d';
        }

        if (isset($data['is_featured'])) {
            $update_data['is_featured'] = $data['is_featured'] ? 1 : 0;
            $format[] = '%d';

            // If setting as featured, unset other featured images for this store
            if ($data['is_featured']) {
                $store_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT store_id FROM {$wpdb->prefix}rdc_gallery_images WHERE id = %d",
                    $image_id
                ));

                if ($store_id) {
                    $wpdb->update(
                        $wpdb->prefix . 'rdc_gallery_images',
                        array('is_featured' => 0),
                        array('store_id' => $store_id),
                        array('%d'),
                        array('%d')
                    );
                }
            }
        }

        if (empty($update_data)) {
            return new WP_Error('no_data', __('No data to update.', 'rotary-dialysis-core'));
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'rdc_gallery_images',
            $update_data,
            array('id' => $image_id),
            $format,
            array('%d')
        );

        return $updated !== false;
    }

    /**
     * Delete gallery image
     */
    public static function delete_image($image_id) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'rdc_gallery_images',
            array('id' => $image_id),
            array('%d')
        );
    }

    /**
     * Get featured image for a store
     */
    public static function get_featured_image($store_id) {
        global $wpdb;

        $image = $wpdb->get_row($wpdb->prepare(
            "SELECT g.*, p.guid as image_url
            FROM {$wpdb->prefix}rdc_gallery_images g
            LEFT JOIN {$wpdb->posts} p ON g.attachment_id = p.ID
            WHERE g.store_id = %d AND g.is_featured = 1
            LIMIT 1",
            $store_id
        ));

        if ($image) {
            $image->thumbnail_url = wp_get_attachment_image_url($image->attachment_id, 'medium');
            $image->full_url = wp_get_attachment_image_url($image->attachment_id, 'full');
        }

        return $image;
    }
}
