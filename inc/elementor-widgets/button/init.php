<?php
/**
 * @module-title: Button
 * @module-version: 1.0.0
 * @module-description: Advanced button widget with size presets and variants, configurable via Site Settings
 * @module-usage: Drag and drop the "Button" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_button_widget');
function tbp_register_button_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Button());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_button_enqueue_assets');
function tbp_button_enqueue_assets() {
    wp_register_style(
        'tbp-button',
        TBP_CORE_URL . 'inc/elementor-widgets/button/assets/css/button.css',
        [],
        TBP_CORE_VERSION
    );
}

// Register Site Settings
add_action('elementor/kit/register_tabs', 'tbp_register_button_site_settings', 20);
function tbp_register_button_site_settings($kit) {
    require_once __DIR__ . '/site-settings.php';
    $kit->register_tab('tbp-buttons', \TBP_Core\Settings\Button_Settings::class);
}
