<?php
/**
 * @module-title: Slinky Menu
 * @module-version: 1.0.0
 * @module-description: Responsive sliding navigation menu for mobile devices
 * @module-usage: Use for mobile navigation with nested menu support
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_slinky_menu_widget');
function tbp_register_slinky_menu_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Slinky_Menu());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_slinky_menu_enqueue_assets');
function tbp_slinky_menu_enqueue_assets() {
    wp_register_style(
        'tbp-slinky-menu',
        TBP_CORE_URL . 'inc/elementor-widgets/slinky-menu/assets/css/slinky-menu.css',
        [],
        TBP_CORE_VERSION
    );

    wp_register_script(
        'tbp-slinky-menu',
        TBP_CORE_URL . 'inc/elementor-widgets/slinky-menu/assets/js/slinky-menu.js',
        [],
        TBP_CORE_VERSION,
        true
    );
}
