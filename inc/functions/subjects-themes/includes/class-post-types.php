<?php
/**
 * Register Subject and Topic Post Types
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Subject_Topic_Post_Types {

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
            'supports' => ['title', 'editor', 'excerpt'],
            'show_in_rest' => true,
        ];

        register_post_type('tbp_subject', $subject_args);

        // Topic Post Type
        $topic_labels = [
            'name' => __('Topics', 'tbp-core'),
            'singular_name' => __('Topic', 'tbp-core'),
            'add_new' => __('Add New', 'tbp-core'),
            'add_new_item' => __('Add New Topic', 'tbp-core'),
            'edit_item' => __('Edit Topic', 'tbp-core'),
            'new_item' => __('New Topic', 'tbp-core'),
            'view_item' => __('View Topic', 'tbp-core'),
            'search_items' => __('Search Topics', 'tbp-core'),
            'not_found' => __('No topics found', 'tbp-core'),
            'not_found_in_trash' => __('No topics found in trash', 'tbp-core'),
            'menu_name' => __('Topics', 'tbp-core'),
        ];

        $topic_args = [
            'labels' => $topic_labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add to TBP Core menu
            'query_var' => true,
            'rewrite' => ['slug' => 'topic'],
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
        ];

        register_post_type('tbp_topic', $topic_args);
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

        // Add Topics as second item (position 1)
        add_submenu_page(
            'tbp-academic',
            __('Topics', 'tbp-core'),
            __('Topics', 'tbp-core'),
            'edit_posts',
            'edit.php?post_type=tbp_topic',
            null,
            1
        );
    }
}
