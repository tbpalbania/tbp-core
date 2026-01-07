<?php
/**
 * Condition: Portal Pages
 * Matches pages configured as patient portal pages
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Condition_Portal_Pages extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    /**
     * Get condition group type
     */
    public static function get_type() {
        return 'singular';
    }

    /**
     * Get condition name
     */
    public function get_name() {
        return 'portal_pages';
    }

    /**
     * Get condition label
     */
    public function get_label() {
        return __('Portal Pages', 'tbp-core');
    }

    /**
     * Get all label
     */
    public function get_all_label() {
        return __('All Portal Pages', 'tbp-core');
    }

    /**
     * Register sub-conditions for specific portal pages
     */
    public function register_sub_conditions() {
        $portal_pages = TBP_Elementor_Conditions::get_portal_page_options();

        foreach ($portal_pages as $option_name => $label) {
            // Create a dynamic sub-condition for each portal page
            $this->register_sub_condition(new TBP_Condition_Portal_Page_Single($option_name, $label));
        }
    }

    /**
     * Check condition - matches ANY portal page
     */
    public function check($args) {
        if (!is_singular('page')) {
            return false;
        }

        $current_page_id = get_queried_object_id();
        $portal_page_ids = TBP_Elementor_Conditions::get_portal_page_ids();

        return in_array($current_page_id, $portal_page_ids);
    }
}

/**
 * Single Portal Page Condition
 * Dynamically created for each portal page option
 */
class TBP_Condition_Portal_Page_Single extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    private $option_name;
    private $page_label;

    public function __construct($option_name = '', $label = '') {
        $this->option_name = $option_name;
        $this->page_label = $label;
        parent::__construct();
    }

    /**
     * Get condition group type
     */
    public static function get_type() {
        return 'singular';
    }

    /**
     * Get condition name
     */
    public function get_name() {
        // Convert option name to a valid condition name
        // e.g., 'tbp_portal_dashboard_page' -> 'portal_dashboard'
        $name = str_replace(['tbp_portal_', 'tbp_', '_page'], '', $this->option_name);
        return 'portal_' . $name;
    }

    /**
     * Get condition label
     */
    public function get_label() {
        return $this->page_label;
    }

    /**
     * Get all label
     */
    public function get_all_label() {
        return $this->page_label;
    }

    /**
     * Check condition - matches specific portal page
     */
    public function check($args) {
        if (!is_singular('page')) {
            return false;
        }

        $current_page_id = get_queried_object_id();
        $portal_page_id = get_option($this->option_name);

        return $portal_page_id && (int) $current_page_id === (int) $portal_page_id;
    }
}
