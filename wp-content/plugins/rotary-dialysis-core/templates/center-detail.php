<?php
/**
 * Template: Center Detail Page
 *
 * Display full details for a dialysis center
 */

if (!defined('ABSPATH')) {
    exit;
}

$day_labels = array(
    'mon' => __('Monday', 'rotary-dialysis-core'),
    'tue' => __('Tuesday', 'rotary-dialysis-core'),
    'wed' => __('Wednesday', 'rotary-dialysis-core'),
    'thu' => __('Thursday', 'rotary-dialysis-core'),
    'fri' => __('Friday', 'rotary-dialysis-core'),
    'sat' => __('Saturday', 'rotary-dialysis-core'),
    'sun' => __('Sunday', 'rotary-dialysis-core'),
);

$status_labels = array(
    'available' => __('Available', 'rotary-dialysis-core'),
    'limited' => __('Limited', 'rotary-dialysis-core'),
    'full' => __('Full', 'rotary-dialysis-core'),
    'unknown' => __('Unknown', 'rotary-dialysis-core'),
);

$availability_label = $status_labels[$store->availability['status']] ?? $status_labels['unknown'];
?>

<div class="rdc-center-detail">
    <!-- Hero Section -->
    <section class="rdc-center-hero">
        <div class="rdc-center-hero-content">
            <div class="rdc-center-hero-main">
                <h1 class="rdc-center-title"><?php echo esc_html($store->title); ?></h1>

                <div class="rdc-center-meta">
                    <?php if (!empty($store->street) || !empty($store->city)): ?>
                    <p class="rdc-center-address">
                        <span class="dashicons dashicons-location"></span>
                        <?php
                        $address_parts = array_filter(array($store->street, $store->city, $store->state));
                        echo esc_html(implode(', ', $address_parts));
                        ?>
                    </p>
                    <?php endif; ?>

                    <?php if (!empty($store->phone)): ?>
                    <p class="rdc-center-phone">
                        <span class="dashicons dashicons-phone"></span>
                        <a href="tel:<?php echo esc_attr($store->phone); ?>"><?php echo esc_html($store->phone); ?></a>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="rdc-center-badges">
                    <span class="rdc-bed-badge rdc-bed-badge--<?php echo esc_attr($store->availability['status']); ?>">
                        <?php echo esc_html($availability_label); ?>
                        <?php if ($store->availability['total_beds'] > 0): ?>
                        (<?php echo esc_html($store->availability['available_beds']); ?>/<?php echo esc_html($store->availability['total_beds']); ?> <?php esc_html_e('beds', 'rotary-dialysis-core'); ?>)
                        <?php endif; ?>
                    </span>

                    <?php if ($store->rating_stats && $store->rating_stats['total_reviews'] > 0): ?>
                    <span class="rdc-rating-badge">
                        <span class="rdc-rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="dashicons dashicons-star-<?php echo $i <= round($store->rating_stats['average_rating']) ? 'filled' : 'empty'; ?>"></span>
                            <?php endfor; ?>
                        </span>
                        <span class="rdc-rating-value"><?php echo number_format($store->rating_stats['average_rating'], 1); ?></span>
                        <span class="rdc-rating-count">(<?php echo esc_html($store->rating_stats['total_reviews']); ?> <?php esc_html_e('reviews', 'rotary-dialysis-core'); ?>)</span>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rdc-center-hero-actions">
                <a href="#booking" class="button rdc-btn rdc-btn--primary">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <?php esc_html_e('Book Appointment', 'rotary-dialysis-core'); ?>
                </a>
                <?php if (!empty($store->phone)): ?>
                <a href="tel:<?php echo esc_attr($store->phone); ?>" class="button rdc-btn rdc-btn--secondary">
                    <span class="dashicons dashicons-phone"></span>
                    <?php esc_html_e('Call Now', 'rotary-dialysis-core'); ?>
                </a>
                <?php endif; ?>
                <?php if (!empty($store->lat) && !empty($store->lng)): ?>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($store->lat); ?>,<?php echo esc_attr($store->lng); ?>"
                   target="_blank" class="button rdc-btn rdc-btn--outline">
                    <span class="dashicons dashicons-location-alt"></span>
                    <?php esc_html_e('Get Directions', 'rotary-dialysis-core'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <?php if (!empty($store->gallery)): ?>
    <section class="rdc-center-gallery">
        <div class="rdc-gallery-carousel">
            <?php foreach ($store->gallery as $image): ?>
            <div class="rdc-gallery-slide">
                <a href="<?php echo esc_url($image->full_url); ?>" data-lightbox="center-gallery">
                    <img src="<?php echo esc_url($image->thumbnail_url); ?>" alt="<?php echo esc_attr($image->title); ?>">
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Main Content with Tabs -->
    <section class="rdc-center-content">
        <div class="rdc-tabs" id="center-tabs">
            <div class="rdc-tabs-nav">
                <button class="rdc-tab-btn active" data-tab="overview"><?php esc_html_e('Overview', 'rotary-dialysis-core'); ?></button>
                <button class="rdc-tab-btn" data-tab="hours"><?php esc_html_e('Hours & Shifts', 'rotary-dialysis-core'); ?></button>
                <button class="rdc-tab-btn" data-tab="cost"><?php esc_html_e('Cost & Eligibility', 'rotary-dialysis-core'); ?></button>
                <button class="rdc-tab-btn" data-tab="documents"><?php esc_html_e('Documents', 'rotary-dialysis-core'); ?></button>
                <button class="rdc-tab-btn" data-tab="doctors"><?php esc_html_e('Doctors', 'rotary-dialysis-core'); ?></button>
            </div>

            <!-- Overview Tab -->
            <div class="rdc-tab-content active" data-tab="overview">
                <div class="rdc-overview-grid">
                    <div class="rdc-overview-main">
                        <?php if (!empty($store->description)): ?>
                        <div class="rdc-section">
                            <h3><?php esc_html_e('About This Center', 'rotary-dialysis-core'); ?></h3>
                            <div class="rdc-prose">
                                <?php echo wp_kses_post(wpautop($store->description)); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="rdc-section">
                            <h3><?php esc_html_e('Facilities', 'rotary-dialysis-core'); ?></h3>
                            <ul class="rdc-facilities-list">
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Hemodialysis', 'rotary-dialysis-core'); ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Trained Nursing Staff', 'rotary-dialysis-core'); ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Emergency Care', 'rotary-dialysis-core'); ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Wheel Chair Access', 'rotary-dialysis-core'); ?></li>
                                <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Waiting Area for Attendants', 'rotary-dialysis-core'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="rdc-overview-sidebar">
                        <div class="rdc-contact-card">
                            <h4><?php esc_html_e('Contact Information', 'rotary-dialysis-core'); ?></h4>

                            <?php if (!empty($store->phone)): ?>
                            <div class="rdc-contact-item">
                                <span class="dashicons dashicons-phone"></span>
                                <div>
                                    <strong><?php esc_html_e('Phone', 'rotary-dialysis-core'); ?></strong>
                                    <a href="tel:<?php echo esc_attr($store->phone); ?>"><?php echo esc_html($store->phone); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($store->email)): ?>
                            <div class="rdc-contact-item">
                                <span class="dashicons dashicons-email-alt"></span>
                                <div>
                                    <strong><?php esc_html_e('Email', 'rotary-dialysis-core'); ?></strong>
                                    <a href="mailto:<?php echo esc_attr($store->email); ?>"><?php echo esc_html($store->email); ?></a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($store->street)): ?>
                            <div class="rdc-contact-item">
                                <span class="dashicons dashicons-location"></span>
                                <div>
                                    <strong><?php esc_html_e('Address', 'rotary-dialysis-core'); ?></strong>
                                    <address>
                                        <?php echo esc_html($store->street); ?><br>
                                        <?php if (!empty($store->city)): ?><?php echo esc_html($store->city); ?><?php endif; ?>
                                        <?php if (!empty($store->state)): ?>, <?php echo esc_html($store->state); ?><?php endif; ?>
                                        <?php if (!empty($store->postal_code)): ?> <?php echo esc_html($store->postal_code); ?><?php endif; ?>
                                    </address>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($store->lat) && !empty($store->lng)): ?>
                        <div class="rdc-map-embed">
                            <iframe
                                width="100%"
                                height="200"
                                frameborder="0"
                                scrolling="no"
                                src="https://maps.google.com/maps?q=<?php echo esc_attr($store->lat); ?>,<?php echo esc_attr($store->lng); ?>&z=15&output=embed">
                            </iframe>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Hours Tab -->
            <div class="rdc-tab-content" data-tab="hours">
                <div class="rdc-section">
                    <h3><?php esc_html_e('Operating Hours', 'rotary-dialysis-core'); ?></h3>
                    <?php if (!empty($store->open_hours)): ?>
                    <div class="rdc-hours-table">
                        <?php
                        $hours = json_decode($store->open_hours, true);
                        if ($hours):
                            foreach ($day_labels as $key => $label):
                                $day_hours = $hours[$key] ?? null;
                        ?>
                        <div class="rdc-hours-row">
                            <span class="rdc-hours-day"><?php echo esc_html($label); ?></span>
                            <span class="rdc-hours-time">
                                <?php
                                if ($day_hours && !empty($day_hours['open'])) {
                                    echo esc_html($day_hours['open'] . ' - ' . $day_hours['close']);
                                } else {
                                    esc_html_e('Closed', 'rotary-dialysis-core');
                                }
                                ?>
                            </span>
                        </div>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <?php else: ?>
                    <p><?php esc_html_e('Please contact the center for operating hours.', 'rotary-dialysis-core'); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($shifts)): ?>
                <div class="rdc-section">
                    <h3><?php esc_html_e('Dialysis Shifts', 'rotary-dialysis-core'); ?></h3>
                    <div class="rdc-shifts-grid">
                        <?php foreach ($shifts as $shift): ?>
                        <div class="rdc-shift-card">
                            <h4><?php echo esc_html($shift->shift_name); ?></h4>
                            <p class="rdc-shift-time">
                                <span class="dashicons dashicons-clock"></span>
                                <?php echo esc_html($shift->start_time); ?> - <?php echo esc_html($shift->end_time); ?>
                            </p>
                            <p class="rdc-shift-capacity">
                                <span class="dashicons dashicons-groups"></span>
                                <?php printf(esc_html__('%d patients per shift', 'rotary-dialysis-core'), $shift->capacity); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Cost Tab -->
            <div class="rdc-tab-content" data-tab="cost">
                <div class="rdc-cost-section">
                    <h3><?php esc_html_e('Treatment Costs', 'rotary-dialysis-core'); ?></h3>
                    <div class="rdc-cost-info">
                        <div class="rdc-cost-card rdc-cost-card--highlight">
                            <span class="rdc-cost-label"><?php esc_html_e('Dialysis Session', 'rotary-dialysis-core'); ?></span>
                            <span class="rdc-cost-value">
                                <?php if (!empty($center_meta['cost_per_session'])): ?>
                                    &#8377;<?php echo esc_html(number_format($center_meta['cost_per_session'])); ?>
                                <?php else: ?>
                                    <?php esc_html_e('Contact for pricing', 'rotary-dialysis-core'); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <p class="rdc-cost-note">
                            <span class="dashicons dashicons-info"></span>
                            <?php esc_html_e('Subsidized rates available for eligible patients. Many centers offer free dialysis for BPL card holders.', 'rotary-dialysis-core'); ?>
                        </p>
                    </div>
                </div>

                <div class="rdc-eligibility-section">
                    <h3><?php esc_html_e('Eligibility Criteria', 'rotary-dialysis-core'); ?></h3>
                    <ul class="rdc-eligibility-list">
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Patients with Chronic Kidney Disease (CKD) Stage 5', 'rotary-dialysis-core'); ?>
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Referral from treating nephrologist', 'rotary-dialysis-core'); ?>
                        </li>
                        <li>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Complete documentation (ID proof, medical records)', 'rotary-dialysis-core'); ?>
                        </li>
                        <li>
                            <span class="dashicons dashicons-info-outline"></span>
                            <?php esc_html_e('BPL card holders may be eligible for free treatment', 'rotary-dialysis-core'); ?>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Documents Tab -->
            <div class="rdc-tab-content" data-tab="documents">
                <?php
                $documents = RDC_Document_Service::get_documents($store_id);
                if (!empty($documents)):
                ?>
                <div class="rdc-documents-section">
                    <h3><?php esc_html_e('Required Documents', 'rotary-dialysis-core'); ?></h3>
                    <p class="rdc-documents-intro"><?php esc_html_e('Please bring the following documents for your first visit:', 'rotary-dialysis-core'); ?></p>

                    <div class="rdc-documents-list">
                        <?php foreach ($documents as $doc): ?>
                        <div class="rdc-document-item <?php echo $doc->is_mandatory ? 'rdc-document-item--mandatory' : ''; ?>">
                            <div class="rdc-document-icon">
                                <span class="dashicons dashicons-media-document"></span>
                            </div>
                            <div class="rdc-document-info">
                                <h4>
                                    <?php echo esc_html($doc->document_name); ?>
                                    <?php if ($doc->is_mandatory): ?>
                                    <span class="rdc-mandatory-badge"><?php esc_html_e('Required', 'rotary-dialysis-core'); ?></span>
                                    <?php endif; ?>
                                </h4>
                                <?php if (!empty($doc->description)): ?>
                                <p><?php echo esc_html($doc->description); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($doc->template_attachment_id)): ?>
                            <a href="<?php echo esc_url(wp_get_attachment_url($doc->template_attachment_id)); ?>"
                               class="rdc-document-download" download>
                                <span class="dashicons dashicons-download"></span>
                                <?php esc_html_e('Download', 'rotary-dialysis-core'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <p><?php esc_html_e('Please contact the center for information about required documents.', 'rotary-dialysis-core'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Doctors Tab -->
            <div class="rdc-tab-content" data-tab="doctors">
                <?php
                $doctors = RDC_Doctor_Post_Type::get_doctors_for_store($store_id);
                if (!empty($doctors)):
                ?>
                <div class="rdc-doctors-section">
                    <h3><?php esc_html_e('Medical Team', 'rotary-dialysis-core'); ?></h3>
                    <div class="rdc-doctors-grid" data-columns="2">
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
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <p><?php esc_html_e('Medical team information coming soon.', 'rotary-dialysis-core'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="rdc-center-reviews" id="reviews">
        <div class="rdc-reviews-header">
            <h2><?php esc_html_e('Patient Reviews', 'rotary-dialysis-core'); ?></h2>
            <?php if ($store->rating_stats && $store->rating_stats['total_reviews'] > 0): ?>
            <div class="rdc-reviews-summary">
                <span class="rdc-reviews-average"><?php echo number_format($store->rating_stats['average_rating'], 1); ?></span>
                <div class="rdc-reviews-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="dashicons dashicons-star-<?php echo $i <= round($store->rating_stats['average_rating']) ? 'filled' : 'empty'; ?>"></span>
                    <?php endfor; ?>
                </div>
                <span class="rdc-reviews-total"><?php printf(esc_html__('Based on %d reviews', 'rotary-dialysis-core'), $store->rating_stats['total_reviews']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="rdc-reviews-content">
            <div class="rdc-reviews-list-wrapper">
                <?php
                $reviews = RDC_Review_Service::get_reviews($store_id, array('limit' => 5, 'status' => 'approved'));
                if (!empty($reviews)):
                ?>
                <div class="rdc-reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="rdc-review-card">
                        <div class="rdc-review-header">
                            <div class="rdc-review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="dashicons dashicons-star-<?php echo $i <= $review->rating ? 'filled' : 'empty'; ?>"></span>
                                <?php endfor; ?>
                            </div>
                            <span class="rdc-review-date"><?php echo esc_html(human_time_diff(strtotime($review->created_at), current_time('timestamp'))); ?> <?php esc_html_e('ago', 'rotary-dialysis-core'); ?></span>
                        </div>
                        <?php if (!empty($review->review_text)): ?>
                        <p class="rdc-review-text"><?php echo esc_html($review->review_text); ?></p>
                        <?php endif; ?>
                        <div class="rdc-review-author">
                            <span class="rdc-review-name">
                                <?php
                                $name = $review->reviewer_name ?: __('Anonymous', 'rotary-dialysis-core');
                                // Show only first name and initial
                                $parts = explode(' ', $name);
                                if (count($parts) > 1) {
                                    echo esc_html($parts[0] . ' ' . substr($parts[1], 0, 1) . '.');
                                } else {
                                    echo esc_html($name);
                                }
                                ?>
                            </span>
                            <?php if ($review->is_verified): ?>
                            <span class="rdc-verified-badge" title="<?php esc_attr_e('Verified', 'rotary-dialysis-core'); ?>">
                                <span class="dashicons dashicons-yes"></span>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="rdc-no-reviews"><?php esc_html_e('No reviews yet. Be the first to share your experience!', 'rotary-dialysis-core'); ?></p>
                <?php endif; ?>
            </div>

            <div class="rdc-review-form-wrapper">
                <h3><?php esc_html_e('Share Your Experience', 'rotary-dialysis-core'); ?></h3>
                <?php include RDC_PLUGIN_DIR . 'templates/review-form.php'; ?>
            </div>
        </div>
    </section>

    <!-- Booking Section -->
    <section class="rdc-center-booking" id="booking">
        <h2><?php esc_html_e('Book an Appointment', 'rotary-dialysis-core'); ?></h2>
        <?php include RDC_PLUGIN_DIR . 'templates/booking-form.php'; ?>
    </section>
</div>
