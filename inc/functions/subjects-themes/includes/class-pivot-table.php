<?php
/**
 * Pivot Table for Subject-Theme Many-to-Many Relationship
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Subject_Theme_Pivot {

    private static $instance = null;
    private static $table_name;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'tbp_subject_themes';

        // Create table on activation
        add_action('admin_init', [$this, 'maybe_create_table']);

        // Clean up when posts are deleted
        add_action('before_delete_post', [$this, 'delete_relationships']);
    }

    /**
     * Create the pivot table if it doesn't exist
     */
    public function maybe_create_table() {
        if (get_option('tbp_subject_themes_table_version') === '1.0') {
            return;
        }

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            subject_id bigint(20) unsigned NOT NULL,
            theme_id bigint(20) unsigned NOT NULL,
            theme_order int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY subject_theme (subject_id, theme_id),
            KEY subject_id (subject_id),
            KEY theme_id (theme_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        update_option('tbp_subject_themes_table_version', '1.0');
    }

    /**
     * Get table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'tbp_subject_themes';
    }

    /**
     * Add a theme to a subject
     */
    public static function add_theme_to_subject($subject_id, $theme_id, $order = 0) {
        global $wpdb;

        return $wpdb->replace(
            self::get_table_name(),
            [
                'subject_id' => absint($subject_id),
                'theme_id' => absint($theme_id),
                'theme_order' => absint($order),
            ],
            ['%d', '%d', '%d']
        );
    }

    /**
     * Remove a theme from a subject
     */
    public static function remove_theme_from_subject($subject_id, $theme_id) {
        global $wpdb;

        return $wpdb->delete(
            self::get_table_name(),
            [
                'subject_id' => absint($subject_id),
                'theme_id' => absint($theme_id),
            ],
            ['%d', '%d']
        );
    }

    /**
     * Get all themes for a subject (ordered)
     */
    public static function get_themes_for_subject($subject_id) {
        global $wpdb;
        $table = self::get_table_name();

        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, st.theme_order
             FROM {$wpdb->posts} t
             INNER JOIN {$table} st ON t.ID = st.theme_id
             WHERE st.subject_id = %d
             AND t.post_status = 'publish'
             ORDER BY st.theme_order ASC",
            absint($subject_id)
        ));
    }

    /**
     * Get theme IDs for a subject
     */
    public static function get_theme_ids_for_subject($subject_id) {
        global $wpdb;
        $table = self::get_table_name();

        return $wpdb->get_col($wpdb->prepare(
            "SELECT theme_id FROM {$table} WHERE subject_id = %d ORDER BY theme_order ASC",
            absint($subject_id)
        ));
    }

    /**
     * Get all subjects for a theme
     */
    public static function get_subjects_for_theme($theme_id) {
        global $wpdb;
        $table = self::get_table_name();

        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*
             FROM {$wpdb->posts} s
             INNER JOIN {$table} st ON s.ID = st.subject_id
             WHERE st.theme_id = %d
             AND s.post_status = 'publish'
             ORDER BY s.post_title ASC",
            absint($theme_id)
        ));
    }

    /**
     * Sync themes for a subject (replace all)
     */
    public static function sync_themes_for_subject($subject_id, $theme_ids = []) {
        global $wpdb;
        $table = self::get_table_name();

        // Delete existing relationships
        $wpdb->delete($table, ['subject_id' => absint($subject_id)], ['%d']);

        // Add new relationships with order
        if (!empty($theme_ids)) {
            foreach ($theme_ids as $order => $theme_id) {
                self::add_theme_to_subject($subject_id, $theme_id, $order);
            }
        }
    }

    /**
     * Update theme order within a subject
     */
    public static function update_theme_order($subject_id, $theme_id, $order) {
        global $wpdb;

        return $wpdb->update(
            self::get_table_name(),
            ['theme_order' => absint($order)],
            [
                'subject_id' => absint($subject_id),
                'theme_id' => absint($theme_id),
            ],
            ['%d'],
            ['%d', '%d']
        );
    }

    /**
     * Delete all relationships for a post
     */
    public function delete_relationships($post_id) {
        global $wpdb;
        $post = get_post($post_id);

        if (!$post) {
            return;
        }

        $table = self::get_table_name();

        if ($post->post_type === 'tbp_subject') {
            $wpdb->delete($table, ['subject_id' => $post_id], ['%d']);
        } elseif ($post->post_type === 'tbp_theme') {
            $wpdb->delete($table, ['theme_id' => $post_id], ['%d']);
        }
    }

    /**
     * Get theme count for a subject
     */
    public static function get_theme_count($subject_id) {
        global $wpdb;
        $table = self::get_table_name();

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE subject_id = %d",
            absint($subject_id)
        ));
    }

    /**
     * Get subject count for a theme
     */
    public static function get_subject_count($theme_id) {
        global $wpdb;
        $table = self::get_table_name();

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE theme_id = %d",
            absint($theme_id)
        ));
    }
}
