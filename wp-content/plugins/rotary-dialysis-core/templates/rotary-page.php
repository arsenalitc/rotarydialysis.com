<?php
/**
 * Template: Rotary Projects Page
 *
 * Displays Rotary Club information, impact stats, and project history.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="rdc-rotary-page">
    <!-- Hero Section -->
    <section class="rdc-rotary-hero">
        <div class="rdc-rotary-hero-content">
            <div class="rdc-rotary-logo">
                <img src="<?php echo RDC_PLUGIN_URL; ?>public/images/rotary-logo.png" alt="Rotary International" onerror="this.style.display='none'">
            </div>
            <h1 class="rdc-rotary-title"><?php esc_html_e('Project KIND', 'rotary-dialysis-core'); ?></h1>
            <p class="rdc-rotary-subtitle"><?php esc_html_e('Kidney Initiative for Nephrological Dialysis', 'rotary-dialysis-core'); ?></p>
            <p class="rdc-rotary-tagline"><?php esc_html_e('A Rotary Club of Madras Industrial City Initiative', 'rotary-dialysis-core'); ?></p>
            <div class="rdc-rotary-hero-actions">
                <a href="#impact" class="rdc-btn rdc-btn--primary"><?php esc_html_e('See Our Impact', 'rotary-dialysis-core'); ?></a>
                <a href="#support" class="rdc-btn rdc-btn--outline"><?php esc_html_e('Support Us', 'rotary-dialysis-core'); ?></a>
            </div>
        </div>
    </section>

    <!-- Impact Stats Section -->
    <section class="rdc-rotary-stats" id="impact">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Our Impact', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Making dialysis accessible to those who need it most', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-stats-counter-grid">
                <div class="rdc-stat-counter" data-target="<?php echo esc_attr($stats['centers'] ?? 25); ?>">
                    <span class="rdc-stat-icon"><span class="dashicons dashicons-building"></span></span>
                    <span class="rdc-stat-number">0</span>
                    <span class="rdc-stat-suffix">+</span>
                    <span class="rdc-stat-label"><?php esc_html_e('Dialysis Centers', 'rotary-dialysis-core'); ?></span>
                </div>
                <div class="rdc-stat-counter" data-target="<?php echo esc_attr($stats['patients'] ?? 5000); ?>">
                    <span class="rdc-stat-icon"><span class="dashicons dashicons-groups"></span></span>
                    <span class="rdc-stat-number">0</span>
                    <span class="rdc-stat-suffix">+</span>
                    <span class="rdc-stat-label"><?php esc_html_e('Patients Served', 'rotary-dialysis-core'); ?></span>
                </div>
                <div class="rdc-stat-counter" data-target="<?php echo esc_attr($stats['sessions'] ?? 150000); ?>">
                    <span class="rdc-stat-icon"><span class="dashicons dashicons-heart"></span></span>
                    <span class="rdc-stat-number">0</span>
                    <span class="rdc-stat-suffix">+</span>
                    <span class="rdc-stat-label"><?php esc_html_e('Dialysis Sessions', 'rotary-dialysis-core'); ?></span>
                </div>
                <div class="rdc-stat-counter" data-target="<?php echo esc_attr($stats['beds'] ?? 100); ?>">
                    <span class="rdc-stat-icon"><span class="dashicons dashicons-admin-home"></span></span>
                    <span class="rdc-stat-number">0</span>
                    <span class="rdc-stat-suffix">+</span>
                    <span class="rdc-stat-label"><?php esc_html_e('Dialysis Beds', 'rotary-dialysis-core'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="rdc-rotary-about" id="about">
        <div class="rdc-rotary-container">
            <div class="rdc-about-grid">
                <div class="rdc-about-content">
                    <h2 class="rdc-section-title"><?php esc_html_e('The Challenge', 'rotary-dialysis-core'); ?></h2>
                    <p><?php esc_html_e('Chronic Kidney Disease (CKD) affects millions of Indians, with many requiring regular dialysis treatment to survive. Unfortunately, the high cost of dialysis puts this life-saving treatment out of reach for many families, especially those from economically weaker sections.', 'rotary-dialysis-core'); ?></p>
                    <p><?php esc_html_e('A typical dialysis session costs between Rs. 1,500-3,000, and patients need 2-3 sessions per week. This translates to a monthly expense of Rs. 15,000-30,000 - an impossible burden for most families.', 'rotary-dialysis-core'); ?></p>
                </div>
                <div class="rdc-about-content">
                    <h2 class="rdc-section-title"><?php esc_html_e('Our Solution', 'rotary-dialysis-core'); ?></h2>
                    <p><?php esc_html_e('Project KIND (Kidney Initiative for Nephrological Dialysis) was launched by the Rotary Club of Madras Industrial City to provide affordable dialysis services across Chennai and Tamil Nadu.', 'rotary-dialysis-core'); ?></p>
                    <ul class="rdc-solution-list">
                        <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Free dialysis for BPL card holders', 'rotary-dialysis-core'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Subsidized rates for economically weaker sections', 'rotary-dialysis-core'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('State-of-the-art equipment and trained staff', 'rotary-dialysis-core'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Partnership with government hospitals', 'rotary-dialysis-core'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="rdc-rotary-timeline" id="timeline">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Our Journey', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Key milestones in Project KIND', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-timeline">
                <?php
                $milestones = array(
                    array(
                        'year' => '2018',
                        'title' => __('Project Launch', 'rotary-dialysis-core'),
                        'desc' => __('Rotary Club of Madras Industrial City launches Project KIND with the inauguration of the first dialysis center.', 'rotary-dialysis-core'),
                    ),
                    array(
                        'year' => '2019',
                        'title' => __('Expansion', 'rotary-dialysis-core'),
                        'desc' => __('5 new centers opened across Chennai, serving over 500 patients monthly.', 'rotary-dialysis-core'),
                    ),
                    array(
                        'year' => '2020',
                        'title' => __('COVID Response', 'rotary-dialysis-core'),
                        'desc' => __('Continued operations throughout the pandemic with enhanced safety protocols, ensuring uninterrupted care.', 'rotary-dialysis-core'),
                    ),
                    array(
                        'year' => '2021',
                        'title' => __('District Partnership', 'rotary-dialysis-core'),
                        'desc' => __('Partnership with Rotary District 3232 brings project to scale with 15+ centers operational.', 'rotary-dialysis-core'),
                    ),
                    array(
                        'year' => '2022',
                        'title' => __('Recognition', 'rotary-dialysis-core'),
                        'desc' => __('Project KIND receives Rotary International recognition for community service excellence.', 'rotary-dialysis-core'),
                    ),
                    array(
                        'year' => '2023',
                        'title' => __('25 Centers', 'rotary-dialysis-core'),
                        'desc' => __('Milestone achieved with 25 operational centers serving patients across Tamil Nadu.', 'rotary-dialysis-core'),
                    ),
                );

                foreach ($milestones as $index => $milestone):
                ?>
                <div class="rdc-timeline-item <?php echo $index % 2 === 0 ? 'rdc-timeline-left' : 'rdc-timeline-right'; ?>">
                    <div class="rdc-timeline-content">
                        <span class="rdc-timeline-year"><?php echo esc_html($milestone['year']); ?></span>
                        <h3><?php echo esc_html($milestone['title']); ?></h3>
                        <p><?php echo esc_html($milestone['desc']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <?php if (!empty($gallery_images)): ?>
    <section class="rdc-rotary-gallery" id="gallery">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Photo Gallery', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Moments from our journey', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-gallery-masonry">
                <?php foreach ($gallery_images as $image): ?>
                <div class="rdc-gallery-item">
                    <a href="<?php echo esc_url($image['full_url']); ?>" class="rdc-gallery-link">
                        <img src="<?php echo esc_url($image['thumb_url']); ?>" alt="<?php echo esc_attr($image['caption']); ?>">
                        <?php if (!empty($image['caption'])): ?>
                        <span class="rdc-gallery-caption"><?php echo esc_html($image['caption']); ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Team Section -->
    <?php if (!empty($team_members)): ?>
    <section class="rdc-rotary-team" id="team">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Our Team', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Leadership and coordinators driving Project KIND', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-team-grid">
                <?php foreach ($team_members as $member): ?>
                <div class="rdc-team-card">
                    <?php if (!empty($member['photo'])): ?>
                    <div class="rdc-team-photo">
                        <img src="<?php echo esc_url($member['photo']); ?>" alt="<?php echo esc_attr($member['name']); ?>">
                    </div>
                    <?php else: ?>
                    <div class="rdc-team-photo rdc-team-photo--placeholder">
                        <span class="dashicons dashicons-businessman"></span>
                    </div>
                    <?php endif; ?>
                    <h3 class="rdc-team-name"><?php echo esc_html($member['name']); ?></h3>
                    <p class="rdc-team-role"><?php echo esc_html($member['role']); ?></p>
                    <?php if (!empty($member['club'])): ?>
                    <p class="rdc-team-club"><?php echo esc_html($member['club']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Partners Section -->
    <?php if (!empty($partners)): ?>
    <section class="rdc-rotary-partners" id="partners">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Our Partners', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Working together for a healthier community', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-partners-carousel">
                <?php foreach ($partners as $partner): ?>
                <div class="rdc-partner-item">
                    <?php if (!empty($partner['logo'])): ?>
                    <img src="<?php echo esc_url($partner['logo']); ?>" alt="<?php echo esc_attr($partner['name']); ?>">
                    <?php else: ?>
                    <span class="rdc-partner-name"><?php echo esc_html($partner['name']); ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="rdc-rotary-cta" id="support">
        <div class="rdc-rotary-container">
            <h2 class="rdc-section-title"><?php esc_html_e('Support Our Mission', 'rotary-dialysis-core'); ?></h2>
            <p class="rdc-section-subtitle"><?php esc_html_e('Every contribution helps save a life', 'rotary-dialysis-core'); ?></p>

            <div class="rdc-cta-grid">
                <div class="rdc-cta-card">
                    <span class="rdc-cta-icon"><span class="dashicons dashicons-heart"></span></span>
                    <h3><?php esc_html_e('Donate', 'rotary-dialysis-core'); ?></h3>
                    <p><?php esc_html_e('Your donation directly funds dialysis sessions for patients who cannot afford treatment.', 'rotary-dialysis-core'); ?></p>
                    <a href="<?php echo esc_url($donate_url ?? '#'); ?>" class="rdc-btn rdc-btn--primary"><?php esc_html_e('Donate Now', 'rotary-dialysis-core'); ?></a>
                </div>
                <div class="rdc-cta-card">
                    <span class="rdc-cta-icon"><span class="dashicons dashicons-groups"></span></span>
                    <h3><?php esc_html_e('Volunteer', 'rotary-dialysis-core'); ?></h3>
                    <p><?php esc_html_e('Join us as a volunteer. We need medical professionals, administrators, and community coordinators.', 'rotary-dialysis-core'); ?></p>
                    <a href="<?php echo esc_url($volunteer_url ?? '#'); ?>" class="rdc-btn rdc-btn--primary"><?php esc_html_e('Join Us', 'rotary-dialysis-core'); ?></a>
                </div>
                <div class="rdc-cta-card">
                    <span class="rdc-cta-icon"><span class="dashicons dashicons-admin-multisite"></span></span>
                    <h3><?php esc_html_e('Partner', 'rotary-dialysis-core'); ?></h3>
                    <p><?php esc_html_e('Partner with us to establish new dialysis centers or support existing ones with equipment and supplies.', 'rotary-dialysis-core'); ?></p>
                    <a href="<?php echo esc_url($partner_url ?? '#'); ?>" class="rdc-btn rdc-btn--primary"><?php esc_html_e('Partner With Us', 'rotary-dialysis-core'); ?></a>
                </div>
            </div>

            <div class="rdc-rotary-contact">
                <h3><?php esc_html_e('Contact Us', 'rotary-dialysis-core'); ?></h3>
                <p><?php esc_html_e('Rotary Club of Madras Industrial City', 'rotary-dialysis-core'); ?></p>
                <?php if (!empty($contact_email)): ?>
                <p><a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></p>
                <?php endif; ?>
                <?php if (!empty($contact_phone)): ?>
                <p><a href="tel:<?php echo esc_attr($contact_phone); ?>"><?php echo esc_html($contact_phone); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
