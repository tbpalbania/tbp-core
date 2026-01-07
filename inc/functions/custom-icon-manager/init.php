<?php
/**
 * @module-title: Custom Icon Manager
 * @module-version: 1.0.1
 * @module-description: Manage custom icon packs for Elementor and ACF with 16 icon libraries (20,000+ icons)
 * @module-usage: Activate module, then go to TBP Core > Icons to activate/deactivate icon packs
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_ICONS_PATH', __DIR__ . '/');
define('TBP_ICONS_URL', TBP_CORE_URL . 'inc/functions/custom-icon-manager/');
define('TBP_ICONS_ASSETS_URL', TBP_ICONS_URL . 'assets/');

require_once TBP_ICONS_PATH . 'includes/class-icon-manager.php';

TBP_Icon_Manager::instance();

add_action('admin_menu', 'tbp_icons_add_submenu', 20);
function tbp_icons_add_submenu() {
    add_submenu_page(
        'tbp-core',
        __('Icons', 'tbp-core'),
        __('Icons', 'tbp-core'),
        'manage_options',
        'tbp-icons',
        'tbp_icons_admin_page'
    );
}

function tbp_icons_admin_page() {
    require_once TBP_ICONS_PATH . 'admin/admin-page.php';
}

add_action('elementor/icons_manager/additional_tabs', 'tbp_register_icon_packs');
function tbp_register_icon_packs($tabs) {
    $active_packs = get_option('tbp_active_icon_packs', []);

    if (empty($active_packs)) {
        return $tabs;
    }

    foreach ($active_packs as $pack_key => $status) {
        if ($status === 'active') {
            $pack_file = TBP_ICONS_PATH . 'icon-packs/' . $pack_key . '/init.php';
            if (file_exists($pack_file)) {
                $pack_tabs = require $pack_file;
                if (is_array($pack_tabs)) {
                    $tabs = array_merge($tabs, $pack_tabs);
                }
            }
        }
    }

    return $tabs;
}
