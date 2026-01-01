<?php
/**
 * Admin Documents Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle add/edit document
if (isset($_POST['rdc_save_document']) && wp_verify_nonce($_POST['rdc_nonce'], 'rdc_save_document')) {
    $document_id = isset($_POST['document_id']) ? absint($_POST['document_id']) : 0;
    $data = array(
        'store_id' => !empty($_POST['store_id']) ? absint($_POST['store_id']) : null,
        'document_name' => sanitize_text_field($_POST['document_name']),
        'description' => sanitize_textarea_field($_POST['description']),
        'is_mandatory' => isset($_POST['is_mandatory']) ? 1 : 0,
        'template_attachment_id' => !empty($_POST['template_attachment_id']) ? absint($_POST['template_attachment_id']) : null,
        'sort_order' => absint($_POST['sort_order']),
    );

    if ($document_id) {
        $wpdb->update($wpdb->prefix . 'rdc_documents', $data, array('id' => $document_id));
        echo '<div class="notice notice-success"><p>' . esc_html__('Document updated.', 'rotary-dialysis-core') . '</p></div>';
    } else {
        $wpdb->insert($wpdb->prefix . 'rdc_documents', $data);
        echo '<div class="notice notice-success"><p>' . esc_html__('Document added.', 'rotary-dialysis-core') . '</p></div>';
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'rdc_delete_document')) {
    $delete_id = absint($_GET['delete']);
    $wpdb->delete($wpdb->prefix . 'rdc_documents', array('id' => $delete_id), array('%d'));
    echo '<div class="notice notice-success"><p>' . esc_html__('Document deleted.', 'rotary-dialysis-core') . '</p></div>';
}

// Get documents
$documents = $wpdb->get_results(
    "SELECT d.*, s.title as store_name
    FROM {$wpdb->prefix}rdc_documents d
    LEFT JOIN {$wpdb->prefix}asl_stores s ON d.store_id = s.id
    ORDER BY d.sort_order ASC, d.document_name ASC"
);

// Get centers for dropdown
$centers = $wpdb->get_results(
    "SELECT id, title FROM {$wpdb->prefix}asl_stores WHERE is_disabled = 0 ORDER BY title"
);
?>

<div class="wrap">
    <h1>
        <?php esc_html_e('Required Documents', 'rotary-dialysis-core'); ?>
        <button type="button" class="page-title-action" id="rdc-add-document">
            <?php esc_html_e('Add New', 'rotary-dialysis-core'); ?>
        </button>
    </h1>

    <p class="description">
        <?php esc_html_e('Manage the list of documents required for dialysis appointments. Documents with no specific center apply to all centers.', 'rotary-dialysis-core'); ?>
    </p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:5%"><?php esc_html_e('Order', 'rotary-dialysis-core'); ?></th>
                <th style="width:25%"><?php esc_html_e('Document Name', 'rotary-dialysis-core'); ?></th>
                <th style="width:30%"><?php esc_html_e('Description', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Center', 'rotary-dialysis-core'); ?></th>
                <th style="width:10%"><?php esc_html_e('Mandatory', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Actions', 'rotary-dialysis-core'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($documents)): ?>
            <tr>
                <td colspan="6"><?php esc_html_e('No documents found. Add your first document.', 'rotary-dialysis-core'); ?></td>
            </tr>
            <?php else: ?>
            <?php foreach ($documents as $doc): ?>
            <tr>
                <td><?php echo esc_html($doc->sort_order); ?></td>
                <td><strong><?php echo esc_html($doc->document_name); ?></strong></td>
                <td><?php echo esc_html($doc->description); ?></td>
                <td>
                    <?php
                    if ($doc->store_id) {
                        echo esc_html($doc->store_name);
                    } else {
                        echo '<em>' . esc_html__('All Centers', 'rotary-dialysis-core') . '</em>';
                    }
                    ?>
                </td>
                <td>
                    <?php if ($doc->is_mandatory): ?>
                        <span class="dashicons dashicons-yes" style="color:green;"></span>
                    <?php else: ?>
                        <span class="dashicons dashicons-minus" style="color:#999;"></span>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="button button-small rdc-edit-document"
                            data-id="<?php echo esc_attr($doc->id); ?>"
                            data-name="<?php echo esc_attr($doc->document_name); ?>"
                            data-description="<?php echo esc_attr($doc->description); ?>"
                            data-store-id="<?php echo esc_attr($doc->store_id); ?>"
                            data-mandatory="<?php echo esc_attr($doc->is_mandatory); ?>"
                            data-sort-order="<?php echo esc_attr($doc->sort_order); ?>"
                            data-template-id="<?php echo esc_attr($doc->template_attachment_id); ?>">
                        <?php esc_html_e('Edit', 'rotary-dialysis-core'); ?>
                    </button>
                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('delete', $doc->id), 'rdc_delete_document')); ?>"
                       class="button button-small"
                       onclick="return confirm('<?php esc_attr_e('Delete this document?', 'rotary-dialysis-core'); ?>');">
                        <?php esc_html_e('Delete', 'rotary-dialysis-core'); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Document Modal -->
<div id="rdc-document-modal" class="rdc-modal" style="display:none;">
    <div class="rdc-modal-content">
        <h2 id="rdc-document-modal-title"><?php esc_html_e('Add Document', 'rotary-dialysis-core'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('rdc_save_document', 'rdc_nonce'); ?>
            <input type="hidden" name="document_id" id="rdc-doc-id" value="">

            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Document Name', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <input type="text" name="document_name" id="rdc-doc-name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Description', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <textarea name="description" id="rdc-doc-description" rows="3" class="large-text"></textarea>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Specific Center', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <select name="store_id" id="rdc-doc-store">
                            <option value=""><?php esc_html_e('All Centers', 'rotary-dialysis-core'); ?></option>
                            <?php foreach ($centers as $center): ?>
                                <option value="<?php echo esc_attr($center->id); ?>">
                                    <?php echo esc_html($center->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Mandatory', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="is_mandatory" id="rdc-doc-mandatory" value="1">
                            <?php esc_html_e('This document is required', 'rotary-dialysis-core'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Sort Order', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <input type="number" name="sort_order" id="rdc-doc-sort" class="small-text" value="0" min="0">
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="rdc_save_document" class="button button-primary">
                    <?php esc_html_e('Save Document', 'rotary-dialysis-core'); ?>
                </button>
                <button type="button" class="button rdc-modal-close">
                    <?php esc_html_e('Cancel', 'rotary-dialysis-core'); ?>
                </button>
            </p>
        </form>
    </div>
</div>

<style>
.rdc-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}
.rdc-modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 4px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
    function resetModal() {
        $('#rdc-document-modal-title').text('<?php echo esc_js(__('Add Document', 'rotary-dialysis-core')); ?>');
        $('#rdc-doc-id').val('');
        $('#rdc-doc-name').val('');
        $('#rdc-doc-description').val('');
        $('#rdc-doc-store').val('');
        $('#rdc-doc-mandatory').prop('checked', false);
        $('#rdc-doc-sort').val('0');
    }

    $('#rdc-add-document').on('click', function() {
        resetModal();
        $('#rdc-document-modal').show();
    });

    $('.rdc-edit-document').on('click', function() {
        var $btn = $(this);
        $('#rdc-document-modal-title').text('<?php echo esc_js(__('Edit Document', 'rotary-dialysis-core')); ?>');
        $('#rdc-doc-id').val($btn.data('id'));
        $('#rdc-doc-name').val($btn.data('name'));
        $('#rdc-doc-description').val($btn.data('description'));
        $('#rdc-doc-store').val($btn.data('store-id') || '');
        $('#rdc-doc-mandatory').prop('checked', $btn.data('mandatory') == 1);
        $('#rdc-doc-sort').val($btn.data('sort-order'));
        $('#rdc-document-modal').show();
    });

    $('.rdc-modal-close').on('click', function() {
        $('#rdc-document-modal').hide();
    });

    $('#rdc-document-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>
