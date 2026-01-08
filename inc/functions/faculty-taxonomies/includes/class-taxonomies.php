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

        // Auto-generate unique slugs for terms with same names
        add_action('admin_footer-edit-tags.php', [$this, 'add_slug_generation_script']);
        add_action('admin_footer-term.php', [$this, 'add_slug_generation_script']);

        // Allow duplicate term names by appending parent info
        add_filter('pre_insert_term', [$this, 'allow_duplicate_term_names'], 10, 2);

        // Save term meta (also handles parent relationships)
        add_action('created_tbp_faculty', [$this, 'save_faculty_icon']);
        add_action('edited_tbp_faculty', [$this, 'save_faculty_icon']);
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

        // Add icon column to faculty
        add_filter('manage_edit-tbp_faculty_columns', [$this, 'add_faculty_icon_column']);
        add_filter('manage_tbp_faculty_custom_column', [$this, 'render_faculty_icon_column'], 10, 3);
    }

    /**
     * Add icon column to faculty list
     */
    public function add_faculty_icon_column($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            if ($key === 'name') {
                $new_columns['icon'] = __('Icon', 'tbp-core');
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    /**
     * Render icon column
     */
    public function render_faculty_icon_column($content, $column_name, $term_id) {
        if ($column_name !== 'icon') {
            return $content;
        }

        $icon_id = get_term_meta($term_id, 'faculty_icon', true);
        if ($icon_id) {
            $icon_url = wp_get_attachment_image_url($icon_id, 'thumbnail');
            if ($icon_url) {
                return '<img src="' . esc_url($icon_url) . '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">';
            }
        }

        return '<span style="display:inline-block;width:40px;height:40px;background:#f0f0f1;border-radius:4px;"></span>';
    }

    /**
     * Add JavaScript to auto-generate unique slugs based on parent selection
     * This allows terms with the same name but different parents to coexist
     */
    public function add_slug_generation_script() {
        $screen = get_current_screen();
        $our_taxonomies = ['tbp_faculty', 'tbp_department', 'tbp_cycle', 'tbp_profile'];

        if (!$screen || !in_array($screen->taxonomy, $our_taxonomies)) {
            return;
        }

        $parent_fields = [
            'tbp_department' => 'parent_faculty',
            'tbp_cycle' => 'parent_department',
            'tbp_profile' => 'parent_cycle',
        ];

        $parent_field = isset($parent_fields[$screen->taxonomy]) ? $parent_fields[$screen->taxonomy] : '';
        ?>
        <script>
        jQuery(document).ready(function($) {
            var parentField = '<?php echo esc_js($parent_field); ?>';

            function updateSlugWithParent() {
                var $nameField = $('#tag-name, #name');
                var $slugField = $('#tag-slug, #slug');
                var $parentField = parentField ? $('#' + parentField) : null;

                if (!$nameField.length || !$slugField.length) return;

                var name = $nameField.val();
                var parentId = $parentField ? $parentField.val() : '';

                if (name) {
                    // Generate base slug from name
                    var slug = name.toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim();

                    // Append parent ID if selected to ensure uniqueness
                    if (parentId) {
                        slug = slug + '-p' + parentId;
                    }

                    $slugField.val(slug);
                }
            }

            // Update slug when name or parent changes (only on add form)
            if ($('#tag-name').length) {
                $('#tag-name').on('blur', updateSlugWithParent);
                if (parentField) {
                    $('#' + parentField).on('change', updateSlugWithParent);
                }
            }
        });
        </script>
        <?php
    }

    /**
     * Allow duplicate term names by appending parent term info
     * This runs before WordPress's term_exists check
     */
    public function allow_duplicate_term_names($term, $taxonomy) {
        $our_taxonomies = ['tbp_faculty', 'tbp_department', 'tbp_cycle', 'tbp_profile'];

        if (!in_array($taxonomy, $our_taxonomies)) {
            return $term;
        }

        // Check if term with this name already exists
        $existing = term_exists($term, $taxonomy);

        if (!$existing) {
            return $term;
        }

        // Get parent info from POST to create unique identifier
        $parent_name = '';
        switch ($taxonomy) {
            case 'tbp_department':
                if (isset($_POST['parent_faculty']) && $_POST['parent_faculty']) {
                    $parent_term = get_term(absint($_POST['parent_faculty']), 'tbp_faculty');
                    if ($parent_term && !is_wp_error($parent_term)) {
                        $parent_name = $parent_term->name;
                    }
                }
                break;
            case 'tbp_cycle':
                if (isset($_POST['parent_department']) && $_POST['parent_department']) {
                    $parent_term = get_term(absint($_POST['parent_department']), 'tbp_department');
                    if ($parent_term && !is_wp_error($parent_term)) {
                        $parent_name = $parent_term->name;
                    }
                }
                break;
            case 'tbp_profile':
                if (isset($_POST['parent_cycle']) && $_POST['parent_cycle']) {
                    $parent_term = get_term(absint($_POST['parent_cycle']), 'tbp_cycle');
                    if ($parent_term && !is_wp_error($parent_term)) {
                        $parent_name = $parent_term->name;
                    }
                }
                break;
        }

        // Append parent name to make the term name unique
        if ($parent_name) {
            $new_term = $term . ' (' . $parent_name . ')';
            // Check if this modified name also exists
            if (term_exists($new_term, $taxonomy)) {
                // Add counter to parent suffix
                $counter = 2;
                while (term_exists($new_term . ' ' . $counter, $taxonomy)) {
                    $counter++;
                }
                $new_term = $new_term . ' ' . $counter;
            }
            $term = $new_term;
        } else {
            // Fallback: append counter
            $counter = 2;
            $new_term = $term . ' (' . $counter . ')';
            while (term_exists($new_term, $taxonomy)) {
                $counter++;
                $new_term = $term . ' (' . $counter . ')';
            }
            $term = $new_term;
        }

        return $term;
    }

    /**
     * Register all taxonomies
     * Note: Taxonomies are hierarchical to allow duplicate names with different parents
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
                'parent_item' => 'Parent ' . $config['singular'],
                'parent_item_colon' => 'Parent ' . $config['singular'] . ':',
            ];

            $args = [
                'labels' => $labels,
                'hierarchical' => true, // Allows duplicate names with different parents
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
     * Add Academic menu and taxonomy submenus
     */
    public function add_taxonomy_menus() {
        // Create Academic parent menu
        add_menu_page(
            __('Academic', 'tbp-core'),
            __('Academic', 'tbp-core'),
            'manage_options',
            'tbp-academic',
            '__return_null',
            'dashicons-welcome-learn-more',
            58
        );

        // Add taxonomy submenus
        add_submenu_page(
            'tbp-academic',
            'Faculties',
            'Faculties',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_faculty'
        );
        add_submenu_page(
            'tbp-academic',
            'Departments',
            'Departments',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_department'
        );
        add_submenu_page(
            'tbp-academic',
            'Cycles',
            'Cycles',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_cycle'
        );
        add_submenu_page(
            'tbp-academic',
            'Profiles',
            'Profiles',
            'manage_options',
            'edit-tags.php?taxonomy=tbp_profile'
        );

        // Remove the auto-generated first submenu that duplicates parent
        remove_submenu_page('tbp-academic', 'tbp-academic');
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
     * Faculty icon field - Add form
     */
    public function add_faculty_fields() {
        wp_enqueue_media();
        ?>
        <div class="form-field">
            <label for="faculty_icon"><?php _e('Faculty Icon', 'tbp-core'); ?></label>
            <div class="tbp-faculty-icon-field">
                <input type="hidden" name="faculty_icon" id="faculty_icon" value="">
                <div class="tbp-faculty-icon-preview"></div>
                <button type="button" class="button tbp-faculty-icon-upload"><?php _e('Select Icon', 'tbp-core'); ?></button>
                <button type="button" class="button tbp-faculty-icon-remove" style="display:none;"><?php _e('Remove', 'tbp-core'); ?></button>
            </div>
            <p><?php _e('Upload an icon/logo for this faculty.', 'tbp-core'); ?></p>
        </div>
        <?php
        $this->faculty_icon_script();
    }

    /**
     * Faculty icon field - Edit form
     */
    public function edit_faculty_fields($term, $taxonomy) {
        wp_enqueue_media();
        $icon_id = get_term_meta($term->term_id, 'faculty_icon', true);
        $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'thumbnail') : '';
        ?>
        <tr class="form-field">
            <th scope="row"><label for="faculty_icon"><?php _e('Faculty Icon', 'tbp-core'); ?></label></th>
            <td>
                <div class="tbp-faculty-icon-field">
                    <input type="hidden" name="faculty_icon" id="faculty_icon" value="<?php echo esc_attr($icon_id); ?>">
                    <div class="tbp-faculty-icon-preview">
                        <?php if ($icon_url) : ?>
                            <img src="<?php echo esc_url($icon_url); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button tbp-faculty-icon-upload"><?php _e('Select Icon', 'tbp-core'); ?></button>
                    <button type="button" class="button tbp-faculty-icon-remove" <?php echo $icon_id ? '' : 'style="display:none;"'; ?>><?php _e('Remove', 'tbp-core'); ?></button>
                </div>
                <p class="description"><?php _e('Upload an icon/logo for this faculty.', 'tbp-core'); ?></p>
            </td>
        </tr>
        <?php
        $this->faculty_icon_script();
    }

    /**
     * Save faculty icon
     */
    public function save_faculty_icon($term_id) {
        if (isset($_POST['faculty_icon'])) {
            update_term_meta($term_id, 'faculty_icon', absint($_POST['faculty_icon']));
        }
    }

    /**
     * Faculty icon upload script
     */
    private function faculty_icon_script() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            var frame;

            $('.tbp-faculty-icon-upload').on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '<?php _e('Select Faculty Icon', 'tbp-core'); ?>',
                    button: { text: '<?php _e('Use this icon', 'tbp-core'); ?>' },
                    multiple: false,
                    library: { type: 'image' }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                    $('#faculty_icon').val(attachment.id);
                    $('.tbp-faculty-icon-preview').html('<img src="' + url + '" alt="">');
                    $('.tbp-faculty-icon-remove').show();
                });

                frame.open();
            });

            $('.tbp-faculty-icon-remove').on('click', function(e) {
                e.preventDefault();
                $('#faculty_icon').val('');
                $('.tbp-faculty-icon-preview').html('');
                $(this).hide();
            });
        });
        </script>
        <style>
            .tbp-faculty-icon-field { display: flex; align-items: center; gap: 10px; }
            .tbp-faculty-icon-preview { width: 60px; height: 60px; background: #f0f0f1; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
            .tbp-faculty-icon-preview img { max-width: 100%; max-height: 100%; border-radius: 4px; }
        </style>
        <?php
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
