<?php
/**
 * @module-title: AI Assistant
 * @module-version: 1.0.0
 * @module-description: AI-powered content assistant using Gemini API for CPT enhancement
 * @module-usage: Configure API key in Settings > AI Settings, then use AI features in enabled post types
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define module constants
define('TBP_AI_PATH', plugin_dir_path(__FILE__));
define('TBP_AI_URL', plugin_dir_url(__FILE__));
define('TBP_AI_VERSION', '1.0.0');

/**
 * AI Assistant Module
 */
class TBP_AI_Assistant {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once TBP_AI_PATH . 'class-settings.php';
        require_once TBP_AI_PATH . 'class-ai-handler.php';
    }

    private function init_hooks() {
        // Initialize settings page
        TBP_AI_Settings::instance();

        // Initialize AI handler
        TBP_AI_Handler::instance();

        // Enqueue assets for enabled post types
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Register post meta for language preference
        add_action('init', [$this, 'register_post_meta']);
    }

    /**
     * Register post meta for AI settings
     */
    public function register_post_meta() {
        $enabled_cpts = get_option('tbp_ai_enabled_cpts', []);

        foreach ($enabled_cpts as $post_type) {
            register_post_meta($post_type, '_tbp_ai_language', [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'default' => '',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                }
            ]);
        }
    }

    /**
     * Enqueue admin assets for enabled post types
     */
    public function enqueue_admin_assets($hook) {
        // Only on post edit screens
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        $screen = get_current_screen();
        $enabled_cpts = get_option('tbp_ai_enabled_cpts', []);

        // Check if current post type is enabled
        if (!in_array($screen->post_type, $enabled_cpts)) {
            return;
        }

        // Check if API key is configured
        $api_key = get_option('tbp_ai_api_key', '');
        if (empty($api_key)) {
            return;
        }

        wp_enqueue_style(
            'tbp-ai-editor',
            TBP_AI_URL . 'assets/css/ai-editor.css',
            [],
            TBP_AI_VERSION
        );

        wp_enqueue_script(
            'tbp-ai-editor',
            TBP_AI_URL . 'assets/js/ai-editor.js',
            ['jquery', 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-blocks'],
            TBP_AI_VERSION,
            true
        );

        wp_localize_script('tbp-ai-editor', 'tbpAI', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_ai_nonce'),
            'postId' => get_the_ID(),
            'postType' => $screen->post_type,
            'defaultLanguage' => get_option('tbp_ai_default_language', 'en'),
            'languages' => TBP_AI_Settings::get_available_languages(),
            'strings' => [
                'generating' => __('Generating...', 'tbp-core'),
                'enhance' => __('Enhance with AI', 'tbp-core'),
                'error' => __('An error occurred. Please try again.', 'tbp-core'),
                'noApiKey' => __('Please configure your Gemini API key in Settings > AI Settings.', 'tbp-core'),
            ],
        ]);

        // ACF Integration
        if (class_exists('ACF')) {
            wp_enqueue_script(
                'tbp-ai-acf',
                TBP_AI_URL . 'assets/js/ai-acf.js',
                ['jquery', 'acf-input'],
                TBP_AI_VERSION,
                true
            );
        }
    }

    /**
     * Check if AI is configured and ready
     */
    public static function is_configured() {
        $api_key = get_option('tbp_ai_api_key', '');
        return !empty($api_key);
    }

    /**
     * Get enabled CPTs
     */
    public static function get_enabled_cpts() {
        return get_option('tbp_ai_enabled_cpts', []);
    }
}

// Initialize module
TBP_AI_Assistant::instance();

// Deactivation hook
add_action('tbp_ai_assistant_deactivate', function() {
    // Cleanup if needed
});
