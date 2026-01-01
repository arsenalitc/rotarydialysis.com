<?php
/**
 * Plugin Name: Rotary Dialysis Core
 * Plugin URI: https://rotarydialysis.com
 * Description: Core functionality for Rotary Dialysis Centers - ratings, galleries, bed availability, appointments, and more.
 * Version: 1.0.0
 * Author: Rotary Club of Madras Industrial City
 * Author URI: https://rotarydialysis.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rotary-dialysis-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('RDC_VERSION', '1.0.0');
define('RDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RDC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RDC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
final class Rotary_Dialysis_Core {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_rest_api();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // Core classes
        require_once RDC_PLUGIN_DIR . 'includes/class-rdc-activator.php';
        require_once RDC_PLUGIN_DIR . 'includes/class-rdc-deactivator.php';
        require_once RDC_PLUGIN_DIR . 'includes/class-rdc-i18n.php';

        // Services
        require_once RDC_PLUGIN_DIR . 'includes/services/class-rdc-review-service.php';
        require_once RDC_PLUGIN_DIR . 'includes/services/class-rdc-gallery-service.php';
        require_once RDC_PLUGIN_DIR . 'includes/services/class-rdc-availability-service.php';
        require_once RDC_PLUGIN_DIR . 'includes/services/class-rdc-appointment-service.php';
        require_once RDC_PLUGIN_DIR . 'includes/services/class-rdc-email-service.php';

        // Post Types
        require_once RDC_PLUGIN_DIR . 'includes/class-rdc-doctor-post-type.php';

        // Integrations
        require_once RDC_PLUGIN_DIR . 'includes/integrations/class-rdc-asl-integration.php';
        require_once RDC_PLUGIN_DIR . 'includes/integrations/class-rdc-tawkto-integration.php';

        // REST API
        require_once RDC_PLUGIN_DIR . 'includes/api/class-rdc-rest-controller.php';
        require_once RDC_PLUGIN_DIR . 'includes/api/class-rdc-reviews-controller.php';
        require_once RDC_PLUGIN_DIR . 'includes/api/class-rdc-gallery-controller.php';
        require_once RDC_PLUGIN_DIR . 'includes/api/class-rdc-availability-controller.php';
        require_once RDC_PLUGIN_DIR . 'includes/api/class-rdc-appointments-controller.php';

        // Admin
        if (is_admin()) {
            require_once RDC_PLUGIN_DIR . 'admin/class-rdc-admin.php';
        }

        // Public
        require_once RDC_PLUGIN_DIR . 'public/class-rdc-public.php';
        require_once RDC_PLUGIN_DIR . 'public/class-rdc-shortcodes.php';
    }

    /**
     * Set up localization
     */
    private function set_locale() {
        $i18n = new RDC_i18n();
        add_action('plugins_loaded', array($i18n, 'load_plugin_textdomain'));
    }

    /**
     * Register admin hooks
     */
    private function define_admin_hooks() {
        if (is_admin()) {
            $admin = new RDC_Admin();
            add_action('admin_menu', array($admin, 'add_admin_menu'));
            add_action('admin_init', array($admin, 'register_settings'));
            add_action('admin_enqueue_scripts', array($admin, 'enqueue_styles'));
            add_action('admin_enqueue_scripts', array($admin, 'enqueue_scripts'));
        }
    }

    /**
     * Register public hooks
     */
    private function define_public_hooks() {
        $public = new RDC_Public();
        add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));

        // Tawk.to integration
        $tawkto = new RDC_Tawkto_Integration();
        add_action('wp_footer', array($tawkto, 'render_widget'));

        // Register shortcodes
        $shortcodes = new RDC_Shortcodes();
        add_action('init', array($shortcodes, 'register'));
    }

    /**
     * Register REST API routes
     */
    private function register_rest_api() {
        add_action('rest_api_init', function() {
            $reviews = new RDC_Reviews_Controller();
            $reviews->register_routes();

            $gallery = new RDC_Gallery_Controller();
            $gallery->register_routes();

            $availability = new RDC_Availability_Controller();
            $availability->register_routes();

            $appointments = new RDC_Appointments_Controller();
            $appointments->register_routes();
        });
    }
}

/**
 * Activation hook
 */
register_activation_hook(__FILE__, array('RDC_Activator', 'activate'));

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, array('RDC_Deactivator', 'deactivate'));

/**
 * Initialize plugin
 */
function rotary_dialysis_core() {
    return Rotary_Dialysis_Core::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'rotary_dialysis_core');
