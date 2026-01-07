<?php
/**
 * @module-title: Children of Current
 * @module-version: 1.0.0
 * @module-description: Query filter to show only children of the current post
 * @module-usage: Add "children_of_current" as Query ID in any Elementor posts widget
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter query to show only children of current post
 *
 * Usage: Set Query ID to "children_of_current" in Elementor widget
 */
add_action('elementor/query/children_of_current', 'tbp_query_children_of_current');
function tbp_query_children_of_current($query) {
    $current_post_id = get_the_ID();

    if ($current_post_id) {
        $query->set('post_parent', $current_post_id);
    }
}
