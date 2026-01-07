<?php
/**
 * @module-title: ACF Relationship Tests
 * @module-version: 1.0.0
 * @module-description: Query filter to show posts from ACF relationship field "tests"
 * @module-usage: Add "acf_tests" as Query ID in any Elementor posts widget
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter query to show posts from ACF relationship field "tests"
 *
 * Usage: Set Query ID to "acf_tests" in Elementor widget
 */
add_action('elementor/query/acf_tests', 'tbp_query_acf_relationship_tests');
function tbp_query_acf_relationship_tests($query) {
    // Get current post ID from queried object (safer than get_the_ID in query context)
    $current_post_id = get_queried_object_id();

    if (!$current_post_id || !function_exists('get_field')) {
        $query->set('post__in', [0]);
        return;
    }

    // Get the relationship field value (use false to get raw IDs, avoids extra queries)
    $tests = get_field('tests', $current_post_id, false);

    if (empty($tests) || !is_array($tests)) {
        $query->set('post__in', [0]);
        return;
    }

    // Ensure we have integer IDs
    $post_ids = array_map('intval', $tests);
    $post_ids = array_filter($post_ids);

    if (empty($post_ids)) {
        $query->set('post__in', [0]);
        return;
    }

    $query->set('post__in', $post_ids);
    $query->set('orderby', 'post__in');
}
