<?php
/**
 * @module-title: Alphabet Filter
 * @module-version: 1.0.0
 * @module-description: Alphabet-based filtering widget for archive pages
 * @module-usage: Drag and drop the "Alphabet Filter" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_alphabet_filter_widget');
function tbp_register_alphabet_filter_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Alphabet_Filter());
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_alphabet_filter_enqueue_assets');
function tbp_alphabet_filter_enqueue_assets() {
    wp_register_style(
        'tbp-alphabet-filter',
        TBP_CORE_URL . 'inc/elementor-widgets/alphabet-filter/assets/css/alphabet-filter.css',
        [],
        TBP_CORE_VERSION
    );

    wp_register_script(
        'tbp-alphabet-filter',
        TBP_CORE_URL . 'inc/elementor-widgets/alphabet-filter/assets/js/alphabet-filter.js',
        ['jquery'],
        TBP_CORE_VERSION,
        true
    );

    wp_localize_script('tbp-alphabet-filter', 'tbpAlphabetFilter', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbp_alphabet_filter_nonce'),
    ]);
}

// Handle URL-based filtering
add_action('pre_get_posts', 'tbp_alphabet_filter_query');
function tbp_alphabet_filter_query($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!is_archive() && !is_home() && !is_post_type_archive()) {
        return;
    }

    $letter = isset($_GET['letter']) ? sanitize_text_field($_GET['letter']) : '';
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // If no filters, return early
    if (empty($letter) && empty($search)) {
        return;
    }

    // Validate letter
    $valid_letter = !empty($letter) && strlen($letter) === 1 && ctype_alpha($letter);
    if ($valid_letter) {
        $letter = strtoupper($letter);
    }

    // Add where clause filter
    add_filter('posts_where', function($where) use ($letter, $search, $valid_letter) {
        global $wpdb;

        // Filter by first letter (AND)
        if ($valid_letter) {
            $where .= $wpdb->prepare(" AND UPPER(SUBSTRING({$wpdb->posts}.post_title, 1, 1)) = %s", $letter);
        }

        // Filter by search keyword using LIKE (AND)
        if (!empty($search)) {
            $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }

        return $where;
    });
}

// AJAX handler for getting filtered posts
add_action('wp_ajax_tbp_alphabet_filter', 'tbp_alphabet_filter_ajax');
add_action('wp_ajax_nopriv_tbp_alphabet_filter', 'tbp_alphabet_filter_ajax');
function tbp_alphabet_filter_ajax() {
    check_ajax_referer('tbp_alphabet_filter_nonce', 'nonce');

    $letter = isset($_POST['letter']) ? sanitize_text_field($_POST['letter']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';

    $args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
    ];

    // Add taxonomy filter if provided
    if (!empty($taxonomy) && !empty($term)) {
        $args['tax_query'] = [
            [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $term,
            ],
        ];
    }

    // Validate letter
    $valid_letter = !empty($letter) && strlen($letter) === 1 && ctype_alpha($letter);
    if ($valid_letter) {
        $letter = strtoupper($letter);
    }

    // Add where clause filter for letter AND search
    if ($valid_letter || !empty($search)) {
        add_filter('posts_where', function($where) use ($letter, $search, $valid_letter) {
            global $wpdb;

            // Filter by first letter (AND)
            if ($valid_letter) {
                $where .= $wpdb->prepare(" AND UPPER(SUBSTRING({$wpdb->posts}.post_title, 1, 1)) = %s", $letter);
            }

            // Filter by search keyword using LIKE (AND)
            if (!empty($search)) {
                $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
            }

            return $where;
        });
    }

    $query = new WP_Query($args);

    $posts = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'url' => get_permalink(),
                'excerpt' => get_the_excerpt(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success([
        'posts' => $posts,
        'found' => $query->found_posts,
    ]);
}
