<?php
/**
 * Template: Documents List
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rdc-documents-wrapper">
    <h3><?php esc_html_e('Required Documents', 'rotary-dialysis-core'); ?></h3>

    <ul class="rdc-documents-list">
        <?php foreach ($documents as $doc): ?>
        <li class="rdc-document-item <?php echo $doc->is_mandatory ? 'rdc-document--mandatory' : ''; ?>">
            <div class="rdc-document-header">
                <span class="rdc-document-icon">📄</span>
                <span class="rdc-document-name"><?php echo esc_html($doc->document_name); ?></span>
                <?php if ($doc->is_mandatory): ?>
                <span class="rdc-document-badge rdc-document-badge--mandatory"><?php esc_html_e('Required', 'rotary-dialysis-core'); ?></span>
                <?php else: ?>
                <span class="rdc-document-badge rdc-document-badge--optional"><?php esc_html_e('Optional', 'rotary-dialysis-core'); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($doc->description): ?>
            <p class="rdc-document-description"><?php echo esc_html($doc->description); ?></p>
            <?php endif; ?>
            <?php if ($doc->template_attachment_id): ?>
            <a href="<?php echo esc_url(wp_get_attachment_url($doc->template_attachment_id)); ?>" class="rdc-document-download" download>
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e('Download Template', 'rotary-dialysis-core'); ?>
            </a>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
