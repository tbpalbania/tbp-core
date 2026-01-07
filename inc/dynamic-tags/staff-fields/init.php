<?php
/**
 * @module-title: Staff Fields Dynamic Tags
 * @module-version: 1.0.0
 * @module-description: Dynamic tags for Staff post type fields
 * @module-usage: Use in Elementor to display staff member data
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register dynamic tags group and tags
add_action('elementor/dynamic_tags/register', 'tbp_register_staff_dynamic_tags');
function tbp_register_staff_dynamic_tags($dynamic_tags_manager) {
    // Register group
    $dynamic_tags_manager->register_group(
        'tbp-staff',
        [
            'title' => __('TBP Staff', 'tbp-core'),
        ]
    );

    // Register tags
    require_once __DIR__ . '/tags/staff-text-tag.php';
    require_once __DIR__ . '/tags/staff-biography-tag.php';
    require_once __DIR__ . '/tags/staff-education-tag.php';
    require_once __DIR__ . '/tags/staff-experience-tag.php';

    $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Staff_Text_Tag());
    $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Staff_Biography_Tag());
    $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Staff_Education_Tag());
    $dynamic_tags_manager->register(new \TBP_Core\DynamicTags\Staff_Experience_Tag());
}
