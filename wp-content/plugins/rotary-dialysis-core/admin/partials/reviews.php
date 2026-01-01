<?php
/**
 * Admin Reviews Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle actions
if (isset($_POST['rdc_review_action']) && isset($_POST['review_id'])) {
    check_admin_referer('rdc_review_action', 'rdc_nonce');

    $review_id = absint($_POST['review_id']);
    $action = sanitize_text_field($_POST['rdc_review_action']);

    if ($action === 'approve') {
        $wpdb->update(
            $wpdb->prefix . 'rdc_reviews',
            array('status' => 'approved'),
            array('id' => $review_id),
            array('%s'),
            array('%d')
        );
        RDC_Review_Service::update_stats($wpdb->get_var($wpdb->prepare(
            "SELECT store_id FROM {$wpdb->prefix}rdc_reviews WHERE id = %d",
            $review_id
        )));
    } elseif ($action === 'reject') {
        $wpdb->update(
            $wpdb->prefix . 'rdc_reviews',
            array('status' => 'rejected'),
            array('id' => $review_id),
            array('%s'),
            array('%d')
        );
    } elseif ($action === 'delete') {
        $wpdb->delete($wpdb->prefix . 'rdc_reviews', array('id' => $review_id), array('%d'));
    }

    echo '<div class="notice notice-success"><p>' . esc_html__('Review updated.', 'rotary-dialysis-core') . '</p></div>';
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$where = $status_filter !== 'all' ? $wpdb->prepare("WHERE r.status = %s", $status_filter) : '';

// Get reviews
$reviews = $wpdb->get_results(
    "SELECT r.*, s.title as store_name
    FROM {$wpdb->prefix}rdc_reviews r
    LEFT JOIN {$wpdb->prefix}asl_stores s ON r.store_id = s.id
    $where
    ORDER BY r.created_at DESC"
);

// Count by status
$counts = $wpdb->get_results(
    "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}rdc_reviews GROUP BY status",
    OBJECT_K
);
?>

<div class="wrap">
    <h1><?php esc_html_e('Review Moderation', 'rotary-dialysis-core'); ?></h1>

    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'all')); ?>" <?php echo $status_filter === 'all' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('All', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo array_sum(array_column($counts, 'count')); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'pending')); ?>" <?php echo $status_filter === 'pending' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Pending', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['pending']) ? $counts['pending']->count : 0; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'approved')); ?>" <?php echo $status_filter === 'approved' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Approved', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['approved']) ? $counts['approved']->count : 0; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'rejected')); ?>" <?php echo $status_filter === 'rejected' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Rejected', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['rejected']) ? $counts['rejected']->count : 0; ?>)</span>
            </a>
        </li>
    </ul>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Rating', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Review', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Center', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Reviewer', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Status', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Date', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Actions', 'rotary-dialysis-core'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
            <tr>
                <td colspan="7"><?php esc_html_e('No reviews found.', 'rotary-dialysis-core'); ?></td>
            </tr>
            <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <tr>
                <td>
                    <span class="rdc-stars">
                        <?php echo str_repeat('★', $review->rating) . str_repeat('☆', 5 - $review->rating); ?>
                    </span>
                </td>
                <td><?php echo esc_html(wp_trim_words($review->review_text, 20)); ?></td>
                <td><?php echo esc_html($review->store_name); ?></td>
                <td>
                    <?php echo esc_html($review->reviewer_name); ?>
                    <br><small><?php echo esc_html($review->reviewer_email); ?></small>
                    <?php if ($review->is_verified): ?>
                        <span class="rdc-verified-badge" title="<?php esc_attr_e('Email Verified', 'rotary-dialysis-core'); ?>">✓</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="rdc-status rdc-status--<?php echo esc_attr($review->status); ?>">
                        <?php echo esc_html(ucfirst($review->status)); ?>
                    </span>
                </td>
                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review->created_at))); ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('rdc_review_action', 'rdc_nonce'); ?>
                        <input type="hidden" name="review_id" value="<?php echo esc_attr($review->id); ?>">
                        <?php if ($review->status !== 'approved'): ?>
                            <button type="submit" name="rdc_review_action" value="approve" class="button button-small button-primary">
                                <?php esc_html_e('Approve', 'rotary-dialysis-core'); ?>
                            </button>
                        <?php endif; ?>
                        <?php if ($review->status !== 'rejected'): ?>
                            <button type="submit" name="rdc_review_action" value="reject" class="button button-small">
                                <?php esc_html_e('Reject', 'rotary-dialysis-core'); ?>
                            </button>
                        <?php endif; ?>
                        <button type="submit" name="rdc_review_action" value="delete" class="button button-small"
                                onclick="return confirm('<?php esc_attr_e('Delete this review?', 'rotary-dialysis-core'); ?>');">
                            <?php esc_html_e('Delete', 'rotary-dialysis-core'); ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
