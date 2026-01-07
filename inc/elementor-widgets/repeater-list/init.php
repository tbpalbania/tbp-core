<?php
/**
 * @module-title: Repeater List
 * @module-version: 1.0.0
 * @module-description: Icon list widget with static/dynamic ACF repeater support
 * @module-usage: Drag and drop the "Repeater List" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_repeater_list_widget');
function tbp_register_repeater_list_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Repeater_List());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_repeater_list_enqueue_assets');
function tbp_repeater_list_enqueue_assets() {
    wp_register_style(
        'tbp-repeater-list',
        TBP_CORE_URL . 'inc/elementor-widgets/repeater-list/assets/css/repeater-list.css',
        [],
        TBP_CORE_VERSION
    );

    wp_register_script(
        'tbp-repeater-list',
        TBP_CORE_URL . 'inc/elementor-widgets/repeater-list/assets/js/repeater-list.js',
        ['jquery'],
        TBP_CORE_VERSION,
        true
    );
}

// AJAX handler for getting ACF repeater fields
add_action('wp_ajax_tbp_get_acf_repeater_fields', 'tbp_get_acf_repeater_fields_ajax');
function tbp_get_acf_repeater_fields_ajax() {
    check_ajax_referer('tbp_repeater_list_nonce', 'nonce');

    $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';

    if (empty($field_key)) {
        wp_send_json_error(['message' => 'No field key provided']);
    }

    $field = acf_get_field($field_key);

    if (!$field || $field['type'] !== 'repeater') {
        wp_send_json_error(['message' => 'Invalid repeater field']);
    }

    $sub_fields = [];
    if (!empty($field['sub_fields'])) {
        foreach ($field['sub_fields'] as $sub_field) {
            $sub_fields[] = [
                'key' => $sub_field['key'],
                'name' => $sub_field['name'],
                'label' => $sub_field['label'],
                'type' => $sub_field['type'],
            ];
        }
    }

    wp_send_json_success(['sub_fields' => $sub_fields]);
}
