<?php
/**
 * @module-title: Breadcrumbs
 * @module-version: 1.0.0
 * @module-description: SEO-friendly breadcrumbs with RankMath integration, CPT/taxonomy support, and ellipsis truncation
 * @module-usage: Drag and drop the "Breadcrumbs" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('elementor/widgets/register', 'tbp_register_breadcrumbs_widget');
function tbp_register_breadcrumbs_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Breadcrumbs());
}

add_action('wp_enqueue_scripts', 'tbp_breadcrumbs_enqueue_assets');
function tbp_breadcrumbs_enqueue_assets() {
    wp_register_style(
        'tbp-breadcrumbs',
        TBP_CORE_URL . 'inc/elementor-widgets/breadcrumbs/assets/css/breadcrumbs.css',
        [],
        TBP_CORE_VERSION
    );
}
