<?php
/**
 * @module-title: Subjects & Themes
 * @module-version: 1.0.0
 * @module-description: LMS-like module with Subject and Theme post types. Many-to-many relationship with academic taxonomy integration.
 * @module-usage: Subject meta: _tbp_subject_code, _tbp_subject_credits, _tbp_subject_duration, tbp_academic_* (chain selector). Theme meta: _tbp_theme_duration, _tbp_theme_objectives, _tbp_theme_resources.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_SUBJECTS_PATH', plugin_dir_path(__FILE__));
define('TBP_SUBJECTS_URL', plugin_dir_url(__FILE__));

// Load components
require_once TBP_SUBJECTS_PATH . 'includes/class-post-types.php';
require_once TBP_SUBJECTS_PATH . 'includes/class-pivot-table.php';
require_once TBP_SUBJECTS_PATH . 'includes/class-subject-fields.php';
require_once TBP_SUBJECTS_PATH . 'includes/class-theme-fields.php';
require_once TBP_SUBJECTS_PATH . 'includes/class-themes-metabox.php';

/**
 * Initialize the module
 */
class TBP_Subjects_Themes {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Initialize components
        TBP_Subject_Theme_Post_Types::instance();
        TBP_Subject_Theme_Pivot::instance();
        TBP_Subject_Fields::instance();
        TBP_Theme_Fields::instance();
        TBP_Themes_Metabox::instance();

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets($hook) {
        global $post_type;

        if (!in_array($post_type, ['tbp_subject', 'tbp_theme'])) {
            return;
        }

        wp_enqueue_style(
            'tbp-subjects-admin',
            TBP_SUBJECTS_URL . 'assets/css/admin.css',
            [],
            TBP_CORE_VERSION
        );

        wp_enqueue_script(
            'tbp-subjects-admin',
            TBP_SUBJECTS_URL . 'assets/js/admin.js',
            ['jquery'],
            TBP_CORE_VERSION,
            true
        );
    }
}

TBP_Subjects_Themes::instance();
