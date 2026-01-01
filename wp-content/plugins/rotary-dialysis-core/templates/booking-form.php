<?php
/**
 * Template: Booking Form
 */

if (!defined('ABSPATH')) {
    exit;
}

$min_date = date('Y-m-d', strtotime('+1 day'));
$max_date = date('Y-m-d', strtotime('+' . $advance_days . ' days'));
?>

<div class="rdc-booking-form-wrapper" data-store-id="<?php echo esc_attr($store_id); ?>">
    <h3><?php esc_html_e('Book an Appointment', 'rotary-dialysis-core'); ?></h3>

    <?php if ($store): ?>
    <p class="rdc-booking-center">
        <strong><?php echo esc_html($store->title); ?></strong><br>
        <?php echo esc_html($store->street); ?>
    </p>
    <?php endif; ?>

    <form class="rdc-booking-form" method="post">
        <div class="rdc-form-row">
            <label for="rdc-patient-name-<?php echo $store_id; ?>">
                <?php esc_html_e('Patient Name', 'rotary-dialysis-core'); ?> <span class="required">*</span>
            </label>
            <input type="text" id="rdc-patient-name-<?php echo $store_id; ?>" name="patient_name" required>
        </div>

        <div class="rdc-form-row">
            <label for="rdc-patient-phone-<?php echo $store_id; ?>">
                <?php esc_html_e('Phone Number', 'rotary-dialysis-core'); ?> <span class="required">*</span>
            </label>
            <input type="tel" id="rdc-patient-phone-<?php echo $store_id; ?>" name="patient_phone" required placeholder="+91 XXXXX XXXXX">
        </div>

        <div class="rdc-form-row">
            <label for="rdc-patient-email-<?php echo $store_id; ?>">
                <?php esc_html_e('Email Address', 'rotary-dialysis-core'); ?>
            </label>
            <input type="email" id="rdc-patient-email-<?php echo $store_id; ?>" name="patient_email">
            <small><?php esc_html_e('Optional. We will send confirmation to this email.', 'rotary-dialysis-core'); ?></small>
        </div>

        <div class="rdc-form-row">
            <label for="rdc-preferred-date-<?php echo $store_id; ?>">
                <?php esc_html_e('Preferred Date', 'rotary-dialysis-core'); ?> <span class="required">*</span>
            </label>
            <input type="date" id="rdc-preferred-date-<?php echo $store_id; ?>" name="preferred_date"
                   min="<?php echo esc_attr($min_date); ?>"
                   max="<?php echo esc_attr($max_date); ?>" required>
        </div>

        <?php if (!empty($shifts)): ?>
        <div class="rdc-form-row">
            <label for="rdc-shift-<?php echo $store_id; ?>">
                <?php esc_html_e('Preferred Shift', 'rotary-dialysis-core'); ?>
            </label>
            <select id="rdc-shift-<?php echo $store_id; ?>" name="shift_id">
                <option value=""><?php esc_html_e('Any available', 'rotary-dialysis-core'); ?></option>
                <?php foreach ($shifts as $shift): ?>
                <option value="<?php echo esc_attr($shift->id); ?>">
                    <?php echo esc_html($shift->shift_name); ?>
                    (<?php echo esc_html(date('g:i A', strtotime($shift->start_time))); ?> -
                     <?php echo esc_html(date('g:i A', strtotime($shift->end_time))); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="rdc-form-row">
            <label for="rdc-message-<?php echo $store_id; ?>">
                <?php esc_html_e('Additional Message', 'rotary-dialysis-core'); ?>
            </label>
            <textarea id="rdc-message-<?php echo $store_id; ?>" name="message" rows="3" placeholder="<?php esc_attr_e('Any special requirements or notes...', 'rotary-dialysis-core'); ?>"></textarea>
        </div>

        <div class="rdc-form-row">
            <button type="submit" class="rdc-button rdc-button--primary">
                <?php esc_html_e('Request Appointment', 'rotary-dialysis-core'); ?>
            </button>
        </div>

        <div class="rdc-form-message" style="display: none;"></div>

        <p class="rdc-form-note">
            <small><?php esc_html_e('Your appointment request will be reviewed by the center. We will contact you to confirm.', 'rotary-dialysis-core'); ?></small>
        </p>
    </form>
</div>
