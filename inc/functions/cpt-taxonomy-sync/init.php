<?php
/**
 * @module-title: CPT Taxonomy Sync
 * @module-description: Bidirectional synchronization between Custom Post Types and Taxonomies. Automatically sync posts with terms and vice versa.
 * @module-version: 1.0.0
 * @module-usage: Enable this module, then go to TBP Core > Manage Sync to create sync rules between CPTs and Taxonomies.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define module constants
define('TBP_CTS_PATH', plugin_dir_path(__FILE__));
define('TBP_CTS_URL', plugin_dir_url(__FILE__));
define('TBP_CTS_VERSION', '1.0.0');

/**
 * CPT Taxonomy Sync Module
 */
class TBP_CPT_Taxonomy_Sync {

    private static $_instance = null;
    private $sync_rules = [];

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->load_sync_rules();
        $this->init_hooks();
    }

    /**
     * Load sync rules from database
     */
    private function load_sync_rules() {
        $this->sync_rules = get_option('tbp_cts_sync_rules', []);
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // AJAX handlers
        add_action('wp_ajax_tbp_cts_save_rule', [$this, 'ajax_save_rule']);
        add_action('wp_ajax_tbp_cts_delete_rule', [$this, 'ajax_delete_rule']);
        add_action('wp_ajax_tbp_cts_toggle_rule', [$this, 'ajax_toggle_rule']);
        add_action('wp_ajax_tbp_cts_get_items', [$this, 'ajax_get_items']);
        add_action('wp_ajax_tbp_cts_manual_sync', [$this, 'ajax_manual_sync']);
        add_action('wp_ajax_tbp_cts_update_rule', [$this, 'ajax_update_rule']);

        // Load sync handlers
        require_once TBP_CTS_PATH . 'includes/class-sync-handler.php';
        TBP_CTS_Sync_Handler::instance($this->sync_rules);
    }

    /**
     * Add admin submenu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tbp-core',
            __('Manage Sync', 'tbp-core'),
            __('Manage Sync', 'tbp-core'),
            'manage_options',
            'tbp-manage-sync',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'tbp-core_page_tbp-manage-sync') {
            return;
        }

        wp_enqueue_style(
            'tbp-cts-admin',
            TBP_CTS_URL . 'assets/css/admin.css',
            [],
            TBP_CTS_VERSION
        );

        wp_enqueue_script(
            'tbp-cts-admin',
            TBP_CTS_URL . 'assets/js/admin.js',
            ['jquery'],
            TBP_CTS_VERSION,
            true
        );

        wp_localize_script('tbp-cts-admin', 'tbpCTS', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_cts_nonce'),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this sync rule?', 'tbp-core'),
                'saving' => __('Saving...', 'tbp-core'),
                'saved' => __('Saved!', 'tbp-core'),
                'error' => __('An error occurred. Please try again.', 'tbp-core'),
            ],
        ]);
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $sync_rules = $this->sync_rules;
        $post_types = $this->get_post_types();
        $taxonomies = $this->get_taxonomies();
        ?>
        <div class="wrap tbp-cts-wrap">
            <h1><?php echo esc_html__('Manage Sync Rules', 'tbp-core'); ?></h1>
            <p class="description"><?php echo esc_html__('Create bidirectional sync rules between Custom Post Types and Taxonomies.', 'tbp-core'); ?></p>

            <!-- Add New Rule Section -->
            <div class="tbp-cts-add-rule">
                <h2><?php echo esc_html__('Add New Sync Rule', 'tbp-core'); ?></h2>
                <form id="tbp-cts-new-rule-form" class="tbp-cts-form">
                    <div class="tbp-cts-form-row">
                        <div class="tbp-cts-form-group tbp-cts-source">
                            <label><?php echo esc_html__('Source', 'tbp-core'); ?></label>
                            <div class="tbp-cts-type-select">
                                <select name="source_type" id="source_type" class="tbp-cts-type-dropdown">
                                    <option value=""><?php echo esc_html__('Select Type', 'tbp-core'); ?></option>
                                    <option value="post_type"><?php echo esc_html__('Post Type', 'tbp-core'); ?></option>
                                    <option value="taxonomy"><?php echo esc_html__('Taxonomy', 'tbp-core'); ?></option>
                                </select>
                                <select name="source_name" id="source_name" class="tbp-cts-name-dropdown" disabled>
                                    <option value=""><?php echo esc_html__('Select...', 'tbp-core'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="tbp-cts-arrow">
                            <span class="dashicons dashicons-leftright"></span>
                        </div>

                        <div class="tbp-cts-form-group tbp-cts-destination">
                            <label><?php echo esc_html__('Destination', 'tbp-core'); ?></label>
                            <div class="tbp-cts-type-select">
                                <select name="dest_type" id="dest_type" class="tbp-cts-type-dropdown">
                                    <option value=""><?php echo esc_html__('Select Type', 'tbp-core'); ?></option>
                                    <option value="post_type"><?php echo esc_html__('Post Type', 'tbp-core'); ?></option>
                                    <option value="taxonomy"><?php echo esc_html__('Taxonomy', 'tbp-core'); ?></option>
                                </select>
                                <select name="dest_name" id="dest_name" class="tbp-cts-name-dropdown" disabled>
                                    <option value=""><?php echo esc_html__('Select...', 'tbp-core'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="tbp-cts-form-row tbp-cts-options-row">
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_create" value="1" checked>
                                <?php echo esc_html__('Sync on Create', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_update" value="1" checked>
                                <?php echo esc_html__('Sync on Update', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_delete" value="1" checked>
                                <?php echo esc_html__('Sync on Delete', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="bidirectional" value="1" checked>
                                <?php echo esc_html__('Bidirectional', 'tbp-core'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="tbp-cts-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php echo esc_html__('Add Sync Rule', 'tbp-core'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Existing Rules -->
            <div class="tbp-cts-rules-list">
                <h2><?php echo esc_html__('Existing Sync Rules', 'tbp-core'); ?></h2>

                <?php if (empty($sync_rules)) : ?>
                    <div class="tbp-cts-no-rules">
                        <p><?php echo esc_html__('No sync rules defined yet. Create one above.', 'tbp-core'); ?></p>
                    </div>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="column-status"><?php echo esc_html__('Status', 'tbp-core'); ?></th>
                                <th class="column-source"><?php echo esc_html__('Source', 'tbp-core'); ?></th>
                                <th class="column-direction"><?php echo esc_html__('Direction', 'tbp-core'); ?></th>
                                <th class="column-destination"><?php echo esc_html__('Destination', 'tbp-core'); ?></th>
                                <th class="column-options"><?php echo esc_html__('Options', 'tbp-core'); ?></th>
                                <th class="column-last-sync"><?php echo esc_html__('Last Synced', 'tbp-core'); ?></th>
                                <th class="column-actions"><?php echo esc_html__('Actions', 'tbp-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="tbp-cts-rules-tbody">
                            <?php foreach ($sync_rules as $id => $rule) : ?>
                                <?php $this->render_rule_row($id, $rule); ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="tbp-cts-edit-modal" class="tbp-cts-modal">
            <div class="tbp-cts-modal-content">
                <div class="tbp-cts-modal-header">
                    <h2><?php echo esc_html__('Edit Sync Rule', 'tbp-core'); ?></h2>
                    <button type="button" class="tbp-cts-modal-close">&times;</button>
                </div>
                <form id="tbp-cts-edit-rule-form" class="tbp-cts-form">
                    <input type="hidden" name="rule_id" id="edit_rule_id">

                    <div class="tbp-cts-form-row">
                        <div class="tbp-cts-form-group tbp-cts-source">
                            <label><?php echo esc_html__('Source', 'tbp-core'); ?></label>
                            <div class="tbp-cts-type-select">
                                <select name="source_type" id="edit_source_type" class="tbp-cts-type-dropdown">
                                    <option value=""><?php echo esc_html__('Select Type', 'tbp-core'); ?></option>
                                    <option value="post_type"><?php echo esc_html__('Post Type', 'tbp-core'); ?></option>
                                    <option value="taxonomy"><?php echo esc_html__('Taxonomy', 'tbp-core'); ?></option>
                                </select>
                                <select name="source_name" id="edit_source_name" class="tbp-cts-name-dropdown">
                                    <option value=""><?php echo esc_html__('Select...', 'tbp-core'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="tbp-cts-arrow">
                            <span class="dashicons dashicons-leftright"></span>
                        </div>

                        <div class="tbp-cts-form-group tbp-cts-destination">
                            <label><?php echo esc_html__('Destination', 'tbp-core'); ?></label>
                            <div class="tbp-cts-type-select">
                                <select name="dest_type" id="edit_dest_type" class="tbp-cts-type-dropdown">
                                    <option value=""><?php echo esc_html__('Select Type', 'tbp-core'); ?></option>
                                    <option value="post_type"><?php echo esc_html__('Post Type', 'tbp-core'); ?></option>
                                    <option value="taxonomy"><?php echo esc_html__('Taxonomy', 'tbp-core'); ?></option>
                                </select>
                                <select name="dest_name" id="edit_dest_name" class="tbp-cts-name-dropdown">
                                    <option value=""><?php echo esc_html__('Select...', 'tbp-core'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="tbp-cts-form-row tbp-cts-options-row">
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_create" id="edit_sync_on_create" value="1">
                                <?php echo esc_html__('Sync on Create', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_update" id="edit_sync_on_update" value="1">
                                <?php echo esc_html__('Sync on Update', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="sync_on_delete" id="edit_sync_on_delete" value="1">
                                <?php echo esc_html__('Sync on Delete', 'tbp-core'); ?>
                            </label>
                        </div>
                        <div class="tbp-cts-form-group">
                            <label>
                                <input type="checkbox" name="bidirectional" id="edit_bidirectional" value="1">
                                <?php echo esc_html__('Bidirectional', 'tbp-core'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="tbp-cts-form-actions">
                        <button type="button" class="button tbp-cts-modal-cancel"><?php echo esc_html__('Cancel', 'tbp-core'); ?></button>
                        <button type="submit" class="button button-primary"><?php echo esc_html__('Save Changes', 'tbp-core'); ?></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data for JS -->
        <script type="text/javascript">
            var tbpCTSData = {
                post_types: <?php echo wp_json_encode($post_types); ?>,
                taxonomies: <?php echo wp_json_encode($taxonomies); ?>
            };
        </script>
        <?php
    }

    /**
     * Render a single rule row
     */
    private function render_rule_row($id, $rule) {
        $source_label = $this->get_item_label($rule['source_type'], $rule['source_name']);
        $dest_label = $this->get_item_label($rule['dest_type'], $rule['dest_name']);
        $is_active = !empty($rule['active']);
        $is_bidirectional = !empty($rule['bidirectional']);
        $last_sync = !empty($rule['last_sync']) ? $rule['last_sync'] : '';

        $options = [];
        if (!empty($rule['sync_on_create'])) $options[] = __('Create', 'tbp-core');
        if (!empty($rule['sync_on_update'])) $options[] = __('Update', 'tbp-core');
        if (!empty($rule['sync_on_delete'])) $options[] = __('Delete', 'tbp-core');

        // Store rule data for edit modal
        $rule_data = [
            'id' => $id,
            'source_type' => $rule['source_type'],
            'source_name' => $rule['source_name'],
            'dest_type' => $rule['dest_type'],
            'dest_name' => $rule['dest_name'],
            'sync_on_create' => !empty($rule['sync_on_create']),
            'sync_on_update' => !empty($rule['sync_on_update']),
            'sync_on_delete' => !empty($rule['sync_on_delete']),
            'bidirectional' => $is_bidirectional,
        ];
        ?>
        <tr data-rule-id="<?php echo esc_attr($id); ?>" data-rule='<?php echo esc_attr(wp_json_encode($rule_data)); ?>'>
            <td class="column-status">
                <label class="tbp-cts-toggle">
                    <input type="checkbox" class="tbp-cts-toggle-status" <?php checked($is_active); ?>>
                    <span class="tbp-cts-toggle-slider"></span>
                </label>
            </td>
            <td class="column-source">
                <span class="tbp-cts-type-badge tbp-cts-type-<?php echo esc_attr($rule['source_type']); ?>">
                    <?php echo $rule['source_type'] === 'post_type' ? __('CPT', 'tbp-core') : __('Tax', 'tbp-core'); ?>
                </span>
                <?php echo esc_html($source_label); ?>
            </td>
            <td class="column-direction">
                <?php if ($is_bidirectional) : ?>
                    <span class="dashicons dashicons-leftright"></span>
                <?php else : ?>
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                <?php endif; ?>
            </td>
            <td class="column-destination">
                <span class="tbp-cts-type-badge tbp-cts-type-<?php echo esc_attr($rule['dest_type']); ?>">
                    <?php echo $rule['dest_type'] === 'post_type' ? __('CPT', 'tbp-core') : __('Tax', 'tbp-core'); ?>
                </span>
                <?php echo esc_html($dest_label); ?>
            </td>
            <td class="column-options">
                <?php echo esc_html(implode(', ', $options)); ?>
            </td>
            <td class="column-last-sync">
                <?php if ($last_sync) : ?>
                    <span class="tbp-cts-last-sync" title="<?php echo esc_attr($last_sync); ?>">
                        <?php echo esc_html(human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ' . __('ago', 'tbp-core')); ?>
                    </span>
                <?php else : ?>
                    <span class="tbp-cts-never-synced"><?php echo esc_html__('Never', 'tbp-core'); ?></span>
                <?php endif; ?>
            </td>
            <td class="column-actions">
                <div class="tbp-cts-actions-wrap">
                    <button type="button" class="button button-small tbp-cts-sync-now" title="<?php esc_attr_e('Sync Now', 'tbp-core'); ?>">
                        <span class="dashicons dashicons-update"></span>
                    </button>
                    <button type="button" class="button button-small tbp-cts-edit-rule" title="<?php esc_attr_e('Edit', 'tbp-core'); ?>">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                    <button type="button" class="button button-small tbp-cts-delete-rule" title="<?php esc_attr_e('Delete', 'tbp-core'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </td>
        </tr>
        <?php
    }

    /**
     * Get label for a post type or taxonomy
     */
    private function get_item_label($type, $name) {
        if ($type === 'post_type') {
            $post_type = get_post_type_object($name);
            return $post_type ? $post_type->labels->singular_name : $name;
        } else {
            $taxonomy = get_taxonomy($name);
            return $taxonomy ? $taxonomy->labels->singular_name : $name;
        }
    }

    /**
     * Get registered post types
     * Includes non-public types that have show_ui enabled
     */
    private function get_post_types() {
        // Get public post types
        $public_types = get_post_types(['public' => true], 'objects');

        // Also get non-public types that have show_ui enabled (like lab_result)
        $ui_types = get_post_types(['show_ui' => true, 'public' => false], 'objects');

        $post_types = array_merge($public_types, $ui_types);
        $result = [];

        foreach ($post_types as $pt) {
            if ($pt->name === 'attachment') continue;
            $result[$pt->name] = $pt->labels->singular_name;
        }

        // Sort alphabetically by label
        asort($result);

        return $result;
    }

    /**
     * Get registered taxonomies
     * Includes non-public taxonomies that have show_ui enabled
     */
    private function get_taxonomies() {
        // Get public taxonomies
        $public_taxes = get_taxonomies(['public' => true], 'objects');

        // Also get non-public taxonomies that have show_ui enabled
        $ui_taxes = get_taxonomies(['show_ui' => true, 'public' => false], 'objects');

        $taxonomies = array_merge($public_taxes, $ui_taxes);
        $result = [];

        foreach ($taxonomies as $tax) {
            $result[$tax->name] = $tax->labels->singular_name;
        }

        // Sort alphabetically by label
        asort($result);

        return $result;
    }

    /**
     * AJAX: Save a sync rule
     */
    public function ajax_save_rule() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        $source_name = sanitize_text_field($_POST['source_name'] ?? '');
        $dest_type = sanitize_text_field($_POST['dest_type'] ?? '');
        $dest_name = sanitize_text_field($_POST['dest_name'] ?? '');

        if (empty($source_type) || empty($source_name) || empty($dest_type) || empty($dest_name)) {
            wp_send_json_error(['message' => __('Please fill in all required fields.', 'tbp-core')]);
        }

        // Check for duplicate rule
        foreach ($this->sync_rules as $rule) {
            if ($rule['source_type'] === $source_type &&
                $rule['source_name'] === $source_name &&
                $rule['dest_type'] === $dest_type &&
                $rule['dest_name'] === $dest_name) {
                wp_send_json_error(['message' => __('This sync rule already exists.', 'tbp-core')]);
            }
        }

        $rule_id = uniqid('rule_');
        $new_rule = [
            'source_type' => $source_type,
            'source_name' => $source_name,
            'dest_type' => $dest_type,
            'dest_name' => $dest_name,
            'sync_on_create' => !empty($_POST['sync_on_create']),
            'sync_on_update' => !empty($_POST['sync_on_update']),
            'sync_on_delete' => !empty($_POST['sync_on_delete']),
            'bidirectional' => !empty($_POST['bidirectional']),
            'active' => true,
            'created' => current_time('mysql'),
        ];

        $this->sync_rules[$rule_id] = $new_rule;
        update_option('tbp_cts_sync_rules', $this->sync_rules);

        // Get HTML for the new row
        ob_start();
        $this->render_rule_row($rule_id, $new_rule);
        $row_html = ob_get_clean();

        wp_send_json_success([
            'message' => __('Sync rule created successfully.', 'tbp-core'),
            'rule_id' => $rule_id,
            'row_html' => $row_html,
        ]);
    }

    /**
     * AJAX: Delete a sync rule
     */
    public function ajax_delete_rule() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $rule_id = sanitize_text_field($_POST['rule_id'] ?? '');

        if (empty($rule_id) || !isset($this->sync_rules[$rule_id])) {
            wp_send_json_error(['message' => __('Rule not found.', 'tbp-core')]);
        }

        unset($this->sync_rules[$rule_id]);
        update_option('tbp_cts_sync_rules', $this->sync_rules);

        wp_send_json_success(['message' => __('Sync rule deleted.', 'tbp-core')]);
    }

    /**
     * AJAX: Toggle rule status
     */
    public function ajax_toggle_rule() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $rule_id = sanitize_text_field($_POST['rule_id'] ?? '');
        $active = !empty($_POST['active']);

        if (empty($rule_id) || !isset($this->sync_rules[$rule_id])) {
            wp_send_json_error(['message' => __('Rule not found.', 'tbp-core')]);
        }

        $this->sync_rules[$rule_id]['active'] = $active;
        update_option('tbp_cts_sync_rules', $this->sync_rules);

        wp_send_json_success([
            'message' => $active ? __('Sync rule activated.', 'tbp-core') : __('Sync rule deactivated.', 'tbp-core'),
        ]);
    }

    /**
     * AJAX: Get post types or taxonomies
     */
    public function ajax_get_items() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        $type = sanitize_text_field($_POST['type'] ?? '');

        if ($type === 'post_type') {
            wp_send_json_success($this->get_post_types());
        } elseif ($type === 'taxonomy') {
            wp_send_json_success($this->get_taxonomies());
        } else {
            wp_send_json_error(['message' => __('Invalid type.', 'tbp-core')]);
        }
    }

    /**
     * AJAX: Update a sync rule
     */
    public function ajax_update_rule() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $rule_id = sanitize_text_field($_POST['rule_id'] ?? '');

        if (empty($rule_id) || !isset($this->sync_rules[$rule_id])) {
            wp_send_json_error(['message' => __('Rule not found.', 'tbp-core')]);
        }

        $source_type = sanitize_text_field($_POST['source_type'] ?? '');
        $source_name = sanitize_text_field($_POST['source_name'] ?? '');
        $dest_type = sanitize_text_field($_POST['dest_type'] ?? '');
        $dest_name = sanitize_text_field($_POST['dest_name'] ?? '');

        if (empty($source_type) || empty($source_name) || empty($dest_type) || empty($dest_name)) {
            wp_send_json_error(['message' => __('Please fill in all required fields.', 'tbp-core')]);
        }

        // Update rule
        $this->sync_rules[$rule_id]['source_type'] = $source_type;
        $this->sync_rules[$rule_id]['source_name'] = $source_name;
        $this->sync_rules[$rule_id]['dest_type'] = $dest_type;
        $this->sync_rules[$rule_id]['dest_name'] = $dest_name;
        $this->sync_rules[$rule_id]['sync_on_create'] = !empty($_POST['sync_on_create']);
        $this->sync_rules[$rule_id]['sync_on_update'] = !empty($_POST['sync_on_update']);
        $this->sync_rules[$rule_id]['sync_on_delete'] = !empty($_POST['sync_on_delete']);
        $this->sync_rules[$rule_id]['bidirectional'] = !empty($_POST['bidirectional']);

        update_option('tbp_cts_sync_rules', $this->sync_rules);

        // Get HTML for the updated row
        ob_start();
        $this->render_rule_row($rule_id, $this->sync_rules[$rule_id]);
        $row_html = ob_get_clean();

        wp_send_json_success([
            'message' => __('Sync rule updated successfully.', 'tbp-core'),
            'row_html' => $row_html,
        ]);
    }

    /**
     * AJAX: Manual sync
     */
    public function ajax_manual_sync() {
        check_ajax_referer('tbp_cts_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $rule_id = sanitize_text_field($_POST['rule_id'] ?? '');

        if (empty($rule_id) || !isset($this->sync_rules[$rule_id])) {
            wp_send_json_error(['message' => __('Rule not found.', 'tbp-core')]);
        }

        $rule = $this->sync_rules[$rule_id];
        $synced_count = 0;

        // Perform sync based on source type
        if ($rule['source_type'] === 'post_type') {
            $synced_count = $this->sync_all_posts($rule);
        } elseif ($rule['source_type'] === 'taxonomy') {
            $synced_count = $this->sync_all_terms($rule);
        }

        // Update last sync time
        $this->sync_rules[$rule_id]['last_sync'] = current_time('mysql');
        update_option('tbp_cts_sync_rules', $this->sync_rules);

        // Get updated row HTML
        ob_start();
        $this->render_rule_row($rule_id, $this->sync_rules[$rule_id]);
        $row_html = ob_get_clean();

        wp_send_json_success([
            'message' => sprintf(__('Synced %d items successfully.', 'tbp-core'), $synced_count),
            'synced_count' => $synced_count,
            'row_html' => $row_html,
        ]);
    }

    /**
     * Sync all posts for a rule
     */
    private function sync_all_posts($rule) {
        $posts = get_posts([
            'post_type' => $rule['source_name'],
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
        ]);

        $count = 0;
        $handler = TBP_CTS_Sync_Handler::instance($this->sync_rules);

        foreach ($posts as $post) {
            if ($rule['dest_type'] === 'taxonomy') {
                $handler->manual_sync_post_to_term($post, $rule['dest_name']);
            } elseif ($rule['dest_type'] === 'post_type') {
                $handler->manual_sync_post_to_post($post, $rule['dest_name']);
            }
            $count++;
        }

        return $count;
    }

    /**
     * Sync all terms for a rule
     */
    private function sync_all_terms($rule) {
        $terms = get_terms([
            'taxonomy' => $rule['source_name'],
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return 0;
        }

        $count = 0;
        $handler = TBP_CTS_Sync_Handler::instance($this->sync_rules);

        foreach ($terms as $term) {
            if ($rule['dest_type'] === 'post_type') {
                $handler->manual_sync_term_to_post($term, $rule['dest_name']);
            } elseif ($rule['dest_type'] === 'taxonomy') {
                $handler->manual_sync_term_to_term($term, $rule['dest_name']);
            }
            $count++;
        }

        return $count;
    }

    /**
     * Get sync rules
     */
    public function get_sync_rules() {
        return $this->sync_rules;
    }
}

// Initialize
TBP_CPT_Taxonomy_Sync::instance();
