<?php
/**
 * @module-title: Siblings of Current
 * @module-version: 1.0.0
 * @module-description: Query filter to show siblings of the current post (same parent)
 * @module-usage: Add "siblings_of_current" as Query ID in any Elementor posts widget
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter query to show siblings of current post (posts with same parent, excluding current)
 *
 * Usage: Set Query ID to "siblings_of_current" in Elementor widget
 */
add_action('elementor/query/siblings_of_current', 'tbp_query_siblings_of_current');
function tbp_query_siblings_of_current($query) {
    $current_post_id = get_queried_object_id();

    if (!$current_post_id) {
        $query->set('post__in', [0]);
        return;
    }

    $current_post = get_post($current_post_id);

    if (!$current_post) {
        $query->set('post__in', [0]);
        return;
    }

    // Get parent ID (0 if no parent)
    $parent_id = $current_post->post_parent;

    // Set query to get posts with same parent
    $query->set('post_parent', $parent_id);

    // Exclude current post from results
    $query->set('post__not_in', [$current_post_id]);
}
