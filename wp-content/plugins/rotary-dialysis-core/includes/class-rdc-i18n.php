<?php
/**
 * Internationalization
 *
 * Loads and defines the internationalization files for this plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_i18n {

    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'rotary-dialysis-core',
            false,
            dirname(RDC_PLUGIN_BASENAME) . '/languages/'
        );
    }
}
