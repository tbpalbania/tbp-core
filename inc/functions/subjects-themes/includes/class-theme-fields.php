<?php
/**
 * Theme Custom Fields
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Theme_Fields {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_tbp_theme', [$this, 'save_meta'], 10, 2);

        // Register ACF field group for resources
        add_action('acf/init', [$this, 'register_acf_fields']);

        // Add columns to admin list
        add_filter('manage_tbp_theme_posts_columns', [$this, 'add_columns']);
        add_action('manage_tbp_theme_posts_custom_column', [$this, 'render_columns'], 10, 2);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'tbp_theme_details',
            __('Theme Details', 'tbp-core'),
            [$this, 'render_details_metabox'],
            'tbp_theme',
            'normal',
            'high'
        );

        add_meta_box(
            'tbp_theme_subjects',
            __('Linked Subjects', 'tbp-core'),
            [$this, 'render_subjects_metabox'],
            'tbp_theme',
            'side',
            'default'
        );
    }

    public function render_details_metabox($post) {
        wp_nonce_field('tbp_theme_details', 'tbp_theme_details_nonce');

        $duration = get_post_meta($post->ID, '_tbp_theme_duration', true);
        $objectives = get_post_meta($post->ID, '_tbp_theme_objectives', true);
        ?>
        <table class="form-table tbp-theme-fields">
            <tr>
                <th><label for="tbp_theme_duration"><?php _e('Duration (hours)', 'tbp-core'); ?></label></th>
                <td>
                    <input type="number"
                           id="tbp_theme_duration"
                           name="_tbp_theme_duration"
                           value="<?php echo esc_attr($duration); ?>"
                           class="small-text"
                           min="0"
                           step="0.5">
                </td>
            </tr>
            <tr>
                <th><label for="tbp_theme_objectives"><?php _e('Learning Objectives', 'tbp-core'); ?></label></th>
                <td>
                    <textarea id="tbp_theme_objectives"
                              name="_tbp_theme_objectives"
                              rows="5"
                              class="large-text"><?php echo esc_textarea($objectives); ?></textarea>
                    <p class="description"><?php _e('Enter the learning objectives for this theme.', 'tbp-core'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_subjects_metabox($post) {
        $subjects = TBP_Subject_Theme_Pivot::get_subjects_for_theme($post->ID);

        if (empty($subjects)) {
            echo '<p>' . __('This theme is not linked to any subjects yet.', 'tbp-core') . '</p>';
            return;
        }

        echo '<ul class="tbp-linked-subjects">';
        foreach ($subjects as $subject) {
            printf(
                '<li><a href="%s">%s</a></li>',
                esc_url(get_edit_post_link($subject->ID)),
                esc_html($subject->post_title)
            );
        }
        echo '</ul>';
    }

    /**
     * Register ACF fields for resources (dropzone)
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_tbp_theme_resources',
            'title' => __('Resources', 'tbp-core'),
            'fields' => [
                [
                    'key' => 'field_tbp_theme_resources',
                    'label' => __('Theme Resources', 'tbp-core'),
                    'name' => '_tbp_theme_resources',
                    'type' => 'tbp_dropzone',
                    'instructions' => __('Upload files, documents, or other resources for this theme.', 'tbp-core'),
                    'required' => 0,
                    'max_files' => 0,
                    'max_size' => 50,
                    'allowed_types' => 'pdf, doc, docx, xls, xlsx, ppt, pptx, zip, jpg, png, gif, mp4, mp3',
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                    'display_mode' => 'list',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'tbp_theme',
                    ],
                ],
            ],
            'menu_order' => 10,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);
    }

    public function save_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['tbp_theme_details_nonce']) ||
            !wp_verify_nonce($_POST['tbp_theme_details_nonce'], 'tbp_theme_details')) {
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

        // Save theme details
        if (isset($_POST['_tbp_theme_duration'])) {
            update_post_meta($post_id, '_tbp_theme_duration', sanitize_text_field($_POST['_tbp_theme_duration']));
        }

        if (isset($_POST['_tbp_theme_objectives'])) {
            update_post_meta($post_id, '_tbp_theme_objectives', sanitize_textarea_field($_POST['_tbp_theme_objectives']));
        }
    }

    public function add_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['duration'] = __('Duration', 'tbp-core');
                $new_columns['subjects_count'] = __('Subjects', 'tbp-core');
            }
        }

        return $new_columns;
    }

    public function render_columns($column, $post_id) {
        switch ($column) {
            case 'duration':
                $duration = get_post_meta($post_id, '_tbp_theme_duration', true);
                if ($duration) {
                    echo esc_html($duration) . ' ' . __('hrs', 'tbp-core');
                } else {
                    echo '—';
                }
                break;

            case 'subjects_count':
                $count = TBP_Subject_Theme_Pivot::get_subject_count($post_id);
                echo esc_html($count);
                break;
        }
    }
}
