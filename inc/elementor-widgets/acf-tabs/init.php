<?php
/**
 * @module-title: ACF Tabs
 * @module-version: 1.0.0
 * @module-description: Display ACF tab fields as interactive tabs with auto-detection, horizontal/vertical layouts, and sticky support
 * @module-usage: Drag and drop the "ACF Tabs" widget from Elementor panel. It auto-detects ACF tab fields from the current post.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register widget
add_action('elementor/widgets/register', function($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\ACF_Tabs());
});

// Register styles
add_action('elementor/frontend/after_enqueue_styles', function() {
    wp_enqueue_style(
        'tbp-acf-tabs',
        plugin_dir_url(__FILE__) . 'assets/css/acf-tabs.css',
        [],
        TBP_CORE_VERSION
    );
});

// Register scripts
add_action('elementor/frontend/after_enqueue_scripts', function() {
    wp_enqueue_script(
        'tbp-acf-tabs',
        plugin_dir_url(__FILE__) . 'assets/js/acf-tabs.js',
        ['jquery'],
        TBP_CORE_VERSION,
        true
    );
});
