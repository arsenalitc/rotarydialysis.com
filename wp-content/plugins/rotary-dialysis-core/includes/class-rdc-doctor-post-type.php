<?php
/**
 * Doctor Custom Post Type
 *
 * Registers and manages the Doctor post type.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Doctor_Post_Type {

    /**
     * Post type name
     */
    const POST_TYPE = 'rdc_doctor';

    /**
     * Initialize
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_meta'));
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__, 'add_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'render_columns'), 10, 2);
    }

    /**
     * Register the Doctor post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'                  => _x('Doctors', 'Post type general name', 'rotary-dialysis-core'),
            'singular_name'         => _x('Doctor', 'Post type singular name', 'rotary-dialysis-core'),
            'menu_name'             => _x('Doctors', 'Admin Menu text', 'rotary-dialysis-core'),
            'add_new'               => __('Add New', 'rotary-dialysis-core'),
            'add_new_item'          => __('Add New Doctor', 'rotary-dialysis-core'),
            'edit_item'             => __('Edit Doctor', 'rotary-dialysis-core'),
            'new_item'              => __('New Doctor', 'rotary-dialysis-core'),
            'view_item'             => __('View Doctor', 'rotary-dialysis-core'),
            'search_items'          => __('Search Doctors', 'rotary-dialysis-core'),
            'not_found'             => __('No doctors found', 'rotary-dialysis-core'),
            'not_found_in_trash'    => __('No doctors found in Trash', 'rotary-dialysis-core'),
            'all_items'             => __('All Doctors', 'rotary-dialysis-core'),
            'featured_image'        => __('Doctor Photo', 'rotary-dialysis-core'),
            'set_featured_image'    => __('Set doctor photo', 'rotary-dialysis-core'),
            'remove_featured_image' => __('Remove doctor photo', 'rotary-dialysis-core'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'rotary-dialysis',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'doctor'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-businessman',
            'supports'           => array('title', 'editor', 'thumbnail'),
            'show_in_rest'       => true,
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'rdc_doctor_details',
            __('Doctor Details', 'rotary-dialysis-core'),
            array(__CLASS__, 'render_details_metabox'),
            self::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'rdc_doctor_centers',
            __('Associated Centers', 'rotary-dialysis-core'),
            array(__CLASS__, 'render_centers_metabox'),
            self::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Render details metabox
     */
    public static function render_details_metabox($post) {
        wp_nonce_field('rdc_doctor_meta', 'rdc_doctor_nonce');

        $specialization = get_post_meta($post->ID, '_rdc_specialization', true);
        $qualifications = get_post_meta($post->ID, '_rdc_qualifications', true);
        $experience_years = get_post_meta($post->ID, '_rdc_experience_years', true);
        $registration_no = get_post_meta($post->ID, '_rdc_registration_no', true);
        $phone = get_post_meta($post->ID, '_rdc_phone', true);
        $email = get_post_meta($post->ID, '_rdc_email', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="rdc_specialization"><?php esc_html_e('Specialization', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="text" id="rdc_specialization" name="rdc_specialization"
                           value="<?php echo esc_attr($specialization); ?>" class="regular-text"
                           placeholder="<?php esc_attr_e('e.g., Nephrologist', 'rotary-dialysis-core'); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="rdc_qualifications"><?php esc_html_e('Qualifications', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="text" id="rdc_qualifications" name="rdc_qualifications"
                           value="<?php echo esc_attr($qualifications); ?>" class="regular-text"
                           placeholder="<?php esc_attr_e('e.g., MBBS, MD, DM (Nephrology)', 'rotary-dialysis-core'); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="rdc_experience_years"><?php esc_html_e('Years of Experience', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="number" id="rdc_experience_years" name="rdc_experience_years"
                           value="<?php echo esc_attr($experience_years); ?>" class="small-text" min="0" max="60">
                </td>
            </tr>
            <tr>
                <th><label for="rdc_registration_no"><?php esc_html_e('Registration Number', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="text" id="rdc_registration_no" name="rdc_registration_no"
                           value="<?php echo esc_attr($registration_no); ?>" class="regular-text"
                           placeholder="<?php esc_attr_e('Medical Council Registration', 'rotary-dialysis-core'); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="rdc_phone"><?php esc_html_e('Contact Phone', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="tel" id="rdc_phone" name="rdc_phone"
                           value="<?php echo esc_attr($phone); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="rdc_email"><?php esc_html_e('Contact Email', 'rotary-dialysis-core'); ?></label></th>
                <td>
                    <input type="email" id="rdc_email" name="rdc_email"
                           value="<?php echo esc_attr($email); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render centers metabox
     */
    public static function render_centers_metabox($post) {
        global $wpdb;

        // Get all centers
        $centers = $wpdb->get_results(
            "SELECT id, title FROM {$wpdb->prefix}asl_stores WHERE is_disabled = 0 ORDER BY title"
        );

        // Get assigned centers
        $assigned = $wpdb->get_results($wpdb->prepare(
            "SELECT store_id, is_primary, availability_days FROM {$wpdb->prefix}rdc_doctor_centers WHERE doctor_id = %d",
            $post->ID
        ), OBJECT_K);

        $days = array(
            'mon' => __('Mon', 'rotary-dialysis-core'),
            'tue' => __('Tue', 'rotary-dialysis-core'),
            'wed' => __('Wed', 'rotary-dialysis-core'),
            'thu' => __('Thu', 'rotary-dialysis-core'),
            'fri' => __('Fri', 'rotary-dialysis-core'),
            'sat' => __('Sat', 'rotary-dialysis-core'),
            'sun' => __('Sun', 'rotary-dialysis-core'),
        );
        ?>
        <div class="rdc-doctor-centers">
            <?php if (empty($centers)): ?>
                <p><?php esc_html_e('No dialysis centers found.', 'rotary-dialysis-core'); ?></p>
            <?php else: ?>
                <?php foreach ($centers as $center):
                    $is_assigned = isset($assigned[$center->id]);
                    $is_primary = $is_assigned && $assigned[$center->id]->is_primary;
                    $avail_days = $is_assigned ? explode(',', $assigned[$center->id]->availability_days) : array();
                ?>
                <div class="rdc-center-item" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox" name="rdc_centers[]" value="<?php echo esc_attr($center->id); ?>"
                               <?php checked($is_assigned); ?>>
                        <strong><?php echo esc_html($center->title); ?></strong>
                    </label>

                    <div class="rdc-center-options" style="margin-left: 20px; <?php echo !$is_assigned ? 'display:none;' : ''; ?>">
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="radio" name="rdc_primary_center" value="<?php echo esc_attr($center->id); ?>"
                                   <?php checked($is_primary); ?>>
                            <?php esc_html_e('Primary Center', 'rotary-dialysis-core'); ?>
                        </label>

                        <div style="margin-top: 5px;">
                            <small><?php esc_html_e('Available on:', 'rotary-dialysis-core'); ?></small><br>
                            <?php foreach ($days as $day_key => $day_label): ?>
                            <label style="display: inline-block; margin-right: 5px;">
                                <input type="checkbox" name="rdc_center_days[<?php echo esc_attr($center->id); ?>][]"
                                       value="<?php echo esc_attr($day_key); ?>"
                                       <?php checked(in_array($day_key, $avail_days)); ?>>
                                <?php echo esc_html($day_label); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <script>
                jQuery(document).ready(function($) {
                    $('.rdc-center-item input[type="checkbox"][name="rdc_centers[]"]').on('change', function() {
                        $(this).closest('.rdc-center-item').find('.rdc-center-options').toggle(this.checked);
                    });
                });
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save meta data
     */
    public static function save_meta($post_id) {
        // Verify nonce
        if (!isset($_POST['rdc_doctor_nonce']) || !wp_verify_nonce($_POST['rdc_doctor_nonce'], 'rdc_doctor_meta')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta fields
        $fields = array(
            'rdc_specialization' => '_rdc_specialization',
            'rdc_qualifications' => '_rdc_qualifications',
            'rdc_experience_years' => '_rdc_experience_years',
            'rdc_registration_no' => '_rdc_registration_no',
            'rdc_phone' => '_rdc_phone',
            'rdc_email' => '_rdc_email',
        );

        foreach ($fields as $post_field => $meta_key) {
            if (isset($_POST[$post_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_field]));
            }
        }

        // Save center associations
        global $wpdb;

        // Delete existing associations
        $wpdb->delete($wpdb->prefix . 'rdc_doctor_centers', array('doctor_id' => $post_id), array('%d'));

        // Insert new associations
        if (!empty($_POST['rdc_centers']) && is_array($_POST['rdc_centers'])) {
            $primary_center = isset($_POST['rdc_primary_center']) ? absint($_POST['rdc_primary_center']) : 0;

            foreach ($_POST['rdc_centers'] as $store_id) {
                $store_id = absint($store_id);
                $is_primary = ($store_id === $primary_center) ? 1 : 0;

                $days = isset($_POST['rdc_center_days'][$store_id])
                    ? implode(',', array_map('sanitize_text_field', $_POST['rdc_center_days'][$store_id]))
                    : '';

                $wpdb->insert(
                    $wpdb->prefix . 'rdc_doctor_centers',
                    array(
                        'doctor_id' => $post_id,
                        'store_id' => $store_id,
                        'is_primary' => $is_primary,
                        'availability_days' => $days,
                    ),
                    array('%d', '%d', '%d', '%s')
                );
            }
        }
    }

    /**
     * Add custom columns
     */
    public static function add_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['specialization'] = __('Specialization', 'rotary-dialysis-core');
                $new_columns['experience'] = __('Experience', 'rotary-dialysis-core');
                $new_columns['centers'] = __('Centers', 'rotary-dialysis-core');
            }
        }
        return $new_columns;
    }

    /**
     * Render custom columns
     */
    public static function render_columns($column, $post_id) {
        global $wpdb;

        switch ($column) {
            case 'specialization':
                echo esc_html(get_post_meta($post_id, '_rdc_specialization', true) ?: '—');
                break;

            case 'experience':
                $years = get_post_meta($post_id, '_rdc_experience_years', true);
                if ($years) {
                    printf(
                        _n('%d year', '%d years', $years, 'rotary-dialysis-core'),
                        $years
                    );
                } else {
                    echo '—';
                }
                break;

            case 'centers':
                $centers = $wpdb->get_col($wpdb->prepare(
                    "SELECT s.title FROM {$wpdb->prefix}rdc_doctor_centers dc
                    JOIN {$wpdb->prefix}asl_stores s ON dc.store_id = s.id
                    WHERE dc.doctor_id = %d",
                    $post_id
                ));
                echo $centers ? esc_html(implode(', ', $centers)) : '—';
                break;
        }
    }

    /**
     * Get doctors for a store
     */
    public static function get_doctors_for_store($store_id) {
        global $wpdb;

        $doctor_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT doctor_id FROM {$wpdb->prefix}rdc_doctor_centers WHERE store_id = %d",
            $store_id
        ));

        if (empty($doctor_ids)) {
            return array();
        }

        $doctors = get_posts(array(
            'post_type' => self::POST_TYPE,
            'post__in' => $doctor_ids,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        // Enrich with meta data
        foreach ($doctors as &$doctor) {
            $doctor->specialization = get_post_meta($doctor->ID, '_rdc_specialization', true);
            $doctor->qualifications = get_post_meta($doctor->ID, '_rdc_qualifications', true);
            $doctor->experience_years = get_post_meta($doctor->ID, '_rdc_experience_years', true);
            $doctor->photo_url = get_the_post_thumbnail_url($doctor->ID, 'medium');

            // Get availability for this store
            $assoc = $wpdb->get_row($wpdb->prepare(
                "SELECT is_primary, availability_days FROM {$wpdb->prefix}rdc_doctor_centers
                WHERE doctor_id = %d AND store_id = %d",
                $doctor->ID,
                $store_id
            ));

            $doctor->is_primary = $assoc ? $assoc->is_primary : false;
            $doctor->availability_days = $assoc ? explode(',', $assoc->availability_days) : array();
        }

        return $doctors;
    }

    /**
     * Get all stores for a doctor
     */
    public static function get_stores_for_doctor($doctor_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, dc.is_primary, dc.availability_days
            FROM {$wpdb->prefix}rdc_doctor_centers dc
            JOIN {$wpdb->prefix}asl_stores s ON dc.store_id = s.id
            WHERE dc.doctor_id = %d
            ORDER BY dc.is_primary DESC, s.title ASC",
            $doctor_id
        ));
    }
}

// Initialize
RDC_Doctor_Post_Type::init();
