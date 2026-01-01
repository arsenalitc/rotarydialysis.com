<?php
/**
 * Document Service
 *
 * Handles document-related business logic.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Document_Service {

    /**
     * Get documents for a store
     */
    public static function get_documents($store_id = null) {
        global $wpdb;

        if ($store_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rdc_documents
                WHERE store_id IS NULL OR store_id = %d
                ORDER BY is_mandatory DESC, sort_order ASC, document_name ASC",
                $store_id
            ));
        }

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rdc_documents
            ORDER BY is_mandatory DESC, sort_order ASC, document_name ASC"
        );
    }

    /**
     * Get a single document
     */
    public static function get_document($document_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_documents WHERE id = %d",
            $document_id
        ));
    }

    /**
     * Add a document
     */
    public static function add_document($data) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'rdc_documents',
            array(
                'store_id' => isset($data['store_id']) ? absint($data['store_id']) : null,
                'document_name' => sanitize_text_field($data['document_name']),
                'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : '',
                'is_mandatory' => isset($data['is_mandatory']) ? 1 : 0,
                'template_attachment_id' => isset($data['template_attachment_id']) ? absint($data['template_attachment_id']) : null,
                'sort_order' => isset($data['sort_order']) ? absint($data['sort_order']) : 0,
            ),
            array('%d', '%s', '%s', '%d', '%d', '%d')
        );

        if (!$inserted) {
            return new WP_Error('db_error', __('Failed to add document.', 'rotary-dialysis-core'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a document
     */
    public static function update_document($document_id, $data) {
        global $wpdb;

        $update_data = array();
        $format = array();

        if (isset($data['document_name'])) {
            $update_data['document_name'] = sanitize_text_field($data['document_name']);
            $format[] = '%s';
        }

        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $format[] = '%s';
        }

        if (isset($data['is_mandatory'])) {
            $update_data['is_mandatory'] = $data['is_mandatory'] ? 1 : 0;
            $format[] = '%d';
        }

        if (isset($data['template_attachment_id'])) {
            $update_data['template_attachment_id'] = absint($data['template_attachment_id']);
            $format[] = '%d';
        }

        if (isset($data['sort_order'])) {
            $update_data['sort_order'] = absint($data['sort_order']);
            $format[] = '%d';
        }

        if (empty($update_data)) {
            return new WP_Error('no_data', __('No data to update.', 'rotary-dialysis-core'));
        }

        $updated = $wpdb->update(
            $wpdb->prefix . 'rdc_documents',
            $update_data,
            array('id' => $document_id),
            $format,
            array('%d')
        );

        return $updated !== false;
    }

    /**
     * Delete a document
     */
    public static function delete_document($document_id) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'rdc_documents',
            array('id' => $document_id),
            array('%d')
        );
    }

    /**
     * Get mandatory documents
     */
    public static function get_mandatory_documents($store_id = null) {
        global $wpdb;

        if ($store_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}rdc_documents
                WHERE (store_id IS NULL OR store_id = %d) AND is_mandatory = 1
                ORDER BY sort_order ASC, document_name ASC",
                $store_id
            ));
        }

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rdc_documents
            WHERE is_mandatory = 1
            ORDER BY sort_order ASC, document_name ASC"
        );
    }
}
