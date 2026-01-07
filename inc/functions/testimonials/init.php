<?php
/**
 * @module-title: Testimonials
 * @module-version: 1.0.0
 * @module-description: Custom testimonials system with star ratings and custom database tables
 * @module-usage: Manage customer testimonials with ratings, moderation, and Elementor integration
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define module constants
define('TBP_TESTIMONIALS_PATH', plugin_dir_path(__FILE__));
define('TBP_TESTIMONIALS_URL', plugin_dir_url(__FILE__));
define('TBP_TESTIMONIALS_VERSION', '1.0.0');
define('TBP_TESTIMONIALS_DB_VERSION', '1.0.0');

/**
 * Main Testimonials Class
 */
class TBP_Testimonials {

    private static $instance = null;

    /**
     * Database table names
     */
    public static $table_testimonials;
    public static $table_testimonialsmeta;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        self::$table_testimonials = $wpdb->prefix . 'testimonials';
        self::$table_testimonialsmeta = $wpdb->prefix . 'testimonialsmeta';

        $this->init();
    }

    public function init() {
        // Load CPT class
        require_once TBP_TESTIMONIALS_PATH . 'includes/class-cpt.php';


        // Note: Custom tables are no longer used. Testimonials now use CPT (wp_posts/wp_postmeta)

        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_settings_submenu'], 99);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            add_action('admin_init', [$this, 'register_settings']);

            // AJAX handlers
            add_action('wp_ajax_tbp_testimonials_get_settings', [$this, 'ajax_get_settings']);
            add_action('wp_ajax_tbp_testimonials_save_settings', [$this, 'ajax_save_settings']);
            add_action('wp_ajax_tbp_testimonials_approve', [$this, 'ajax_approve_testimonial']);
            add_action('wp_ajax_tbp_testimonials_reject', [$this, 'ajax_reject_testimonial']);
            add_action('wp_ajax_tbp_testimonials_delete', [$this, 'ajax_delete_testimonial']);
            add_action('wp_ajax_tbp_testimonials_bulk_action', [$this, 'ajax_bulk_action']);
            add_action('wp_ajax_tbp_testimonials_get', [$this, 'ajax_get_testimonial']);
            add_action('wp_ajax_tbp_testimonials_save', [$this, 'ajax_save_testimonial_admin']);
            add_action('wp_ajax_tbp_testimonials_get_with_replies', [$this, 'ajax_get_with_replies']);
            add_action('wp_ajax_tbp_testimonials_add_reply', [$this, 'ajax_add_reply']);
        }

        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_assets']);

        // AJAX for public testimonial submission
        add_action('wp_ajax_tbp_submit_testimonial', [$this, 'ajax_submit_testimonial']);
        add_action('wp_ajax_nopriv_tbp_submit_testimonial', [$this, 'ajax_submit_testimonial']);

        // Register Elementor widgets and dynamic tags
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);

        // Shortcode
        add_shortcode('tbp_testimonial_form', [$this, 'render_form_shortcode']);
        add_shortcode('tbp_testimonials', [$this, 'render_testimonials_shortcode']);
    }

    /**
     * Create database tables
     */
    public function maybe_create_tables() {
        $installed_version = get_option('tbp_testimonials_db_version', '0');

        if (version_compare($installed_version, TBP_TESTIMONIALS_DB_VERSION, '<')) {
            $this->create_tables();
            update_option('tbp_testimonials_db_version', TBP_TESTIMONIALS_DB_VERSION);
        }
    }

    /**
     * Create testimonials tables
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql_testimonials = "CREATE TABLE " . self::$table_testimonials . " (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            author_name varchar(255) NOT NULL,
            author_email varchar(255) NOT NULL,
            author_company varchar(255) DEFAULT '',
            author_position varchar(255) DEFAULT '',
            author_avatar bigint(20) UNSIGNED DEFAULT NULL,
            content longtext NOT NULL,
            rating tinyint(1) UNSIGNED NOT NULL DEFAULT 5,
            status varchar(20) NOT NULL DEFAULT 'pending',
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            product_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT '',
            user_agent text,
            featured tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY rating (rating),
            KEY user_id (user_id),
            KEY featured (featured),
            KEY date_created (date_created)
        ) $charset_collate;";

        $sql_meta = "CREATE TABLE " . self::$table_testimonialsmeta . " (
            meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            testimonial_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext,
            PRIMARY KEY (meta_id),
            KEY testimonial_id (testimonial_id),
            KEY meta_key (meta_key(191))
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_testimonials);
        dbDelta($sql_meta);
    }

    /**
     * Add settings submenu under Testimonials CPT
     */
    public function add_settings_submenu() {
        add_submenu_page(
            'edit.php?post_type=tbp_testimonial',
            __('Settings', 'tbp-core'),
            __('Settings', 'tbp-core'),
            'manage_options',
            'tbp-testimonials-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // General settings
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_allow_guests', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_require_approval', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_allow_rating', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_min_rating', [
            'type' => 'integer',
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_max_length', [
            'type' => 'integer',
            'default' => 1000,
            'sanitize_callback' => 'absint',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_min_length', [
            'type' => 'integer',
            'default' => 20,
            'sanitize_callback' => 'absint',
        ]);

        // Notification settings
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_notify_admin', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_notification_email', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_email',
        ]);

        // Anti-spam settings
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_rate_limit', [
            'type' => 'integer',
            'default' => 3,
            'sanitize_callback' => 'absint',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_rate_period', [
            'type' => 'integer',
            'default' => 24,
            'sanitize_callback' => 'absint',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_require_email_verification', [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        // Display settings
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_show_rating', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_show_date', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
        register_setting('tbp_testimonials_settings', 'tbp_testimonials_show_company', [
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;

        // Load on testimonial CPT pages and settings page
        $is_testimonial_page = ($post_type === 'tbp_testimonial');
        $is_settings_page = (strpos($hook, 'tbp-testimonials-settings') !== false);

        if (!$is_testimonial_page && !$is_settings_page) {
            return;
        }

        wp_enqueue_style(
            'tbp-testimonials-admin',
            TBP_TESTIMONIALS_URL . 'assets/css/admin.css',
            [],
            TBP_TESTIMONIALS_VERSION
        );

        wp_enqueue_script(
            'tbp-testimonials-admin',
            TBP_TESTIMONIALS_URL . 'assets/js/admin.js',
            ['jquery'],
            TBP_TESTIMONIALS_VERSION,
            true
        );

        wp_localize_script('tbp-testimonials-admin', 'tbpTestimonials', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tbp_testimonials_admin'),
            'i18n'    => [
                'confirm_delete'    => __('Are you sure you want to delete this testimonial?', 'tbp-core'),
                'confirm_bulk'      => __('Are you sure you want to perform this action on the selected items?', 'tbp-core'),
                'saving'            => __('Saving...', 'tbp-core'),
                'saved'             => __('Settings saved successfully.', 'tbp-core'),
                'error'             => __('An error occurred. Please try again.', 'tbp-core'),
                'select_items'      => __('Please select at least one item.', 'tbp-core'),
                'add_testimonial'   => __('Add Testimonial', 'tbp-core'),
                'edit_testimonial'  => __('Edit Testimonial', 'tbp-core'),
                'required'          => __('Please fill in all required fields.', 'tbp-core'),
                'reply_required'    => __('Please enter a reply.', 'tbp-core'),
                'existing_replies'  => __('Existing Replies', 'tbp-core'),
            ],
        ]);
    }

    /**
     * Register frontend assets
     */
    public function register_frontend_assets() {
        wp_register_style(
            'tbp-testimonials',
            TBP_TESTIMONIALS_URL . 'assets/css/frontend.css',
            [],
            TBP_TESTIMONIALS_VERSION
        );

        wp_register_script(
            'tbp-testimonials',
            TBP_TESTIMONIALS_URL . 'assets/js/frontend.js',
            ['jquery'],
            TBP_TESTIMONIALS_VERSION,
            true
        );

        wp_localize_script('tbp-testimonials', 'tbpTestimonialsForm', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tbp_testimonials_public'),
            'i18n'    => [
                'submitting'   => __('Submitting...', 'tbp-core'),
                'success'      => __('Thank you! Your testimonial has been submitted.', 'tbp-core'),
                'success_pending' => __('Thank you! Your testimonial has been submitted and is pending approval.', 'tbp-core'),
                'error'        => __('An error occurred. Please try again.', 'tbp-core'),
                'required'     => __('Please fill in all required fields.', 'tbp-core'),
                'too_short'    => __('Your testimonial is too short.', 'tbp-core'),
                'too_long'     => __('Your testimonial is too long.', 'tbp-core'),
                'invalid_email' => __('Please enter a valid email address.', 'tbp-core'),
                'rate_limited' => __('You have submitted too many testimonials. Please try again later.', 'tbp-core'),
                'guests_disabled' => __('Please log in to submit a testimonial.', 'tbp-core'),
            ],
            'settings' => [
                'allow_guests'   => (bool) get_option('tbp_testimonials_allow_guests', true),
                'allow_rating'   => (bool) get_option('tbp_testimonials_allow_rating', true),
                'min_length'     => (int) get_option('tbp_testimonials_min_length', 20),
                'max_length'     => (int) get_option('tbp_testimonials_max_length', 1000),
            ],
        ]);
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        require_once TBP_TESTIMONIALS_PATH . 'admin/settings-page.php';
    }

    /**
     * AJAX: Get settings
     */
    public function ajax_get_settings() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $settings = [
            'allow_guests'               => (bool) get_option('tbp_testimonials_allow_guests', true),
            'require_approval'           => (bool) get_option('tbp_testimonials_require_approval', true),
            'allow_rating'               => (bool) get_option('tbp_testimonials_allow_rating', true),
            'min_rating'                 => (int) get_option('tbp_testimonials_min_rating', 1),
            'max_length'                 => (int) get_option('tbp_testimonials_max_length', 1000),
            'min_length'                 => (int) get_option('tbp_testimonials_min_length', 20),
            'notify_admin'               => (bool) get_option('tbp_testimonials_notify_admin', true),
            'notification_email'         => get_option('tbp_testimonials_notification_email', get_option('admin_email')),
            'rate_limit'                 => (int) get_option('tbp_testimonials_rate_limit', 3),
            'rate_period'                => (int) get_option('tbp_testimonials_rate_period', 24),
            'require_email_verification' => (bool) get_option('tbp_testimonials_require_email_verification', false),
            'show_rating'                => (bool) get_option('tbp_testimonials_show_rating', true),
            'show_date'                  => (bool) get_option('tbp_testimonials_show_date', true),
            'show_company'               => (bool) get_option('tbp_testimonials_show_company', true),
        ];

        wp_send_json_success($settings);
    }

    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $settings = [
            'tbp_testimonials_allow_guests'               => isset($_POST['allow_guests']) && $_POST['allow_guests'] === 'true',
            'tbp_testimonials_require_approval'           => isset($_POST['require_approval']) && $_POST['require_approval'] === 'true',
            'tbp_testimonials_allow_rating'               => isset($_POST['allow_rating']) && $_POST['allow_rating'] === 'true',
            'tbp_testimonials_min_rating'                 => absint($_POST['min_rating'] ?? 1),
            'tbp_testimonials_max_length'                 => absint($_POST['max_length'] ?? 1000),
            'tbp_testimonials_min_length'                 => absint($_POST['min_length'] ?? 20),
            'tbp_testimonials_notify_admin'               => isset($_POST['notify_admin']) && $_POST['notify_admin'] === 'true',
            'tbp_testimonials_notification_email'         => sanitize_email($_POST['notification_email'] ?? ''),
            'tbp_testimonials_rate_limit'                 => absint($_POST['rate_limit'] ?? 3),
            'tbp_testimonials_rate_period'                => absint($_POST['rate_period'] ?? 24),
            'tbp_testimonials_require_email_verification' => isset($_POST['require_email_verification']) && $_POST['require_email_verification'] === 'true',
            'tbp_testimonials_show_rating'                => isset($_POST['show_rating']) && $_POST['show_rating'] === 'true',
            'tbp_testimonials_show_date'                  => isset($_POST['show_date']) && $_POST['show_date'] === 'true',
            'tbp_testimonials_show_company'               => isset($_POST['show_company']) && $_POST['show_company'] === 'true',
        ];

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }

        wp_send_json_success(['message' => __('Settings saved successfully.', 'tbp-core')]);
    }

    /**
     * AJAX: Approve testimonial
     */
    public function ajax_approve_testimonial() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        global $wpdb;
        $result = $wpdb->update(
            self::$table_testimonials,
            ['status' => 'approved'],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to approve testimonial.', 'tbp-core')]);
        }

        wp_send_json_success(['message' => __('Testimonial approved.', 'tbp-core')]);
    }

    /**
     * AJAX: Reject testimonial
     */
    public function ajax_reject_testimonial() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        global $wpdb;
        $result = $wpdb->update(
            self::$table_testimonials,
            ['status' => 'rejected'],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to reject testimonial.', 'tbp-core')]);
        }

        wp_send_json_success(['message' => __('Testimonial rejected.', 'tbp-core')]);
    }

    /**
     * AJAX: Delete testimonial
     */
    public function ajax_delete_testimonial() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        global $wpdb;

        // Delete meta first
        $wpdb->delete(self::$table_testimonialsmeta, ['testimonial_id' => $id], ['%d']);

        // Delete testimonial
        $result = $wpdb->delete(self::$table_testimonials, ['id' => $id], ['%d']);

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to delete testimonial.', 'tbp-core')]);
        }

        wp_send_json_success(['message' => __('Testimonial deleted.', 'tbp-core')]);
    }

    /**
     * AJAX: Bulk action
     */
    public function ajax_bulk_action() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $ids = isset($_POST['ids']) ? array_map('absint', (array) $_POST['ids']) : [];

        if (empty($ids)) {
            wp_send_json_error(['message' => __('No items selected.', 'tbp-core')]);
        }

        global $wpdb;
        $count = 0;

        foreach ($ids as $id) {
            switch ($action) {
                case 'approve':
                    $result = $wpdb->update(self::$table_testimonials, ['status' => 'approved'], ['id' => $id], ['%s'], ['%d']);
                    break;
                case 'reject':
                    $result = $wpdb->update(self::$table_testimonials, ['status' => 'rejected'], ['id' => $id], ['%s'], ['%d']);
                    break;
                case 'delete':
                    $wpdb->delete(self::$table_testimonialsmeta, ['testimonial_id' => $id], ['%d']);
                    $result = $wpdb->delete(self::$table_testimonials, ['id' => $id], ['%d']);
                    break;
                case 'feature':
                    $result = $wpdb->update(self::$table_testimonials, ['featured' => 1], ['id' => $id], ['%d'], ['%d']);
                    break;
                case 'unfeature':
                    $result = $wpdb->update(self::$table_testimonials, ['featured' => 0], ['id' => $id], ['%d'], ['%d']);
                    break;
                default:
                    continue 2;
            }
            if ($result !== false) {
                $count++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(__('%d testimonials updated.', 'tbp-core'), $count),
            'count'   => $count,
        ]);
    }

    /**
     * AJAX: Get single testimonial
     */
    public function ajax_get_testimonial() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        $testimonial = self::get_testimonial($id);
        if (!$testimonial) {
            wp_send_json_error(['message' => __('Testimonial not found.', 'tbp-core')]);
        }

        wp_send_json_success($testimonial);
    }

    /**
     * AJAX: Save testimonial (admin)
     */
    public function ajax_save_testimonial_admin() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        $data = [
            'author_name'     => sanitize_text_field($_POST['author_name'] ?? ''),
            'author_email'    => sanitize_email($_POST['author_email'] ?? ''),
            'author_company'  => sanitize_text_field($_POST['author_company'] ?? ''),
            'author_position' => sanitize_text_field($_POST['author_position'] ?? ''),
            'content'         => sanitize_textarea_field($_POST['content'] ?? ''),
            'rating'          => absint($_POST['rating'] ?? 5),
            'status'          => sanitize_text_field($_POST['status'] ?? 'approved'),
            'featured'        => absint($_POST['featured'] ?? 0),
            'product_id'      => absint($_POST['product_id'] ?? 0) ?: null,
        ];

        // Validate
        if (empty($data['author_name']) || empty($data['author_email']) || empty($data['content'])) {
            wp_send_json_error(['message' => __('Please fill in all required fields.', 'tbp-core')]);
        }

        if (!is_email($data['author_email'])) {
            wp_send_json_error(['message' => __('Please enter a valid email address.', 'tbp-core')]);
        }

        global $wpdb;

        if ($id > 0) {
            // Update
            $data['date_modified'] = current_time('mysql');
            $result = $wpdb->update(self::$table_testimonials, $data, ['id' => $id]);

            if ($result === false) {
                wp_send_json_error(['message' => __('Failed to update testimonial.', 'tbp-core')]);
            }

            wp_send_json_success(['message' => __('Testimonial updated successfully.', 'tbp-core'), 'id' => $id]);
        } else {
            // Insert
            $data['date_created'] = current_time('mysql');
            $data['ip_address'] = '';
            $result = $wpdb->insert(self::$table_testimonials, $data);

            if ($result === false) {
                wp_send_json_error(['message' => __('Failed to create testimonial.', 'tbp-core')]);
            }

            wp_send_json_success(['message' => __('Testimonial created successfully.', 'tbp-core'), 'id' => $wpdb->insert_id]);
        }
    }

    /**
     * AJAX: Get testimonial with replies
     */
    public function ajax_get_with_replies() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $id = absint($_POST['id'] ?? 0);
        if (!$id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        $testimonial = self::get_testimonial($id);
        if (!$testimonial) {
            wp_send_json_error(['message' => __('Testimonial not found.', 'tbp-core')]);
        }

        // Get replies from meta
        $replies = self::get_meta($id, '_replies');
        if (!is_array($replies)) {
            $replies = [];
        }

        wp_send_json_success([
            'testimonial' => $testimonial,
            'replies'     => $replies,
        ]);
    }

    /**
     * AJAX: Add reply to testimonial
     */
    public function ajax_add_reply() {
        check_ajax_referer('tbp_testimonials_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $testimonial_id = absint($_POST['testimonial_id'] ?? 0);
        $content = sanitize_textarea_field($_POST['content'] ?? '');

        if (!$testimonial_id) {
            wp_send_json_error(['message' => __('Invalid testimonial ID.', 'tbp-core')]);
        }

        if (empty($content)) {
            wp_send_json_error(['message' => __('Please enter a reply.', 'tbp-core')]);
        }

        // Get current user
        $current_user = wp_get_current_user();

        // Get existing replies
        $replies = self::get_meta($testimonial_id, '_replies');
        if (!is_array($replies)) {
            $replies = [];
        }

        // Add new reply
        $replies[] = [
            'id'          => uniqid(),
            'author_name' => $current_user->display_name,
            'author_id'   => $current_user->ID,
            'content'     => $content,
            'date'        => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            'timestamp'   => current_time('timestamp'),
        ];

        // Save replies
        self::update_meta($testimonial_id, '_replies', $replies);

        wp_send_json_success(['message' => __('Reply added successfully.', 'tbp-core')]);
    }

    /**
     * AJAX: Submit testimonial (public)
     */
    public function ajax_submit_testimonial() {
        check_ajax_referer('tbp_testimonials_public', 'nonce');

        // Check if guests allowed
        $allow_guests = (bool) get_option('tbp_testimonials_allow_guests', true);
        if (!$allow_guests && !is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in to submit a testimonial.', 'tbp-core')]);
        }

        // Honeypot check
        if (!empty($_POST['website_url'])) {
            wp_send_json_error(['message' => __('Spam detected.', 'tbp-core')]);
        }

        // Rate limiting
        $ip = $this->get_client_ip();
        $rate_limit = (int) get_option('tbp_testimonials_rate_limit', 3);
        $rate_period = (int) get_option('tbp_testimonials_rate_period', 24);

        if ($rate_limit > 0) {
            $rate_key = 'tbp_testimonial_rate_' . md5($ip);
            $rate_count = (int) get_transient($rate_key);

            if ($rate_count >= $rate_limit) {
                wp_send_json_error(['message' => __('You have submitted too many testimonials. Please try again later.', 'tbp-core')]);
            }

            set_transient($rate_key, $rate_count + 1, $rate_period * HOUR_IN_SECONDS);
        }

        // Sanitize input
        $author_name = sanitize_text_field($_POST['author_name'] ?? '');
        $author_email = sanitize_email($_POST['author_email'] ?? '');
        $author_company = sanitize_text_field($_POST['author_company'] ?? '');
        $author_position = sanitize_text_field($_POST['author_position'] ?? '');
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        $rating = (int) ($_POST['rating'] ?? 5);
        $product_id = absint($_POST['product_id'] ?? 0);

        // Validation
        $min_length = (int) get_option('tbp_testimonials_min_length', 20);
        $max_length = (int) get_option('tbp_testimonials_max_length', 1000);
        $min_rating = (int) get_option('tbp_testimonials_min_rating', 1);

        $errors = [];
        if (empty($author_name)) {
            $errors[] = __('Name is required.', 'tbp-core');
        }
        if (empty($author_email) || !is_email($author_email)) {
            $errors[] = __('Valid email is required.', 'tbp-core');
        }
        if (empty($content)) {
            $errors[] = __('Testimonial content is required.', 'tbp-core');
        }
        if (strlen($content) < $min_length) {
            $errors[] = sprintf(__('Testimonial must be at least %d characters.', 'tbp-core'), $min_length);
        }
        if (strlen($content) > $max_length) {
            $errors[] = sprintf(__('Testimonial cannot exceed %d characters.', 'tbp-core'), $max_length);
        }
        if ($rating < $min_rating || $rating > 5) {
            $errors[] = __('Invalid rating.', 'tbp-core');
        }

        if (!empty($errors)) {
            wp_send_json_error(['message' => implode(' ', $errors)]);
        }

        // Determine status
        $require_approval = (bool) get_option('tbp_testimonials_require_approval', true);
        $status = $require_approval ? 'pending' : 'approved';

        // Insert testimonial
        global $wpdb;
        $result = $wpdb->insert(
            self::$table_testimonials,
            [
                'author_name'     => $author_name,
                'author_email'    => $author_email,
                'author_company'  => $author_company,
                'author_position' => $author_position,
                'content'         => $content,
                'rating'          => $rating,
                'status'          => $status,
                'user_id'         => is_user_logged_in() ? get_current_user_id() : null,
                'product_id'      => $product_id ?: null,
                'ip_address'      => $ip,
                'user_agent'      => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'date_created'    => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error(['message' => __('Failed to submit testimonial. Please try again.', 'tbp-core')]);
        }

        $testimonial_id = $wpdb->insert_id;

        // Send notification email
        if ((bool) get_option('tbp_testimonials_notify_admin', true)) {
            $this->send_notification_email($testimonial_id, [
                'name'    => $author_name,
                'email'   => $author_email,
                'rating'  => $rating,
                'content' => $content,
            ]);
        }

        $message = $require_approval
            ? __('Thank you! Your testimonial has been submitted and is pending approval.', 'tbp-core')
            : __('Thank you! Your testimonial has been published.', 'tbp-core');

        wp_send_json_success([
            'message' => $message,
            'id'      => $testimonial_id,
        ]);
    }

    /**
     * Send notification email
     */
    private function send_notification_email($testimonial_id, $data) {
        $to = get_option('tbp_testimonials_notification_email');
        if (empty($to)) {
            $to = get_option('admin_email');
        }

        $subject = sprintf(__('[New Testimonial] %s - %d Stars', 'tbp-core'), $data['name'], $data['rating']);

        $message = sprintf(
            __("A new testimonial has been submitted.\n\n" .
               "Name: %s\n" .
               "Email: %s\n" .
               "Rating: %d/5\n\n" .
               "Testimonial:\n%s\n\n" .
               "View/Moderate: %s", 'tbp-core'),
            $data['name'],
            $data['email'],
            $data['rating'],
            $data['content'],
            admin_url('admin.php?page=tbp-testimonials')
        );

        wp_mail($to, $subject, $message);
    }

    /**
     * Get client IP
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        return '0.0.0.0';
    }

    /**
     * Register Elementor widgets
     */
    public function register_widgets($widgets_manager) {
        require_once TBP_TESTIMONIALS_PATH . 'includes/class-widget-form.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/class-widget-grid.php';

        $widgets_manager->register(new \TBP_Core\Widgets\Testimonial_Form_Widget());
        $widgets_manager->register(new \TBP_Core\Widgets\Testimonials_Grid_Widget());
    }

    /**
     * Register dynamic tags
     */
    public function register_dynamic_tags($dynamic_tags_manager) {
        // Register group
        $dynamic_tags_manager->register_group('tbp-testimonials', [
            'title' => __('TBP Testimonials', 'tbp-core'),
        ]);

        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-author.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-content.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-rating.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-rating-number.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-avg-rating.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-date.php';
        require_once TBP_TESTIMONIALS_PATH . 'includes/dynamic-tags/class-testimonial-company.php';

        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Author_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Content_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Rating_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Rating_Number_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Avg_Rating_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Date_Tag());
        $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Testimonial_Company_Tag());
    }

    /**
     * Render form shortcode
     */
    public function render_form_shortcode($atts) {
        wp_enqueue_style('tbp-testimonials');
        wp_enqueue_script('tbp-testimonials');

        $atts = shortcode_atts([
            'product_id' => 0,
        ], $atts, 'tbp_testimonial_form');

        ob_start();
        include TBP_TESTIMONIALS_PATH . 'includes/templates/form.php';
        return ob_get_clean();
    }

    /**
     * Render testimonials shortcode
     */
    public function render_testimonials_shortcode($atts) {
        wp_enqueue_style('tbp-testimonials');

        $atts = shortcode_atts([
            'count'         => 10,
            'columns'       => 3,
            'order'         => 'DESC',
            'orderby'       => 'date_created',
            'featured_only' => false,
            'min_rating'    => 0,
            'product_id'    => 0,
        ], $atts, 'tbp_testimonials');

        $testimonials = self::get_testimonials([
            'limit'         => $atts['count'],
            'order'         => $atts['order'],
            'orderby'       => $atts['orderby'],
            'featured_only' => filter_var($atts['featured_only'], FILTER_VALIDATE_BOOLEAN),
            'min_rating'    => (int) $atts['min_rating'],
            'product_id'    => (int) $atts['product_id'],
        ]);

        ob_start();
        include TBP_TESTIMONIALS_PATH . 'includes/templates/grid.php';
        return ob_get_clean();
    }

    /**
     * Get testimonials (static helper)
     */
    public static function get_testimonials($args = []) {
        global $wpdb;

        $defaults = [
            'status'        => 'approved',
            'limit'         => 10,
            'offset'        => 0,
            'order'         => 'DESC',
            'orderby'       => 'date_created',
            'featured_only' => false,
            'min_rating'    => 0,
            'product_id'    => 0,
            'user_id'       => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        $where = ['1=1'];
        $values = [];

        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }

        if ($args['featured_only']) {
            $where[] = 'featured = 1';
        }

        if ($args['min_rating'] > 0) {
            $where[] = 'rating >= %d';
            $values[] = $args['min_rating'];
        }

        if ($args['product_id'] > 0) {
            $where[] = 'product_id = %d';
            $values[] = $args['product_id'];
        }

        if ($args['user_id'] > 0) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }

        $orderby = in_array($args['orderby'], ['date_created', 'rating', 'author_name']) ? $args['orderby'] : 'date_created';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM " . self::$table_testimonials . " WHERE " . implode(' AND ', $where) . " ORDER BY {$orderby} {$order}";

        if ($args['limit'] > 0) {
            $sql .= " LIMIT %d OFFSET %d";
            $values[] = $args['limit'];
            $values[] = $args['offset'];
        }

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return $wpdb->get_results($sql);
    }

    /**
     * Get single testimonial
     */
    public static function get_testimonial($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$table_testimonials . " WHERE id = %d",
            $id
        ));
    }

    /**
     * Get testimonial meta
     */
    public static function get_meta($testimonial_id, $meta_key = '', $single = true) {
        global $wpdb;

        if (empty($meta_key)) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT meta_key, meta_value FROM " . self::$table_testimonialsmeta . " WHERE testimonial_id = %d",
                $testimonial_id
            ));

            $meta = [];
            foreach ($results as $row) {
                $meta[$row->meta_key] = maybe_unserialize($row->meta_value);
            }
            return $meta;
        }

        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM " . self::$table_testimonialsmeta . " WHERE testimonial_id = %d AND meta_key = %s",
            $testimonial_id,
            $meta_key
        ));

        return $single ? maybe_unserialize($value) : [$value];
    }

    /**
     * Update testimonial meta
     */
    public static function update_meta($testimonial_id, $meta_key, $meta_value) {
        global $wpdb;

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM " . self::$table_testimonialsmeta . " WHERE testimonial_id = %d AND meta_key = %s",
            $testimonial_id,
            $meta_key
        ));

        if ($existing) {
            return $wpdb->update(
                self::$table_testimonialsmeta,
                ['meta_value' => maybe_serialize($meta_value)],
                ['testimonial_id' => $testimonial_id, 'meta_key' => $meta_key],
                ['%s'],
                ['%d', '%s']
            );
        }

        return $wpdb->insert(
            self::$table_testimonialsmeta,
            [
                'testimonial_id' => $testimonial_id,
                'meta_key'       => $meta_key,
                'meta_value'     => maybe_serialize($meta_value),
            ],
            ['%d', '%s', '%s']
        );
    }

    /**
     * Get average rating
     */
    public static function get_average_rating($product_id = 0) {
        global $wpdb;

        $where = "status = 'approved'";
        $values = [];

        if ($product_id > 0) {
            $where .= " AND product_id = %d";
            $values[] = $product_id;
        }

        $sql = "SELECT AVG(rating) FROM " . self::$table_testimonials . " WHERE {$where}";

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return round((float) $wpdb->get_var($sql), 1);
    }

    /**
     * Get testimonials count
     */
    public static function get_count($status = '', $product_id = 0) {
        global $wpdb;

        $where = ['1=1'];
        $values = [];

        if (!empty($status)) {
            $where[] = 'status = %s';
            $values[] = $status;
        }

        if ($product_id > 0) {
            $where[] = 'product_id = %d';
            $values[] = $product_id;
        }

        $sql = "SELECT COUNT(*) FROM " . self::$table_testimonials . " WHERE " . implode(' AND ', $where);

        if (!empty($values)) {
            $sql = $wpdb->prepare($sql, $values);
        }

        return (int) $wpdb->get_var($sql);
    }
}

// Initialize
TBP_Testimonials::instance();

// Deactivation hook
add_action('tbp_core_testimonials_deactivate', function() {
    // Optionally clean up settings on deactivation
    // Note: We don't delete tables on deactivation to preserve data
});
