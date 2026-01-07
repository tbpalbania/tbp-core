<?php
/**
 * @module-title: Staff Timeline
 * @module-version: 1.0.0
 * @module-description: Display staff education and experience in a beautiful timeline layout
 * @module-usage: Add to Elementor pages to show staff member credentials
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register widget
add_action('elementor/widgets/register', function($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Staff_Timeline_Widget());
});

// Register widget category
add_action('elementor/elements/categories_registered', function($elements_manager) {
    $elements_manager->add_category('tbp-staff', [
        'title' => __('TBP Staff', 'tbp-core'),
        'icon'  => 'fa fa-users',
    ]);
});

// Frontend styles
add_action('elementor/frontend/after_enqueue_styles', function() {
    wp_enqueue_style(
        'tbp-staff-timeline',
        TBP_CORE_URL . 'inc/elementor-widgets/staff-timeline/assets/css/style.css',
        [],
        TBP_CORE_VERSION
    );
});
