<?php
/**
 * @module-title: List Childrens
 * @module-version: 1.0.0
 * @module-description: Dynamic list widget that displays children of the current post
 * @module-usage: Drag and drop the "List Childrens" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_list_childrens_widget');
function tbp_register_list_childrens_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\List_Childrens());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_list_childrens_enqueue_assets');
function tbp_list_childrens_enqueue_assets() {
    wp_register_style(
        'tbp-list-childrens',
        TBP_CORE_URL . 'inc/elementor-widgets/list-childrens/assets/css/list-childrens.css',
        [],
        TBP_CORE_VERSION
    );
}
