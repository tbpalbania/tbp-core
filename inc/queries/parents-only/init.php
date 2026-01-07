<?php
/**
 * @module-title: Parents Only
 * @module-version: 1.0.0
 * @module-description: Query filter to show only parent posts (no children) - works with any hierarchical post type
 * @module-usage: Add "parents_only" as Query ID in any Elementor posts widget
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter query to show only parent posts
 *
 * Usage: Set Query ID to "parents_only" in Elementor widget
 */
add_action('elementor/query/parents_only', 'tbp_query_parents_only');
function tbp_query_parents_only($query) {
    $query->set('post_parent', 0);
}
