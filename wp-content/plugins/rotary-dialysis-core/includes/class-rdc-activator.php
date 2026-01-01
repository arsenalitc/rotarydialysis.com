<?php
/**
 * Plugin Activator
 *
 * Creates database tables and sets up initial configuration on activation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::create_roles();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Store version for future upgrades
        update_option('rdc_version', RDC_VERSION);
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix . 'rdc_';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // 1. Reviews table
        $sql_reviews = "CREATE TABLE {$prefix}reviews (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            rating tinyint(1) UNSIGNED NOT NULL,
            review_text text,
            reviewer_name varchar(100) NOT NULL,
            reviewer_email varchar(100) NOT NULL,
            is_verified tinyint(1) DEFAULT 0,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY status (status),
            KEY rating (rating)
        ) $charset_collate;";

        // 2. Review stats table (cached aggregates)
        $sql_review_stats = "CREATE TABLE {$prefix}review_stats (
            store_id bigint(20) UNSIGNED NOT NULL,
            average_rating decimal(3,2) DEFAULT 0.00,
            total_reviews int(11) DEFAULT 0,
            rating_1_count int(11) DEFAULT 0,
            rating_2_count int(11) DEFAULT 0,
            rating_3_count int(11) DEFAULT 0,
            rating_4_count int(11) DEFAULT 0,
            rating_5_count int(11) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (store_id)
        ) $charset_collate;";

        // 3. Gallery images table
        $sql_gallery = "CREATE TABLE {$prefix}gallery_images (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            attachment_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255),
            caption text,
            sort_order int(11) DEFAULT 0,
            is_featured tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY sort_order (sort_order)
        ) $charset_collate;";

        // 4. Center staff table
        $sql_staff = "CREATE TABLE {$prefix}center_staff (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            role varchar(20) DEFAULT 'staff',
            permissions text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY store_user (store_id, user_id),
            KEY store_id (store_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // 5. Bed availability table
        $sql_availability = "CREATE TABLE {$prefix}bed_availability (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            shift varchar(20) DEFAULT 'all',
            total_beds int(11) DEFAULT 0,
            available_beds int(11) DEFAULT 0,
            updated_by_user_id bigint(20) UNSIGNED,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY store_shift (store_id, shift),
            KEY store_id (store_id)
        ) $charset_collate;";

        // 6. Availability log table
        $sql_availability_log = "CREATE TABLE {$prefix}availability_log (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            shift varchar(20) DEFAULT 'all',
            previous_available int(11),
            new_available int(11),
            updated_by_user_id bigint(20) UNSIGNED,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY store_id (store_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        // 7. Shifts table
        $sql_shifts = "CREATE TABLE {$prefix}shifts (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            shift_name varchar(50) NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            capacity int(11) DEFAULT 10,
            is_active tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            KEY store_id (store_id)
        ) $charset_collate;";

        // 8. Appointments table
        $sql_appointments = "CREATE TABLE {$prefix}appointments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            shift_id bigint(20) UNSIGNED,
            confirmation_code varchar(20) NOT NULL,
            patient_name varchar(100) NOT NULL,
            patient_phone varchar(20) NOT NULL,
            patient_email varchar(100),
            preferred_date date NOT NULL,
            message text,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY confirmation_code (confirmation_code),
            KEY store_id (store_id),
            KEY preferred_date (preferred_date),
            KEY status (status)
        ) $charset_collate;";

        // 9. Email tokens table
        $sql_email_tokens = "CREATE TABLE {$prefix}email_tokens (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            token_hash varchar(64) NOT NULL,
            purpose varchar(20) NOT NULL,
            reference_id bigint(20) UNSIGNED,
            expires_at datetime NOT NULL,
            is_used tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY token_hash (token_hash),
            KEY email (email),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        // 10. Center meta table
        $sql_center_meta = "CREATE TABLE {$prefix}center_meta (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(100) NOT NULL,
            meta_value longtext,
            PRIMARY KEY (id),
            UNIQUE KEY store_meta (store_id, meta_key),
            KEY store_id (store_id),
            KEY meta_key (meta_key)
        ) $charset_collate;";

        // 11. Documents table
        $sql_documents = "CREATE TABLE {$prefix}documents (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            store_id bigint(20) UNSIGNED,
            document_name varchar(255) NOT NULL,
            description text,
            is_mandatory tinyint(1) DEFAULT 0,
            template_attachment_id bigint(20) UNSIGNED,
            sort_order int(11) DEFAULT 0,
            PRIMARY KEY (id),
            KEY store_id (store_id)
        ) $charset_collate;";

        // 12. Doctor centers table
        $sql_doctor_centers = "CREATE TABLE {$prefix}doctor_centers (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            doctor_id bigint(20) UNSIGNED NOT NULL,
            store_id bigint(20) UNSIGNED NOT NULL,
            is_primary tinyint(1) DEFAULT 0,
            availability_days varchar(100),
            PRIMARY KEY (id),
            UNIQUE KEY doctor_store (doctor_id, store_id),
            KEY doctor_id (doctor_id),
            KEY store_id (store_id)
        ) $charset_collate;";

        // Execute all table creation
        dbDelta($sql_reviews);
        dbDelta($sql_review_stats);
        dbDelta($sql_gallery);
        dbDelta($sql_staff);
        dbDelta($sql_availability);
        dbDelta($sql_availability_log);
        dbDelta($sql_shifts);
        dbDelta($sql_appointments);
        dbDelta($sql_email_tokens);
        dbDelta($sql_center_meta);
        dbDelta($sql_documents);
        dbDelta($sql_doctor_centers);
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        $defaults = array(
            'rdc_tawkto_property_id' => '',
            'rdc_tawkto_widget_id' => '',
            'rdc_tawkto_enabled' => '1',
            'rdc_admin_email' => get_option('admin_email'),
            'rdc_review_moderation_enabled' => '1',
            'rdc_booking_advance_days' => '30',
            'rdc_availability_threshold_warning' => '30',
            'rdc_availability_threshold_critical' => '10',
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Create custom roles and capabilities
     */
    private static function create_roles() {
        // Add Center Manager role
        add_role('rdc_center_manager', __('Center Manager', 'rotary-dialysis-core'), array(
            'read' => true,
            'upload_files' => true,
            'rdc_manage_center' => true,
            'rdc_manage_gallery' => true,
            'rdc_manage_availability' => true,
            'rdc_view_appointments' => true,
            'rdc_manage_appointments' => true,
        ));

        // Add Center Staff role
        add_role('rdc_center_staff', __('Center Staff', 'rotary-dialysis-core'), array(
            'read' => true,
            'rdc_manage_availability' => true,
            'rdc_view_appointments' => true,
        ));

        // Add capabilities to admin
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('rdc_manage_center');
            $admin->add_cap('rdc_manage_gallery');
            $admin->add_cap('rdc_manage_availability');
            $admin->add_cap('rdc_view_appointments');
            $admin->add_cap('rdc_manage_appointments');
            $admin->add_cap('rdc_moderate_reviews');
            $admin->add_cap('rdc_manage_settings');
        }
    }
}
