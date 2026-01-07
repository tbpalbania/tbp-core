<?php
/**
 * @module-title: Search Results Grid
 * @module-version: 1.0.0
 * @module-description: Responsive grid of search results grouped by post type
 * @module-usage: Use in Search Results Elementor template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_search_results_grid_widget');
function tbp_register_search_results_grid_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Search_Results_Grid());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_search_results_grid_enqueue_assets');
function tbp_search_results_grid_enqueue_assets() {
    wp_register_style(
        'tbp-search-results-grid',
        TBP_CORE_URL . 'inc/elementor-widgets/search-results-grid/assets/css/search-results-grid.css',
        [],
        TBP_CORE_VERSION
    );
}
