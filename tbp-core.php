<?php
/**
 * Plugin Name: TBP Core
 * Plugin URI: https://tbp.al
 * Description: Core functionality for TBP - Functions, Queries, Elementor Widgets, and Dynamic Tags
 * Version: 1.0.4
 * Author: Trusted Business Partners
 * Author URI: https://tbp.al
 * Text Domain: tbp-core
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TBP_CORE_VERSION', '1.0.4');
define('TBP_CORE_FILE', __FILE__);
define('TBP_CORE_PATH', plugin_dir_path(__FILE__));
define('TBP_CORE_URL', plugin_dir_url(__FILE__));
define('TBP_CORE_ASSETS_URL', TBP_CORE_URL . 'assets/');
define('TBP_CORE_ADMIN_URL', TBP_CORE_URL . 'admin/');

/**
 * Main TBP Core Class
 */
final class TBP_Core {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->init_hooks();
        $this->init_updater();
    }

    /**
     * Initialize GitHub updater
     */
    private function init_updater() {
        require_once TBP_CORE_PATH . 'admin/update/class-github-updater.php';
        new TBP_GitHub_Updater();
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init'], 0);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('admin_init', [$this, 'handle_module_actions']);
    }

    public function init() {
        load_plugin_textdomain('tbp-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
        $this->load_modules();
    }

    private function load_modules() {
        $active_modules = get_option('tbp_core_active_modules', []);

        $module_types = ['functions', 'queries', 'elementor-widgets', 'dynamic-tags', 'acf-fields'];

        foreach ($module_types as $type) {
            if (!empty($active_modules[$type])) {
                foreach ($active_modules[$type] as $module_key => $status) {
                    if ($status === 'active') {
                        $this->load_module($type, $module_key);
                    }
                }
            }
        }
    }

    private function load_module($type, $module_key) {
        $init_file = TBP_CORE_PATH . "inc/{$type}/{$module_key}/init.php";
        if (file_exists($init_file)) {
            require_once $init_file;
        }
    }

    public function admin_menu() {
        // Custom SVG icon (base64 encoded)
        $icon_svg = 'data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDQ0NyAzMTciIHdpZHRoPSI0NDciIGhlaWdodD0iMzE3Ij4KCTxzdHlsZT4KCQkuczAgeyBmaWxsOiAjOWJhMmE3IH0gCgk8L3N0eWxlPgoJPGcgaWQ9IkxheWVyXzEtMiI+CgkJPGcgaWQ9Ikljb25fVjIiPgoJCQk8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsYXNzPSJzMCIgZD0ibTE3Ny40NiAyNC4wN3Y1Mi4xN2gtMTQ2LjQ2di01Mi4xN3oiLz4KCQkJPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGFzcz0iczAiIGQ9Im0yMDguMDMgMjR2MjY3aC02MS4xMXYtMjY3eiIvPgoJCQk8cGF0aCBjbGFzcz0iczAiIGQ9Im0yMDcuNjcgMjkwLjk0bDAuMDMtNDYuNTEgMTA4LjY1IDAuMDdxMTcuMjkgMC4wMSAyNy4yOS0xMCAxMC0xMC4wMiAxMC4wMS0yNS4xNCAwLjAxLTkuODMtNC42LTE3Ljc3LTQuNjEtNy45NS0xMi44OC0xMi40OS04LjI2LTQuNTQtMTkuNzgtNC41NWwtMjUtMC4wMSAwLjAzLTQ1LjM4IDIwIDAuMDFxMTQuNiAwLjAyIDIzLjgzLTcuMTYgOS4yMy03LjE4IDkuMjQtMjIuMzFjMC0xMC4wOS0zLjA3LTE2LjgzLTkuMjItMjEuNzVxLTkuMjItNy4zNy0yMy44Mi03LjM5bC0xMDMuNjUtMC4wNiAwLjAyLTQ2LjQ5IDExNS4xOCAwLjA2cTI2LjE0IDAuMDIgNDQuNTcgOS40OCAxOC40NCA5LjQ2IDI3Ljg2IDI1LjM1IDkuNCAxNS44OCA5LjM5IDM0Ljc5LTAuMDEgMjUuMzQtMTYuMTYgNDIuMTUtMTYuMTUgMTYuODItNDcuNjcgMjMuMjJsMS41NS0yMC4wNHEzNC41OCA2LjQ1IDUzLjAxIDI2LjEyIDE4LjQzIDE5LjY3IDE4LjQyIDQ4LjQxLTAuMDIgMjEuOTMtMTAuOTggMzkuMzEtMTAuOTYgMTcuMzktMzEuNTMgMjcuNzctMjAuNTcgMTAuMzktNDkuNCAxMC4zN2MwIDAtMTE0LjM5LTAuMDctMTE0LjM5LTAuMDZ6Ii8+CgkJCTxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xhc3M9InMwIiBkPSJtMjg3LjEyIDEyOS4xN2wtNzQuNi0wLjU3djQ1LjczbDc0LjYgMC4yMnYtNDUuMzh6Ii8+CgkJPC9nPgoJPC9nPgo8L3N2Zz4=';

        // TBP Core Modules page (main menu)
        add_menu_page(
            __('TBP Core', 'tbp-core'),
            __('TBP Core', 'tbp-core'),
            'manage_options',
            'tbp-core',
            [$this, 'admin_page'],
            $icon_svg,
            59
        );

        // Modules submenu (same as parent)
        add_submenu_page(
            'tbp-core',
            __('Modules', 'tbp-core'),
            __('Modules', 'tbp-core'),
            'manage_options',
            'tbp-core',
            [$this, 'admin_page']
        );

        // Settings submenu
        add_submenu_page(
            'tbp-core',
            __('Settings', 'tbp-core'),
            __('Settings', 'tbp-core'),
            'manage_options',
            'tbp-core-settings',
            [$this, 'settings_page']
        );
    }

    public function admin_page() {
        require_once TBP_CORE_PATH . 'admin/admin-page.php';
    }

    public function settings_page() {
        require_once TBP_CORE_PATH . 'admin/settings-page.php';
    }

    public function admin_enqueue_scripts($hook) {
        // Enqueue on both modules and settings pages
        if (!in_array($hook, ['toplevel_page_tbp-core', 'tbp-core_page_tbp-core-settings'])) {
            return;
        }

        wp_enqueue_style('tbp-core-admin', TBP_CORE_ADMIN_URL . 'css/admin.css', [], TBP_CORE_VERSION);
        wp_enqueue_script('tbp-core-admin', TBP_CORE_ADMIN_URL . 'js/admin.js', ['jquery'], TBP_CORE_VERSION, true);

        wp_localize_script('tbp-core-admin', 'tbpCoreAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_core_admin_nonce'),
        ]);
    }

    public function handle_module_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'tbp-core') {
            return;
        }

        if (!isset($_GET['action']) || !isset($_GET['module'])) {
            return;
        }

        $action = sanitize_text_field($_GET['action']);
        $module_key = sanitize_text_field($_GET['module']);
        $module_type = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'functions';

        if (!in_array($action, ['activate', 'deactivate'])) {
            return;
        }

        check_admin_referer('tbp_module_action_' . $module_key);

        $active_modules = get_option('tbp_core_active_modules', []);

        if (!isset($active_modules[$module_type])) {
            $active_modules[$module_type] = [];
        }

        if ($action === 'activate') {
            $active_modules[$module_type][$module_key] = 'active';
        } else {
            $active_modules[$module_type][$module_key] = 'inactive';
            $hook_name = 'tbp_' . str_replace('-', '_', $module_key) . '_deactivate';
            do_action($hook_name);
        }

        update_option('tbp_core_active_modules', $active_modules);

        $redirect_url = remove_query_arg(['action', 'module', '_wpnonce']);
        $redirect_url = add_query_arg('message', $action === 'activate' ? 'activated' : 'deactivated', $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }
}

TBP_Core::instance();
