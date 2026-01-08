<?php
/**
 * @module-title: University Roles
 * @module-version: 1.0.0
 * @module-description: Removes default WordPress roles (Subscriber, Contributor, Author, Editor) and adds university-specific roles (Rector, Dean, Department Director, Coordinator, Secretary, Student).
 * @module-usage: Activate the module to apply role changes. Deactivate to restore default roles.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * University Roles Module
 */
class TBP_University_Roles {

    private static $instance = null;

    /**
     * Roles to remove
     */
    private $roles_to_remove = [
        'subscriber',
        'contributor',
        'author',
        'editor',
    ];

    /**
     * New university roles with capabilities
     * All roles have minimal capabilities (no admin access)
     */
    private $university_roles = [
        'rector' => [
            'display_name' => 'Rector',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'dean' => [
            'display_name' => 'Dean',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'department_director' => [
            'display_name' => 'Department Director',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'coordinator' => [
            'display_name' => 'Coordinator',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'secretary' => [
            'display_name' => 'Secretary',
            'capabilities' => [
                'read' => true,
            ],
        ],
        'student' => [
            'display_name' => 'Student',
            'capabilities' => [
                'read' => true,
            ],
        ],
    ];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the module
     */
    private function init() {
        // Apply roles on activation (only once)
        if (!get_option('tbp_university_roles_applied')) {
            $this->apply_roles();
            update_option('tbp_university_roles_applied', true);
        }

        // Hook for deactivation
        add_action('tbp_university_roles_deactivate', [$this, 'restore_roles']);

        // Block admin access for university roles
        add_action('admin_init', [$this, 'block_admin_access']);

        // Hide admin bar for university roles
        add_action('after_setup_theme', [$this, 'hide_admin_bar']);

        // Remove website field from user profiles
        add_filter('user_contactmethods', [$this, 'remove_website_field'], 10, 2);
        add_action('admin_head', [$this, 'hide_website_field_css']);
    }

    /**
     * Remove website from contact methods
     */
    public function remove_website_field($methods, $user = null) {
        unset($methods['url']);
        return $methods;
    }

    /**
     * Hide website field with CSS (for core fields)
     */
    public function hide_website_field_css() {
        $screen = get_current_screen();
        if ($screen && in_array($screen->id, ['user-edit', 'profile', 'user-new', 'user'])) {
            ?>
            <style>
                .user-url-wrap { display: none !important; }
            </style>
            <?php
        }
    }

    /**
     * Block admin dashboard access for university roles
     */
    public function block_admin_access() {
        if (is_admin() && !wp_doing_ajax() && !current_user_can('administrator')) {
            $user = wp_get_current_user();
            $user_roles = $user->roles;
            $university_role_slugs = array_keys($this->university_roles);

            if (array_intersect($user_roles, $university_role_slugs)) {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Hide admin bar for university roles
     */
    public function hide_admin_bar() {
        if (!current_user_can('administrator')) {
            $user = wp_get_current_user();
            $user_roles = $user->roles;
            $university_role_slugs = array_keys($this->university_roles);

            if (array_intersect($user_roles, $university_role_slugs)) {
                show_admin_bar(false);
            }
        }
    }

    /**
     * Apply university roles
     */
    public function apply_roles() {
        // Remove default roles
        foreach ($this->roles_to_remove as $role) {
            if (get_role($role)) {
                remove_role($role);
            }
        }

        // Add university roles
        foreach ($this->university_roles as $role_slug => $role_data) {
            if (!get_role($role_slug)) {
                add_role($role_slug, $role_data['display_name'], $role_data['capabilities']);
            }
        }
    }

    /**
     * Restore default WordPress roles (on module deactivation)
     */
    public function restore_roles() {
        // Remove university roles
        foreach ($this->university_roles as $role_slug => $role_data) {
            if (get_role($role_slug)) {
                remove_role($role_slug);
            }
        }

        // Restore default WordPress roles
        $default_roles = [
            'subscriber' => [
                'read' => true,
            ],
            'contributor' => [
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
            ],
            'author' => [
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                'edit_published_posts' => true,
                'delete_published_posts' => true,
            ],
            'editor' => [
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'publish_posts' => true,
                'upload_files' => true,
                'edit_published_posts' => true,
                'delete_published_posts' => true,
                'edit_others_posts' => true,
                'delete_others_posts' => true,
                'read_private_posts' => true,
                'edit_private_posts' => true,
                'delete_private_posts' => true,
                'manage_categories' => true,
                'moderate_comments' => true,
                'edit_pages' => true,
                'edit_others_pages' => true,
                'edit_published_pages' => true,
                'publish_pages' => true,
                'delete_pages' => true,
                'delete_others_pages' => true,
                'delete_published_pages' => true,
                'read_private_pages' => true,
                'edit_private_pages' => true,
                'delete_private_pages' => true,
            ],
        ];

        foreach ($default_roles as $role_slug => $capabilities) {
            if (!get_role($role_slug)) {
                add_role($role_slug, ucfirst($role_slug), $capabilities);
            }
        }

        delete_option('tbp_university_roles_applied');
    }
}

TBP_University_Roles::instance();
