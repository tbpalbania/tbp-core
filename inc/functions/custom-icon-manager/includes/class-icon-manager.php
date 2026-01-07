<?php
if (!defined('ABSPATH')) {
    exit;
}

class TBP_Icon_Manager {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('admin_init', [$this, 'handle_icon_pack_actions']);
    }

    public static function get_icon_packs() {
        return [
            'elegant-icons' => [
                'name' => 'Elegant Icons',
                'count' => '360+',
                'version' => '1.0.0',
            ],
            'themify-icons' => [
                'name' => 'Themify Icons',
                'count' => '320+',
                'version' => '1.0.0',
            ],
            'linearicons' => [
                'name' => 'Linearicons',
                'count' => '170+',
                'version' => '1.0.0',
            ],
            'lineawesome' => [
                'name' => 'Line Awesome',
                'count' => '1540+',
                'version' => '1.0.0',
            ],
            'icofont' => [
                'name' => 'IcoFont',
                'count' => '2100+',
                'version' => '1.0.0',
            ],
            'materialdesign' => [
                'name' => 'Material Design',
                'count' => '4200+',
                'version' => '1.0.0',
            ],
            'ion-icons' => [
                'name' => 'Ion Icons',
                'count' => '696+',
                'version' => '1.0.0',
            ],
            'line-icons' => [
                'name' => 'Line Icons',
                'count' => '266+',
                'version' => '1.0.0',
            ],
            'icomoon' => [
                'name' => 'IcoMoon',
                'count' => '490+',
                'version' => '1.0.0',
            ],
            'iconic' => [
                'name' => 'Iconic',
                'count' => '223+',
                'version' => '1.0.0',
            ],
            'open-iconic' => [
                'name' => 'Open Iconic',
                'count' => '223+',
                'version' => '1.0.0',
            ],
            'simpleline' => [
                'name' => 'Simple Line',
                'count' => '189+',
                'version' => '1.0.0',
            ],
            'elusive' => [
                'name' => 'Elusive',
                'count' => '304+',
                'version' => '1.0.0',
            ],
            'devicons' => [
                'name' => 'Devicons',
                'count' => '192+',
                'version' => '1.0.0',
            ],
            'brands' => [
                'name' => 'Brand Icons',
                'count' => '447+',
                'version' => '1.0.0',
            ],
            'tabler-icons' => [
                'name' => 'Tabler Icons',
                'count' => '6000+',
                'version' => '3.35.0',
            ],
        ];
    }

    public function handle_icon_pack_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'tbp-icons') {
            return;
        }

        if (!isset($_GET['action']) || !isset($_GET['pack'])) {
            return;
        }

        $action = sanitize_text_field($_GET['action']);
        $pack_key = sanitize_text_field($_GET['pack']);

        if (!in_array($action, ['activate', 'deactivate'])) {
            return;
        }

        check_admin_referer('tbp_icon_pack_action_' . $pack_key);

        $active_packs = get_option('tbp_active_icon_packs', []);

        if ($action === 'activate') {
            $active_packs[$pack_key] = 'active';
        } else {
            $active_packs[$pack_key] = 'inactive';
        }

        update_option('tbp_active_icon_packs', $active_packs);

        $redirect_url = remove_query_arg(['action', 'pack', '_wpnonce']);
        $redirect_url = add_query_arg('message', $action === 'activate' ? 'activated' : 'deactivated', $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }
}
