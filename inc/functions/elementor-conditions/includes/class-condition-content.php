<?php
/**
 * Condition: Page Content
 * Matches pages based on whether they have content or not
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parent condition for Page Content
 */
class TBP_Condition_Page_Content extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'page_content';
    }

    public function get_label() {
        return __('Page Content', 'tbp-core');
    }

    public function get_all_label() {
        return __('Any Page Content', 'tbp-core');
    }

    public function register_sub_conditions() {
        $this->register_sub_condition(new TBP_Condition_Content_Empty());
        $this->register_sub_condition(new TBP_Condition_Content_Has());
    }

    public function check($args) {
        return is_singular();
    }
}

/**
 * Content Empty Condition
 */
class TBP_Condition_Content_Empty extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'content_empty';
    }

    public function get_label() {
        return __('Empty Content', 'tbp-core');
    }

    public function get_all_label() {
        return __('Pages with Empty Content', 'tbp-core');
    }

    /**
     * Check if page content is empty
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        return self::is_content_empty($post_id);
    }

    /**
     * Check if post content is considered empty
     */
    public static function is_content_empty($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return true;
        }

        $content = $post->post_content;

        // Remove whitespace
        $content = trim($content);

        // Empty string
        if (empty($content)) {
            return true;
        }

        // Check for Elementor - if edited with Elementor, check Elementor data
        if (TBP_Elementor_Conditions::is_edited_with_elementor($post_id)) {
            $elementor_data = get_post_meta($post_id, '_elementor_data', true);
            if (empty($elementor_data) || $elementor_data === '[]') {
                return true;
            }
            // Has Elementor data, not empty
            return false;
        }

        // For Gutenberg - check if only empty blocks or no real content
        if (has_blocks($content)) {
            // Parse blocks and check if any have content
            $blocks = parse_blocks($content);
            return self::are_blocks_empty($blocks);
        }

        // Classic editor - check if content is just whitespace/empty tags
        $stripped = wp_strip_all_tags($content);
        $stripped = preg_replace('/\s+/', '', $stripped);

        return empty($stripped);
    }

    /**
     * Check if all blocks are empty
     */
    private static function are_blocks_empty($blocks) {
        foreach ($blocks as $block) {
            // Skip null blocks (usually spacing between blocks)
            if (empty($block['blockName'])) {
                continue;
            }

            // Check inner blocks recursively
            if (!empty($block['innerBlocks'])) {
                if (!self::are_blocks_empty($block['innerBlocks'])) {
                    return false;
                }
            }

            // Check if block has content in innerHTML
            if (!empty($block['innerHTML'])) {
                $inner = wp_strip_all_tags($block['innerHTML']);
                $inner = preg_replace('/\s+/', '', $inner);
                if (!empty($inner)) {
                    return false;
                }
            }

            // Some blocks have content in attributes (like image, video, etc.)
            $content_blocks = ['core/image', 'core/video', 'core/audio', 'core/file', 'core/embed', 'core/shortcode'];
            if (in_array($block['blockName'], $content_blocks)) {
                if (!empty($block['attrs'])) {
                    return false;
                }
            }
        }

        return true;
    }
}

/**
 * Has Content Condition
 */
class TBP_Condition_Content_Has extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    public static function get_type() {
        return 'singular';
    }

    public function get_name() {
        return 'content_has';
    }

    public function get_label() {
        return __('Has Content', 'tbp-core');
    }

    public function get_all_label() {
        return __('Pages with Content', 'tbp-core');
    }

    /**
     * Check if page has content
     */
    public function check($args) {
        if (!is_singular()) {
            return false;
        }

        $post_id = get_queried_object_id();
        if (!$post_id) {
            return false;
        }

        // Opposite of empty
        return !TBP_Condition_Content_Empty::is_content_empty($post_id);
    }
}
