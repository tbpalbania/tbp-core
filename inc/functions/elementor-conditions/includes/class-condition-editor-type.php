<?php
/**
 * Condition: Editor Type
 * Matches pages based on which editor was used (Gutenberg, Classic, Elementor)
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent condition for Editor Type
 */
class TBP_Condition_Editor_Type extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'editor_type';
    }

    public function get_label() {
        return __('Editor Type', 'tbp-core');
    }

    public function get_all_label() {
        return __('Any Editor', 'tbp-core');
    }

    public function register_sub_conditions() {
        $this->register_sub_condition(new TBP_Condition_Gutenberg_Only());
        $this->register_sub_condition(new TBP_Condition_Classic_Only());
        $this->register_sub_condition(new TBP_Condition_Elementor_Only());
    }

    public function check($args) {
        return is_singular();
    }
}

/**
 * Gutenberg Only Condition
 */
class TBP_Condition_Gutenberg_Only extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'gutenberg_only';
    }

    public function get_label() {
        return __('Gutenberg (Block Editor)', 'tbp-core');
    }

    public function get_all_label() {
        return __('Gutenberg Pages', 'tbp-core');
    }

    /**
     * Check if page uses Gutenberg (has blocks)
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        // Must NOT be edited with Elementor
        if (TBP_Elementor_Conditions::is_edited_with_elementor($post_id)) {
            return false;
        }

        // Check if content has blocks
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        return has_blocks($post->post_content);
    }
}

/**
 * Classic Editor Only Condition
 */
class TBP_Condition_Classic_Only extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'classic_only';
    }

    public function get_label() {
        return __('Classic Editor', 'tbp-core');
    }

    public function get_all_label() {
        return __('Classic Editor Pages', 'tbp-core');
    }

    /**
     * Check if page uses Classic Editor (no blocks, no Elementor)
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        // Must NOT be edited with Elementor
        if (TBP_Elementor_Conditions::is_edited_with_elementor($post_id)) {
            return false;
        }

        // Must NOT have blocks (Gutenberg)
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        return !has_blocks($post->post_content);
    }
}

/**
 * Elementor Only Condition
 */
class TBP_Condition_Elementor_Only extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'elementor_only';
    }

    public function get_label() {
        return __('Elementor', 'tbp-core');
    }

    public function get_all_label() {
        return __('Elementor Pages', 'tbp-core');
    }

    /**
     * Check if page is edited with Elementor
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        return TBP_Elementor_Conditions::is_edited_with_elementor($post_id);
    }
}
