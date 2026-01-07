<?php
/**
 * TBP Condition Group
 * Parent group for all TBP custom conditions
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Condition_Group extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    /**
     * Get condition group type
     * Must return 'singular' for conditions to appear in singular display conditions
     */
    public static function get_type() {
        return 'singular';
    }

    /**
     * Get condition name
     */
    public function get_name() {
        return 'tbp';
    }

    /**
     * Get condition label
     */
    public function get_label() {
        return __('TBP Conditions', 'tbp-core');
    }

    /**
     * Get all label (for "All TBP Conditions")
     */
    public function get_all_label() {
        return __('All TBP Conditions', 'tbp-core');
    }

    /**
     * Register sub-conditions
     */
    public function register_sub_conditions() {
        // Editor Type (Gutenberg, Classic, Elementor)
        $this->register_sub_condition(new TBP_Condition_Editor_Type());

        // Not Edited with Elementor (legacy/shortcut)
        $this->register_sub_condition(new TBP_Condition_Not_Elementor());

        // Page Content (Empty/Has Content)
        $this->register_sub_condition(new TBP_Condition_Page_Content());

        // Portal Pages
        $this->register_sub_condition(new TBP_Condition_Portal_Pages());
    }

    /**
     * Check condition
     */
    public function check($args) {
        // Parent group always returns false (sub-conditions do the work)
        return false;
    }
}
