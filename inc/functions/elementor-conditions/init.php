<?php
/**
 * @module-title: Elementor Display Conditions
 * @module-version: 1.0.0
 * @module-description: Custom display conditions for Elementor Theme Builder
 * @module-usage: Requires Elementor Pro with Theme Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define module constants
define('TBP_ELEMENTOR_CONDITIONS_PATH', plugin_dir_path(__FILE__));
define('TBP_ELEMENTOR_CONDITIONS_URL', plugin_dir_url(__FILE__));
define('TBP_ELEMENTOR_CONDITIONS_VERSION', '1.0.0');

/**
 * Elementor Display Conditions Module
 */
class TBP_Elementor_Conditions {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        // Wait for Elementor Pro to load before registering conditions
        add_action('elementor_pro/init', [$this, 'on_elementor_pro_init']);
    }

    /**
     * Called when Elementor Pro initializes
     */
    public function on_elementor_pro_init() {
        add_action('elementor/theme/register_conditions', [$this, 'register_conditions']);
    }

    /**
     * Register custom conditions
     */
    public function register_conditions($conditions_manager) {
        // Load condition classes
        require_once TBP_ELEMENTOR_CONDITIONS_PATH . 'includes/class-condition-group.php';
        require_once TBP_ELEMENTOR_CONDITIONS_PATH . 'includes/class-condition-not-elementor.php';
        require_once TBP_ELEMENTOR_CONDITIONS_PATH . 'includes/class-condition-portal-pages.php';
        require_once TBP_ELEMENTOR_CONDITIONS_PATH . 'includes/class-condition-editor-type.php';
        require_once TBP_ELEMENTOR_CONDITIONS_PATH . 'includes/class-condition-content.php';

        // Get the singular condition (for single posts/pages)
        $singular = $conditions_manager->get_condition('singular');
        if (!$singular) {
            return;
        }

        // Register TBP condition group under singular
        $singular->register_sub_condition(new TBP_Condition_Group());
    }

    /**
     * Get all portal page options
     */
    public static function get_portal_page_options() {
        $pages = [
            'tbp_portal_dashboard_page'       => __('Dashboard', 'tbp-core'),
            'tbp_portal_login_page'           => __('Login', 'tbp-core'),
            'tbp_portal_register_page'        => __('Register', 'tbp-core'),
            'tbp_portal_forgot_password_page' => __('Forgot Password', 'tbp-core'),
            'tbp_portal_lab_results_page'     => __('Lab Results', 'tbp-core'),
            'tbp_portal_lab_result_view_page' => __('Lab Result View', 'tbp-core'),
            'tbp_portal_my_account_page'      => __('My Account', 'tbp-core'),
            'tbp_portal_support_page'         => __('Support/Tickets', 'tbp-core'),
            'tbp_portal_testimonial_page'     => __('Testimonial', 'tbp-core'),
            'tbp_appointments_list_page'      => __('Appointments List', 'tbp-core'),
            'tbp_appointment_view_page'       => __('Appointment View', 'tbp-core'),
            'tbp_appointment_booking_page'    => __('Appointment Booking', 'tbp-core'),
        ];

        return apply_filters('tbp_portal_page_options', $pages);
    }

    /**
     * Get all portal page IDs
     */
    public static function get_portal_page_ids() {
        $page_options = self::get_portal_page_options();
        $page_ids = [];

        foreach (array_keys($page_options) as $option_name) {
            $page_id = get_option($option_name);
            if ($page_id) {
                $page_ids[] = (int) $page_id;
            }
        }

        return array_filter($page_ids);
    }

    /**
     * Check if a post is edited with Elementor
     */
    public static function is_edited_with_elementor($post_id) {
        if (!$post_id) {
            return false;
        }

        $edit_mode = get_post_meta($post_id, '_elementor_edit_mode', true);
        return $edit_mode === 'builder';
    }
}

// Initialize
TBP_Elementor_Conditions::instance();

// Deactivation hook
add_action('tbp_elementor_conditions_deactivate', function() {
    // Cleanup if needed
});
