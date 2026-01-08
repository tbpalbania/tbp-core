<?php
/**
 * @module-title: Firebase Messaging
 * @module-version: 1.0.0
 * @module-description: Real-time chat messaging using Firebase Firestore
 * @module-usage: Requires Google Integration module. Adds floating chat button to portal pages.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_MESSAGING_PATH', __DIR__ . '/');
define('TBP_MESSAGING_URL', TBP_CORE_URL . 'inc/functions/firebase-messaging/');

/**
 * TBP Firebase Messaging
 */
class TBP_Firebase_Messaging {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Check dependency
        if (!$this->check_dependencies()) {
            add_action('admin_notices', [$this, 'dependency_notice']);
            return;
        }

        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Render chat UI
        add_action('wp_footer', [$this, 'render_chat_ui']);

        // AJAX handlers
        add_action('wp_ajax_tbp_messaging_get_users', [$this, 'ajax_get_users']);
        add_action('wp_ajax_tbp_messaging_get_conversation', [$this, 'ajax_get_conversation']);
    }

    /**
     * Check if Google Integration module is active
     */
    private function check_dependencies() {
        return class_exists('TBP_Google_Integration');
    }

    /**
     * Show dependency notice
     */
    public function dependency_notice() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Firebase Messaging requires the Google Integration module to be active.', 'tbp-core');
        echo '</p></div>';
    }

    /**
     * Enqueue scripts on portal pages
     */
    public function enqueue_scripts() {
        if (!$this->is_portal_page() || !is_user_logged_in()) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'tbp-messaging',
            TBP_MESSAGING_URL . 'assets/css/messaging.css',
            [],
            TBP_CORE_VERSION
        );

        // JS
        wp_enqueue_script(
            'tbp-messaging',
            TBP_MESSAGING_URL . 'assets/js/messaging.js',
            ['jquery', 'tbp-google-integration'],
            TBP_CORE_VERSION,
            true
        );

        $user = wp_get_current_user();

        wp_localize_script('tbp-messaging', 'tbpMessaging', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_messaging_nonce'),
            'currentUser' => [
                'id' => $user->ID,
                'uid' => 'wp_' . $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'avatar' => get_avatar_url($user->ID, ['size' => 40]),
                'role' => $this->get_user_role($user->ID),
            ],
            'i18n' => [
                'chat' => __('Chat', 'tbp-core'),
                'messages' => __('Messages', 'tbp-core'),
                'online' => __('Online', 'tbp-core'),
                'offline' => __('Offline', 'tbp-core'),
                'typing' => __('typing...', 'tbp-core'),
                'noMessages' => __('No messages yet', 'tbp-core'),
                'typeMessage' => __('Type a message...', 'tbp-core'),
                'send' => __('Send', 'tbp-core'),
                'close' => __('Close', 'tbp-core'),
                'noUsers' => __('No users online', 'tbp-core'),
                'searchUsers' => __('Search users...', 'tbp-core'),
                'justNow' => __('Just now', 'tbp-core'),
            ],
        ]);
    }

    /**
     * Check if on portal page
     */
    private function is_portal_page() {
        if (class_exists('TBP_Portal_Layout') && method_exists('TBP_Portal_Layout', 'is_portal_page')) {
            return TBP_Portal_Layout::is_portal_page();
        }
        return false;
    }

    /**
     * Get user role
     */
    private function get_user_role($user_id) {
        if (class_exists('TBP_Portal_Roles')) {
            return TBP_Portal_Roles::get_user_role($user_id);
        }
        $user = get_userdata($user_id);
        return $user->roles[0] ?? 'subscriber';
    }

    /**
     * Render chat UI in footer
     */
    public function render_chat_ui() {
        if (!$this->is_portal_page() || !is_user_logged_in()) {
            return;
        }
        ?>
        <!-- TBP Messaging UI -->
        <div id="tbp-messaging" class="tbp-messaging">
            <!-- Floating Button -->
            <button type="button" class="tbp-messaging-fab" id="tbp-messaging-fab">
                <i class="ti ti-message-circle"></i>
                <span class="tbp-messaging-fab-badge" style="display: none;">0</span>
            </button>

            <!-- Users Offcanvas -->
            <div class="tbp-messaging-offcanvas" id="tbp-messaging-offcanvas">
                <div class="tbp-messaging-offcanvas-header">
                    <h3><?php _e('Messages', 'tbp-core'); ?></h3>
                    <button type="button" class="tbp-messaging-offcanvas-close" id="tbp-messaging-offcanvas-close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="tbp-messaging-offcanvas-search">
                    <i class="ti ti-search"></i>
                    <input type="text" id="tbp-messaging-search" placeholder="<?php esc_attr_e('Search users...', 'tbp-core'); ?>">
                </div>
                <div class="tbp-messaging-offcanvas-tabs">
                    <button type="button" class="tbp-messaging-tab active" data-tab="online">
                        <?php _e('Online', 'tbp-core'); ?>
                        <span class="tbp-messaging-tab-count" id="tbp-online-count">0</span>
                    </button>
                    <button type="button" class="tbp-messaging-tab" data-tab="recent">
                        <?php _e('Recent', 'tbp-core'); ?>
                    </button>
                </div>
                <div class="tbp-messaging-offcanvas-body">
                    <div class="tbp-messaging-users-list" id="tbp-messaging-users">
                        <!-- Users populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Chat Windows Container -->
            <div class="tbp-messaging-chats" id="tbp-messaging-chats">
                <!-- Chat windows inserted here by JS -->
            </div>
        </div>

        <!-- Chat Window Template -->
        <template id="tbp-chat-window-template">
            <div class="tbp-chat-window" data-conversation-id="">
                <div class="tbp-chat-header">
                    <div class="tbp-chat-header-user">
                        <div class="tbp-chat-avatar">
                            <img src="" alt="">
                            <span class="tbp-chat-status"></span>
                        </div>
                        <div class="tbp-chat-user-info">
                            <span class="tbp-chat-user-name"></span>
                            <span class="tbp-chat-user-status"></span>
                        </div>
                    </div>
                    <div class="tbp-chat-header-actions">
                        <button type="button" class="tbp-chat-minimize">
                            <i class="ti ti-minus"></i>
                        </button>
                        <button type="button" class="tbp-chat-close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>
                <div class="tbp-chat-body">
                    <div class="tbp-chat-messages">
                        <!-- Messages populated by JS -->
                    </div>
                    <div class="tbp-chat-typing" style="display: none;">
                        <span class="tbp-chat-typing-dots">
                            <span></span><span></span><span></span>
                        </span>
                        <span class="tbp-chat-typing-text"></span>
                    </div>
                </div>
                <div class="tbp-chat-footer">
                    <div class="tbp-chat-input-wrapper">
                        <input type="text" class="tbp-chat-input" placeholder="<?php esc_attr_e('Type a message...', 'tbp-core'); ?>">
                        <button type="button" class="tbp-chat-send">
                            <i class="ti ti-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <?php
    }

    /**
     * AJAX: Get users list
     */
    public function ajax_get_users() {
        check_ajax_referer('tbp_messaging_nonce', 'nonce');

        $current_user_id = get_current_user_id();
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        // Get portal users (for now, get all - permissions will filter later)
        $args = [
            'role__in' => ['student', 'secretary', 'coordinator', 'department_director', 'dean', 'rector'],
            'exclude' => [$current_user_id],
            'number' => 50,
        ];

        if ($search) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        $users = get_users($args);

        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'uid' => 'wp_' . $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'avatar' => get_avatar_url($user->ID, ['size' => 40]),
                'role' => $this->get_user_role($user->ID),
                'roleName' => $this->get_role_display_name($user->ID),
            ];
        }

        wp_send_json_success(['users' => $result]);
    }

    /**
     * Get role display name
     */
    private function get_role_display_name($user_id) {
        if (class_exists('TBP_Portal_Roles')) {
            $role = TBP_Portal_Roles::get_user_role($user_id);
            return TBP_Portal_Roles::get_role_display_name($role);
        }
        return ucfirst($this->get_user_role($user_id));
    }

    /**
     * AJAX: Get or create conversation
     */
    public function ajax_get_conversation() {
        check_ajax_referer('tbp_messaging_nonce', 'nonce');

        $other_user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;

        if (!$other_user_id) {
            wp_send_json_error(['message' => 'Invalid user']);
        }

        $current_user_id = get_current_user_id();

        // Generate conversation ID (sorted UIDs for consistency)
        $uids = ['wp_' . $current_user_id, 'wp_' . $other_user_id];
        sort($uids);
        $conversation_id = implode('_', $uids);

        $other_user = get_userdata($other_user_id);

        wp_send_json_success([
            'conversationId' => $conversation_id,
            'otherUser' => [
                'id' => $other_user->ID,
                'uid' => 'wp_' . $other_user->ID,
                'name' => $other_user->display_name,
                'avatar' => get_avatar_url($other_user->ID, ['size' => 40]),
                'role' => $this->get_user_role($other_user->ID),
            ],
        ]);
    }
}

// Initialize
TBP_Firebase_Messaging::instance();
