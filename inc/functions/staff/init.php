<?php
/**
 * @module-title: Staff
 * @module-version: 1.0.0
 * @module-description: Staff post type with custom fields for team members
 * @module-usage: Create and manage staff/team member profiles
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Staff {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('admin_menu', [$this, 'add_submenu'], 99);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_staff', [$this, 'save_meta_boxes'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('wp_insert_post_data', [$this, 'auto_generate_title'], 10, 2);
        add_filter('get_the_excerpt', [$this, 'auto_fill_excerpt'], 10, 2);

        // AJAX handlers for repeater
        add_action('wp_ajax_tbp_staff_add_education', [$this, 'ajax_add_education_row']);
        add_action('wp_ajax_tbp_staff_add_experience', [$this, 'ajax_add_experience_row']);
    }

    public function register_post_type() {
        $labels = [
            'name'                  => __('Staff', 'tbp-core'),
            'singular_name'         => __('Staff Member', 'tbp-core'),
            'menu_name'             => __('Staff', 'tbp-core'),
            'name_admin_bar'        => __('Staff Member', 'tbp-core'),
            'add_new'               => __('Add New', 'tbp-core'),
            'add_new_item'          => __('Add New Staff Member', 'tbp-core'),
            'new_item'              => __('New Staff Member', 'tbp-core'),
            'edit_item'             => __('Edit Staff Member', 'tbp-core'),
            'view_item'             => __('View Staff Member', 'tbp-core'),
            'all_items'             => __('All Staff', 'tbp-core'),
            'search_items'          => __('Search Staff', 'tbp-core'),
            'not_found'             => __('No staff members found.', 'tbp-core'),
            'not_found_in_trash'    => __('No staff members found in Trash.', 'tbp-core'),
            'featured_image'        => __('Staff Photo', 'tbp-core'),
            'set_featured_image'    => __('Set staff photo', 'tbp-core'),
            'remove_featured_image' => __('Remove staff photo', 'tbp-core'),
            'use_featured_image'    => __('Use as staff photo', 'tbp-core'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll add it as submenu
            'query_var'          => true,
            'rewrite'            => ['slug' => 'staff', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'supports'           => ['thumbnail', 'excerpt'],
            'show_in_rest'       => true,
        ];

        register_post_type('staff', $args);
    }

    public function add_submenu() {
        add_submenu_page(
            'general-settings',
            __('All Staff', 'tbp-core'),
            __('All Staff', 'tbp-core'),
            'edit_posts',
            'edit.php?post_type=staff'
        );
    }

    public function add_meta_boxes() {
        add_meta_box(
            'tbp_staff_details',
            __('Staff Details', 'tbp-core'),
            [$this, 'render_details_meta_box'],
            'staff',
            'normal',
            'high'
        );

        add_meta_box(
            'tbp_staff_education',
            __('Education', 'tbp-core'),
            [$this, 'render_education_meta_box'],
            'staff',
            'normal',
            'default'
        );

        add_meta_box(
            'tbp_staff_experience',
            __('Experience', 'tbp-core'),
            [$this, 'render_experience_meta_box'],
            'staff',
            'normal',
            'default'
        );
    }

    public function render_details_meta_box($post) {
        wp_nonce_field('tbp_staff_details_nonce', 'tbp_staff_nonce');

        $designation = get_post_meta($post->ID, '_tbp_staff_designation', true);
        $first_name = get_post_meta($post->ID, '_tbp_staff_first_name', true);
        $last_name = get_post_meta($post->ID, '_tbp_staff_last_name', true);
        $position = get_post_meta($post->ID, '_tbp_staff_position', true);
        $biography = get_post_meta($post->ID, '_tbp_staff_biography', true);
        ?>
        <div class="tbp-staff-fields">
            <div class="tbp-staff-row tbp-staff-row--third">
                <div class="tbp-staff-field" style="flex: 0 0 120px;">
                    <label for="tbp_staff_designation"><?php esc_html_e('Designation', 'tbp-core'); ?></label>
                    <input type="text"
                           id="tbp_staff_designation"
                           name="tbp_staff_designation"
                           value="<?php echo esc_attr($designation); ?>"
                           class="widefat"
                           placeholder="<?php esc_attr_e('e.g., Dr.', 'tbp-core'); ?>">
                </div>
                <div class="tbp-staff-field">
                    <label for="tbp_staff_first_name"><?php esc_html_e('First Name', 'tbp-core'); ?> <span class="required">*</span></label>
                    <input type="text"
                           id="tbp_staff_first_name"
                           name="tbp_staff_first_name"
                           value="<?php echo esc_attr($first_name); ?>"
                           class="widefat tbp-staff-name-field"
                           required>
                </div>
                <div class="tbp-staff-field">
                    <label for="tbp_staff_last_name"><?php esc_html_e('Last Name', 'tbp-core'); ?> <span class="required">*</span></label>
                    <input type="text"
                           id="tbp_staff_last_name"
                           name="tbp_staff_last_name"
                           value="<?php echo esc_attr($last_name); ?>"
                           class="widefat tbp-staff-name-field"
                           required>
                </div>
            </div>

            <div class="tbp-staff-row">
                <div class="tbp-staff-field tbp-staff-field--readonly">
                    <label for="tbp_staff_title_preview"><?php esc_html_e('Generated Title', 'tbp-core'); ?></label>
                    <input type="text"
                           id="tbp_staff_title_preview"
                           value="<?php echo esc_attr($post->post_title); ?>"
                           class="widefat"
                           readonly
                           disabled>
                    <p class="description"><?php esc_html_e('Title is auto-generated from First Name and Last Name.', 'tbp-core'); ?></p>
                </div>
            </div>

            <div class="tbp-staff-row">
                <div class="tbp-staff-field">
                    <label for="tbp_staff_position"><?php esc_html_e('Position', 'tbp-core'); ?></label>
                    <input type="text"
                           id="tbp_staff_position"
                           name="tbp_staff_position"
                           value="<?php echo esc_attr($position); ?>"
                           class="widefat"
                           placeholder="<?php esc_attr_e('e.g., Senior Developer, Marketing Manager', 'tbp-core'); ?>">
                </div>
            </div>

            <div class="tbp-staff-row">
                <div class="tbp-staff-field">
                    <label for="tbp_staff_biography"><?php esc_html_e('Biography', 'tbp-core'); ?></label>
                    <?php
                    wp_editor($biography, 'tbp_staff_biography', [
                        'textarea_name' => 'tbp_staff_biography',
                        'textarea_rows' => 10,
                        'media_buttons' => true,
                        'teeny'         => false,
                        'quicktags'     => true,
                    ]);
                    ?>
                    <p class="description"><?php esc_html_e('If excerpt is empty, it will be auto-filled from this biography.', 'tbp-core'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_education_meta_box($post) {
        $education = get_post_meta($post->ID, '_tbp_staff_education', true);
        if (!is_array($education)) {
            $education = [];
        }
        ?>
        <div class="tbp-staff-repeater" data-type="education">
            <div class="tbp-staff-repeater__items" id="tbp-education-items">
                <?php
                if (!empty($education)) {
                    foreach ($education as $index => $item) {
                        $this->render_education_row($index, $item);
                    }
                }
                ?>
            </div>
            <button type="button" class="button tbp-staff-repeater__add" data-type="education">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add Education', 'tbp-core'); ?>
            </button>
        </div>
        <?php
    }

    public function render_education_row($index, $item = []) {
        $degree = $item['degree'] ?? '';
        $institution = $item['institution'] ?? '';
        $start_date = $item['start_date'] ?? '';
        $end_date = $item['end_date'] ?? '';
        $current = $item['current'] ?? '';
        $description = $item['description'] ?? '';
        ?>
        <div class="tbp-staff-repeater__item tbp-staff-repeater__item--collapsed" data-index="<?php echo esc_attr($index); ?>">
            <div class="tbp-staff-repeater__header">
                <span class="tbp-staff-repeater__handle dashicons dashicons-menu"></span>
                <span class="tbp-staff-repeater__title"><?php echo $degree ? esc_html($degree) : esc_html__('New Education', 'tbp-core'); ?></span>
                <button type="button" class="tbp-staff-repeater__toggle" aria-expanded="false">
                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                </button>
                <button type="button" class="tbp-staff-repeater__remove">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="tbp-staff-repeater__content">
                <div class="tbp-staff-row tbp-staff-row--half">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Degree / Certificate', 'tbp-core'); ?></label>
                        <input type="text"
                               name="tbp_staff_education[<?php echo esc_attr($index); ?>][degree]"
                               value="<?php echo esc_attr($degree); ?>"
                               class="widefat tbp-staff-repeater__title-field"
                               placeholder="<?php esc_attr_e('e.g., Bachelor of Science', 'tbp-core'); ?>">
                    </div>
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Institution', 'tbp-core'); ?></label>
                        <input type="text"
                               name="tbp_staff_education[<?php echo esc_attr($index); ?>][institution]"
                               value="<?php echo esc_attr($institution); ?>"
                               class="widefat"
                               placeholder="<?php esc_attr_e('e.g., Harvard University', 'tbp-core'); ?>">
                    </div>
                </div>
                <div class="tbp-staff-row tbp-staff-row--third">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('From', 'tbp-core'); ?></label>
                        <input type="month"
                               name="tbp_staff_education[<?php echo esc_attr($index); ?>][start_date]"
                               value="<?php echo esc_attr($start_date); ?>"
                               class="widefat">
                    </div>
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('To', 'tbp-core'); ?></label>
                        <input type="month"
                               name="tbp_staff_education[<?php echo esc_attr($index); ?>][end_date]"
                               value="<?php echo esc_attr($end_date); ?>"
                               class="widefat tbp-staff-end-date"
                               <?php echo $current ? 'disabled' : ''; ?>>
                    </div>
                    <div class="tbp-staff-field tbp-staff-field--checkbox">
                        <label>
                            <input type="checkbox"
                                   name="tbp_staff_education[<?php echo esc_attr($index); ?>][current]"
                                   value="1"
                                   class="tbp-staff-current-checkbox"
                                   <?php checked($current, '1'); ?>>
                            <?php esc_html_e('Present', 'tbp-core'); ?>
                        </label>
                    </div>
                </div>
                <div class="tbp-staff-row">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Description', 'tbp-core'); ?></label>
                        <textarea name="tbp_staff_education[<?php echo esc_attr($index); ?>][description]"
                                  rows="3"
                                  class="widefat"
                                  placeholder="<?php esc_attr_e('Additional details about this education...', 'tbp-core'); ?>"><?php echo esc_textarea($description); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_experience_meta_box($post) {
        $experience = get_post_meta($post->ID, '_tbp_staff_experience', true);
        if (!is_array($experience)) {
            $experience = [];
        }
        ?>
        <div class="tbp-staff-repeater" data-type="experience">
            <div class="tbp-staff-repeater__items" id="tbp-experience-items">
                <?php
                if (!empty($experience)) {
                    foreach ($experience as $index => $item) {
                        $this->render_experience_row($index, $item);
                    }
                }
                ?>
            </div>
            <button type="button" class="button tbp-staff-repeater__add" data-type="experience">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add Experience', 'tbp-core'); ?>
            </button>
        </div>
        <?php
    }

    public function render_experience_row($index, $item = []) {
        $job_title = $item['job_title'] ?? '';
        $company = $item['company'] ?? '';
        $start_date = $item['start_date'] ?? '';
        $end_date = $item['end_date'] ?? '';
        $current = $item['current'] ?? '';
        $description = $item['description'] ?? '';
        ?>
        <div class="tbp-staff-repeater__item tbp-staff-repeater__item--collapsed" data-index="<?php echo esc_attr($index); ?>">
            <div class="tbp-staff-repeater__header">
                <span class="tbp-staff-repeater__handle dashicons dashicons-menu"></span>
                <span class="tbp-staff-repeater__title"><?php echo $job_title ? esc_html($job_title) : esc_html__('New Experience', 'tbp-core'); ?></span>
                <button type="button" class="tbp-staff-repeater__toggle" aria-expanded="false">
                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                </button>
                <button type="button" class="tbp-staff-repeater__remove">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="tbp-staff-repeater__content">
                <div class="tbp-staff-row tbp-staff-row--half">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Job Title', 'tbp-core'); ?></label>
                        <input type="text"
                               name="tbp_staff_experience[<?php echo esc_attr($index); ?>][job_title]"
                               value="<?php echo esc_attr($job_title); ?>"
                               class="widefat tbp-staff-repeater__title-field"
                               placeholder="<?php esc_attr_e('e.g., Senior Developer', 'tbp-core'); ?>">
                    </div>
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Company', 'tbp-core'); ?></label>
                        <input type="text"
                               name="tbp_staff_experience[<?php echo esc_attr($index); ?>][company]"
                               value="<?php echo esc_attr($company); ?>"
                               class="widefat"
                               placeholder="<?php esc_attr_e('e.g., Google Inc.', 'tbp-core'); ?>">
                    </div>
                </div>
                <div class="tbp-staff-row tbp-staff-row--third">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('From', 'tbp-core'); ?></label>
                        <input type="month"
                               name="tbp_staff_experience[<?php echo esc_attr($index); ?>][start_date]"
                               value="<?php echo esc_attr($start_date); ?>"
                               class="widefat">
                    </div>
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('To', 'tbp-core'); ?></label>
                        <input type="month"
                               name="tbp_staff_experience[<?php echo esc_attr($index); ?>][end_date]"
                               value="<?php echo esc_attr($end_date); ?>"
                               class="widefat tbp-staff-end-date"
                               <?php echo $current ? 'disabled' : ''; ?>>
                    </div>
                    <div class="tbp-staff-field tbp-staff-field--checkbox">
                        <label>
                            <input type="checkbox"
                                   name="tbp_staff_experience[<?php echo esc_attr($index); ?>][current]"
                               value="1"
                                   class="tbp-staff-current-checkbox"
                                   <?php checked($current, '1'); ?>>
                            <?php esc_html_e('Present', 'tbp-core'); ?>
                        </label>
                    </div>
                </div>
                <div class="tbp-staff-row">
                    <div class="tbp-staff-field">
                        <label><?php esc_html_e('Description', 'tbp-core'); ?></label>
                        <textarea name="tbp_staff_experience[<?php echo esc_attr($index); ?>][description]"
                                  rows="3"
                                  class="widefat"
                                  placeholder="<?php esc_attr_e('Describe responsibilities and achievements...', 'tbp-core'); ?>"><?php echo esc_textarea($description); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function save_meta_boxes($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['tbp_staff_nonce']) || !wp_verify_nonce($_POST['tbp_staff_nonce'], 'tbp_staff_details_nonce')) {
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

        // Save basic fields
        $fields = [
            'tbp_staff_designation' => '_tbp_staff_designation',
            'tbp_staff_first_name'  => '_tbp_staff_first_name',
            'tbp_staff_last_name'   => '_tbp_staff_last_name',
            'tbp_staff_position'    => '_tbp_staff_position',
            'tbp_staff_biography'   => '_tbp_staff_biography',
        ];

        foreach ($fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                if ($field === 'tbp_staff_biography') {
                    update_post_meta($post_id, $meta_key, wp_kses_post($_POST[$field]));
                } else {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
                }
            }
        }

        // Save hidden combined field: Designation + Full Name
        $designation = isset($_POST['tbp_staff_designation']) ? sanitize_text_field($_POST['tbp_staff_designation']) : '';
        $first_name = isset($_POST['tbp_staff_first_name']) ? sanitize_text_field($_POST['tbp_staff_first_name']) : '';
        $last_name = isset($_POST['tbp_staff_last_name']) ? sanitize_text_field($_POST['tbp_staff_last_name']) : '';
        $full_name = trim($first_name . ' ' . $last_name);
        $full_name_with_designation = !empty($designation) ? trim($designation . ' ' . $full_name) : $full_name;
        update_post_meta($post_id, '_tbp_staff_full_name_with_designation', $full_name_with_designation);

        // Save education repeater
        if (isset($_POST['tbp_staff_education']) && is_array($_POST['tbp_staff_education'])) {
            $education = [];
            foreach ($_POST['tbp_staff_education'] as $item) {
                $education[] = [
                    'degree'      => sanitize_text_field($item['degree'] ?? ''),
                    'institution' => sanitize_text_field($item['institution'] ?? ''),
                    'start_date'  => sanitize_text_field($item['start_date'] ?? ''),
                    'end_date'    => sanitize_text_field($item['end_date'] ?? ''),
                    'current'     => isset($item['current']) ? '1' : '',
                    'description' => sanitize_textarea_field($item['description'] ?? ''),
                ];
            }
            update_post_meta($post_id, '_tbp_staff_education', $education);
        } else {
            delete_post_meta($post_id, '_tbp_staff_education');
        }

        // Save experience repeater
        if (isset($_POST['tbp_staff_experience']) && is_array($_POST['tbp_staff_experience'])) {
            $experience = [];
            foreach ($_POST['tbp_staff_experience'] as $item) {
                $experience[] = [
                    'job_title'   => sanitize_text_field($item['job_title'] ?? ''),
                    'company'     => sanitize_text_field($item['company'] ?? ''),
                    'start_date'  => sanitize_text_field($item['start_date'] ?? ''),
                    'end_date'    => sanitize_text_field($item['end_date'] ?? ''),
                    'current'     => isset($item['current']) ? '1' : '',
                    'description' => sanitize_textarea_field($item['description'] ?? ''),
                ];
            }
            update_post_meta($post_id, '_tbp_staff_experience', $experience);
        } else {
            delete_post_meta($post_id, '_tbp_staff_experience');
        }
    }

    public function auto_generate_title($data, $postarr) {
        if ($data['post_type'] !== 'staff') {
            return $data;
        }

        $first_name = isset($_POST['tbp_staff_first_name']) ? sanitize_text_field($_POST['tbp_staff_first_name']) : '';
        $last_name = isset($_POST['tbp_staff_last_name']) ? sanitize_text_field($_POST['tbp_staff_last_name']) : '';

        if (!empty($first_name) || !empty($last_name)) {
            $data['post_title'] = trim($first_name . ' ' . $last_name);
            $data['post_name'] = sanitize_title($data['post_title']);
        }

        return $data;
    }

    public function auto_fill_excerpt($excerpt, $post) {
        if ($post->post_type !== 'staff' || !empty($excerpt)) {
            return $excerpt;
        }

        $biography = get_post_meta($post->ID, '_tbp_staff_biography', true);
        if (!empty($biography)) {
            return wp_trim_words(wp_strip_all_tags($biography), 55, '...');
        }

        return $excerpt;
    }

    public function enqueue_admin_assets($hook) {
        global $post_type;

        if ($post_type !== 'staff' || !in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_style(
            'tbp-staff-admin',
            TBP_CORE_URL . 'inc/functions/staff/assets/css/admin.css',
            [],
            TBP_CORE_VERSION
        );

        wp_enqueue_script(
            'tbp-staff-admin',
            TBP_CORE_URL . 'inc/functions/staff/assets/js/admin.js',
            ['jquery', 'jquery-ui-sortable'],
            TBP_CORE_VERSION,
            true
        );

        wp_localize_script('tbp-staff-admin', 'tbpStaffAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tbp_staff_admin'),
            'i18n'    => [
                'newEducation'  => __('New Education', 'tbp-core'),
                'newExperience' => __('New Experience', 'tbp-core'),
                'confirmDelete' => __('Are you sure you want to remove this item?', 'tbp-core'),
            ],
        ]);
    }

    public function ajax_add_education_row() {
        check_ajax_referer('tbp_staff_admin', 'nonce');

        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;

        ob_start();
        $this->render_education_row($index);
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    public function ajax_add_experience_row() {
        check_ajax_referer('tbp_staff_admin', 'nonce');

        $index = isset($_POST['index']) ? intval($_POST['index']) : 0;

        ob_start();
        $this->render_experience_row($index);
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    // Static helper methods for templates and dynamic tags
    public static function get_staff_field($field, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        $meta_key = '_tbp_staff_' . $field;
        return get_post_meta($post_id, $meta_key, true);
    }

    public static function get_designation($post_id = null) {
        return self::get_staff_field('designation', $post_id);
    }

    public static function get_first_name($post_id = null) {
        return self::get_staff_field('first_name', $post_id);
    }

    public static function get_last_name($post_id = null) {
        return self::get_staff_field('last_name', $post_id);
    }

    public static function get_full_name($post_id = null, $include_designation = false) {
        $first = self::get_first_name($post_id);
        $last = self::get_last_name($post_id);
        $full_name = trim($first . ' ' . $last);

        if ($include_designation) {
            $designation = self::get_designation($post_id);
            if (!empty($designation)) {
                $full_name = trim($designation . ' ' . $full_name);
            }
        }

        return $full_name;
    }

    public static function get_position($post_id = null) {
        return self::get_staff_field('position', $post_id);
    }

    public static function get_full_name_with_designation($post_id = null) {
        return self::get_staff_field('full_name_with_designation', $post_id);
    }

    public static function get_biography($post_id = null) {
        return self::get_staff_field('biography', $post_id);
    }

    public static function get_education($post_id = null) {
        $education = self::get_staff_field('education', $post_id);
        return is_array($education) ? $education : [];
    }

    public static function get_experience($post_id = null) {
        $experience = self::get_staff_field('experience', $post_id);
        return is_array($experience) ? $experience : [];
    }
}

// Initialize
TBP_Staff::instance();

// Flush rewrite rules on activation
add_action('tbp_staff_activate', function() {
    TBP_Staff::instance()->register_post_type();
    flush_rewrite_rules();
});

// Also flush on init if needed (first time)
add_action('init', function() {
    if (get_option('tbp_staff_flush_rewrite') !== 'done') {
        flush_rewrite_rules();
        update_option('tbp_staff_flush_rewrite', 'done');
    }
}, 99);

// Clear flush flag on deactivation
add_action('tbp_staff_deactivate', function() {
    delete_option('tbp_staff_flush_rewrite');
});
