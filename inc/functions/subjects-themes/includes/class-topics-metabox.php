<?php
/**
 * Topics Metabox for Subject
 * Allows multi-selecting and ordering topics within a subject
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Topics_Metabox {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_tbp_subject', [$this, 'save_topics'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_meta_box() {
        add_meta_box(
            'tbp_subject_topics',
            __('Subject Topics', 'tbp-core'),
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
        wp_nonce_field('tbp_subject_topics', 'tbp_subject_topics_nonce');

        // Get all available topics
        $all_topics = get_posts([
            'post_type' => 'tbp_topic',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish',
        ]);

        // Get assigned topics for this subject (ordered)
        $assigned_topic_ids = TBP_Subject_Topic_Pivot::get_topic_ids_for_subject($post->ID);

        // Separate assigned and available topics
        $assigned_topics = [];
        $available_topics = [];

        foreach ($all_topics as $topic) {
            if (in_array($topic->ID, $assigned_topic_ids)) {
                // Maintain order from pivot table
                $order = array_search($topic->ID, $assigned_topic_ids);
                $assigned_topics[$order] = $topic;
            } else {
                $available_topics[] = $topic;
            }
        }

        // Sort assigned topics by their order
        ksort($assigned_topics);
        $assigned_topics = array_values($assigned_topics);
        ?>
        <div class="tbp-topics-metabox">
            <div class="tbp-topics-container">
                <div class="tbp-topics-column tbp-topics-available">
                    <h4><?php _e('Available Topics', 'tbp-core'); ?></h4>
                    <div class="tbp-topics-search">
                        <input type="text" class="tbp-topics-search-input" placeholder="<?php esc_attr_e('Search topics...', 'tbp-core'); ?>">
                    </div>
                    <ul class="tbp-topics-list" id="tbp-available-topics">
                        <?php foreach ($available_topics as $topic) : ?>
                            <li class="tbp-topic-item" data-id="<?php echo esc_attr($topic->ID); ?>">
                                <span class="tbp-topic-title"><?php echo esc_html($topic->post_title); ?></span>
                                <button type="button" class="tbp-topic-add button-link" title="<?php esc_attr_e('Add', 'tbp-core'); ?>">
                                    <span class="dashicons dashicons-plus-alt2"></span>
                                </button>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($available_topics)) : ?>
                            <li class="tbp-topics-empty"><?php _e('No topics available', 'tbp-core'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="tbp-topics-column tbp-topics-assigned">
                    <h4><?php _e('Assigned Topics', 'tbp-core'); ?> <span class="tbp-topics-count">(<?php echo count($assigned_topics); ?>)</span></h4>
                    <p class="description"><?php _e('Drag to reorder topics.', 'tbp-core'); ?></p>
                    <ul class="tbp-topics-list tbp-topics-sortable" id="tbp-assigned-topics">
                        <?php foreach ($assigned_topics as $topic) : ?>
                            <li class="tbp-topic-item" data-id="<?php echo esc_attr($topic->ID); ?>">
                                <span class="tbp-topic-handle dashicons dashicons-menu"></span>
                                <span class="tbp-topic-title"><?php echo esc_html($topic->post_title); ?></span>
                                <button type="button" class="tbp-topic-remove button-link" title="<?php esc_attr_e('Remove', 'tbp-core'); ?>">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                                <input type="hidden" name="tbp_subject_topics[]" value="<?php echo esc_attr($topic->ID); ?>">
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($assigned_topics)) : ?>
                            <li class="tbp-topics-empty"><?php _e('No topics assigned. Add topics from the left.', 'tbp-core'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <p class="tbp-topics-actions">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=tbp_topic')); ?>" class="button" target="_blank">
                    <?php _e('Create New Topic', 'tbp-core'); ?>
                </a>
            </p>
        </div>

        <style>
            .tbp-topics-metabox { margin: -6px -12px -12px; }
            .tbp-topics-container { display: flex; gap: 0; border-top: 1px solid #c3c4c7; }
            .tbp-topics-column { flex: 1; padding: 12px; }
            .tbp-topics-column:first-child { border-right: 1px solid #c3c4c7; background: #f6f7f7; }
            .tbp-topics-column h4 { margin: 0 0 10px; padding: 0; }
            .tbp-topics-count { font-weight: normal; color: #646970; }
            .tbp-topics-search { margin-bottom: 10px; }
            .tbp-topics-search-input { width: 100%; }
            .tbp-topics-list { margin: 0; padding: 0; list-style: none; max-height: 300px; overflow-y: auto; border: 1px solid #c3c4c7; background: #fff; }
            .tbp-topic-item { display: flex; align-items: center; padding: 8px 10px; border-bottom: 1px solid #f0f0f1; gap: 8px; }
            .tbp-topic-item:last-child { border-bottom: 0; }
            .tbp-topic-title { flex: 1; }
            .tbp-topic-handle { cursor: move; color: #c3c4c7; }
            .tbp-topic-handle:hover { color: #2271b1; }
            .tbp-topic-add, .tbp-topic-remove { color: #2271b1; cursor: pointer; padding: 0; }
            .tbp-topic-add:hover { color: #135e96; }
            .tbp-topic-remove { color: #b32d2e; }
            .tbp-topic-remove:hover { color: #8a2424; }
            .tbp-topics-empty { padding: 20px; text-align: center; color: #646970; font-style: italic; }
            .tbp-topics-sortable .tbp-topic-item { background: #fff; }
            .tbp-topics-sortable .tbp-topic-item.ui-sortable-helper { box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
            .tbp-topics-sortable .ui-sortable-placeholder { visibility: visible !important; background: #f0f6fc; border: 1px dashed #2271b1; }
            .tbp-topics-actions { margin: 12px 12px 0; padding-top: 12px; border-top: 1px solid #c3c4c7; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var $available = $('#tbp-available-topics');
            var $assigned = $('#tbp-assigned-topics');

            // Initialize sortable
            $assigned.sortable({
                handle: '.tbp-topic-handle',
                placeholder: 'tbp-topic-placeholder',
                update: function() {
                    updateCount();
                }
            });

            // Add topic
            $(document).on('click', '.tbp-topic-add', function() {
                var $item = $(this).closest('.tbp-topic-item');
                var id = $item.data('id');
                var title = $item.find('.tbp-topic-title').text();

                // Remove empty message if exists
                $assigned.find('.tbp-topics-empty').remove();

                // Create new item for assigned list
                var $newItem = $('<li class="tbp-topic-item" data-id="' + id + '">' +
                    '<span class="tbp-topic-handle dashicons dashicons-menu"></span>' +
                    '<span class="tbp-topic-title">' + title + '</span>' +
                    '<button type="button" class="tbp-topic-remove button-link" title="Remove">' +
                    '<span class="dashicons dashicons-no-alt"></span></button>' +
                    '<input type="hidden" name="tbp_subject_topics[]" value="' + id + '">' +
                    '</li>');

                $assigned.append($newItem);
                $item.remove();

                // Add empty message to available if needed
                if ($available.find('.tbp-topic-item').length === 0) {
                    $available.append('<li class="tbp-topics-empty">No topics available</li>');
                }

                updateCount();
            });

            // Remove topic
            $(document).on('click', '.tbp-topic-remove', function() {
                var $item = $(this).closest('.tbp-topic-item');
                var id = $item.data('id');
                var title = $item.find('.tbp-topic-title').text();

                // Remove empty message if exists
                $available.find('.tbp-topics-empty').remove();

                // Create new item for available list
                var $newItem = $('<li class="tbp-topic-item" data-id="' + id + '">' +
                    '<span class="tbp-topic-title">' + title + '</span>' +
                    '<button type="button" class="tbp-topic-add button-link" title="Add">' +
                    '<span class="dashicons dashicons-plus-alt2"></span></button>' +
                    '</li>');

                // Insert in alphabetical order
                var inserted = false;
                $available.find('.tbp-topic-item').each(function() {
                    if ($(this).find('.tbp-topic-title').text() > title) {
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
                if ($assigned.find('.tbp-topic-item').length === 0) {
                    $assigned.append('<li class="tbp-topics-empty">No topics assigned. Add topics from the left.</li>');
                }

                updateCount();
            });

            // Search filter
            $('.tbp-topics-search-input').on('keyup', function() {
                var search = $(this).val().toLowerCase();
                $available.find('.tbp-topic-item').each(function() {
                    var title = $(this).find('.tbp-topic-title').text().toLowerCase();
                    $(this).toggle(title.indexOf(search) > -1);
                });
            });

            function updateCount() {
                var count = $assigned.find('.tbp-topic-item').length;
                $('.tbp-topics-count').text('(' + count + ')');
            }
        });
        </script>
        <?php
    }

    public function save_topics($post_id, $post) {
        // Check nonce
        if (!isset($_POST['tbp_subject_topics_nonce']) ||
            !wp_verify_nonce($_POST['tbp_subject_topics_nonce'], 'tbp_subject_topics')) {
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

        // Get topic IDs from form
        $topic_ids = isset($_POST['tbp_subject_topics']) ? array_map('absint', $_POST['tbp_subject_topics']) : [];

        // Sync topics using pivot table
        TBP_Subject_Topic_Pivot::sync_topics_for_subject($post_id, $topic_ids);
    }
}
