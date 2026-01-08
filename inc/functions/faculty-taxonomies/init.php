<?php
/**
 * @module-title: Faculty, Department, Cycle & Profile Taxonomies
 * @module-version: 1.0.0
 * @module-description: Adds hierarchical taxonomies (Faculty → Department → Cycle → Profile) with chain selection. Linked to users via user meta.
 * @module-usage: User meta keys: tbp_academic_faculty, tbp_academic_department, tbp_academic_cycle, tbp_academic_profile. Use TBP_Chain_Selector::render() for custom forms, TBP_Chain_Selector::get_user_meta($user_id) or TBP_Chain_Selector::get_post_meta($post_id) to retrieve values.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_FACULTY_TAX_PATH', plugin_dir_path(__FILE__));
define('TBP_FACULTY_TAX_URL', plugin_dir_url(__FILE__));

// Load components
require_once TBP_FACULTY_TAX_PATH . 'includes/class-taxonomies.php';
require_once TBP_FACULTY_TAX_PATH . 'includes/class-chain-selector.php';
require_once TBP_FACULTY_TAX_PATH . 'includes/class-user-fields.php';

/**
 * Initialize the module
 */
class TBP_Faculty_Taxonomies {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Initialize components
        TBP_Academic_Taxonomies::instance();
        TBP_Chain_Selector::instance();
        TBP_Academic_User_Fields::instance();
    }
}

TBP_Faculty_Taxonomies::instance();
