<?php
/**
 * @module-title: Pusher Notifications
 * @module-version: 1.0.0
 * @module-description: Real-time push notifications using Pusher for the portal dashboard
 * @module-usage: Configure Pusher credentials in TBP Core > Pusher Config, then use helper functions to send notifications
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_PUSHER_PATH', __DIR__ . '/');
define('TBP_PUSHER_URL', TBP_CORE_URL . 'inc/functions/pusher-notifications/');

/**
 * TBP Pusher Notifications
 */
class TBP_Pusher_Notifications {

    private static $instance = null;
    private $settings = [];

    /**
     * Notification types with icons
     */
    private static $notification_types = [
        'info' => ['icon' => 'ti-info-circle', 'color' => '#3b82f6'],
        'success' => ['icon' => 'ti-circle-check', 'color' => '#22c55e'],
        'warning' => ['icon' => 'ti-alert-triangle', 'color' => '#f59e0b'],
        'error' => ['icon' => 'ti-alert-circle', 'color' => '#ef4444'],
        'announcement' => ['icon' => 'ti-speakerphone', 'color' => '#8b5cf6'],
        'task' => ['icon' => 'ti-checkbox', 'color' => '#06b6d4'],
        'message' => ['icon' => 'ti-message', 'color' => '#ec4899'],
    ];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->settings = $this->get_settings();

        // Create database table on init
        add_action('init', [$this, 'maybe_create_table']);

        // Admin settings
        add_filter('tbp_core_settings_tabs', [$this, 'add_settings_tab'], 5);
        add_filter('tbp_core_settings_sections', [$this, 'add_settings_sections']);
        add_filter('tbp_core_settings_fields', [$this, 'add_settings_fields']);

        // REST API endpoints
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // AJAX handlers
        add_action('wp_ajax_tbp_get_notifications', [$this, 'ajax_get_notifications']);
        add_action('wp_ajax_tbp_mark_notification_read', [$this, 'ajax_mark_notification_read']);
        add_action('wp_ajax_tbp_mark_all_notifications_read', [$this, 'ajax_mark_all_notifications_read']);
        add_action('wp_ajax_tbp_send_test_notification', [$this, 'ajax_send_test_notification']);
        add_action('wp_ajax_tbp_clear_all_notifications', [$this, 'ajax_clear_all_notifications']);

        // Enqueue scripts on portal pages
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Auto-purge cron
        add_action('tbp_purge_old_notifications', [$this, 'purge_old_notifications']);
        add_action('init', [$this, 'schedule_purge_cron']);

        // Filter notification count for portal topbar
        add_filter('tbp_portal_notification_count', [$this, 'get_unread_count']);

        // Register notification types filter
        add_filter('tbp_notification_types', [$this, 'get_notification_types']);
    }

    /**
     * Get module settings
     */
    public function get_settings() {
        return [
            'app_id' => get_option('tbp_pusher_app_id', ''),
            'app_key' => get_option('tbp_pusher_app_key', ''),
            'app_secret' => get_option('tbp_pusher_app_secret', ''),
            'cluster' => get_option('tbp_pusher_cluster', 'eu'),
            'auto_purge' => get_option('tbp_pusher_auto_purge', '1'),
            'retention_days' => get_option('tbp_pusher_retention_days', '30'),
        ];
    }

    /**
     * Check if Pusher is configured
     */
    public function is_configured() {
        return !empty($this->settings['app_id'])
            && !empty($this->settings['app_key'])
            && !empty($this->settings['app_secret']);
    }

    /**
     * Get notification types
     */
    public function get_notification_types($types = []) {
        return array_merge(self::$notification_types, $types);
    }

    /**
     * Add settings tab
     */
    public function add_settings_tab($tabs) {
        // Insert after 'general' tab
        $new_tabs = [];
        foreach ($tabs as $key => $label) {
            $new_tabs[$key] = $label;
            if ($key === 'general') {
                $new_tabs['pusher'] = __('Pusher Config', 'tbp-core');
            }
        }
        // If general doesn't exist, just append
        if (!isset($new_tabs['pusher'])) {
            $new_tabs['pusher'] = __('Pusher Config', 'tbp-core');
        }
        return $new_tabs;
    }

    /**
     * Add settings sections
     */
    public function add_settings_sections($sections) {
        $sections['pusher'] = [
            'tbp_pusher_credentials' => [
                'title' => __('Pusher Credentials', 'tbp-core'),
                'description' => __('Enter your Pusher app credentials from <a href="https://dashboard.pusher.com" target="_blank">dashboard.pusher.com</a>', 'tbp-core'),
            ],
            'tbp_pusher_retention' => [
                'title' => __('Notification Retention', 'tbp-core'),
                'description' => __('Configure how long notifications are stored.', 'tbp-core'),
            ],
        ];
        return $sections;
    }

    /**
     * Add settings fields
     */
    public function add_settings_fields($fields) {
        $fields['pusher'] = [
            'tbp_pusher_app_id' => [
                'title' => __('App ID', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_pusher_credentials',
                'description' => __('Your Pusher App ID', 'tbp-core'),
            ],
            'tbp_pusher_app_key' => [
                'title' => __('App Key', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_pusher_credentials',
                'description' => __('Your Pusher App Key (public)', 'tbp-core'),
            ],
            'tbp_pusher_app_secret' => [
                'title' => __('App Secret', 'tbp-core'),
                'type' => 'password',
                'section' => 'tbp_pusher_credentials',
                'description' => __('Your Pusher App Secret (keep private)', 'tbp-core'),
            ],
            'tbp_pusher_cluster' => [
                'title' => __('Cluster', 'tbp-core'),
                'type' => 'select',
                'section' => 'tbp_pusher_credentials',
                'options' => [
                    'mt1' => 'mt1 (N. Virginia)',
                    'us2' => 'us2 (Ohio)',
                    'us3' => 'us3 (Oregon)',
                    'eu' => 'eu (Ireland)',
                    'ap1' => 'ap1 (Singapore)',
                    'ap2' => 'ap2 (Mumbai)',
                    'ap3' => 'ap3 (Tokyo)',
                    'ap4' => 'ap4 (Sydney)',
                    'sa1' => 'sa1 (São Paulo)',
                ],
                'default' => 'eu',
            ],
            'tbp_pusher_auto_purge' => [
                'title' => __('Auto Purge Notifications', 'tbp-core'),
                'type' => 'checkbox',
                'section' => 'tbp_pusher_retention',
                'description' => __('Automatically delete old notifications', 'tbp-core'),
                'default' => '1',
            ],
            'tbp_pusher_retention_days' => [
                'title' => __('Retention Period (days)', 'tbp-core'),
                'type' => 'number',
                'section' => 'tbp_pusher_retention',
                'description' => __('Number of days to keep notifications before auto-purge', 'tbp-core'),
                'default' => '30',
                'min' => 1,
                'max' => 365,
            ],
        ];
        return $fields;
    }

    /**
     * Schedule purge cron job
     */
    public function schedule_purge_cron() {
        if ($this->settings['auto_purge'] && !wp_next_scheduled('tbp_purge_old_notifications')) {
            wp_schedule_event(time(), 'daily', 'tbp_purge_old_notifications');
        } elseif (!$this->settings['auto_purge'] && wp_next_scheduled('tbp_purge_old_notifications')) {
            wp_clear_scheduled_hook('tbp_purge_old_notifications');
        }
    }

    /**
     * Purge old notifications
     */
    public function purge_old_notifications() {
        global $wpdb;
        $table = $wpdb->prefix . 'tbp_notifications';
        $days = absint($this->settings['retention_days']) ?: 30;

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days
        ));
    }

    /**
     * Enqueue scripts on portal pages
     */
    public function enqueue_scripts() {
        // Check if we're on a portal page
        $is_portal = false;

        if (class_exists('TBP_Portal_Layout') && method_exists('TBP_Portal_Layout', 'is_portal_page')) {
            $is_portal = TBP_Portal_Layout::is_portal_page();
        }

        // Also check via query var as fallback
        if (!$is_portal && get_query_var('tbp_portal_page')) {
            $is_portal = true;
        }

        if (!$is_portal) {
            return;
        }

        // Notifications CSS - always load
        wp_enqueue_style(
            'tbp-pusher-notifications',
            TBP_PUSHER_URL . 'assets/css/notifications.css',
            [],
            TBP_CORE_VERSION
        );

        // Pusher JS SDK (only if configured)
        if ($this->is_configured()) {
            wp_enqueue_script(
                'pusher-js',
                'https://js.pusher.com/8.2.0/pusher.min.js',
                [],
                '8.2.0',
                true
            );
        }

        // Our notifications handler - always load for dropdown functionality
        wp_enqueue_script(
            'tbp-pusher-notifications',
            TBP_PUSHER_URL . 'assets/js/notifications.js',
            ['jquery'],
            TBP_CORE_VERSION,
            true
        );

        wp_localize_script('tbp-pusher-notifications', 'tbpPusher', [
            'appKey' => $this->settings['app_key'] ?? '',
            'cluster' => $this->settings['cluster'] ?? 'eu',
            'userId' => get_current_user_id(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_pusher_nonce'),
            'isConfigured' => $this->is_configured(),
            'allNotificationsUrl' => home_url('/portal/all-notifications/'),
        ]);
    }

    /**
     * Create notifications table
     */
    public function maybe_create_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'tbp_notifications';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            $sql = "CREATE TABLE $table (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned NOT NULL DEFAULT 0,
                type varchar(50) NOT NULL DEFAULT 'info',
                title varchar(255) NOT NULL,
                message text NOT NULL,
                icon varchar(100) DEFAULT NULL,
                action_url varchar(500) DEFAULT NULL,
                data longtext,
                created_at datetime NOT NULL,
                is_read tinyint(1) NOT NULL DEFAULT 0,
                read_at datetime DEFAULT NULL,
                expires_at datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY is_read (is_read),
                KEY created_at (created_at),
                KEY type (type)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('tbp/v1', '/notifications', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_notifications'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('tbp/v1', '/notifications/send', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_send_notification'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * Core send notification method
     *
     * @param int    $user_id    Target user ID (0 for broadcast)
     * @param string $type       Notification type
     * @param string $title      Notification title
     * @param string $message    Notification message
     * @param array  $args       Additional arguments
     * @return int|false         Notification ID or false on failure
     */
    public static function send($user_id, $type, $title, $message, $args = []) {
        $defaults = [
            'icon' => null,
            'action_url' => null,
            'data' => [],
            'expires_at' => null,
        ];

        $args = wp_parse_args($args, $defaults);

        // Get icon from type if not specified
        if (!$args['icon']) {
            $types = apply_filters('tbp_notification_types', []);
            $args['icon'] = isset($types[$type]['icon']) ? $types[$type]['icon'] : 'ti-bell';
        }

        /**
         * Filter notification before sending
         *
         * @param array $notification Notification data
         * @param int   $user_id      Target user ID
         */
        $notification = apply_filters('tbp_notification_before_send', [
            'user_id' => $user_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $args['icon'],
            'action_url' => $args['action_url'],
            'data' => $args['data'],
            'created_at' => current_time('mysql'),
            'expires_at' => $args['expires_at'],
        ], $user_id);

        if (!$notification) {
            return false;
        }

        // Store notification in database
        $notification_id = self::store_notification($notification);

        if (!$notification_id) {
            return false;
        }

        // Send via Pusher if configured
        $instance = self::instance();
        if ($instance->is_configured()) {
            $instance->push_notification($user_id, array_merge($notification, ['id' => $notification_id]));
        }

        /**
         * Action after notification is sent
         *
         * @param int   $notification_id
         * @param array $notification
         */
        do_action('tbp_notification_sent', $notification_id, $notification);

        return $notification_id;
    }

    /**
     * Store notification in database
     */
    private static function store_notification($notification) {
        global $wpdb;
        $table = $wpdb->prefix . 'tbp_notifications';

        $result = $wpdb->insert($table, [
            'user_id' => $notification['user_id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'icon' => $notification['icon'],
            'action_url' => $notification['action_url'],
            'data' => json_encode($notification['data']),
            'created_at' => $notification['created_at'],
            'expires_at' => $notification['expires_at'],
            'is_read' => 0,
        ]);

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Push notification via Pusher
     */
    private function push_notification($user_id, $notification) {
        if (!$this->is_configured()) {
            return false;
        }

        $channel = $user_id > 0 ? 'private-user-' . $user_id : 'presence-portal';

        /**
         * Filter Pusher channel
         *
         * @param string $channel
         * @param int    $user_id
         * @param array  $notification
         */
        $channel = apply_filters('tbp_pusher_channel', $channel, $user_id, $notification);

        /**
         * Action to send notification via Pusher
         * Implement actual Pusher SDK here or via hook
         *
         * @param string $channel
         * @param array  $notification
         * @param array  $settings Pusher settings
         */
        do_action('tbp_pusher_send', $channel, $notification, $this->settings);

        return true;
    }

    /**
     * Get notifications for user
     */
    public function get_notifications($user_id = null, $args = []) {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $defaults = [
            'limit' => 20,
            'offset' => 0,
            'unread_only' => false,
            'type' => null,
        ];

        $args = wp_parse_args($args, $defaults);
        $table = $wpdb->prefix . 'tbp_notifications';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return [];
        }

        $where = $wpdb->prepare("WHERE (user_id = %d OR user_id = 0)", $user_id);
        $where .= " AND (expires_at IS NULL OR expires_at > NOW())";

        if ($args['unread_only']) {
            $where .= " AND is_read = 0";
        }

        if ($args['type']) {
            $where .= $wpdb->prepare(" AND type = %s", $args['type']);
        }

        $sql = $wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );

        $results = $wpdb->get_results($sql, ARRAY_A);

        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data'], true);
            $row['time_ago'] = human_time_diff(strtotime($row['created_at']), current_time('timestamp')) . ' ' . __('ago', 'tbp-core');
        }

        /**
         * Filter notifications before returning
         *
         * @param array $notifications
         * @param int   $user_id
         */
        return apply_filters('tbp_get_notifications', $results, $user_id);
    }

    /**
     * Get total notifications count for pagination
     */
    public function get_notifications_count($user_id = null, $unread_only = false) {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $table = $wpdb->prefix . 'tbp_notifications';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return 0;
        }

        $where = $wpdb->prepare("WHERE (user_id = %d OR user_id = 0)", $user_id);
        $where .= " AND (expires_at IS NULL OR expires_at > NOW())";

        if ($unread_only) {
            $where .= " AND is_read = 0";
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
    }

    /**
     * Get unread notification count
     */
    public function get_unread_count($count = 0) {
        if (!is_user_logged_in()) {
            return 0;
        }
        return $this->get_notifications_count(get_current_user_id(), true);
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $user_id = null) {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $table = $wpdb->prefix . 'tbp_notifications';

        // Only mark if notification belongs to user or is broadcast
        return $wpdb->query($wpdb->prepare(
            "UPDATE $table SET is_read = 1, read_at = %s WHERE id = %d AND (user_id = %d OR user_id = 0)",
            current_time('mysql'),
            $notification_id,
            $user_id
        ));
    }

    /**
     * Mark all notifications as read for user
     */
    public function mark_all_as_read($user_id = null) {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $table = $wpdb->prefix . 'tbp_notifications';

        return $wpdb->query($wpdb->prepare(
            "UPDATE $table SET is_read = 1, read_at = %s WHERE (user_id = %d OR user_id = 0) AND is_read = 0",
            current_time('mysql'),
            $user_id
        ));
    }

    /**
     * Delete notification
     */
    public function delete_notification($notification_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'tbp_notifications';

        return $wpdb->delete($table, ['id' => $notification_id]);
    }

    /**
     * AJAX: Get notifications
     */
    public function ajax_get_notifications() {
        check_ajax_referer('tbp_pusher_nonce', 'nonce');

        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 5;
        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        $notifications = $this->get_notifications(null, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        wp_send_json_success([
            'notifications' => $notifications,
            'unread_count' => $this->get_unread_count(),
            'total_count' => $this->get_notifications_count(),
        ]);
    }

    /**
     * AJAX: Mark notification as read
     */
    public function ajax_mark_notification_read() {
        check_ajax_referer('tbp_pusher_nonce', 'nonce');

        $notification_id = absint($_POST['notification_id'] ?? 0);

        if (!$notification_id) {
            wp_send_json_error(['message' => 'Invalid notification ID']);
        }

        $this->mark_as_read($notification_id);

        wp_send_json_success([
            'unread_count' => $this->get_unread_count(),
        ]);
    }

    /**
     * AJAX: Mark all notifications as read
     */
    public function ajax_mark_all_notifications_read() {
        check_ajax_referer('tbp_pusher_nonce', 'nonce');

        $this->mark_all_as_read();

        wp_send_json_success([
            'unread_count' => 0,
        ]);
    }

    /**
     * AJAX: Send test notification (admin only)
     */
    public function ajax_send_test_notification() {
        check_ajax_referer('tbp_core_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = absint($_POST['user_id'] ?? 0);

        if (!$user_id) {
            wp_send_json_error(['message' => 'Please enter a valid User ID']);
        }

        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(['message' => 'User not found']);
        }

        $notification_id = self::send(
            $user_id,
            'info',
            __('Test Notification', 'tbp-core'),
            __('This is a test notification sent from TBP Core settings.', 'tbp-core'),
            [
                'icon' => 'ti-bell-ringing',
                'action_url' => home_url('/portal/dashboard/'),
            ]
        );

        if ($notification_id) {
            wp_send_json_success([
                'message' => sprintf(__('Test notification sent to %s (ID: %d)', 'tbp-core'), $user->display_name, $user_id),
                'notification_id' => $notification_id,
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to send notification']);
        }
    }

    /**
     * AJAX: Clear all notifications for current user
     */
    public function ajax_clear_all_notifications() {
        check_ajax_referer('tbp_pusher_nonce', 'nonce');

        $user_id = get_current_user_id();
        $deleted = $this->clear_all_notifications($user_id);

        wp_send_json_success([
            'message' => sprintf(__('%d notifications cleared', 'tbp-core'), $deleted),
            'deleted' => $deleted,
        ]);
    }

    /**
     * Clear all notifications for a user
     */
    public function clear_all_notifications($user_id = null) {
        global $wpdb;

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $table = $wpdb->prefix . 'tbp_notifications';

        // Delete user-specific and broadcast notifications for this user
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE user_id = %d OR user_id = 0",
            $user_id
        ));

        return $deleted;
    }

    /**
     * REST: Get notifications
     */
    public function rest_get_notifications($request) {
        $limit = $request->get_param('limit') ?: 20;
        $offset = $request->get_param('offset') ?: 0;

        $notifications = $this->get_notifications(null, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return new WP_REST_Response([
            'notifications' => $notifications,
            'unread_count' => $this->get_unread_count(),
            'total_count' => $this->get_notifications_count(),
        ]);
    }

    /**
     * REST: Send notification
     */
    public function rest_send_notification($request) {
        $params = $request->get_json_params();

        $user_id = absint($params['user_id'] ?? 0);
        $type = sanitize_text_field($params['type'] ?? 'info');
        $title = sanitize_text_field($params['title'] ?? '');
        $message = sanitize_textarea_field($params['message'] ?? '');
        $args = [
            'icon' => sanitize_text_field($params['icon'] ?? ''),
            'action_url' => esc_url_raw($params['action_url'] ?? ''),
            'data' => $params['data'] ?? [],
        ];

        if (empty($title) || empty($message)) {
            return new WP_Error('missing_fields', 'Title and message are required', ['status' => 400]);
        }

        $notification_id = self::send($user_id, $type, $title, $message, $args);

        if (!$notification_id) {
            return new WP_Error('send_failed', 'Failed to send notification', ['status' => 500]);
        }

        return new WP_REST_Response([
            'success' => true,
            'notification_id' => $notification_id,
        ]);
    }

    /**
     * Get users by organizational filters
     */
    public static function get_users_by_filters($args = []) {
        $defaults = [
            'role' => null,
            'roles' => [],
            'faculty' => null,
            'faculties' => [],
            'department' => null,
            'departments' => [],
            'cycle' => null,
            'cycles' => [],
            'profile' => null,
            'profiles' => [],
        ];

        $args = wp_parse_args($args, $defaults);

        // Build user query
        $user_args = ['fields' => 'ID'];

        // Handle roles
        if ($args['role']) {
            $user_args['role'] = $args['role'];
        } elseif (!empty($args['roles'])) {
            $user_args['role__in'] = $args['roles'];
        }

        $users = get_users($user_args);

        // Filter by organizational assignments
        $filtered_users = [];

        foreach ($users as $user_id) {
            $include = true;

            // Check faculties
            if ($args['faculty'] || !empty($args['faculties'])) {
                $user_faculties = get_user_meta($user_id, 'tbp_faculties', true) ?: [];
                $check_faculties = $args['faculty'] ? [$args['faculty']] : $args['faculties'];
                if (empty(array_intersect($check_faculties, $user_faculties))) {
                    $include = false;
                }
            }

            // Check departments
            if ($include && ($args['department'] || !empty($args['departments']))) {
                $user_departments = get_user_meta($user_id, 'tbp_departments', true) ?: [];
                $check_departments = $args['department'] ? [$args['department']] : $args['departments'];
                if (empty(array_intersect($check_departments, $user_departments))) {
                    $include = false;
                }
            }

            // Check cycles
            if ($include && ($args['cycle'] || !empty($args['cycles']))) {
                $user_cycles = get_user_meta($user_id, 'tbp_cycles', true) ?: [];
                $check_cycles = $args['cycle'] ? [$args['cycle']] : $args['cycles'];
                if (empty(array_intersect($check_cycles, $user_cycles))) {
                    $include = false;
                }
            }

            // Check profiles
            if ($include && ($args['profile'] || !empty($args['profiles']))) {
                $user_profiles = get_user_meta($user_id, 'tbp_profiles', true) ?: [];
                $check_profiles = $args['profile'] ? [$args['profile']] : $args['profiles'];
                if (empty(array_intersect($check_profiles, $user_profiles))) {
                    $include = false;
                }
            }

            if ($include) {
                $filtered_users[] = $user_id;
            }
        }

        /**
         * Filter the users list
         *
         * @param array $filtered_users
         * @param array $args
         */
        return apply_filters('tbp_notification_recipients', $filtered_users, $args);
    }

    /**
     * Send to multiple users
     */
    public static function send_to_users($user_ids, $type, $title, $message, $args = []) {
        $sent = [];
        foreach ($user_ids as $user_id) {
            $notification_id = self::send($user_id, $type, $title, $message, $args);
            if ($notification_id) {
                $sent[$user_id] = $notification_id;
            }
        }
        return $sent;
    }
}

// Initialize
TBP_Pusher_Notifications::instance();

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Send notification to a single user
 *
 * @param int    $user_id  Target user ID
 * @param string $type     Notification type (info, success, warning, error, announcement, task, message)
 * @param string $title    Notification title
 * @param string $message  Notification message
 * @param array  $args     Optional: icon, action_url, data, expires_at
 * @return int|false       Notification ID or false on failure
 */
function tbp_notify_user($user_id, $type, $title, $message, $args = []) {
    return TBP_Pusher_Notifications::send($user_id, $type, $title, $message, $args);
}

/**
 * Send notification to multiple users
 *
 * @param array  $user_ids Array of user IDs
 * @param string $type     Notification type
 * @param string $title    Notification title
 * @param string $message  Notification message
 * @param array  $args     Optional arguments
 * @return array           Array of user_id => notification_id
 */
function tbp_notify_users($user_ids, $type, $title, $message, $args = []) {
    return TBP_Pusher_Notifications::send_to_users($user_ids, $type, $title, $message, $args);
}

/**
 * Send notification to all users with a specific role
 *
 * @param string $role    Role slug (e.g., 'student', 'secretary')
 * @param string $type    Notification type
 * @param string $title   Notification title
 * @param string $message Notification message
 * @param array  $args    Optional arguments
 * @return array          Array of user_id => notification_id
 */
function tbp_notify_role($role, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['role' => $role]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification to users with any of the specified roles
 *
 * @param array  $roles   Array of role slugs
 * @param string $type    Notification type
 * @param string $title   Notification title
 * @param string $message Notification message
 * @param array  $args    Optional arguments
 * @return array          Array of user_id => notification_id
 */
function tbp_notify_roles($roles, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['roles' => $roles]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification to users in a specific faculty
 *
 * @param int    $faculty_id Faculty term ID
 * @param string $type       Notification type
 * @param string $title      Notification title
 * @param string $message    Notification message
 * @param array  $args       Optional arguments
 * @return array             Array of user_id => notification_id
 */
function tbp_notify_faculty($faculty_id, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['faculty' => $faculty_id]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification to users in a specific department
 *
 * @param int    $department_id Department term ID
 * @param string $type          Notification type
 * @param string $title         Notification title
 * @param string $message       Notification message
 * @param array  $args          Optional arguments
 * @return array                Array of user_id => notification_id
 */
function tbp_notify_department($department_id, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['department' => $department_id]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification to users in a specific cycle
 *
 * @param int    $cycle_id Cycle term ID
 * @param string $type     Notification type
 * @param string $title    Notification title
 * @param string $message  Notification message
 * @param array  $args     Optional arguments
 * @return array           Array of user_id => notification_id
 */
function tbp_notify_cycle($cycle_id, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['cycle' => $cycle_id]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification to users with a specific profile
 *
 * @param int    $profile_id Profile term ID
 * @param string $type       Notification type
 * @param string $title      Notification title
 * @param string $message    Notification message
 * @param array  $args       Optional arguments
 * @return array             Array of user_id => notification_id
 */
function tbp_notify_profile($profile_id, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters(['profile' => $profile_id]);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Send notification with custom query filters
 * Allows combining role, faculty, department, cycle, profile filters
 *
 * @param array  $filters  Array of filters (role, roles, faculty, faculties, department, departments, cycle, cycles, profile, profiles)
 * @param string $type     Notification type
 * @param string $title    Notification title
 * @param string $message  Notification message
 * @param array  $args     Optional arguments
 * @return array           Array of user_id => notification_id
 */
function tbp_notify_query($filters, $type, $title, $message, $args = []) {
    $users = TBP_Pusher_Notifications::get_users_by_filters($filters);
    return TBP_Pusher_Notifications::send_to_users($users, $type, $title, $message, $args);
}

/**
 * Broadcast notification to all portal users (user_id = 0)
 * This notification will be visible to everyone
 *
 * @param string $type    Notification type
 * @param string $title   Notification title
 * @param string $message Notification message
 * @param array  $args    Optional arguments
 * @return int|false      Notification ID or false on failure
 */
function tbp_broadcast($type, $title, $message, $args = []) {
    return TBP_Pusher_Notifications::send(0, $type, $title, $message, $args);
}

/**
 * Get notification instance (for advanced usage)
 *
 * @return TBP_Pusher_Notifications
 */
function tbp_notifications() {
    return TBP_Pusher_Notifications::instance();
}
