<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Icon Manager', 'tbp-core'); ?></h1>
    <p><?php echo esc_html__('Manage icon packs for Elementor - 16 icon libraries with 20,000+ icons', 'tbp-core'); ?></p>

    <?php
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        if ($message === 'activated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Icon pack activated.', 'tbp-core') . '</p></div>';
        } elseif ($message === 'deactivated') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Icon pack deactivated.', 'tbp-core') . '</p></div>';
        }
    }

    require_once TBP_ICONS_PATH . 'admin/class-icons-list-table.php';
    $list_table = new TBP_Icons_List_Table();
    $list_table->prepare_items();
    $list_table->views();
    ?>

    <form method="get">
        <input type="hidden" name="page" value="tbp-icons" />
        <?php if (isset($_GET['status'])): ?>
            <input type="hidden" name="status" value="<?php echo esc_attr($_GET['status']); ?>" />
        <?php endif; ?>
        <?php $list_table->search_box(__('Search Icon Packs', 'tbp-core'), 'icon_pack'); ?>
    </form>

    <form method="post">
        <?php $list_table->display(); ?>
    </form>
</div>
