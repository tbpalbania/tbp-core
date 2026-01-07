<?php
/**
 * @module-title: Dynamic Icon
 * @module-version: 1.0.0
 * @module-description: Icon widget with dynamic ACF field support for icons from current post
 * @module-usage: Drag and drop the "Dynamic Icon" widget from Elementor panel, select ACF field as source
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('elementor/widgets/register', 'tbp_register_dynamic_icon_widget');
function tbp_register_dynamic_icon_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Dynamic_Icon());
}

add_action('wp_enqueue_scripts', 'tbp_dynamic_icon_enqueue_assets');
function tbp_dynamic_icon_enqueue_assets() {
    wp_register_style(
        'tbp-dynamic-icon',
        TBP_CORE_URL . 'inc/elementor-widgets/dynamic-icon/assets/css/dynamic-icon.css',
        [],
        TBP_CORE_VERSION
    );
}
