<?php
/**
 * Template: Doctors List
 */

if (!defined('ABSPATH')) {
    exit;
}

$day_labels = array(
    'mon' => __('Mon', 'rotary-dialysis-core'),
    'tue' => __('Tue', 'rotary-dialysis-core'),
    'wed' => __('Wed', 'rotary-dialysis-core'),
    'thu' => __('Thu', 'rotary-dialysis-core'),
    'fri' => __('Fri', 'rotary-dialysis-core'),
    'sat' => __('Sat', 'rotary-dialysis-core'),
    'sun' => __('Sun', 'rotary-dialysis-core'),
);
?>

<div class="rdc-doctors-wrapper">
    <div class="rdc-doctors-grid" data-columns="<?php echo esc_attr($columns); ?>">
        <?php foreach ($doctors as $doctor): ?>
        <div class="rdc-doctor-card <?php echo $doctor->is_primary ? 'rdc-doctor-card--primary' : ''; ?>">
            <?php if ($doctor->photo_url): ?>
            <div class="rdc-doctor-photo">
                <img src="<?php echo esc_url($doctor->photo_url); ?>" alt="<?php echo esc_attr($doctor->post_title); ?>">
                <?php if ($doctor->is_primary): ?>
                <span class="rdc-doctor-badge"><?php esc_html_e('Primary', 'rotary-dialysis-core'); ?></span>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="rdc-doctor-photo rdc-doctor-photo--placeholder">
                <span class="dashicons dashicons-businessman"></span>
                <?php if ($doctor->is_primary): ?>
                <span class="rdc-doctor-badge"><?php esc_html_e('Primary', 'rotary-dialysis-core'); ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="rdc-doctor-info">
                <h4 class="rdc-doctor-name"><?php echo esc_html($doctor->post_title); ?></h4>

                <?php if ($doctor->specialization): ?>
                <p class="rdc-doctor-specialization"><?php echo esc_html($doctor->specialization); ?></p>
                <?php endif; ?>

                <?php if ($doctor->qualifications): ?>
                <p class="rdc-doctor-qualifications"><?php echo esc_html($doctor->qualifications); ?></p>
                <?php endif; ?>

                <?php if ($doctor->experience_years): ?>
                <p class="rdc-doctor-experience">
                    <span class="dashicons dashicons-awards"></span>
                    <?php printf(
                        esc_html(_n('%d year experience', '%d years experience', $doctor->experience_years, 'rotary-dialysis-core')),
                        $doctor->experience_years
                    ); ?>
                </p>
                <?php endif; ?>

                <?php if ($show_availability && !empty($doctor->availability_days)): ?>
                <div class="rdc-doctor-availability">
                    <small><?php esc_html_e('Available:', 'rotary-dialysis-core'); ?></small>
                    <span class="rdc-doctor-days">
                        <?php
                        $available_days = array_map(function($day) use ($day_labels) {
                            return isset($day_labels[$day]) ? $day_labels[$day] : $day;
                        }, $doctor->availability_days);
                        echo esc_html(implode(', ', $available_days));
                        ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
