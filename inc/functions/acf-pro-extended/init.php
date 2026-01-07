<?php
/**
 * @module-title: ACF Pro Extended
 * @module-description: Extends ACF Pro with custom field types and enhancements. Enables the ACF Fields tab for managing custom fields.
 * @module-version: 1.0.0
 * @module-usage: Enable this module to access custom ACF field types. Manage individual fields from the ACF Fields tab.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define module constants
define('TBP_ACF_EXTENDED_PATH', plugin_dir_path(__FILE__));
define('TBP_ACF_EXTENDED_URL', plugin_dir_url(__FILE__));
define('TBP_ACF_EXTENDED_VERSION', '1.0.0');

/**
 * ACF Pro Extended Module
 */
class TBP_ACF_Pro_Extended {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->init();
    }

    public static function init() {
        // Check if ACF Pro is active
        if (!class_exists('ACF')) {
            add_action('admin_notices', [__CLASS__, 'acf_missing_notice']);
            return;
        }

        // Load active ACF fields
        add_action('acf/include_field_types', [__CLASS__, 'load_acf_fields']);
    }

    /**
     * Show notice if ACF Pro is not active
     */
    public static function acf_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('ACF Pro Extended requires ACF Pro to be installed and activated.', 'tbp-core'); ?></p>
        </div>
        <?php
    }

    /**
     * Load active ACF field types
     */
    public static function load_acf_fields() {
        $active_modules = get_option('tbp_core_active_modules', []);

        if (empty($active_modules['acf-fields'])) {
            return;
        }

        foreach ($active_modules['acf-fields'] as $field_key => $status) {
            if ($status === 'active') {
                $field_file = TBP_CORE_PATH . "inc/acf-fields/{$field_key}/init.php";
                if (file_exists($field_file)) {
                    require_once $field_file;
                }
            }
        }
    }

    /**
     * Check if ACF Pro Extended is active
     */
    public static function is_active() {
        $active_modules = get_option('tbp_core_active_modules', []);
        return isset($active_modules['functions']['acf-pro-extended'])
            && $active_modules['functions']['acf-pro-extended'] === 'active';
    }
}

// Initialize
TBP_ACF_Pro_Extended::instance();

// Deactivation hook
add_action('tbp_acf_pro_extended_deactivate', function() {
    // Cleanup if needed
});
