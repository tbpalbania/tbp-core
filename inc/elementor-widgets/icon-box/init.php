<?php
/**
 * @module-title: Icon Box
 * @module-version: 1.0.0
 * @module-description: Icon Box widget with gradient background support for stacked icons
 * @module-usage: Drag and drop the "Icon Box" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('elementor/widgets/register', 'tbp_register_icon_box_widget');
function tbp_register_icon_box_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Icon_Box());
}

add_action('wp_enqueue_scripts', 'tbp_icon_box_enqueue_assets');
function tbp_icon_box_enqueue_assets() {
    wp_register_style(
        'tbp-icon-box',
        TBP_CORE_URL . 'inc/elementor-widgets/icon-box/assets/css/icon-box.css',
        [],
        TBP_CORE_VERSION
    );
}
