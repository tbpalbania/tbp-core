<?php
/**
 * Topic Custom Fields
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Topic_Fields {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_tbp_topic', [$this, 'save_meta'], 10, 2);

        // Register ACF field group for resources
        add_action('acf/init', [$this, 'register_acf_fields']);

        // Add columns to admin list
        add_filter('manage_tbp_topic_posts_columns', [$this, 'add_columns']);
        add_action('manage_tbp_topic_posts_custom_column', [$this, 'render_columns'], 10, 2);

        // Admin styles
        add_action('admin_head', [$this, 'admin_styles']);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'tbp_topic_details',
            __('Topic Settings', 'tbp-core'),
            [$this, 'render_details_metabox'],
            'tbp_topic',
            'normal',
            'high'
        );

        add_meta_box(
            'tbp_topic_academic',
            __('Academic Assignment', 'tbp-core'),
            [$this, 'render_academic_metabox'],
            'tbp_topic',
            'side',
            'high'
        );

        add_meta_box(
            'tbp_topic_subjects',
            __('Linked Subjects', 'tbp-core'),
            [$this, 'render_subjects_metabox'],
            'tbp_topic',
            'side',
            'default'
        );
    }

    public function render_academic_metabox($post) {
        // Check if chain selector is available
        if (!class_exists('TBP_Chain_Selector')) {
            echo '<p>' . __('Enable "Faculty, Department, Cycle & Profile Taxonomies" module to use academic assignment.', 'tbp-core') . '</p>';
            return;
        }

        wp_nonce_field('tbp_topic_academic', 'tbp_topic_academic_nonce');

        $values = TBP_Chain_Selector::get_post_meta($post->ID);
        TBP_Chain_Selector::render([], $values, 'post');
    }

    public function render_details_metabox($post) {
        wp_nonce_field('tbp_topic_details', 'tbp_topic_details_nonce');

        $duration = get_post_meta($post->ID, '_tbp_topic_duration', true);
        ?>
        <div class="tbp-compact-fields">
            <div class="tbp-field-row">
                <div class="tbp-field tbp-field-half">
                    <label for="tbp_topic_duration">
                        <?php _e('Duration (hours)', 'tbp-core'); ?>
                        <span class="tbp-tooltip" data-tooltip="<?php esc_attr_e('Total duration in hours for this topic.', 'tbp-core'); ?>">?</span>
                    </label>
                    <input type="number"
                           id="tbp_topic_duration"
                           name="_tbp_topic_duration"
                           value="<?php echo esc_attr($duration); ?>"
                           min="0"
                           step="0.5">
                </div>
            </div>
        </div>
        <?php
    }

    public function render_subjects_metabox($post) {
        $subjects = TBP_Subject_Topic_Pivot::get_subjects_for_topic($post->ID);

        if (empty($subjects)) {
            echo '<p>' . __('This topic is not linked to any subjects yet.', 'tbp-core') . '</p>';
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
     * Register ACF fields for syllabus and resources (dropzone)
     */
    public function register_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_tbp_topic_resources',
            'title' => __('Resources', 'tbp-core'),
            'fields' => [
                [
                    'key' => 'field_tbp_topic_syllabus',
                    'label' => __('Syllabus', 'tbp-core'),
                    'name' => '_tbp_topic_syllabus',
                    'type' => 'tbp_dropzone',
                    'instructions' => '',
                    'required' => 0,
                    'max_files' => 1,
                    'max_size' => 50,
                    'allowed_types' => 'pdf, doc, docx',
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                    'display_mode' => 'list',
                ],
                [
                    'key' => 'field_tbp_topic_resources',
                    'label' => __('Topic Resources', 'tbp-core'),
                    'name' => '_tbp_topic_resources',
                    'type' => 'tbp_dropzone',
                    'instructions' => '',
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
                        'value' => 'tbp_topic',
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

    /**
     * Admin styles for compact fields and tooltips
     */
    public function admin_styles() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['tbp_topic', 'tbp_subject'])) {
            return;
        }
        ?>
        <style>
            /* Compact Fields Layout */
            .tbp-compact-fields { padding: 12px 0; }
            .tbp-field-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
            .tbp-field-row:last-child { margin-bottom: 0; }
            .tbp-field { display: flex; flex-direction: column; }
            .tbp-field-half { flex: 0 0 calc(50% - 8px); max-width: calc(50% - 8px); }
            .tbp-field-full { flex: 0 0 100%; max-width: 100%; }
            .tbp-field label { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 600; }
            .tbp-field input[type="text"],
            .tbp-field input[type="number"],
            .tbp-field select,
            .tbp-field textarea { width: 100%; }
            .tbp-field input[type="number"] { max-width: 120px; }

            /* Tooltip */
            .tbp-tooltip {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 16px;
                height: 16px;
                background: #c3c4c7;
                color: #fff;
                border-radius: 50%;
                font-size: 11px;
                font-weight: bold;
                cursor: pointer;
                position: relative;
                user-select: none;
            }
            .tbp-tooltip:hover { background: #2271b1; }
            .tbp-tooltip.active { background: #2271b1; }
            .tbp-tooltip-content {
                display: none;
                position: absolute;
                top: calc(100% + 8px);
                left: 50%;
                transform: translateX(-50%);
                background: #1d2327;
                color: #fff;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: normal;
                white-space: nowrap;
                max-width: 250px;
                white-space: normal;
                z-index: 100;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .tbp-tooltip-content::before {
                content: '';
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 6px solid transparent;
                border-bottom-color: #1d2327;
            }
            .tbp-tooltip.active .tbp-tooltip-content { display: block; }

            @media screen and (max-width: 782px) {
                .tbp-field-half { flex: 0 0 100%; max-width: 100%; }
            }
        </style>
        <script>
        jQuery(document).ready(function($) {
            // Initialize tooltips
            $('.tbp-tooltip').each(function() {
                var $tooltip = $(this);
                var text = $tooltip.data('tooltip');
                if (text) {
                    $tooltip.append('<span class="tbp-tooltip-content">' + text + '</span>');
                }
            });

            // Toggle tooltip on click
            $(document).on('click', '.tbp-tooltip', function(e) {
                e.stopPropagation();
                var $this = $(this);
                $('.tbp-tooltip').not($this).removeClass('active');
                $this.toggleClass('active');
            });

            // Close tooltip when clicking outside
            $(document).on('click', function() {
                $('.tbp-tooltip').removeClass('active');
            });
        });
        </script>
        <?php
    }

    public function save_meta($post_id, $post) {
        // Check nonce for details
        $details_nonce_valid = isset($_POST['tbp_topic_details_nonce']) &&
            wp_verify_nonce($_POST['tbp_topic_details_nonce'], 'tbp_topic_details');

        // Check nonce for academic
        $academic_nonce_valid = isset($_POST['tbp_topic_academic_nonce']) &&
            wp_verify_nonce($_POST['tbp_topic_academic_nonce'], 'tbp_topic_academic');

        if (!$details_nonce_valid && !$academic_nonce_valid) {
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

        // Save topic details
        if ($details_nonce_valid) {
            if (isset($_POST['_tbp_topic_duration'])) {
                update_post_meta($post_id, '_tbp_topic_duration', sanitize_text_field($_POST['_tbp_topic_duration']));
            }
        }

        // Save academic chain selector
        if ($academic_nonce_valid && class_exists('TBP_Chain_Selector')) {
            TBP_Chain_Selector::save_post_meta($post_id);
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
                $duration = get_post_meta($post_id, '_tbp_topic_duration', true);
                if ($duration) {
                    echo esc_html($duration) . ' ' . __('hrs', 'tbp-core');
                } else {
                    echo '—';
                }
                break;

            case 'subjects_count':
                $count = TBP_Subject_Topic_Pivot::get_subject_count($post_id);
                echo esc_html($count);
                break;
        }
    }
}
