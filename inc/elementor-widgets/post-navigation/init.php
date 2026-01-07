<?php
/**
 * @module-title: Post Navigation
 * @module-version: 1.0.0
 * @module-description: Beautiful previous/next post navigation for single posts
 * @module-usage: Drag and drop the "Post Navigation" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_post_navigation_widget');
function tbp_register_post_navigation_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Post_Navigation());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_post_navigation_enqueue_assets');
function tbp_post_navigation_enqueue_assets() {
    wp_register_style(
        'tbp-post-navigation',
        TBP_CORE_URL . 'inc/elementor-widgets/post-navigation/assets/css/post-navigation.css',
        [],
        TBP_CORE_VERSION
    );
}
