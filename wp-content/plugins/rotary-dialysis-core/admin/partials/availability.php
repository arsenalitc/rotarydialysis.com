<?php
/**
 * Admin Bed Availability Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle form submission
if (isset($_POST['rdc_update_availability']) && wp_verify_nonce($_POST['rdc_nonce'], 'rdc_update_availability')) {
    $store_id = absint($_POST['store_id']);
    $total_beds = absint($_POST['total_beds']);
    $available_beds = absint($_POST['available_beds']);
    $shift = sanitize_text_field($_POST['shift']);

    // Get current availability for logging
    $current = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rdc_bed_availability WHERE store_id = %d AND shift = %s",
        $store_id,
        $shift
    ));

    // Update or insert
    $data = array(
        'store_id' => $store_id,
        'shift' => $shift,
        'total_beds' => $total_beds,
        'available_beds' => $available_beds,
        'updated_by_user_id' => get_current_user_id(),
        'updated_at' => current_time('mysql'),
    );

    if ($current) {
        // Log the change
        $wpdb->insert(
            $wpdb->prefix . 'rdc_availability_log',
            array(
                'store_id' => $store_id,
                'shift' => $shift,
                'previous_available' => $current->available_beds,
                'new_available' => $available_beds,
                'updated_by_user_id' => get_current_user_id(),
            ),
            array('%d', '%s', '%d', '%d', '%d')
        );

        $wpdb->update(
            $wpdb->prefix . 'rdc_bed_availability',
            $data,
            array('store_id' => $store_id, 'shift' => $shift),
            array('%d', '%s', '%d', '%d', '%d', '%s'),
            array('%d', '%s')
        );
    } else {
        $wpdb->insert($wpdb->prefix . 'rdc_bed_availability', $data, array('%d', '%s', '%d', '%d', '%d', '%s'));
    }

    echo '<div class="notice notice-success"><p>' . esc_html__('Availability updated successfully.', 'rotary-dialysis-core') . '</p></div>';
}

// Get all centers
$centers = $wpdb->get_results(
    "SELECT s.id, s.title,
            COALESCE(a.total_beds, 0) as total_beds,
            COALESCE(a.available_beds, 0) as available_beds,
            COALESCE(a.shift, 'all') as shift,
            a.updated_at
    FROM {$wpdb->prefix}asl_stores s
    LEFT JOIN {$wpdb->prefix}rdc_bed_availability a ON s.id = a.store_id AND a.shift = 'all'
    WHERE s.is_disabled = 0
    ORDER BY s.title ASC"
);

$threshold_warning = get_option('rdc_availability_threshold_warning', 30);
$threshold_critical = get_option('rdc_availability_threshold_critical', 10);
?>

<div class="wrap">
    <h1><?php esc_html_e('Bed Availability Management', 'rotary-dialysis-core'); ?></h1>

    <div class="rdc-availability-info">
        <p>
            <span class="rdc-bed-badge rdc-bed-badge--available"><?php esc_html_e('Available', 'rotary-dialysis-core'); ?></span>
            <?php printf(esc_html__('Above %d%% capacity', 'rotary-dialysis-core'), $threshold_warning); ?>
        </p>
        <p>
            <span class="rdc-bed-badge rdc-bed-badge--limited"><?php esc_html_e('Limited', 'rotary-dialysis-core'); ?></span>
            <?php printf(esc_html__('%d%% - %d%% capacity', 'rotary-dialysis-core'), $threshold_critical, $threshold_warning); ?>
        </p>
        <p>
            <span class="rdc-bed-badge rdc-bed-badge--full"><?php esc_html_e('Full', 'rotary-dialysis-core'); ?></span>
            <?php printf(esc_html__('Below %d%% capacity', 'rotary-dialysis-core'), $threshold_critical); ?>
        </p>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:30%"><?php esc_html_e('Center', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Status', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Total Beds', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Available', 'rotary-dialysis-core'); ?></th>
                <th style="width:15%"><?php esc_html_e('Last Updated', 'rotary-dialysis-core'); ?></th>
                <th style="width:10%"><?php esc_html_e('Actions', 'rotary-dialysis-core'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($centers as $center):
                $percentage = $center->total_beds > 0 ? ($center->available_beds / $center->total_beds) * 100 : 0;
                if ($percentage <= $threshold_critical) {
                    $status_class = 'full';
                    $status_label = __('Full', 'rotary-dialysis-core');
                } elseif ($percentage <= $threshold_warning) {
                    $status_class = 'limited';
                    $status_label = __('Limited', 'rotary-dialysis-core');
                } else {
                    $status_class = 'available';
                    $status_label = __('Available', 'rotary-dialysis-core');
                }
            ?>
            <tr>
                <td><strong><?php echo esc_html($center->title); ?></strong></td>
                <td>
                    <span class="rdc-bed-badge rdc-bed-badge--<?php echo esc_attr($status_class); ?>">
                        <?php echo esc_html($status_label); ?>
                    </span>
                </td>
                <td><?php echo esc_html($center->total_beds); ?></td>
                <td>
                    <strong><?php echo esc_html($center->available_beds); ?></strong>
                    <?php if ($center->total_beds > 0): ?>
                        <span class="description">(<?php echo round($percentage); ?>%)</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    if ($center->updated_at) {
                        echo esc_html(human_time_diff(strtotime($center->updated_at), current_time('timestamp'))) . ' ' . esc_html__('ago', 'rotary-dialysis-core');
                    } else {
                        echo '<em>' . esc_html__('Never', 'rotary-dialysis-core') . '</em>';
                    }
                    ?>
                </td>
                <td>
                    <button type="button" class="button button-small rdc-edit-availability"
                            data-store-id="<?php echo esc_attr($center->id); ?>"
                            data-store-name="<?php echo esc_attr($center->title); ?>"
                            data-total-beds="<?php echo esc_attr($center->total_beds); ?>"
                            data-available-beds="<?php echo esc_attr($center->available_beds); ?>">
                        <?php esc_html_e('Edit', 'rotary-dialysis-core'); ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div id="rdc-availability-modal" class="rdc-modal" style="display:none;">
    <div class="rdc-modal-content">
        <h2 id="rdc-modal-title"><?php esc_html_e('Update Bed Availability', 'rotary-dialysis-core'); ?></h2>
        <form method="post">
            <?php wp_nonce_field('rdc_update_availability', 'rdc_nonce'); ?>
            <input type="hidden" name="store_id" id="rdc-modal-store-id">
            <input type="hidden" name="shift" value="all">

            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Total Beds', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <input type="number" name="total_beds" id="rdc-modal-total-beds" min="0" class="small-text" required>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Available Beds', 'rotary-dialysis-core'); ?></th>
                    <td>
                        <input type="number" name="available_beds" id="rdc-modal-available-beds" min="0" class="small-text" required>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="rdc_update_availability" class="button button-primary">
                    <?php esc_html_e('Update', 'rotary-dialysis-core'); ?>
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
    max-width: 400px;
    width: 100%;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.rdc-edit-availability').on('click', function() {
        var $btn = $(this);
        $('#rdc-modal-title').text('Update: ' + $btn.data('store-name'));
        $('#rdc-modal-store-id').val($btn.data('store-id'));
        $('#rdc-modal-total-beds').val($btn.data('total-beds'));
        $('#rdc-modal-available-beds').val($btn.data('available-beds'));
        $('#rdc-availability-modal').show();
    });

    $('.rdc-modal-close').on('click', function() {
        $('#rdc-availability-modal').hide();
    });

    $('#rdc-availability-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});
</script>
