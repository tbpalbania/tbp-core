<?php
/**
 * Condition: Not Edited with Elementor
 * Matches pages/posts that are NOT edited with Elementor builder
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Condition_Not_Elementor extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

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
        return 'not_elementor';
    }

    /**
     * Get condition label
     */
    public function get_label() {
        return __('Not Edited with Elementor', 'tbp-core');
    }

    /**
     * Get all label
     */
    public function get_all_label() {
        return __('All Non-Elementor Pages', 'tbp-core');
    }

    /**
     * Check condition
     * Returns true if the current page/post is NOT edited with Elementor
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        // Check if NOT edited with Elementor
        return !TBP_Elementor_Conditions::is_edited_with_elementor($post_id);
    }
}
