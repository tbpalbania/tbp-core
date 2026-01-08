<?php
/**
 * Register Subject and Theme Post Types
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Subject_Theme_Post_Types {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('admin_menu', [$this, 'add_admin_menus']);
    }

    public function register_post_types() {
        // Subject Post Type
        $subject_labels = [
            'name' => __('Subjects', 'tbp-core'),
            'singular_name' => __('Subject', 'tbp-core'),
            'add_new' => __('Add New', 'tbp-core'),
            'add_new_item' => __('Add New Subject', 'tbp-core'),
            'edit_item' => __('Edit Subject', 'tbp-core'),
            'new_item' => __('New Subject', 'tbp-core'),
            'view_item' => __('View Subject', 'tbp-core'),
            'search_items' => __('Search Subjects', 'tbp-core'),
            'not_found' => __('No subjects found', 'tbp-core'),
            'not_found_in_trash' => __('No subjects found in trash', 'tbp-core'),
            'menu_name' => __('Subjects', 'tbp-core'),
        ];

        $subject_args = [
            'labels' => $subject_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add to TBP Core menu
            'query_var' => true,
            'rewrite' => ['slug' => 'subject'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
        ];

        register_post_type('tbp_subject', $subject_args);

        // Theme Post Type
        $theme_labels = [
            'name' => __('Themes', 'tbp-core'),
            'singular_name' => __('Theme', 'tbp-core'),
            'add_new' => __('Add New', 'tbp-core'),
            'add_new_item' => __('Add New Theme', 'tbp-core'),
            'edit_item' => __('Edit Theme', 'tbp-core'),
            'new_item' => __('New Theme', 'tbp-core'),
            'view_item' => __('View Theme', 'tbp-core'),
            'search_items' => __('Search Themes', 'tbp-core'),
            'not_found' => __('No themes found', 'tbp-core'),
            'not_found_in_trash' => __('No themes found in trash', 'tbp-core'),
            'menu_name' => __('Themes', 'tbp-core'),
        ];

        $theme_args = [
            'labels' => $theme_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add to TBP Core menu
            'query_var' => true,
            'rewrite' => ['slug' => 'theme'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ];

        register_post_type('tbp_theme', $theme_args);
    }

    public function add_admin_menus() {
        // Add Subjects as first item (position 0)
        add_submenu_page(
            'tbp-academic',
            __('Subjects', 'tbp-core'),
            __('Subjects', 'tbp-core'),
            'edit_posts',
            'edit.php?post_type=tbp_subject',
            null,
            0
        );

        // Add Themes as second item (position 1)
        add_submenu_page(
            'tbp-academic',
            __('Themes', 'tbp-core'),
            __('Themes', 'tbp-core'),
            'edit_posts',
            'edit.php?post_type=tbp_theme',
            null,
            1
        );
    }
}
