<?php
/**
 * Subject Custom Fields
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Subject_Fields {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_tbp_subject', [$this, 'save_meta'], 10, 2);

        // Add columns to admin list
        add_filter('manage_tbp_subject_posts_columns', [$this, 'add_columns']);
        add_action('manage_tbp_subject_posts_custom_column', [$this, 'render_columns'], 10, 2);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'tbp_subject_details',
            __('Subject Settings', 'tbp-core'),
            [$this, 'render_details_metabox'],
            'tbp_subject',
            'normal',
            'high'
        );

        add_meta_box(
            'tbp_subject_academic',
            __('Academic Assignment', 'tbp-core'),
            [$this, 'render_academic_metabox'],
            'tbp_subject',
            'side',
            'default'
        );
    }

    public function render_details_metabox($post) {
        wp_nonce_field('tbp_subject_details', 'tbp_subject_details_nonce');

        $subject_code = get_post_meta($post->ID, '_tbp_subject_code', true);
        $credits = get_post_meta($post->ID, '_tbp_subject_credits', true);
        $duration = get_post_meta($post->ID, '_tbp_subject_duration', true);
        ?>
        <div class="tbp-compact-fields">
            <div class="tbp-field-row">
                <div class="tbp-field tbp-field-half">
                    <label for="tbp_subject_code">
                        <?php _e('Subject Code', 'tbp-core'); ?>
                        <span class="tbp-tooltip" data-tooltip="<?php esc_attr_e('Unique identifier for this subject (e.g., CS101, MATH201).', 'tbp-core'); ?>">?</span>
                    </label>
                    <input type="text"
                           id="tbp_subject_code"
                           name="_tbp_subject_code"
                           value="<?php echo esc_attr($subject_code); ?>"
                           placeholder="e.g., CS101">
                </div>
                <div class="tbp-field tbp-field-half">
                    <label for="tbp_subject_credits">
                        <?php _e('Credits/ECTS', 'tbp-core'); ?>
                        <span class="tbp-tooltip" data-tooltip="<?php esc_attr_e('Number of credits or ECTS points for this subject.', 'tbp-core'); ?>">?</span>
                    </label>
                    <input type="number"
                           id="tbp_subject_credits"
                           name="_tbp_subject_credits"
                           value="<?php echo esc_attr($credits); ?>"
                           min="0"
                           step="0.5">
                </div>
            </div>
            <div class="tbp-field-row">
                <div class="tbp-field tbp-field-half">
                    <label for="tbp_subject_duration">
                        <?php _e('Total Duration (hours)', 'tbp-core'); ?>
                        <span class="tbp-tooltip" data-tooltip="<?php esc_attr_e('Total teaching hours for this subject.', 'tbp-core'); ?>">?</span>
                    </label>
                    <input type="number"
                           id="tbp_subject_duration"
                           name="_tbp_subject_duration"
                           value="<?php echo esc_attr($duration); ?>"
                           min="0"
                           step="0.5">
                </div>
            </div>
        </div>
        <?php
    }

    public function render_academic_metabox($post) {
        // Check if chain selector is available
        if (!class_exists('TBP_Chain_Selector')) {
            echo '<p>' . __('Enable "Faculty, Department, Cycle & Profile Taxonomies" module to use academic assignment.', 'tbp-core') . '</p>';
            return;
        }

        $values = TBP_Chain_Selector::get_post_meta($post->ID);
        TBP_Chain_Selector::render([], $values, 'post');
    }

    public function save_meta($post_id, $post) {
        // Check nonce
        if (!isset($_POST['tbp_subject_details_nonce']) ||
            !wp_verify_nonce($_POST['tbp_subject_details_nonce'], 'tbp_subject_details')) {
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

        // Save subject details
        $fields = ['_tbp_subject_code', '_tbp_subject_credits', '_tbp_subject_duration'];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save academic chain selector
        if (class_exists('TBP_Chain_Selector')) {
            TBP_Chain_Selector::save_post_meta($post_id);
        }
    }

    public function add_columns($columns) {
        $new_columns = [];

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['subject_code'] = __('Code', 'tbp-core');
                $new_columns['credits'] = __('Credits', 'tbp-core');
                $new_columns['topics_count'] = __('Topics', 'tbp-core');
            }
        }

        return $new_columns;
    }

    public function render_columns($column, $post_id) {
        switch ($column) {
            case 'subject_code':
                echo esc_html(get_post_meta($post_id, '_tbp_subject_code', true) ?: '—');
                break;

            case 'credits':
                $credits = get_post_meta($post_id, '_tbp_subject_credits', true);
                echo $credits ? esc_html($credits) : '—';
                break;

            case 'topics_count':
                $count = TBP_Subject_Topic_Pivot::get_topic_count($post_id);
                echo esc_html($count);
                break;
        }
    }
}
