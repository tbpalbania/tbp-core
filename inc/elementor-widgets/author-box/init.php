<?php
/**
 * @module-title: Author Box
 * @module-version: 1.0.0
 * @module-description: Display current post author information with avatar, bio, and social links
 * @module-usage: Drag and drop the "Author Box" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Auto-load User Profile Fields dependency for social links and custom avatar
add_action('init', 'tbp_author_box_load_dependencies', 5);
function tbp_author_box_load_dependencies() {
    if (!class_exists('TBP_User_Profile_Fields')) {
        $user_profile_fields_path = TBP_CORE_PATH . 'inc/functions/user-profile-fields/init.php';
        if (file_exists($user_profile_fields_path)) {
            require_once $user_profile_fields_path;
        }
    }
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_author_box_widget');
function tbp_register_author_box_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Author_Box());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_author_box_enqueue_assets');
function tbp_author_box_enqueue_assets() {
    wp_register_style(
        'tbp-author-box',
        TBP_CORE_URL . 'inc/elementor-widgets/author-box/assets/css/author-box.css',
        [],
        TBP_CORE_VERSION
    );
}
