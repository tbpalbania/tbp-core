<?php
/**
 * @module-title: Business Hours
 * @module-version: 1.0.0
 * @module-description: Display business hours with customizable day/time columns
 * @module-usage: Drag and drop the "Business Hours" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('elementor/widgets/register', 'tbp_register_business_hours_widget');
function tbp_register_business_hours_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Business_Hours());
}

add_action('wp_enqueue_scripts', 'tbp_business_hours_enqueue_assets');
function tbp_business_hours_enqueue_assets() {
    wp_register_style(
        'tbp-business-hours',
        TBP_CORE_URL . 'inc/elementor-widgets/business-hours/assets/css/business-hours.css',
        [],
        TBP_CORE_VERSION
    );
}
