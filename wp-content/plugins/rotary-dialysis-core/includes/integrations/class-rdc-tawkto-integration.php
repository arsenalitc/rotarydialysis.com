<?php
/**
 * Tawk.to Integration
 *
 * Integrates Tawk.to live chat widget.
 */

if (!defined('ABSPATH')) {
    exit;
}

class RDC_Tawkto_Integration {

    /**
     * Render the Tawk.to widget
     */
    public function render_widget() {
        // Check if enabled
        if (!get_option('rdc_tawkto_enabled', true)) {
            return;
        }

        $property_id = get_option('rdc_tawkto_property_id');
        $widget_id = get_option('rdc_tawkto_widget_id', 'default');

        if (empty($property_id)) {
            return;
        }

        // Don't load in admin
        if (is_admin()) {
            return;
        }
        ?>
        <!--Start of Tawk.to Script-->
        <script type="text/javascript">
        var Tawk_API = Tawk_API || {};
        var Tawk_LoadStart = new Date();

        <?php if (is_user_logged_in()): ?>
        // Pass user info to Tawk.to
        Tawk_API.visitor = {
            name: '<?php echo esc_js(wp_get_current_user()->display_name); ?>',
            email: '<?php echo esc_js(wp_get_current_user()->user_email); ?>'
        };
        <?php endif; ?>

        // Track page views for context
        Tawk_API.onLoad = function() {
            <?php if (is_singular()): ?>
            Tawk_API.setAttributes({
                'page': '<?php echo esc_js(get_the_title()); ?>',
                'url': '<?php echo esc_js(get_permalink()); ?>'
            }, function(error) {});
            <?php endif; ?>
        };

        (function() {
            var s1 = document.createElement("script");
            var s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/<?php echo esc_attr($property_id); ?>/<?php echo esc_attr($widget_id); ?>';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
        </script>
        <!--End of Tawk.to Script-->
        <?php
    }

    /**
     * Check if Tawk.to is configured
     */
    public static function is_configured() {
        $property_id = get_option('rdc_tawkto_property_id');
        return !empty($property_id);
    }

    /**
     * Get widget settings
     */
    public static function get_settings() {
        return array(
            'enabled' => get_option('rdc_tawkto_enabled', true),
            'property_id' => get_option('rdc_tawkto_property_id', ''),
            'widget_id' => get_option('rdc_tawkto_widget_id', 'default'),
        );
    }
}
