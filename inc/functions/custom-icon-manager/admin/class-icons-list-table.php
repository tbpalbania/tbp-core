<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TBP_Icons_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct([
            'singular' => 'icon_pack',
            'plural' => 'icon_packs',
            'ajax' => false,
        ]);
    }

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'name' => __('Icon Pack', 'tbp-core'),
            'count' => __('Icons Count', 'tbp-core'),
            'version' => __('Version', 'tbp-core'),
            'status' => __('Status', 'tbp-core'),
        ];
    }

    protected function get_bulk_actions() {
        return [
            'activate' => __('Activate', 'tbp-core'),
            'deactivate' => __('Deactivate', 'tbp-core'),
        ];
    }

    protected function get_views() {
        $status_links = [];
        $current = isset($_GET['status']) ? $_GET['status'] : 'all';

        $packs = TBP_Icon_Manager::get_icon_packs();
        $active_packs = get_option('tbp_active_icon_packs', []);

        $total = count($packs);
        $active_count = 0;
        $inactive_count = 0;

        foreach ($packs as $key => $pack) {
            $status = isset($active_packs[$key]) && $active_packs[$key] === 'active' ? 'active' : 'inactive';
            if ($status === 'active') {
                $active_count++;
            } else {
                $inactive_count++;
            }
        }

        $class = $current === 'all' ? 'current' : '';
        $all_url = remove_query_arg('status');
        $status_links['all'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url($all_url),
            $class,
            __('All', 'tbp-core'),
            number_format_i18n($total)
        );

        $class = $current === 'active' ? 'current' : '';
        $active_url = add_query_arg('status', 'active');
        $status_links['active'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url($active_url),
            $class,
            __('Active', 'tbp-core'),
            number_format_i18n($active_count)
        );

        $class = $current === 'inactive' ? 'current' : '';
        $inactive_url = add_query_arg('status', 'inactive');
        $status_links['inactive'] = sprintf(
            '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>',
            esc_url($inactive_url),
            $class,
            __('Inactive', 'tbp-core'),
            number_format_i18n($inactive_count)
        );

        return $status_links;
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="packs[]" value="%s" />',
            esc_attr($item['key'])
        );
    }

    public function column_name($item) {
        $page = 'tbp-icons';
        $actions = [];

        if ($item['status'] === 'active') {
            $nonce = wp_create_nonce('tbp_icon_pack_action_' . $item['key']);
            $url = add_query_arg([
                'page' => $page,
                'action' => 'deactivate',
                'pack' => $item['key'],
                '_wpnonce' => $nonce,
            ], admin_url('admin.php'));
            $actions['deactivate'] = sprintf('<a href="%s">%s</a>', esc_url($url), __('Deactivate', 'tbp-core'));
        } else {
            $nonce = wp_create_nonce('tbp_icon_pack_action_' . $item['key']);
            $url = add_query_arg([
                'page' => $page,
                'action' => 'activate',
                'pack' => $item['key'],
                '_wpnonce' => $nonce,
            ], admin_url('admin.php'));
            $actions['activate'] = sprintf('<a href="%s">%s</a>', esc_url($url), __('Activate', 'tbp-core'));
        }

        return sprintf('<strong>%s</strong>%s', esc_html($item['name']), $this->row_actions($actions));
    }

    public function column_status($item) {
        if ($item['status'] === 'active') {
            return '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ' . __('Active', 'tbp-core');
        }
        return '<span class="dashicons dashicons-minus" style="color: #dcdcde;"></span> ' . __('Inactive', 'tbp-core');
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'count':
            case 'version':
                return esc_html($item[$column_name]);
            default:
                return '';
        }
    }

    public function no_items() {
        echo esc_html__('No icon packs found.', 'tbp-core');
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->process_bulk_action();

        $packs = TBP_Icon_Manager::get_icon_packs();
        $active_packs = get_option('tbp_active_icon_packs', []);

        $items = [];
        foreach ($packs as $pack_key => $pack) {
            $status = isset($active_packs[$pack_key]) && $active_packs[$pack_key] === 'active' ? 'active' : 'inactive';

            $items[] = [
                'key' => $pack_key,
                'name' => $pack['name'],
                'count' => $pack['count'],
                'version' => $pack['version'],
                'status' => $status,
            ];
        }

        // Search filter
        $search = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : '';
        if (!empty($search)) {
            $items = array_filter($items, function($item) use ($search) {
                return stripos($item['name'], $search) !== false ||
                       stripos($item['key'], $search) !== false;
            });
        }

        // Status filter
        $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
        if ($status_filter !== 'all') {
            $items = array_filter($items, function($item) use ($status_filter) {
                return $item['status'] === $status_filter;
            });
        }

        // Pagination
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($items);

        $items = array_slice($items, (($current_page - 1) * $per_page), $per_page);

        $this->items = $items;

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function process_bulk_action() {
        if (!isset($_POST['packs']) || !is_array($_POST['packs'])) {
            return;
        }

        $action = $this->current_action();
        if (!$action || !in_array($action, ['activate', 'deactivate'])) {
            return;
        }

        check_admin_referer('bulk-' . $this->_args['plural']);

        $packs = array_map('sanitize_text_field', $_POST['packs']);
        $active_packs = get_option('tbp_active_icon_packs', []);

        foreach ($packs as $pack_key) {
            if ($action === 'activate') {
                $active_packs[$pack_key] = 'active';
            } else {
                $active_packs[$pack_key] = 'inactive';
            }
        }

        update_option('tbp_active_icon_packs', $active_packs);
    }
}
