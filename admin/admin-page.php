<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'functions';
?>
<div class="wrap">
    <h1><?php echo esc_html__('TBP Core', 'tbp-core'); ?></h1>
    <p><?php echo esc_html__('Manage your modules', 'tbp-core'); ?></p>

    <?php
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        if ($message === 'activated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Module activated.', 'tbp-core') . '</p></div>';
        } elseif ($message === 'deactivated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Module deactivated.', 'tbp-core') . '</p></div>';
        }
    }
    settings_errors('tbp_core_messages');
    ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=tbp-core&tab=functions" class="nav-tab <?php echo $current_tab === 'functions' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Functions', 'tbp-core'); ?>
        </a>
        <a href="?page=tbp-core&tab=queries" class="nav-tab <?php echo $current_tab === 'queries' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Queries', 'tbp-core'); ?>
        </a>
        <a href="?page=tbp-core&tab=elementor-widgets" class="nav-tab <?php echo $current_tab === 'elementor-widgets' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Elementor Widgets', 'tbp-core'); ?>
        </a>
        <a href="?page=tbp-core&tab=dynamic-tags" class="nav-tab <?php echo $current_tab === 'dynamic-tags' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Dynamic Tags', 'tbp-core'); ?>
        </a>
        <a href="?page=tbp-core&tab=acf-fields" class="nav-tab <?php echo $current_tab === 'acf-fields' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('ACF Fields', 'tbp-core'); ?>
        </a>
    </h2>

    <?php
    require_once TBP_CORE_PATH . 'admin/components/class-modules-list-table.php';
    $list_table = new TBP_Modules_List_Table($current_tab);
    $list_table->prepare_items();
    $list_table->views();
    ?>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page'] ?? 'tbp-core'); ?>" />
        <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>" />
        <?php
        $list_table->search_box(__('Search Modules', 'tbp-core'), 'module');
        $list_table->display();
        ?>
    </form>
</div>

<div id="tbp-info-modal" class="tbp-info-modal">
    <div class="tbp-info-modal-content">
        <div class="tbp-info-modal-header">
            <h2 class="tbp-info-modal-title"></h2>
            <button class="tbp-info-modal-close">&times;</button>
        </div>
        <div class="tbp-info-modal-body">
            <dl>
                <dt><?php echo esc_html__('Version', 'tbp-core'); ?></dt>
                <dd class="tbp-info-version"></dd>
                <dt><?php echo esc_html__('Description', 'tbp-core'); ?></dt>
                <dd class="tbp-info-description"></dd>
                <div class="tbp-info-usage-section">
                    <dt><?php echo esc_html__('Usage', 'tbp-core'); ?></dt>
                    <dd class="tbp-info-usage"></dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<?php
/**
 * Get all modules of a specific type
 */
function tbp_core_get_modules($type) {
    $modules = [];
    $modules_path = TBP_CORE_PATH . "inc/{$type}";

    if (!is_dir($modules_path)) {
        return $modules;
    }

    $dirs = array_diff(scandir($modules_path), ['.', '..']);

    foreach ($dirs as $dir) {
        $module_path = $modules_path . '/' . $dir;
        $init_file = $module_path . '/init.php';

        if (is_dir($module_path) && file_exists($init_file)) {
            $module_info = tbp_core_get_module_info($init_file);

            $modules[$dir] = [
                'key' => $dir,
                'title' => $module_info['title'] ?? ucwords(str_replace('-', ' ', $dir)),
                'version' => $module_info['version'] ?? '1.0.0',
                'description' => $module_info['description'] ?? '',
                'usage' => $module_info['usage'] ?? '',
            ];
        }
    }

    return $modules;
}

/**
 * Parse module info from init.php file
 */
function tbp_core_get_module_info($file) {
    $info = [
        'title' => '',
        'version' => '1.0.0',
        'description' => '',
        'usage' => '',
    ];

    if (!file_exists($file)) {
        return $info;
    }

    $content = file_get_contents($file);

    if (preg_match('/@module-title:\s*(.+)/i', $content, $matches)) {
        $info['title'] = trim($matches[1]);
    }

    if (preg_match('/@module-version:\s*(.+)/i', $content, $matches)) {
        $info['version'] = trim($matches[1]);
    }

    if (preg_match('/@module-description:\s*(.+)/i', $content, $matches)) {
        $info['description'] = trim($matches[1]);
    }

    if (preg_match('/@module-usage:\s*(.+)/i', $content, $matches)) {
        $info['usage'] = trim($matches[1]);
    }

    return $info;
}
