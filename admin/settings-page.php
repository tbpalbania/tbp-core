<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get tabs from filter
$tabs = apply_filters('tbp_core_settings_tabs', [
    'general' => __('General', 'tbp-core'),
]);

$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Ensure current tab exists
if (!isset($tabs[$current_tab])) {
    $current_tab = 'general';
}

// Handle form submission
if (isset($_POST['tbp_core_settings_nonce']) && wp_verify_nonce($_POST['tbp_core_settings_nonce'], 'tbp_core_save_settings')) {
    $fields = apply_filters('tbp_core_settings_fields', []);

    if (isset($fields[$current_tab])) {
        foreach ($fields[$current_tab] as $field_id => $field) {
            $field_type = $field['type'] ?? 'text';

            if ($field_type === 'checkbox') {
                $value = isset($_POST[$field_id]) ? '1' : '';
            } else {
                $value = isset($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
            }

            update_option($field_id, $value);
        }
    }

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'tbp-core') . '</p></div>';
}

// Get sections and fields for current tab
$all_sections = apply_filters('tbp_core_settings_sections', []);
$all_fields = apply_filters('tbp_core_settings_fields', []);

$sections = isset($all_sections[$current_tab]) ? $all_sections[$current_tab] : [];
$fields = isset($all_fields[$current_tab]) ? $all_fields[$current_tab] : [];
?>
<div class="wrap">
    <h1><?php echo esc_html__('TBP Core Settings', 'tbp-core'); ?></h1>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $tab_id => $tab_label): ?>
            <a href="?page=tbp-core-settings&tab=<?php echo esc_attr($tab_id); ?>"
               class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <div class="tbp-settings-content" style="margin-top: 20px;">
        <form method="post" action="">
            <?php wp_nonce_field('tbp_core_save_settings', 'tbp_core_settings_nonce'); ?>

            <?php if (empty($sections) && empty($fields)): ?>
                <p><?php echo esc_html__('No settings available for this tab.', 'tbp-core'); ?></p>
            <?php else: ?>
                <?php
                // Group fields by section
                $fields_by_section = [];
                foreach ($fields as $field_id => $field) {
                    $section_id = $field['section'] ?? 'default';
                    $fields_by_section[$section_id][$field_id] = $field;
                }

                // Render sections
                foreach ($sections as $section_id => $section):
                    $section_fields = isset($fields_by_section[$section_id]) ? $fields_by_section[$section_id] : [];
                ?>
                    <div class="tbp-settings-section" style="background: #fff; padding: 20px; margin-bottom: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
                        <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                            <?php echo esc_html($section['title']); ?>
                        </h2>
                        <?php if (!empty($section['description'])): ?>
                            <p class="description" style="margin-bottom: 20px;">
                                <?php echo wp_kses_post($section['description']); ?>
                            </p>
                        <?php endif; ?>

                        <table class="form-table" role="presentation">
                            <?php foreach ($section_fields as $field_id => $field):
                                $value = get_option($field_id, $field['default'] ?? '');
                                $field_type = $field['type'] ?? 'text';
                            ?>
                                <tr>
                                    <th scope="row">
                                        <label for="<?php echo esc_attr($field_id); ?>">
                                            <?php echo esc_html($field['title']); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php switch ($field_type):
                                            case 'text':
                                            case 'number':
                                            case 'password': ?>
                                                <input type="<?php echo esc_attr($field_type); ?>"
                                                       id="<?php echo esc_attr($field_id); ?>"
                                                       name="<?php echo esc_attr($field_id); ?>"
                                                       value="<?php echo esc_attr($value); ?>"
                                                       class="regular-text"
                                                       <?php if ($field_type === 'number'): ?>
                                                           <?php if (isset($field['min'])): ?>min="<?php echo esc_attr($field['min']); ?>"<?php endif; ?>
                                                           <?php if (isset($field['max'])): ?>max="<?php echo esc_attr($field['max']); ?>"<?php endif; ?>
                                                       <?php endif; ?>
                                                >
                                                <?php break;

                                            case 'textarea': ?>
                                                <textarea id="<?php echo esc_attr($field_id); ?>"
                                                          name="<?php echo esc_attr($field_id); ?>"
                                                          class="large-text"
                                                          rows="5"><?php echo esc_textarea($value); ?></textarea>
                                                <?php break;

                                            case 'select': ?>
                                                <select id="<?php echo esc_attr($field_id); ?>"
                                                        name="<?php echo esc_attr($field_id); ?>">
                                                    <?php foreach ($field['options'] as $opt_value => $opt_label): ?>
                                                        <option value="<?php echo esc_attr($opt_value); ?>"
                                                                <?php selected($value, $opt_value); ?>>
                                                            <?php echo esc_html($opt_label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php break;

                                            case 'checkbox': ?>
                                                <label>
                                                    <input type="checkbox"
                                                           id="<?php echo esc_attr($field_id); ?>"
                                                           name="<?php echo esc_attr($field_id); ?>"
                                                           value="1"
                                                           <?php checked($value, '1'); ?>
                                                    >
                                                    <?php echo esc_html($field['checkbox_label'] ?? __('Enable', 'tbp-core')); ?>
                                                </label>
                                                <?php break;

                                        endswitch; ?>

                                        <?php if (!empty($field['description'])): ?>
                                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>

                <?php submit_button(__('Save Settings', 'tbp-core')); ?>
            <?php endif; ?>
        </form>

        <?php if ($current_tab === 'pusher'): ?>
        <div class="tbp-settings-section" style="background: #fff; padding: 20px; margin-top: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                <?php echo esc_html__('Test Notification', 'tbp-core'); ?>
            </h2>
            <p class="description" style="margin-bottom: 20px;">
                <?php echo esc_html__('Send a test notification to verify the system is working.', 'tbp-core'); ?>
            </p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="tbp_test_user_id"><?php echo esc_html__('User ID', 'tbp-core'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="tbp_test_user_id" class="regular-text" min="1" placeholder="<?php esc_attr_e('Enter user ID', 'tbp-core'); ?>">
                        <p class="description"><?php echo esc_html__('Enter the ID of the user to receive the test notification.', 'tbp-core'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <button type="button" id="tbp_send_test_notification" class="button button-secondary">
                            <?php echo esc_html__('Send Test Notification', 'tbp-core'); ?>
                        </button>
                        <span id="tbp_test_notification_result" style="margin-left: 10px;"></span>
                    </td>
                </tr>
            </table>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#tbp_send_test_notification').on('click', function() {
                var $btn = $(this);
                var $result = $('#tbp_test_notification_result');
                var userId = $('#tbp_test_user_id').val();

                if (!userId) {
                    $result.html('<span style="color: #d63638;">Please enter a User ID</span>');
                    return;
                }

                $btn.prop('disabled', true).text('<?php echo esc_js(__('Sending...', 'tbp-core')); ?>');
                $result.html('');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tbp_send_test_notification',
                        user_id: userId,
                        nonce: '<?php echo wp_create_nonce('tbp_core_admin_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<span style="color: #00a32a;">' + response.data.message + '</span>');
                        } else {
                            $result.html('<span style="color: #d63638;">' + response.data.message + '</span>');
                        }
                    },
                    error: function() {
                        $result.html('<span style="color: #d63638;"><?php echo esc_js(__('Request failed', 'tbp-core')); ?></span>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php echo esc_js(__('Send Test Notification', 'tbp-core')); ?>');
                    }
                });
            });
        });
        </script>
        <?php endif; ?>
    </div>
</div>
