<?php
/**
 * Themes Metabox for Subject
 * Allows multi-selecting and ordering themes within a subject
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Themes_Metabox {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_tbp_subject', [$this, 'save_themes'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_meta_box() {
        add_meta_box(
            'tbp_subject_themes',
            __('Subject Themes', 'tbp-core'),
            [$this, 'render_metabox'],
            'tbp_subject',
            'normal',
            'default'
        );
    }

    public function enqueue_scripts($hook) {
        global $post_type;

        if ($post_type !== 'tbp_subject' || !in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_script('jquery-ui-sortable');
    }

    public function render_metabox($post) {
        wp_nonce_field('tbp_subject_themes', 'tbp_subject_themes_nonce');

        // Get all available themes
        $all_themes = get_posts([
            'post_type' => 'tbp_theme',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish',
        ]);

        // Get assigned themes for this subject (ordered)
        $assigned_theme_ids = TBP_Subject_Theme_Pivot::get_theme_ids_for_subject($post->ID);

        // Separate assigned and available themes
        $assigned_themes = [];
        $available_themes = [];

        foreach ($all_themes as $theme) {
            if (in_array($theme->ID, $assigned_theme_ids)) {
                // Maintain order from pivot table
                $order = array_search($theme->ID, $assigned_theme_ids);
                $assigned_themes[$order] = $theme;
            } else {
                $available_themes[] = $theme;
            }
        }

        // Sort assigned themes by their order
        ksort($assigned_themes);
        $assigned_themes = array_values($assigned_themes);
        ?>
        <div class="tbp-themes-metabox">
            <div class="tbp-themes-container">
                <div class="tbp-themes-column tbp-themes-available">
                    <h4><?php _e('Available Themes', 'tbp-core'); ?></h4>
                    <div class="tbp-themes-search">
                        <input type="text" class="tbp-themes-search-input" placeholder="<?php esc_attr_e('Search themes...', 'tbp-core'); ?>">
                    </div>
                    <ul class="tbp-themes-list" id="tbp-available-themes">
                        <?php foreach ($available_themes as $theme) : ?>
                            <li class="tbp-theme-item" data-id="<?php echo esc_attr($theme->ID); ?>">
                                <span class="tbp-theme-title"><?php echo esc_html($theme->post_title); ?></span>
                                <button type="button" class="tbp-theme-add button-link" title="<?php esc_attr_e('Add', 'tbp-core'); ?>">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($available_themes)) : ?>
                            <li class="tbp-themes-empty"><?php _e('No themes available', 'tbp-core'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="tbp-themes-column tbp-themes-assigned">
                    <h4><?php _e('Assigned Themes', 'tbp-core'); ?> <span class="tbp-themes-count">(<?php echo count($assigned_themes); ?>)</span></h4>
                    <p class="description"><?php _e('Drag to reorder themes.', 'tbp-core'); ?></p>
                    <ul class="tbp-themes-list tbp-themes-sortable" id="tbp-assigned-themes">
                        <?php foreach ($assigned_themes as $theme) : ?>
                            <li class="tbp-theme-item" data-id="<?php echo esc_attr($theme->ID); ?>">
                                <span class="tbp-theme-handle dashicons dashicons-menu"></span>
                                <span class="tbp-theme-title"><?php echo esc_html($theme->post_title); ?></span>
                                <button type="button" class="tbp-theme-remove button-link" title="<?php esc_attr_e('Remove', 'tbp-core'); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                                <input type="hidden" name="tbp_subject_themes[]" value="<?php echo esc_attr($theme->ID); ?>">
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($assigned_themes)) : ?>
                            <li class="tbp-themes-empty"><?php _e('No themes assigned. Add themes from the left.', 'tbp-core'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <p class="tbp-themes-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=tbp_theme')); ?>" class="button" target="_blank">
                    <?php _e('Create New Theme', 'tbp-core'); ?>
                </a>
            </p>
        </div>

        <style>
            .tbp-themes-metabox { margin: -6px -12px -12px; }
            .tbp-themes-container { display: flex; gap: 0; border-top: 1px solid #c3c4c7; }
            .tbp-themes-column { flex: 1; padding: 12px; }
            .tbp-themes-column:first-child { border-right: 1px solid #c3c4c7; background: #f6f7f7; }
            .tbp-themes-column h4 { margin: 0 0 10px; padding: 0; }
            .tbp-themes-count { font-weight: normal; color: #646970; }
            .tbp-themes-search { margin-bottom: 10px; }
            .tbp-themes-search-input { width: 100%; }
            .tbp-themes-list { margin: 0; padding: 0; list-style: none; max-height: 300px; overflow-y: auto; border: 1px solid #c3c4c7; background: #fff; }
            .tbp-theme-item { display: flex; align-items: center; padding: 8px 10px; border-bottom: 1px solid #f0f0f1; gap: 8px; }
            .tbp-theme-item:last-child { border-bottom: 0; }
            .tbp-theme-title { flex: 1; }
            .tbp-theme-handle { cursor: move; color: #c3c4c7; }
            .tbp-theme-handle:hover { color: #2271b1; }
            .tbp-theme-add, .tbp-theme-remove { color: #2271b1; cursor: pointer; padding: 0; }
            .tbp-theme-add:hover { color: #135e96; }
            .tbp-theme-remove { color: #b32d2e; }
            .tbp-theme-remove:hover { color: #8a2424; }
            .tbp-themes-empty { padding: 20px; text-align: center; color: #646970; font-style: italic; }
            .tbp-themes-sortable .tbp-theme-item { background: #fff; }
            .tbp-themes-sortable .tbp-theme-item.ui-sortable-helper { box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
            .tbp-themes-sortable .ui-sortable-placeholder { visibility: visible !important; background: #f0f6fc; border: 1px dashed #2271b1; }
            .tbp-themes-actions { margin: 12px 12px 0; padding-top: 12px; border-top: 1px solid #c3c4c7; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var $available = $('#tbp-available-themes');
            var $assigned = $('#tbp-assigned-themes');

            // Initialize sortable
            $assigned.sortable({
                handle: '.tbp-theme-handle',
                placeholder: 'tbp-theme-placeholder',
                update: function() {
                    updateCount();
                }
            });

            // Add theme
            $(document).on('click', '.tbp-theme-add', function() {
                var $item = $(this).closest('.tbp-theme-item');
                var id = $item.data('id');
                var title = $item.find('.tbp-theme-title').text();

                // Remove empty message if exists
                $assigned.find('.tbp-themes-empty').remove();

                // Create new item for assigned list
                var $newItem = $('<li class="tbp-theme-item" data-id="' + id + '">' +
                    '<span class="tbp-theme-handle dashicons dashicons-menu"></span>' +
                    '<span class="tbp-theme-title">' + title + '</span>' +
                    '<button type="button" class="tbp-theme-remove button-link" title="Remove">' +
                    '<span class="dashicons dashicons-no-alt"></span></button>' +
                    '<input type="hidden" name="tbp_subject_themes[]" value="' + id + '">' +
                    '</li>');

                $assigned.append($newItem);
                $item.remove();

                // Add empty message to available if needed
                if ($available.find('.tbp-theme-item').length === 0) {
                    $available.append('<li class="tbp-themes-empty">No themes available</li>');
                }

                updateCount();
            });

            // Remove theme
            $(document).on('click', '.tbp-theme-remove', function() {
                var $item = $(this).closest('.tbp-theme-item');
                var id = $item.data('id');
                var title = $item.find('.tbp-theme-title').text();

                // Remove empty message if exists
                $available.find('.tbp-themes-empty').remove();

                // Create new item for available list
                var $newItem = $('<li class="tbp-theme-item" data-id="' + id + '">' +
                    '<span class="tbp-theme-title">' + title + '</span>' +
                    '<button type="button" class="tbp-theme-add button-link" title="Add">' +
                    '<span class="dashicons dashicons-plus-alt2"></span></button>' +
                    '</li>');

                // Insert in alphabetical order
                var inserted = false;
                $available.find('.tbp-theme-item').each(function() {
                    if ($(this).find('.tbp-theme-title').text() > title) {
                        $newItem.insertBefore($(this));
                        inserted = true;
                        return false;
                    }
                });
                if (!inserted) {
                    $available.append($newItem);
                }

                $item.remove();

                // Add empty message to assigned if needed
                if ($assigned.find('.tbp-theme-item').length === 0) {
                    $assigned.append('<li class="tbp-themes-empty">No themes assigned. Add themes from the left.</li>');
                }

                updateCount();
            });

            // Search filter
            $('.tbp-themes-search-input').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $available.find('.tbp-theme-item').each(function() {
                    var title = $(this).find('.tbp-theme-title').text().toLowerCase();
                    $(this).toggle(title.indexOf(search) > -1);
                });
            });

            function updateCount() {
                var count = $assigned.find('.tbp-theme-item').length;
                $('.tbp-themes-count').text('(' + count + ')');
            }
        });
        </script>
        <?php
    }

    public function save_themes($post_id, $post) {
        // Check nonce
        if (!isset($_POST['tbp_subject_themes_nonce']) ||
            !wp_verify_nonce($_POST['tbp_subject_themes_nonce'], 'tbp_subject_themes')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get theme IDs from form
        $theme_ids = isset($_POST['tbp_subject_themes']) ? array_map('absint', $_POST['tbp_subject_themes']) : [];

        // Sync themes using pivot table
        TBP_Subject_Theme_Pivot::sync_themes_for_subject($post_id, $theme_ids);
    }
}
