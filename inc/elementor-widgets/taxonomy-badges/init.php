<?php
/**
 * @module-title: Taxonomy Badges
 * @module-version: 1.0.0
 * @module-description: Display taxonomy terms as badges for the current post
 * @module-usage: Drag and drop the "Taxonomy Badges" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_taxonomy_badges_widget');
function tbp_register_taxonomy_badges_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Taxonomy_Badges());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_taxonomy_badges_enqueue_assets');
function tbp_taxonomy_badges_enqueue_assets() {
    wp_register_style(
        'tbp-taxonomy-badges',
        TBP_CORE_URL . 'inc/elementor-widgets/taxonomy-badges/assets/css/taxonomy-badges.css',
        [],
        TBP_CORE_VERSION
    );
}
