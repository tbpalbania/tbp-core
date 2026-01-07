<?php
/**
 * Reusable WP_List_Table component for TBP Core modules
 *
 * Usage:
 *   $list_table = new TBP_Modules_List_Table('functions');
 *   $list_table->prepare_items();
 *   $list_table->views();
 *   $list_table->display();
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TBP_Modules_List_Table extends WP_List_Table {

    private $module_type;

    public function __construct($module_type) {
        $this->module_type = $module_type;

        parent::__construct([
            'singular' => 'module',
            'plural' => 'modules',
            'ajax' => false,
        ]);

        add_action('wp_ajax_tbp_toggle_module', [$this, 'ajax_toggle_module']);
    }

    public function get_columns() {
        return [
            'cb' => '<input type="checkbox" />',
            'title' => __('Title', 'tbp-core'),
            'version' => __('Version', 'tbp-core'),
            'description' => __('Description', 'tbp-core'),
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

        $modules = tbp_core_get_modules($this->module_type);
        $active_modules = get_option('tbp_core_active_modules', []);

        $total = count($modules);
        $active_count = 0;
        $inactive_count = 0;

        foreach ($modules as $key => $module) {
            $status = isset($active_modules[$this->module_type][$key]) && $active_modules[$this->module_type][$key] === 'active' ? 'active' : 'inactive';
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
            '<input type="checkbox" name="modules[]" value="%s" />',
            esc_attr($item['key'])
        );
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        $this->process_bulk_action();

        $modules = tbp_core_get_modules($this->module_type);
        $active_modules = get_option('tbp_core_active_modules', []);

        $items = [];
        foreach ($modules as $module_key => $module) {
            $status = isset($active_modules[$this->module_type][$module_key]) && $active_modules[$this->module_type][$module_key] === 'active' ? 'active' : 'inactive';

            $items[] = [
                'key' => $module_key,
                'title' => $module['title'],
                'version' => $module['version'],
                'description' => $module['description'],
                'usage' => $module['usage'],
                'status' => $status,
            ];
        }

        $search = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : '';
        if (!empty($search)) {
            $items = array_filter($items, function($item) use ($search) {
                return stripos($item['title'], $search) !== false ||
                       stripos($item['description'], $search) !== false;
            });
        }

        $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
        if ($status_filter !== 'all') {
            $items = array_filter($items, function($item) use ($status_filter) {
                return $item['status'] === $status_filter;
            });
        }

        $per_page = 20;
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
        $modules = isset($_POST['modules']) ? $_POST['modules'] : (isset($_GET['modules']) ? $_GET['modules'] : null);

        if (!$modules || !is_array($modules)) {
            return;
        }

        $action = $this->current_action();
        if (!$action || !in_array($action, ['activate', 'deactivate'])) {
            return;
        }

        check_admin_referer('bulk-' . $this->_args['plural']);

        $modules = array_map('sanitize_text_field', $modules);
        $active_modules = get_option('tbp_core_active_modules', []);

        if (!isset($active_modules[$this->module_type])) {
            $active_modules[$this->module_type] = [];
        }

        foreach ($modules as $module_key) {
            if ($action === 'activate') {
                $active_modules[$this->module_type][$module_key] = 'active';
            } else {
                $active_modules[$this->module_type][$module_key] = 'inactive';
            }
        }

        update_option('tbp_core_active_modules', $active_modules);

        $count = count($modules);
        $message = $action === 'activate'
            ? sprintf(_n('%s module activated.', '%s modules activated.', $count, 'tbp-core'), number_format_i18n($count))
            : sprintf(_n('%s module deactivated.', '%s modules deactivated.', $count, 'tbp-core'), number_format_i18n($count));

        add_settings_error('tbp_core_messages', 'tbp_core_message', $message, 'updated');
    }

    public function no_items() {
        echo esc_html__('No modules found.', 'tbp-core');
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
            case 'version':
            case 'description':
                return esc_html($item[$column_name]);
            default:
                return '';
        }
    }

    public function column_title($item) {
        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : 'tbp-core';
        $tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'functions';

        $actions = [];

        if ($item['status'] === 'active') {
            $nonce = wp_create_nonce('tbp_module_action_' . $item['key']);
            $url = add_query_arg([
                'page' => $page,
                'tab' => $tab,
                'action' => 'deactivate',
                'module' => $item['key'],
                '_wpnonce' => $nonce,
            ], admin_url('admin.php'));
            $actions['deactivate'] = sprintf('<a href="%s">%s</a>', esc_url($url), __('Deactivate', 'tbp-core'));
        } else {
            $nonce = wp_create_nonce('tbp_module_action_' . $item['key']);
            $url = add_query_arg([
                'page' => $page,
                'tab' => $tab,
                'action' => 'activate',
                'module' => $item['key'],
                '_wpnonce' => $nonce,
            ], admin_url('admin.php'));
            $actions['activate'] = sprintf('<a href="%s">%s</a>', esc_url($url), __('Activate', 'tbp-core'));
        }

        $module_data = [
            'title' => $item['title'],
            'version' => $item['version'],
            'description' => $item['description'],
            'usage' => $item['usage'],
        ];
        $actions['info'] = sprintf(
            '<a href="#" class="tbp-module-info" data-info=\'%s\'>%s</a>',
            esc_attr(wp_json_encode($module_data)),
            __('Info', 'tbp-core')
        );

        return sprintf('<strong>%s</strong>%s', esc_html($item['title']), $this->row_actions($actions));
    }

    public function column_status($item) {
        if ($item['status'] === 'active') {
            return '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> ' . __('Active', 'tbp-core');
        }
        return '<span class="dashicons dashicons-minus" style="color: #dcdcde;"></span> ' . __('Inactive', 'tbp-core');
    }

    public function ajax_toggle_module() {
        check_ajax_referer('tbp_core_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $module_key = isset($_POST['module_key']) ? sanitize_text_field($_POST['module_key']) : '';
        $module_type = isset($_POST['module_type']) ? sanitize_text_field($_POST['module_type']) : '';
        $module_action = isset($_POST['module_action']) ? sanitize_text_field($_POST['module_action']) : '';

        if (empty($module_key) || empty($module_type) || empty($module_action)) {
            wp_send_json_error(['message' => __('Invalid request', 'tbp-core')]);
        }

        $active_modules = get_option('tbp_core_active_modules', []);

        if (!isset($active_modules[$module_type])) {
            $active_modules[$module_type] = [];
        }

        if ($module_action === 'activate') {
            $active_modules[$module_type][$module_key] = 'active';
        } else {
            $active_modules[$module_type][$module_key] = 'inactive';
        }

        update_option('tbp_core_active_modules', $active_modules);

        wp_send_json_success(['message' => __('Module updated successfully', 'tbp-core')]);
    }
}
