<?php
/**
 * @module-title: Archive Terms List
 * @module-version: 1.0.0
 * @module-description: Display taxonomy terms from the current archive as a styled list with icons
 * @module-usage: Drag and drop the "Archive Terms List" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_archive_terms_list_widget');
function tbp_register_archive_terms_list_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Archive_Terms_List());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_archive_terms_list_enqueue_assets');
function tbp_archive_terms_list_enqueue_assets() {
    wp_register_style(
        'tbp-archive-terms-list',
        TBP_CORE_URL . 'inc/elementor-widgets/archive-terms-list/assets/css/archive-terms-list.css',
        [],
        TBP_CORE_VERSION
    );
}
