<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>
<div class="wrap">
    <h1><?php echo esc_html__('TBP Core Settings', 'tbp-core'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=tbp-core-settings&tab=general" class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('General', 'tbp-core'); ?>
        </a>
    </h2>

    <div class="tbp-settings-content">
        <?php
        switch ($current_tab) {
            case 'general':
            default:
                // Empty General tab - settings will be added later
                ?>
                <p><?php echo esc_html__('General settings will appear here.', 'tbp-core'); ?></p>
                <?php
                break;
        }
        ?>
    </div>
</div>
