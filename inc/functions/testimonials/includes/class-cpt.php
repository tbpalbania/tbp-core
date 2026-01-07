<?php
/**
 * Testimonial Custom Post Type
 * Registers CPT for Elementor Loop Builder compatibility
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Testimonial_CPT {

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_tbp_testimonial', [$this, 'save_meta'], 10, 2);

        // Admin columns
        add_filter('manage_tbp_testimonial_posts_columns', [$this, 'admin_columns']);
        add_action('manage_tbp_testimonial_posts_custom_column', [$this, 'admin_column_content'], 10, 2);
        add_filter('manage_edit-tbp_testimonial_sortable_columns', [$this, 'sortable_columns']);

        // Quick edit
        add_action('quick_edit_custom_box', [$this, 'quick_edit_fields'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

        // AJAX handler for getting user results
        add_action('wp_ajax_tbp_get_user_results', [$this, 'ajax_get_user_results']);
    }

    /**
     * Register testimonial post type
     */
    public function register_post_type() {
        $labels = [
            'name'                  => _x('Testimonials', 'Post type general name', 'tbp-core'),
            'singular_name'         => _x('Testimonial', 'Post type singular name', 'tbp-core'),
            'menu_name'             => _x('Testimonials', 'Admin Menu text', 'tbp-core'),
            'add_new'               => __('Add New', 'tbp-core'),
            'add_new_item'          => __('Add New Testimonial', 'tbp-core'),
            'edit_item'             => __('Edit Testimonial', 'tbp-core'),
            'new_item'              => __('New Testimonial', 'tbp-core'),
            'view_item'             => __('View Testimonial', 'tbp-core'),
            'search_items'          => __('Search Testimonials', 'tbp-core'),
            'not_found'             => __('No testimonials found', 'tbp-core'),
            'not_found_in_trash'    => __('No testimonials found in Trash', 'tbp-core'),
            'all_items'             => __('All Testimonials', 'tbp-core'),
            'archives'              => __('Testimonial Archives', 'tbp-core'),
            'filter_items_list'     => __('Filter testimonials list', 'tbp-core'),
            'items_list_navigation' => __('Testimonials list navigation', 'tbp-core'),
            'items_list'            => __('Testimonials list', 'tbp-core'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => 26,
            'menu_icon'           => 'dashicons-format-quote',
            'supports'            => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'show_in_rest'        => true,
        ];

        register_post_type('tbp_testimonial', $args);
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Testimonial Category
        register_taxonomy('testimonial_category', 'tbp_testimonial', [
            'labels' => [
                'name'              => _x('Categories', 'taxonomy general name', 'tbp-core'),
                'singular_name'     => _x('Category', 'taxonomy singular name', 'tbp-core'),
                'search_items'      => __('Search Categories', 'tbp-core'),
                'all_items'         => __('All Categories', 'tbp-core'),
                'edit_item'         => __('Edit Category', 'tbp-core'),
                'update_item'       => __('Update Category', 'tbp-core'),
                'add_new_item'      => __('Add New Category', 'tbp-core'),
                'new_item_name'     => __('New Category Name', 'tbp-core'),
                'menu_name'         => __('Categories', 'tbp-core'),
            ],
            'hierarchical'      => true,
            'public'            => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => false,
        ]);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'tbp_testimonial_details',
            __('Testimonial Details', 'tbp-core'),
            [$this, 'render_meta_box'],
            'tbp_testimonial',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box($post) {
        wp_nonce_field('tbp_testimonial_meta', 'tbp_testimonial_nonce');

        $author_type = get_post_meta($post->ID, '_testimonial_author_type', true) ?: 'external';
        $author_name = get_post_meta($post->ID, '_testimonial_author_name', true);
        $author_email = get_post_meta($post->ID, '_testimonial_author_email', true);
        $author_company = get_post_meta($post->ID, '_testimonial_author_company', true);
        $author_position = get_post_meta($post->ID, '_testimonial_author_position', true);
        $rating = get_post_meta($post->ID, '_testimonial_rating', true);
        $featured = get_post_meta($post->ID, '_testimonial_featured', true);
        $result_id = get_post_meta($post->ID, '_testimonial_result_id', true);
        $user_id = get_post_meta($post->ID, '_testimonial_user_id', true);

        if ($rating === '') $rating = 5;
        ?>
        <style>
            .tbp-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .tbp-meta-field { margin-bottom: 15px; }
            .tbp-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; }
            .tbp-meta-field input[type="text"],
            .tbp-meta-field input[type="email"],
            .tbp-meta-field input[type="number"],
            .tbp-meta-field select { width: 100%; }
            .tbp-rating-stars { display: flex; gap: 5px; }
            .tbp-rating-stars input[type="radio"] { display: none; }
            .tbp-rating-stars label { cursor: pointer; font-size: 24px; color: #ddd; }
            .tbp-rating-stars label:hover,
            .tbp-rating-stars label:hover ~ label,
            .tbp-rating-stars input:checked ~ label { color: #ffb900; }
            .tbp-rating-stars { flex-direction: row-reverse; justify-content: flex-end; }
            .tbp-author-type-switcher { display: flex; gap: 10px; margin-bottom: 20px; padding: 15px; background: #f0f0f1; border-radius: 4px; }
            .tbp-author-type-switcher label { display: flex; align-items: center; gap: 5px; cursor: pointer; padding: 8px 15px; background: #fff; border: 1px solid #ccc; border-radius: 4px; font-weight: normal; }
            .tbp-author-type-switcher input:checked + span { font-weight: 600; }
            .tbp-author-type-switcher label:has(input:checked) { border-color: #2271b1; background: #f0f7fc; }
            .tbp-author-fields { display: none; }
            .tbp-author-fields.active { display: block; }
            .tbp-user-info { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 10px; }
            .tbp-user-info p { margin: 5px 0; }
            .tbp-user-info strong { min-width: 80px; display: inline-block; }
        </style>

        <!-- Author Type Switcher -->
        <div class="tbp-author-type-switcher">
            <label>
                <input type="radio" name="testimonial_author_type" value="registered" <?php checked($author_type, 'registered'); ?>>
                <span><?php _e('Registered User', 'tbp-core'); ?></span>
            </label>
            <label>
                <input type="radio" name="testimonial_author_type" value="external" <?php checked($author_type, 'external'); ?>>
                <span><?php _e('External User', 'tbp-core'); ?></span>
            </label>
        </div>

        <div class="tbp-meta-grid">
            <div>
                <!-- Registered User Fields -->
                <div class="tbp-author-fields tbp-author-registered <?php echo $author_type === 'registered' ? 'active' : ''; ?>">
                    <div class="tbp-meta-field">
                        <label for="testimonial_user_id"><?php _e('Select User', 'tbp-core'); ?> *</label>
                        <select id="testimonial_user_id" name="testimonial_user_id" class="tbp-user-select">
                            <option value=""><?php _e('— Select User —', 'tbp-core'); ?></option>
                            <?php
                            $users = get_users(['orderby' => 'display_name', 'order' => 'ASC', 'number' => 200]);
                            foreach ($users as $user) {
                                printf(
                                    '<option value="%d" %s data-name="%s" data-email="%s">%s (%s)</option>',
                                    $user->ID,
                                    selected($user_id, $user->ID, false),
                                    esc_attr($user->display_name),
                                    esc_attr($user->user_email),
                                    esc_html($user->display_name),
                                    esc_html($user->user_email)
                                );
                            }
                            ?>
                        </select>
                    </div>
                    <?php if ($user_id && $author_type === 'registered') :
                        $selected_user = get_user_by('id', $user_id);
                        if ($selected_user) :
                    ?>
                    <div class="tbp-user-info" id="tbp-user-info">
                        <p><strong><?php _e('Name:', 'tbp-core'); ?></strong> <?php echo esc_html($selected_user->display_name); ?></p>
                        <p><strong><?php _e('Email:', 'tbp-core'); ?></strong> <?php echo esc_html($selected_user->user_email); ?></p>
                        <?php if ($selected_user->first_name || $selected_user->last_name) : ?>
                        <p><strong><?php _e('Full Name:', 'tbp-core'); ?></strong> <?php echo esc_html(trim($selected_user->first_name . ' ' . $selected_user->last_name)); ?></p>
                        <?php endif; ?>
                        <p><a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>" target="_blank"><?php _e('View User Profile', 'tbp-core'); ?> &rarr;</a></p>
                    </div>
                    <?php endif; endif; ?>
                </div>

                <!-- External User Fields -->
                <div class="tbp-author-fields tbp-author-external <?php echo $author_type === 'external' ? 'active' : ''; ?>">
                    <div class="tbp-meta-field">
                        <label for="testimonial_author_name"><?php _e('Author Name', 'tbp-core'); ?> *</label>
                        <input type="text" id="testimonial_author_name" name="testimonial_author_name" value="<?php echo esc_attr($author_name); ?>">
                    </div>
                    <div class="tbp-meta-field">
                        <label for="testimonial_author_email"><?php _e('Author Email', 'tbp-core'); ?></label>
                        <input type="email" id="testimonial_author_email" name="testimonial_author_email" value="<?php echo esc_attr($author_email); ?>">
                    </div>
                    <div class="tbp-meta-field">
                        <label for="testimonial_author_company"><?php _e('Company', 'tbp-core'); ?></label>
                        <input type="text" id="testimonial_author_company" name="testimonial_author_company" value="<?php echo esc_attr($author_company); ?>">
                    </div>
                    <div class="tbp-meta-field">
                        <label for="testimonial_author_position"><?php _e('Position/Title', 'tbp-core'); ?></label>
                        <input type="text" id="testimonial_author_position" name="testimonial_author_position" value="<?php echo esc_attr($author_position); ?>">
                    </div>
                </div>
            </div>
            <div>
                <div class="tbp-meta-field">
                    <label><?php _e('Rating', 'tbp-core'); ?></label>
                    <div class="tbp-rating-stars">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <input type="radio" id="rating_<?php echo $i; ?>" name="testimonial_rating" value="<?php echo $i; ?>" <?php checked($rating, $i); ?>>
                            <label for="rating_<?php echo $i; ?>">&#9733;</label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="tbp-meta-field">
                    <label>
                        <input type="checkbox" name="testimonial_featured" value="1" <?php checked($featured, '1'); ?>>
                        <?php _e('Featured Testimonial', 'tbp-core'); ?>
                    </label>
                </div>
                <?php if (post_type_exists('lab_result')) : ?>
                <div class="tbp-meta-field tbp-result-field">
                    <label for="testimonial_result_id"><?php _e('Related Result', 'tbp-core'); ?></label>
                    <select id="testimonial_result_id" name="testimonial_result_id">
                        <option value=""><?php _e('— None —', 'tbp-core'); ?></option>
                        <?php
                        // Build query args
                        $query_args = [
                            'post_type'      => 'lab_result',
                            'post_status'    => 'publish',
                            'posts_per_page' => 100,
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        ];

                        // If registered user with user_id, show only their results
                        if ($author_type === 'registered' && $user_id) {
                            $query_args['meta_query'] = [
                                [
                                    'key'   => '_patient_id',
                                    'value' => $user_id,
                                ]
                            ];
                        }

                        $results_query = new WP_Query($query_args);

                        if ($results_query->have_posts()) {
                            while ($results_query->have_posts()) {
                                $results_query->the_post();
                                printf(
                                    '<option value="%d" %s>%s</option>',
                                    get_the_ID(),
                                    selected($result_id, get_the_ID(), false),
                                    esc_html(get_the_title())
                                );
                            }
                            wp_reset_postdata();
                        }
                        ?>
                    </select>
                    <p class="description tbp-result-hint" style="display: none;"><?php _e('Select a user first to see their results', 'tbp-core'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Toggle author fields based on type
            $('input[name="testimonial_author_type"]').on('change', function() {
                var type = $(this).val();
                $('.tbp-author-fields').removeClass('active');
                $('.tbp-author-' + type).addClass('active');

                // Handle result field visibility and reload
                if (type === 'registered') {
                    var userId = $('#testimonial_user_id').val();
                    if (userId) {
                        loadUserResults(userId);
                    } else {
                        $('.tbp-result-hint').show();
                        $('#testimonial_result_id').html('<option value=""><?php _e('— Select user first —', 'tbp-core'); ?></option>');
                    }
                } else {
                    $('.tbp-result-hint').hide();
                    loadAllResults();
                }
            });

            // Update user info and results when user is selected
            $('#testimonial_user_id').on('change', function() {
                var $selected = $(this).find('option:selected');
                var userId = $(this).val();

                if (userId) {
                    var name = $selected.data('name');
                    var email = $selected.data('email');

                    var infoHtml = '<p><strong><?php _e('Name:', 'tbp-core'); ?></strong> ' + name + '</p>' +
                                   '<p><strong><?php _e('Email:', 'tbp-core'); ?></strong> ' + email + '</p>';

                    if ($('#tbp-user-info').length) {
                        $('#tbp-user-info').html(infoHtml);
                    } else {
                        $('.tbp-author-registered .tbp-meta-field:first').after('<div class="tbp-user-info" id="tbp-user-info">' + infoHtml + '</div>');
                    }

                    // Load results for this user
                    loadUserResults(userId);
                    $('.tbp-result-hint').hide();
                } else {
                    $('#tbp-user-info').remove();
                    $('.tbp-result-hint').show();
                    $('#testimonial_result_id').html('<option value=""><?php _e('— Select user first —', 'tbp-core'); ?></option>');
                }
            });

            // Load results for specific user via AJAX
            function loadUserResults(userId) {
                $('#testimonial_result_id').html('<option value=""><?php _e('Loading...', 'tbp-core'); ?></option>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tbp_get_user_results',
                        user_id: userId,
                        nonce: '<?php echo wp_create_nonce('tbp_testimonial_results'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<option value=""><?php _e('— None —', 'tbp-core'); ?></option>';
                            $.each(response.data, function(i, result) {
                                html += '<option value="' + result.id + '">' + result.title + '</option>';
                            });
                            $('#testimonial_result_id').html(html);

                            if (response.data.length === 0) {
                                $('#testimonial_result_id').html('<option value=""><?php _e('— No results for this user —', 'tbp-core'); ?></option>');
                            }
                        }
                    }
                });
            }

            // Load all results (for external users)
            function loadAllResults() {
                $('#testimonial_result_id').html('<option value=""><?php _e('Loading...', 'tbp-core'); ?></option>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tbp_get_user_results',
                        user_id: 0,
                        nonce: '<?php echo wp_create_nonce('tbp_testimonial_results'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<option value=""><?php _e('— None —', 'tbp-core'); ?></option>';
                            $.each(response.data, function(i, result) {
                                html += '<option value="' + result.id + '">' + result.title + '</option>';
                            });
                            $('#testimonial_result_id').html(html);
                        }
                    }
                });
            }

            // Initial state check for registered user without selection
            if ($('input[name="testimonial_author_type"]:checked').val() === 'registered' && !$('#testimonial_user_id').val()) {
                $('.tbp-result-hint').show();
            }
        });
        </script>
        <?php
    }

    /**
     * Save meta
     */
    public function save_meta($post_id, $post) {
        if (!isset($_POST['tbp_testimonial_nonce']) || !wp_verify_nonce($_POST['tbp_testimonial_nonce'], 'tbp_testimonial_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save author type
        $author_type = sanitize_text_field($_POST['testimonial_author_type'] ?? 'external');
        update_post_meta($post_id, '_testimonial_author_type', $author_type);

        // Save user ID if registered user
        if ($author_type === 'registered') {
            $user_id = absint($_POST['testimonial_user_id'] ?? 0);
            update_post_meta($post_id, '_testimonial_user_id', $user_id);

            // Auto-populate author fields from user data
            if ($user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    update_post_meta($post_id, '_testimonial_author_name', $user->display_name);
                    update_post_meta($post_id, '_testimonial_author_email', $user->user_email);
                }
            }
        } else {
            // External user - save manual fields
            update_post_meta($post_id, '_testimonial_user_id', '');

            $fields = [
                'testimonial_author_name'     => '_testimonial_author_name',
                'testimonial_author_email'    => '_testimonial_author_email',
                'testimonial_author_company'  => '_testimonial_author_company',
                'testimonial_author_position' => '_testimonial_author_position',
            ];

            foreach ($fields as $field => $meta_key) {
                if (isset($_POST[$field])) {
                    $value = $field === 'testimonial_author_email'
                        ? sanitize_email($_POST[$field])
                        : sanitize_text_field($_POST[$field]);
                    update_post_meta($post_id, $meta_key, $value);
                }
            }
        }

        // Save rating
        if (isset($_POST['testimonial_rating'])) {
            update_post_meta($post_id, '_testimonial_rating', absint($_POST['testimonial_rating']));
        }

        // Save result ID
        if (isset($_POST['testimonial_result_id'])) {
            update_post_meta($post_id, '_testimonial_result_id', absint($_POST['testimonial_result_id']));
        }

        // Featured checkbox
        $featured = isset($_POST['testimonial_featured']) ? '1' : '0';
        update_post_meta($post_id, '_testimonial_featured', $featured);
    }

    /**
     * AJAX: Get results for a user
     */
    public function ajax_get_user_results() {
        check_ajax_referer('tbp_testimonial_results', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $user_id = absint($_POST['user_id'] ?? 0);

        // Build query args
        $query_args = [
            'post_type'      => 'lab_result',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ($user_id > 0) {
            // Get results for specific user
            $query_args['meta_query'] = [
                [
                    'key'   => '_patient_id',
                    'value' => $user_id,
                ]
            ];
        }

        $results_query = new \WP_Query($query_args);
        $results = [];

        if ($results_query->have_posts()) {
            while ($results_query->have_posts()) {
                $results_query->the_post();
                $results[] = [
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                ];
            }
            wp_reset_postdata();
        }

        wp_send_json_success($results);
    }

    /**
     * Admin columns
     */
    public function admin_columns($columns) {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['author_name'] = __('Author', 'tbp-core');
                $new_columns['rating'] = __('Rating', 'tbp-core');
                $new_columns['featured'] = __('Featured', 'tbp-core');
            } elseif ($key === 'date') {
                $new_columns[$key] = $value;
            } else {
                $new_columns[$key] = $value;
            }
        }
        return $new_columns;
    }

    /**
     * Admin column content
     */
    public function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'author_name':
                $name = get_post_meta($post_id, '_testimonial_author_name', true);
                $company = get_post_meta($post_id, '_testimonial_author_company', true);
                echo esc_html($name);
                if ($company) {
                    echo '<br><small>' . esc_html($company) . '</small>';
                }
                break;
            case 'rating':
                $rating = (int) get_post_meta($post_id, '_testimonial_rating', true);
                echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                break;
            case 'featured':
                $featured = get_post_meta($post_id, '_testimonial_featured', true);
                echo $featured === '1' ? '★' : '—';
                break;
        }
    }

    /**
     * Sortable columns
     */
    public function sortable_columns($columns) {
        $columns['rating'] = 'rating';
        return $columns;
    }

    /**
     * Quick edit fields
     */
    public function quick_edit_fields($column_name, $post_type) {
        if ($post_type !== 'tbp_testimonial' || $column_name !== 'rating') {
            return;
        }
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="inline-edit-rating">
                    <span class="title"><?php _e('Rating', 'tbp-core'); ?></span>
                    <select name="testimonial_rating">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php _e('Stars', 'tbp-core'); ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label class="inline-edit-featured">
                    <input type="checkbox" name="testimonial_featured" value="1">
                    <span class="checkbox-title"><?php _e('Featured', 'tbp-core'); ?></span>
                </label>
            </div>
        </fieldset>
        <?php
    }

    /**
     * Admin scripts
     */
    public function admin_scripts($hook) {
        global $post_type;
        if ($post_type !== 'tbp_testimonial') {
            return;
        }

        // Quick edit script
        if ($hook === 'edit.php') {
            wp_add_inline_script('inline-edit-post', "
                jQuery(function($) {
                    var wp_inline_edit = inlineEditPost.edit;
                    inlineEditPost.edit = function(id) {
                        wp_inline_edit.apply(this, arguments);
                        var post_id = 0;
                        if (typeof(id) == 'object') post_id = parseInt(this.getId(id));
                        if (post_id > 0) {
                            var row = $('#post-' + post_id);
                            var rating = row.find('.column-rating').text().split('★').length - 1;
                            var featured = row.find('.column-featured').text().trim() === '★';
                            $('select[name=\"testimonial_rating\"]').val(rating);
                            $('input[name=\"testimonial_featured\"]').prop('checked', featured);
                        }
                    };
                });
            ");
        }
    }

    /**
     * Get testimonials as WP_Query
     */
    public static function get_query($args = []) {
        $defaults = [
            'post_type'      => 'tbp_testimonial',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);

        // Handle custom meta queries
        $meta_query = [];

        if (!empty($args['featured_only'])) {
            $meta_query[] = [
                'key'   => '_testimonial_featured',
                'value' => '1',
            ];
            unset($args['featured_only']);
        }

        if (!empty($args['min_rating'])) {
            $meta_query[] = [
                'key'     => '_testimonial_rating',
                'value'   => $args['min_rating'],
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
            unset($args['min_rating']);
        }

        if (!empty($args['result_id'])) {
            $meta_query[] = [
                'key'   => '_testimonial_result_id',
                'value' => $args['result_id'],
            ];
            unset($args['result_id']);
        }

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        return new WP_Query($args);
    }
}

// Initialize
TBP_Testimonial_CPT::instance();
