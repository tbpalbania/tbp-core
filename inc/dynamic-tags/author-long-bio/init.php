<?php
/**
 * @module-title: Author Long Bio
 * @module-version: 1.0.0
 * @module-description: Dynamic tag to display author's long biography
 * @module-usage: Use in any text widget with dynamic tags support
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register dynamic tag
add_action('elementor/dynamic_tags/register', 'tbp_register_author_long_bio_tag');
function tbp_register_author_long_bio_tag($dynamic_tags) {
    // Register the group if not exists
    $dynamic_tags->register_group('tbp-author', [
        'title' => __('TBP Author', 'tbp-core'),
    ]);

    require_once __DIR__ . '/tag.php';
    $dynamic_tags->register(new \TBP_Core\DynamicTags\Author_Long_Bio());
}
