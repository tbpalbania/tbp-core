<?php
/**
 * Chain Selector Component
 * Reusable component for hierarchical taxonomy selection
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Chain_Selector {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_tbp_get_chain_terms', [$this, 'ajax_get_terms']);
        add_action('wp_ajax_nopriv_tbp_get_chain_terms', [$this, 'ajax_get_terms']);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_register_script(
            'tbp-chain-selector',
            TBP_FACULTY_TAX_URL . 'assets/js/chain-selector.js',
            ['jquery'],
            TBP_CORE_VERSION,
            true
        );

        wp_register_style(
            'tbp-chain-selector',
            TBP_FACULTY_TAX_URL . 'assets/css/chain-selector.css',
            [],
            TBP_CORE_VERSION
        );

        wp_localize_script('tbp-chain-selector', 'tbpChainSelector', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_chain_selector'),
        ]);
    }

    /**
     * AJAX handler to get child terms
     */
    public function ajax_get_terms() {
        check_ajax_referer('tbp_chain_selector', 'nonce');

        $parent_id = isset($_POST['parent_id']) ? absint($_POST['parent_id']) : 0;
        $child_taxonomy = isset($_POST['child_taxonomy']) ? sanitize_text_field($_POST['child_taxonomy']) : '';
        $parent_meta_key = isset($_POST['parent_meta_key']) ? sanitize_text_field($_POST['parent_meta_key']) : '';

        if (!$parent_id || !$child_taxonomy || !$parent_meta_key) {
            wp_send_json_error('Missing parameters');
        }

        $terms = get_terms([
            'taxonomy' => $child_taxonomy,
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => $parent_meta_key,
                    'value' => $parent_id,
                    'compare' => '=',
                ],
            ],
        ]);

        if (is_wp_error($terms)) {
            wp_send_json_error($terms->get_error_message());
        }

        $options = [];
        foreach ($terms as $term) {
            $options[] = [
                'id' => $term->term_id,
                'name' => $term->name,
            ];
        }

        wp_send_json_success($options);
    }

    /**
     * Render chain selector fields
     *
     * @param array $args Configuration array
     * @param array $values Current selected values [faculty_id, department_id, cycle_id, profile_id]
     * @param string $context 'user' or 'post'
     * @param string $field_prefix Prefix for field names
     */
    public static function render($args = [], $values = [], $context = 'user', $field_prefix = 'tbp_academic') {
        wp_enqueue_script('tbp-chain-selector');
        wp_enqueue_style('tbp-chain-selector');

        $defaults = [
            'show_faculty' => true,
            'show_department' => true,
            'show_cycle' => true,
            'show_profile' => true,
            'required' => false,
            'wrapper_class' => 'tbp-chain-selector',
        ];

        $args = wp_parse_args($args, $defaults);
        $required = $args['required'] ? 'required' : '';

        // Get current values
        $faculty_id = isset($values['faculty']) ? absint($values['faculty']) : 0;
        $department_id = isset($values['department']) ? absint($values['department']) : 0;
        $cycle_id = isset($values['cycle']) ? absint($values['cycle']) : 0;
        $profile_id = isset($values['profile']) ? absint($values['profile']) : 0;

        // Get all faculties
        $faculties = get_terms([
            'taxonomy' => 'tbp_faculty',
            'hide_empty' => false,
        ]);

        // Get departments for selected faculty
        $departments = $faculty_id ? TBP_Academic_Taxonomies::get_departments_by_faculty($faculty_id) : [];

        // Get cycles for selected department
        $cycles = $department_id ? TBP_Academic_Taxonomies::get_cycles_by_department($department_id) : [];

        // Get profiles for selected cycle
        $profiles = $cycle_id ? TBP_Academic_Taxonomies::get_profiles_by_cycle($cycle_id) : [];

        ?>
        <div class="<?php echo esc_attr($args['wrapper_class']); ?>" data-prefix="<?php echo esc_attr($field_prefix); ?>">
            <?php if ($args['show_faculty']) : ?>
            <div class="tbp-chain-field tbp-chain-faculty">
                <label for="<?php echo esc_attr($field_prefix); ?>_faculty"><?php _e('Faculty', 'tbp-core'); ?></label>
                <select name="<?php echo esc_attr($field_prefix); ?>_faculty"
                        id="<?php echo esc_attr($field_prefix); ?>_faculty"
                        class="tbp-chain-select"
                        data-child="<?php echo esc_attr($field_prefix); ?>_department"
                        data-child-taxonomy="tbp_department"
                        data-parent-meta-key="parent_faculty"
                        <?php echo $required; ?>>
                    <option value=""><?php _e('— Select Faculty —', 'tbp-core'); ?></option>
                    <?php foreach ($faculties as $faculty) : ?>
                        <option value="<?php echo esc_attr($faculty->term_id); ?>" <?php selected($faculty_id, $faculty->term_id); ?>>
                            <?php echo esc_html($faculty->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_department']) : ?>
            <div class="tbp-chain-field tbp-chain-department">
                <label for="<?php echo esc_attr($field_prefix); ?>_department"><?php _e('Department', 'tbp-core'); ?></label>
                <select name="<?php echo esc_attr($field_prefix); ?>_department"
                        id="<?php echo esc_attr($field_prefix); ?>_department"
                        class="tbp-chain-select"
                        data-child="<?php echo esc_attr($field_prefix); ?>_cycle"
                        data-child-taxonomy="tbp_cycle"
                        data-parent-meta-key="parent_department"
                        <?php echo $required; ?>>
                    <option value=""><?php _e('— Select Department —', 'tbp-core'); ?></option>
                    <?php foreach ($departments as $department) : ?>
                        <option value="<?php echo esc_attr($department->term_id); ?>" <?php selected($department_id, $department->term_id); ?>>
                            <?php echo esc_html($department->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_cycle']) : ?>
            <div class="tbp-chain-field tbp-chain-cycle">
                <label for="<?php echo esc_attr($field_prefix); ?>_cycle"><?php _e('Cycle', 'tbp-core'); ?></label>
                <select name="<?php echo esc_attr($field_prefix); ?>_cycle"
                        id="<?php echo esc_attr($field_prefix); ?>_cycle"
                        class="tbp-chain-select"
                        data-child="<?php echo esc_attr($field_prefix); ?>_profile"
                        data-child-taxonomy="tbp_profile"
                        data-parent-meta-key="parent_cycle"
                        <?php echo $required; ?>>
                    <option value=""><?php _e('— Select Cycle —', 'tbp-core'); ?></option>
                    <?php foreach ($cycles as $cycle) : ?>
                        <option value="<?php echo esc_attr($cycle->term_id); ?>" <?php selected($cycle_id, $cycle->term_id); ?>>
                            <?php echo esc_html($cycle->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <?php if ($args['show_profile']) : ?>
            <div class="tbp-chain-field tbp-chain-profile">
                <label for="<?php echo esc_attr($field_prefix); ?>_profile"><?php _e('Profile', 'tbp-core'); ?></label>
                <select name="<?php echo esc_attr($field_prefix); ?>_profile"
                        id="<?php echo esc_attr($field_prefix); ?>_profile"
                        class="tbp-chain-select"
                        <?php echo $required; ?>>
                    <option value=""><?php _e('— Select Profile —', 'tbp-core'); ?></option>
                    <?php foreach ($profiles as $profile) : ?>
                        <option value="<?php echo esc_attr($profile->term_id); ?>" <?php selected($profile_id, $profile->term_id); ?>>
                            <?php echo esc_html($profile->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save chain selector values to user meta
     */
    public static function save_user_meta($user_id, $field_prefix = 'tbp_academic') {
        $fields = ['faculty', 'department', 'cycle', 'profile'];

        foreach ($fields as $field) {
            $field_name = $field_prefix . '_' . $field;
            if (isset($_POST[$field_name])) {
                update_user_meta($user_id, $field_name, absint($_POST[$field_name]));
            }
        }
    }

    /**
     * Get chain selector values from user meta
     */
    public static function get_user_meta($user_id, $field_prefix = 'tbp_academic') {
        return [
            'faculty' => get_user_meta($user_id, $field_prefix . '_faculty', true),
            'department' => get_user_meta($user_id, $field_prefix . '_department', true),
            'cycle' => get_user_meta($user_id, $field_prefix . '_cycle', true),
            'profile' => get_user_meta($user_id, $field_prefix . '_profile', true),
        ];
    }

    /**
     * Save chain selector values to post meta
     */
    public static function save_post_meta($post_id, $field_prefix = 'tbp_academic') {
        $fields = ['faculty', 'department', 'cycle', 'profile'];

        foreach ($fields as $field) {
            $field_name = $field_prefix . '_' . $field;
            if (isset($_POST[$field_name])) {
                update_post_meta($post_id, $field_name, absint($_POST[$field_name]));
            }
        }
    }

    /**
     * Get chain selector values from post meta
     */
    public static function get_post_meta($post_id, $field_prefix = 'tbp_academic') {
        return [
            'faculty' => get_post_meta($post_id, $field_prefix . '_faculty', true),
            'department' => get_post_meta($post_id, $field_prefix . '_department', true),
            'cycle' => get_post_meta($post_id, $field_prefix . '_cycle', true),
            'profile' => get_post_meta($post_id, $field_prefix . '_profile', true),
        ];
    }
}
