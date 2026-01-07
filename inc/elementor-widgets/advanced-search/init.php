<?php
/**
 * @module-title: Advanced Search
 * @module-version: 1.0.0
 * @module-description: Elementor search widget with multi-post-type support and live AJAX search
 * @module-usage: Drag and drop the "Advanced Search" widget from Elementor panel
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor widget
add_action('elementor/widgets/register', 'tbp_register_advanced_search_widget');
function tbp_register_advanced_search_widget($widgets_manager) {
    require_once __DIR__ . '/widget.php';
    $widgets_manager->register(new \TBP_Core\Widgets\Advanced_Search());
}

// Register widget category - prepend to appear first
add_action('elementor/elements/categories_registered', 'tbp_register_widget_category', 5);
function tbp_register_widget_category($elements_manager) {
    $elements_manager->add_category(
        'tbp-core',
        [
            'title' => __('TBP Core', 'tbp-core'),
            'icon' => 'fa fa-plug',
            'active' => true,
        ],
        0 // Position 0 = first
    );
}

// Enqueue widget assets
add_action('wp_enqueue_scripts', 'tbp_advanced_search_enqueue_assets');
function tbp_advanced_search_enqueue_assets() {
    wp_register_style(
        'tbp-advanced-search',
        TBP_CORE_URL . 'inc/elementor-widgets/advanced-search/assets/css/advanced-search.css',
        [],
        TBP_CORE_VERSION
    );

    wp_register_script(
        'tbp-advanced-search',
        TBP_CORE_URL . 'inc/elementor-widgets/advanced-search/assets/js/advanced-search.js',
        ['jquery'],
        TBP_CORE_VERSION,
        true
    );

    wp_localize_script('tbp-advanced-search', 'tbpSearchConfig', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'search' => [
            'search_result' => __('Search Results', 'tbp-core'),
            'more_result' => __('View All Results', 'tbp-core'),
            'not_found' => __('not found', 'tbp-core'),
        ],
    ]);
}

// AJAX search handler
add_action('wp_ajax_tbp_advanced_search', 'tbp_advanced_search_ajax');
add_action('wp_ajax_nopriv_tbp_advanced_search', 'tbp_advanced_search_ajax');

function tbp_advanced_search_ajax() {
    $result = ['results' => [], 'terms' => []];
    $search_input = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
    $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : [];

    if (strlen($search_input) >= 3) {
        $post_types = isset($settings['post_types']) ? $settings['post_types'] : ['post'];

        if (!is_array($post_types)) {
            $post_types = [$post_types];
        }

        $post_types = array_filter($post_types);

        if (empty($post_types)) {
            $post_types = ['post'];
        }

        $query_args = [
            'post_type' => $post_types,
            's' => sanitize_text_field($search_input),
            'posts_per_page' => isset($settings['per_page']) ? absint($settings['per_page']) : 5,
            'post_status' => 'publish',
        ];

        // Handle authors
        $include_users = [];
        $exclude_users = [];
        if (!empty($settings['include_author_ids'])) {
            if (isset($settings['include_by']) && in_array('authors', $settings['include_by'])) {
                $include_users = wp_parse_id_list($settings['include_author_ids']);
            }
        }
        if (!empty($settings['exclude_author_ids'])) {
            if (isset($settings['exclude_by']) && in_array('authors', $settings['exclude_by'])) {
                $exclude_users = wp_parse_id_list($settings['exclude_author_ids']);
                $include_users = array_diff($include_users, $exclude_users);
            }
        }
        if (!empty($include_users)) {
            $query_args['author__in'] = $include_users;
        }
        if (!empty($exclude_users)) {
            $query_args['author__not_in'] = $exclude_users;
        }

        // Handle taxonomy term filters
        $include_terms = [];
        $exclude_terms = [];
        $terms_query = [];

        if (!empty($settings['include_term_ids'])) {
            if (isset($settings['include_by']) && in_array('terms', $settings['include_by'])) {
                $include_terms = wp_parse_id_list($settings['include_term_ids']);
            }
        }
        if (!empty($settings['exclude_term_ids'])) {
            if (isset($settings['exclude_by']) && in_array('terms', $settings['exclude_by'])) {
                $exclude_terms = wp_parse_id_list($settings['exclude_term_ids']);
                $include_terms = array_diff($include_terms, $exclude_terms);
            }
        }

        if (!empty($include_terms)) {
            $tax_terms_map = tbp_map_group_control_query($include_terms);
            foreach ($tax_terms_map as $tax => $terms) {
                $terms_query[] = [
                    'taxonomy' => $tax,
                    'field' => 'term_id',
                    'terms' => $terms,
                    'operator' => 'IN',
                ];
            }
        }

        if (!empty($exclude_terms)) {
            $tax_terms_map = tbp_map_group_control_query($exclude_terms);
            foreach ($tax_terms_map as $tax => $terms) {
                $terms_query[] = [
                    'taxonomy' => $tax,
                    'field' => 'term_id',
                    'terms' => $terms,
                    'operator' => 'NOT IN',
                ];
            }
        }

        if (!empty($terms_query)) {
            $query_args['tax_query'] = $terms_query;
            $query_args['tax_query']['relation'] = 'AND';
        }

        // Query posts
        $query_posts = get_posts($query_args);
        if (!empty($query_posts)) {
            foreach ($query_posts as $post) {
                $content = !empty($post->post_excerpt) ? strip_tags(strip_shortcodes($post->post_excerpt)) : strip_tags(strip_shortcodes($post->post_content));
                if (strlen($content) > 180) {
                    $content = substr($content, 0, 179) . '...';
                }

                // Get featured image
                $thumbnail = '';
                if (has_post_thumbnail($post->ID)) {
                    $thumb_id = get_post_thumbnail_id($post->ID);
                    $thumb_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                    $thumb_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                    if ($thumb_url) {
                        $thumbnail = [
                            'url' => $thumb_url,
                            'alt' => $thumb_alt ?: get_the_title($post->ID),
                        ];
                    }
                }

                // Get post type info for grouping
                $post_type_obj = get_post_type_object($post->post_type);

                $result['results'][] = [
                    'type' => 'post',
                    'title' => get_the_title($post->ID),
                    'text' => $content,
                    'url' => get_permalink($post->ID),
                    'thumbnail' => $thumbnail,
                    'post_type' => $post->post_type,
                    'post_type_label' => $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type,
                ];
            }
        }

        // Search taxonomy terms if enabled
        $search_taxonomies = !empty($settings['search_taxonomies']);
        $taxonomies = isset($settings['taxonomies']) ? $settings['taxonomies'] : [];

        if ($search_taxonomies && !empty($taxonomies)) {
            if (!is_array($taxonomies)) {
                $taxonomies = [$taxonomies];
            }

            $terms = get_terms([
                'taxonomy' => $taxonomies,
                'search' => $search_input,
                'number' => isset($settings['per_page']) ? absint($settings['per_page']) : 5,
                'hide_empty' => false,
            ]);

            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $taxonomy_obj = get_taxonomy($term->taxonomy);

                    // Get term thumbnail if ACF is available
                    $thumbnail = '';
                    if (function_exists('get_field')) {
                        $term_image = get_field('thumbnail', $term->taxonomy . '_' . $term->term_id);

                        if ($term_image) {
                            // Handle different ACF return formats
                            if (is_array($term_image)) {
                                // Return format: Image Array
                                $thumbnail = [
                                    'url' => $term_image['sizes']['thumbnail'] ?? $term_image['url'],
                                    'alt' => $term_image['alt'] ?: $term->name,
                                ];
                            } elseif (is_numeric($term_image)) {
                                // Return format: Image ID
                                $thumb_url = wp_get_attachment_image_url($term_image, 'thumbnail');
                                $thumb_alt = get_post_meta($term_image, '_wp_attachment_image_alt', true);
                                if ($thumb_url) {
                                    $thumbnail = [
                                        'url' => $thumb_url,
                                        'alt' => $thumb_alt ?: $term->name,
                                    ];
                                }
                            } elseif (is_string($term_image)) {
                                // Return format: Image URL
                                $thumbnail = [
                                    'url' => $term_image,
                                    'alt' => $term->name,
                                ];
                            }
                        }
                    }

                    $result['terms'][] = [
                        'type' => 'term',
                        'title' => $term->name,
                        'text' => $term->description ?: '',
                        'url' => get_term_link($term),
                        'thumbnail' => $thumbnail,
                        'taxonomy' => $term->taxonomy,
                        'taxonomy_label' => $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $term->taxonomy,
                        'count' => $term->count,
                    ];
                }
            }
        }
    }

    wp_die(json_encode($result));
}

function tbp_map_group_control_query($term_ids = []) {
    $terms = get_terms([
        'term_taxonomy_id' => $term_ids,
        'hide_empty' => false,
    ]);

    $tax_terms_map = [];
    foreach ($terms as $term) {
        $taxonomy = $term->taxonomy;
        $tax_terms_map[$taxonomy][] = $term->term_id;
    }

    return $tax_terms_map;
}
