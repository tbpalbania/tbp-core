<?php
/**
 * Register Academic Taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Academic_Taxonomies {

    private static $instance = null;

    /**
     * Taxonomy definitions
     */
    public static $taxonomies = [
        'tbp_faculty' => [
            'singular' => 'Faculty',
            'plural' => 'Faculties',
            'slug' => 'faculty',
        ],
        'tbp_department' => [
            'singular' => 'Department',
            'plural' => 'Departments',
            'slug' => 'department',
        ],
        'tbp_cycle' => [
            'singular' => 'Cycle',
            'plural' => 'Cycles',
            'slug' => 'cycle',
        ],
        'tbp_profile' => [
            'singular' => 'Profile',
            'plural' => 'Profiles',
            'slug' => 'profile',
        ],
    ];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'register_taxonomies']);
        add_action('tbp_faculty_add_form_fields', [$this, 'add_faculty_fields']);
        add_action('tbp_faculty_edit_form_fields', [$this, 'edit_faculty_fields'], 10, 2);
        add_action('tbp_department_add_form_fields', [$this, 'add_department_fields']);
        add_action('tbp_department_edit_form_fields', [$this, 'edit_department_fields'], 10, 2);
        add_action('tbp_cycle_add_form_fields', [$this, 'add_cycle_fields']);
        add_action('tbp_cycle_edit_form_fields', [$this, 'edit_cycle_fields'], 10, 2);
        add_action('tbp_profile_add_form_fields', [$this, 'add_profile_fields']);
        add_action('tbp_profile_edit_form_fields', [$this, 'edit_profile_fields'], 10, 2);

        // Save term meta
        add_action('created_tbp_department', [$this, 'save_department_meta']);
        add_action('edited_tbp_department', [$this, 'save_department_meta']);
        add_action('created_tbp_cycle', [$this, 'save_cycle_meta']);
        add_action('edited_tbp_cycle', [$this, 'save_cycle_meta']);
        add_action('created_tbp_profile', [$this, 'save_profile_meta']);
        add_action('edited_tbp_profile', [$this, 'save_profile_meta']);

        // Add parent column to admin
        add_filter('manage_edit-tbp_department_columns', [$this, 'add_parent_column']);
        add_filter('manage_tbp_department_custom_column', [$this, 'render_parent_column'], 10, 3);
        add_filter('manage_edit-tbp_cycle_columns', [$this, 'add_parent_column']);
        add_filter('manage_tbp_cycle_custom_column', [$this, 'render_parent_column'], 10, 3);
        add_filter('manage_edit-tbp_profile_columns', [$this, 'add_parent_column']);
        add_filter('manage_tbp_profile_custom_column', [$this, 'render_parent_column'], 10, 3);
    }

    /**
     * Register all taxonomies
     */
    public function register_taxonomies() {
        foreach (self::$taxonomies as $taxonomy => $config) {
            $labels = [
                'name' => $config['plural'],
                'singular_name' => $config['singular'],
                'search_items' => 'Search ' . $config['plural'],
                'all_items' => 'All ' . $config['plural'],
                'edit_item' => 'Edit ' . $config['singular'],
                'update_item' => 'Update ' . $config['singular'],
                'add_new_item' => 'Add New ' . $config['singular'],
                'new_item_name' => 'New ' . $config['singular'] . ' Name',
                'menu_name' => $config['plural'],
            ];

            $args = [
                'labels' => $labels,
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_admin_column' => false,
                'show_in_nav_menus' => false,
                'show_tagcloud' => false,
                'show_in_rest' => true,
                'rewrite' => ['slug' => $config['slug']],
            ];

            // Register as standalone (not attached to any post type initially)
            register_taxonomy($taxonomy, [], $args);
        }

        // Add admin menu for taxonomies
        add_action('admin_menu', [$this, 'add_taxonomy_menus']);
    }

    /**
     * Add taxonomy menus under TBP Core
     */
    public function add_taxonomy_menus() {
        add_submenu_page(
            'tbp-core',
            'Faculties',
            'Faculties',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_faculty'
        );
        add_submenu_page(
            'tbp-core',
            'Departments',
            'Departments',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_department'
        );
        add_submenu_page(
            'tbp-core',
            'Cycles',
            'Cycles',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_cycle'
        );
        add_submenu_page(
            'tbp-core',
            'Profiles',
            'Profiles',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_profile'
        );
    }

    /**
     * Add parent faculty field to department
     */
    public function add_department_fields() {
        $faculties = get_terms([
            'taxonomy' => 'tbp_faculty',
            'hide_empty' => false,
        ]);
        ?>
        <div class="form-field">
            <label for="parent_faculty"><?php _e('Parent Faculty', 'tbp-core'); ?></label>
            <select name="parent_faculty" id="parent_faculty">
                <option value=""><?php _e('— Select Faculty —', 'tbp-core'); ?></option>
                <?php foreach ($faculties as $faculty) : ?>
                    <option value="<?php echo esc_attr($faculty->term_id); ?>">
                        <?php echo esc_html($faculty->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p><?php _e('Select the faculty this department belongs to.', 'tbp-core'); ?></p>
        </div>
        <?php
    }

    public function edit_department_fields($term, $taxonomy) {
        $parent_faculty = get_term_meta($term->term_id, 'parent_faculty', true);
        $faculties = get_terms([
            'taxonomy' => 'tbp_faculty',
            'hide_empty' => false,
        ]);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="parent_faculty"><?php _e('Parent Faculty', 'tbp-core'); ?></label></th>
            <td>
                <select name="parent_faculty" id="parent_faculty">
                    <option value=""><?php _e('— Select Faculty —', 'tbp-core'); ?></option>
                    <?php foreach ($faculties as $faculty) : ?>
                        <option value="<?php echo esc_attr($faculty->term_id); ?>" <?php selected($parent_faculty, $faculty->term_id); ?>>
                            <?php echo esc_html($faculty->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public function save_department_meta($term_id) {
        if (isset($_POST['parent_faculty'])) {
            update_term_meta($term_id, 'parent_faculty', absint($_POST['parent_faculty']));
        }
    }

    /**
     * Add parent department field to cycle
     */
    public function add_cycle_fields() {
        $departments = get_terms([
            'taxonomy' => 'tbp_department',
            'hide_empty' => false,
        ]);
        ?>
        <div class="form-field">
            <label for="parent_department"><?php _e('Parent Department', 'tbp-core'); ?></label>
            <select name="parent_department" id="parent_department">
                <option value=""><?php _e('— Select Department —', 'tbp-core'); ?></option>
                <?php foreach ($departments as $department) : ?>
                    <option value="<?php echo esc_attr($department->term_id); ?>">
                        <?php echo esc_html($department->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p><?php _e('Select the department this cycle belongs to.', 'tbp-core'); ?></p>
        </div>
        <?php
    }

    public function edit_cycle_fields($term, $taxonomy) {
        $parent_department = get_term_meta($term->term_id, 'parent_department', true);
        $departments = get_terms([
            'taxonomy' => 'tbp_department',
            'hide_empty' => false,
        ]);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="parent_department"><?php _e('Parent Department', 'tbp-core'); ?></label></th>
            <td>
                <select name="parent_department" id="parent_department">
                    <option value=""><?php _e('— Select Department —', 'tbp-core'); ?></option>
                    <?php foreach ($departments as $department) : ?>
                        <option value="<?php echo esc_attr($department->term_id); ?>" <?php selected($parent_department, $department->term_id); ?>>
                            <?php echo esc_html($department->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public function save_cycle_meta($term_id) {
        if (isset($_POST['parent_department'])) {
            update_term_meta($term_id, 'parent_department', absint($_POST['parent_department']));
        }
    }

    /**
     * Add parent cycle field to profile
     */
    public function add_profile_fields() {
        $cycles = get_terms([
            'taxonomy' => 'tbp_cycle',
            'hide_empty' => false,
        ]);
        ?>
        <div class="form-field">
            <label for="parent_cycle"><?php _e('Parent Cycle', 'tbp-core'); ?></label>
            <select name="parent_cycle" id="parent_cycle">
                <option value=""><?php _e('— Select Cycle —', 'tbp-core'); ?></option>
                <?php foreach ($cycles as $cycle) : ?>
                    <option value="<?php echo esc_attr($cycle->term_id); ?>">
                        <?php echo esc_html($cycle->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p><?php _e('Select the cycle this profile belongs to.', 'tbp-core'); ?></p>
        </div>
        <?php
    }

    public function edit_profile_fields($term, $taxonomy) {
        $parent_cycle = get_term_meta($term->term_id, 'parent_cycle', true);
        $cycles = get_terms([
            'taxonomy' => 'tbp_cycle',
            'hide_empty' => false,
        ]);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="parent_cycle"><?php _e('Parent Cycle', 'tbp-core'); ?></label></th>
            <td>
                <select name="parent_cycle" id="parent_cycle">
                    <option value=""><?php _e('— Select Cycle —', 'tbp-core'); ?></option>
                    <?php foreach ($cycles as $cycle) : ?>
                        <option value="<?php echo esc_attr($cycle->term_id); ?>" <?php selected($parent_cycle, $cycle->term_id); ?>>
                            <?php echo esc_html($cycle->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public function save_profile_meta($term_id) {
        if (isset($_POST['parent_cycle'])) {
            update_term_meta($term_id, 'parent_cycle', absint($_POST['parent_cycle']));
        }
    }

    /**
     * Empty fields for faculty (no parent needed)
     */
    public function add_faculty_fields() {
        // Faculty has no parent
    }

    public function edit_faculty_fields($term, $taxonomy) {
        // Faculty has no parent
    }

    /**
     * Add parent column to taxonomy list
     */
    public function add_parent_column($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'name') {
                $new_columns['parent_term'] = __('Parent', 'tbp-core');
            }
        }
        return $new_columns;
    }

    public function render_parent_column($content, $column_name, $term_id) {
        if ($column_name !== 'parent_term') {
            return $content;
        }

        $term = get_term($term_id);
        $parent_meta_key = '';
        $parent_taxonomy = '';

        switch ($term->taxonomy) {
            case 'tbp_department':
                $parent_meta_key = 'parent_faculty';
                $parent_taxonomy = 'tbp_faculty';
                break;
            case 'tbp_cycle':
                $parent_meta_key = 'parent_department';
                $parent_taxonomy = 'tbp_department';
                break;
            case 'tbp_profile':
                $parent_meta_key = 'parent_cycle';
                $parent_taxonomy = 'tbp_cycle';
                break;
        }

        if ($parent_meta_key) {
            $parent_id = get_term_meta($term_id, $parent_meta_key, true);
            if ($parent_id) {
                $parent_term = get_term($parent_id, $parent_taxonomy);
                if ($parent_term && !is_wp_error($parent_term)) {
                    return esc_html($parent_term->name);
                }
            }
        }

        return '—';
    }

    /**
     * Get terms by parent
     */
    public static function get_departments_by_faculty($faculty_id) {
        return get_terms([
            'taxonomy' => 'tbp_department',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => 'parent_faculty',
                    'value' => $faculty_id,
                    'compare' => '=',
                ],
            ],
        ]);
    }

    public static function get_cycles_by_department($department_id) {
        return get_terms([
            'taxonomy' => 'tbp_cycle',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => 'parent_department',
                    'value' => $department_id,
                    'compare' => '=',
                ],
            ],
        ]);
    }

    public static function get_profiles_by_cycle($cycle_id) {
        return get_terms([
            'taxonomy' => 'tbp_profile',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => 'parent_cycle',
                    'value' => $cycle_id,
                    'compare' => '=',
                ],
            ],
        ]);
    }
}
