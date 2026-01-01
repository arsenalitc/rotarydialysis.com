<?php
/**
 * Admin Appointments Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle status update
if (isset($_POST['rdc_appointment_action']) && isset($_POST['appointment_id'])) {
    check_admin_referer('rdc_appointment_action', 'rdc_nonce');

    $appointment_id = absint($_POST['appointment_id']);
    $action = sanitize_text_field($_POST['rdc_appointment_action']);

    $valid_statuses = array('confirmed', 'cancelled', 'completed');
    if (in_array($action, $valid_statuses)) {
        $wpdb->update(
            $wpdb->prefix . 'rdc_appointments',
            array('status' => $action, 'updated_at' => current_time('mysql')),
            array('id' => $appointment_id),
            array('%s', '%s'),
            array('%d')
        );

        // Send email notification
        $appointment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}rdc_appointments WHERE id = %d",
            $appointment_id
        ));

        if ($appointment && $appointment->patient_email) {
            RDC_Email_Service::send_appointment_status_update($appointment, $action);
        }

        echo '<div class="notice notice-success"><p>' . esc_html__('Appointment updated.', 'rotary-dialysis-core') . '</p></div>';
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
$date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

$where = array("1=1");
if ($status_filter !== 'all') {
    $where[] = $wpdb->prepare("a.status = %s", $status_filter);
}
if ($date_filter) {
    $where[] = $wpdb->prepare("a.preferred_date = %s", $date_filter);
}
$where_clause = implode(' AND ', $where);

// Get appointments
$appointments = $wpdb->get_results(
    "SELECT a.*, s.title as store_name
    FROM {$wpdb->prefix}rdc_appointments a
    LEFT JOIN {$wpdb->prefix}asl_stores s ON a.store_id = s.id
    WHERE $where_clause
    ORDER BY a.preferred_date DESC, a.created_at DESC"
);

// Count by status
$counts = $wpdb->get_results(
    "SELECT status, COUNT(*) as count FROM {$wpdb->prefix}rdc_appointments GROUP BY status",
    OBJECT_K
);
?>

<div class="wrap">
    <h1><?php esc_html_e('Appointment Management', 'rotary-dialysis-core'); ?></h1>

    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url(remove_query_arg('status')); ?>" <?php echo $status_filter === 'all' ? 'class="current"' : ''; ?>>
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
            <a href="<?php echo esc_url(add_query_arg('status', 'confirmed')); ?>" <?php echo $status_filter === 'confirmed' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Confirmed', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['confirmed']) ? $counts['confirmed']->count : 0; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'completed')); ?>" <?php echo $status_filter === 'completed' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Completed', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['completed']) ? $counts['completed']->count : 0; ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg('status', 'cancelled')); ?>" <?php echo $status_filter === 'cancelled' ? 'class="current"' : ''; ?>>
                <?php esc_html_e('Cancelled', 'rotary-dialysis-core'); ?>
                <span class="count">(<?php echo isset($counts['cancelled']) ? $counts['cancelled']->count : 0; ?>)</span>
            </a>
        </li>
    </ul>

    <form method="get" class="rdc-date-filter">
        <input type="hidden" name="page" value="rdc-appointments">
        <label for="date-filter"><?php esc_html_e('Filter by date:', 'rotary-dialysis-core'); ?></label>
        <input type="date" id="date-filter" name="date" value="<?php echo esc_attr($date_filter); ?>">
        <button type="submit" class="button"><?php esc_html_e('Filter', 'rotary-dialysis-core'); ?></button>
        <?php if ($date_filter): ?>
            <a href="<?php echo esc_url(remove_query_arg('date')); ?>" class="button"><?php esc_html_e('Clear', 'rotary-dialysis-core'); ?></a>
        <?php endif; ?>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Code', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Patient', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Center', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Date', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Status', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Created', 'rotary-dialysis-core'); ?></th>
                <th><?php esc_html_e('Actions', 'rotary-dialysis-core'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
            <tr>
                <td colspan="7"><?php esc_html_e('No appointments found.', 'rotary-dialysis-core'); ?></td>
            </tr>
            <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><code><?php echo esc_html($appointment->confirmation_code); ?></code></td>
                <td>
                    <strong><?php echo esc_html($appointment->patient_name); ?></strong><br>
                    <small><?php echo esc_html($appointment->patient_phone); ?></small><br>
                    <small><?php echo esc_html($appointment->patient_email); ?></small>
                </td>
                <td><?php echo esc_html($appointment->store_name); ?></td>
                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appointment->preferred_date))); ?></td>
                <td>
                    <span class="rdc-status rdc-status--<?php echo esc_attr($appointment->status); ?>">
                        <?php echo esc_html(ucfirst($appointment->status)); ?>
                    </span>
                </td>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($appointment->created_at))); ?></td>
                <td>
                    <?php if ($appointment->status === 'pending'): ?>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('rdc_appointment_action', 'rdc_nonce'); ?>
                        <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment->id); ?>">
                        <button type="submit" name="rdc_appointment_action" value="confirmed" class="button button-small button-primary">
                            <?php esc_html_e('Confirm', 'rotary-dialysis-core'); ?>
                        </button>
                        <button type="submit" name="rdc_appointment_action" value="cancelled" class="button button-small">
                            <?php esc_html_e('Cancel', 'rotary-dialysis-core'); ?>
                        </button>
                    </form>
                    <?php elseif ($appointment->status === 'confirmed'): ?>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('rdc_appointment_action', 'rdc_nonce'); ?>
                        <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment->id); ?>">
                        <button type="submit" name="rdc_appointment_action" value="completed" class="button button-small button-primary">
                            <?php esc_html_e('Complete', 'rotary-dialysis-core'); ?>
                        </button>
                        <button type="submit" name="rdc_appointment_action" value="cancelled" class="button button-small">
                            <?php esc_html_e('Cancel', 'rotary-dialysis-core'); ?>
                        </button>
                    </form>
                    <?php else: ?>
                    <em><?php esc_html_e('No actions', 'rotary-dialysis-core'); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.rdc-date-filter {
    margin: 15px 0;
}
.rdc-date-filter input[type="date"] {
    margin: 0 5px;
}
</style>
