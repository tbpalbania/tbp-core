<?php
/**
 * @module-title: Google Integration
 * @module-version: 1.0.0
 * @module-description: Google OAuth, Firebase, and Drive integration for the portal
 * @module-usage: Configure credentials in TBP Core Settings > Google Integration, then use helper functions for OAuth and Firebase
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_GOOGLE_PATH', __DIR__ . '/');
define('TBP_GOOGLE_URL', TBP_CORE_URL . 'inc/functions/google-integration/');

/**
 * TBP Google Integration
 */
class TBP_Google_Integration {

    private static $instance = null;
    private $settings = [];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->settings = $this->get_settings();

        // Admin settings
        add_filter('tbp_core_settings_tabs', [$this, 'add_settings_tab'], 10);
        add_filter('tbp_core_settings_sections', [$this, 'add_settings_sections']);
        add_filter('tbp_core_settings_fields', [$this, 'add_settings_fields']);

        // OAuth endpoints
        add_action('init', [$this, 'register_oauth_endpoint']);
        add_action('template_redirect', [$this, 'handle_oauth_callback']);

        // AJAX handlers
        add_action('wp_ajax_tbp_google_oauth_init', [$this, 'ajax_oauth_init']);
        add_action('wp_ajax_tbp_google_refresh_token', [$this, 'ajax_refresh_token']);
        add_action('wp_ajax_tbp_firebase_custom_token', [$this, 'ajax_get_firebase_token']);

        // REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Enqueue scripts on portal pages
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Get module settings
     */
    public function get_settings() {
        return [
            // Firebase
            'firebase_project_id' => get_option('tbp_firebase_project_id', ''),
            'firebase_api_key' => get_option('tbp_firebase_api_key', ''),
            'firebase_auth_domain' => get_option('tbp_firebase_auth_domain', ''),
            'firebase_storage_bucket' => get_option('tbp_firebase_storage_bucket', ''),
            'firebase_messaging_sender_id' => get_option('tbp_firebase_messaging_sender_id', ''),
            'firebase_app_id' => get_option('tbp_firebase_app_id', ''),
            'firebase_service_account' => get_option('tbp_firebase_service_account', ''),
            // Google OAuth
            'google_client_id' => get_option('tbp_google_client_id', ''),
            'google_client_secret' => get_option('tbp_google_client_secret', ''),
        ];
    }

    /**
     * Check if Firebase is configured
     */
    public function is_firebase_configured() {
        return !empty($this->settings['firebase_project_id'])
            && !empty($this->settings['firebase_api_key']);
    }

    /**
     * Check if Google OAuth is configured
     */
    public function is_oauth_configured() {
        return !empty($this->settings['google_client_id'])
            && !empty($this->settings['google_client_secret']);
    }

    /**
     * Get dynamic callback URL
     */
    public static function get_oauth_callback_url() {
        return home_url('/tbp-oauth-callback/');
    }

    /**
     * Get dynamic JavaScript origin
     */
    public static function get_javascript_origin() {
        return home_url();
    }

    /**
     * Add settings tab
     */
    public function add_settings_tab($tabs) {
        $tabs['google'] = __('Google Integration', 'tbp-core');
        return $tabs;
    }

    /**
     * Add settings sections
     */
    public function add_settings_sections($sections) {
        $sections['google'] = [
            'tbp_google_oauth' => [
                'title' => __('Google OAuth 2.0', 'tbp-core'),
                'description' => sprintf(
                    __('Configure OAuth credentials from <a href="%s" target="_blank">Google Cloud Console</a>.<br><br>
                    <strong>Authorized redirect URI:</strong> <code>%s</code><br>
                    <strong>Authorized JavaScript origin:</strong> <code>%s</code>', 'tbp-core'),
                    'https://console.cloud.google.com/apis/credentials',
                    esc_html(self::get_oauth_callback_url()),
                    esc_html(self::get_javascript_origin())
                ),
            ],
            'tbp_firebase_config' => [
                'title' => __('Firebase Configuration', 'tbp-core'),
                'description' => sprintf(
                    __('Get these values from <a href="%s" target="_blank">Firebase Console</a> > Project Settings > General (Web App config).', 'tbp-core'),
                    'https://console.firebase.google.com/'
                ),
            ],
            'tbp_firebase_admin' => [
                'title' => __('Firebase Admin SDK', 'tbp-core'),
                'description' => __('For server-side operations (custom tokens, Firestore admin). Get service account JSON from Firebase Console > Project Settings > Service Accounts.', 'tbp-core'),
            ],
        ];
        return $sections;
    }

    /**
     * Add settings fields
     */
    public function add_settings_fields($fields) {
        $fields['google'] = [
            // Google OAuth
            'tbp_google_client_id' => [
                'title' => __('Client ID', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_google_oauth',
                'description' => __('OAuth 2.0 Client ID', 'tbp-core'),
            ],
            'tbp_google_client_secret' => [
                'title' => __('Client Secret', 'tbp-core'),
                'type' => 'password',
                'section' => 'tbp_google_oauth',
                'description' => __('OAuth 2.0 Client Secret (keep private)', 'tbp-core'),
            ],
            // Firebase Config
            'tbp_firebase_project_id' => [
                'title' => __('Project ID', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('Firebase Project ID (e.g., my-app-12345)', 'tbp-core'),
            ],
            'tbp_firebase_api_key' => [
                'title' => __('API Key', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('Web API Key', 'tbp-core'),
            ],
            'tbp_firebase_auth_domain' => [
                'title' => __('Auth Domain', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('Usually: [project-id].firebaseapp.com', 'tbp-core'),
            ],
            'tbp_firebase_storage_bucket' => [
                'title' => __('Storage Bucket', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('Usually: [project-id].appspot.com', 'tbp-core'),
            ],
            'tbp_firebase_messaging_sender_id' => [
                'title' => __('Messaging Sender ID', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('For Firebase Cloud Messaging', 'tbp-core'),
            ],
            'tbp_firebase_app_id' => [
                'title' => __('App ID', 'tbp-core'),
                'type' => 'text',
                'section' => 'tbp_firebase_config',
                'description' => __('Firebase App ID', 'tbp-core'),
            ],
            // Firebase Admin
            'tbp_firebase_service_account' => [
                'title' => __('Service Account JSON', 'tbp-core'),
                'type' => 'textarea',
                'section' => 'tbp_firebase_admin',
                'description' => __('Paste the entire service account JSON file contents here. Required for custom token generation.', 'tbp-core'),
            ],
        ];
        return $fields;
    }

    /**
     * Register OAuth callback endpoint
     */
    public function register_oauth_endpoint() {
        add_rewrite_rule('^tbp-oauth-callback/?$', 'index.php?tbp_oauth_callback=1', 'top');
        add_filter('query_vars', function($vars) {
            $vars[] = 'tbp_oauth_callback';
            return $vars;
        });
    }

    /**
     * Handle OAuth callback
     */
    public function handle_oauth_callback() {
        if (!get_query_var('tbp_oauth_callback')) {
            return;
        }

        $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
        $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
        $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';

        if ($error) {
            $this->oauth_error_redirect($error);
            return;
        }

        if (!$code || !$state) {
            $this->oauth_error_redirect('missing_params');
            return;
        }

        // Verify state
        $stored_state = get_transient('tbp_oauth_state_' . $state);
        if (!$stored_state) {
            $this->oauth_error_redirect('invalid_state');
            return;
        }

        $user_id = $stored_state['user_id'];
        $redirect_to = $stored_state['redirect_to'];
        $scopes = $stored_state['scopes'];

        delete_transient('tbp_oauth_state_' . $state);

        // Exchange code for tokens
        $tokens = $this->exchange_code_for_tokens($code);

        if (is_wp_error($tokens)) {
            $this->oauth_error_redirect($tokens->get_error_message());
            return;
        }

        // Store tokens for user
        $this->store_user_tokens($user_id, $tokens, $scopes);

        // Redirect back
        wp_safe_redirect($redirect_to ?: home_url('/portal/dashboard/'));
        exit;
    }

    /**
     * Exchange authorization code for tokens
     */
    private function exchange_code_for_tokens($code) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'code' => $code,
                'client_id' => $this->settings['google_client_id'],
                'client_secret' => $this->settings['google_client_secret'],
                'redirect_uri' => self::get_oauth_callback_url(),
                'grant_type' => 'authorization_code',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('oauth_error', $body['error_description'] ?? $body['error']);
        }

        return $body;
    }

    /**
     * Store user tokens
     */
    private function store_user_tokens($user_id, $tokens, $scopes) {
        $token_data = [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? '',
            'expires_at' => time() + ($tokens['expires_in'] ?? 3600),
            'scopes' => $scopes,
            'updated_at' => current_time('mysql'),
        ];

        update_user_meta($user_id, 'tbp_google_tokens', $token_data);

        do_action('tbp_google_tokens_updated', $user_id, $token_data);
    }

    /**
     * Get user tokens
     */
    public function get_user_tokens($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $tokens = get_user_meta($user_id, 'tbp_google_tokens', true);

        if (!$tokens) {
            return null;
        }

        // Check if expired and refresh if needed
        if ($tokens['expires_at'] < time() && !empty($tokens['refresh_token'])) {
            $new_tokens = $this->refresh_access_token($tokens['refresh_token']);
            if (!is_wp_error($new_tokens)) {
                $tokens['access_token'] = $new_tokens['access_token'];
                $tokens['expires_at'] = time() + ($new_tokens['expires_in'] ?? 3600);
                $tokens['updated_at'] = current_time('mysql');
                update_user_meta($user_id, 'tbp_google_tokens', $tokens);
            }
        }

        return $tokens;
    }

    /**
     * Refresh access token
     */
    private function refresh_access_token($refresh_token) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'refresh_token' => $refresh_token,
                'client_id' => $this->settings['google_client_id'],
                'client_secret' => $this->settings['google_client_secret'],
                'grant_type' => 'refresh_token',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('refresh_error', $body['error_description'] ?? $body['error']);
        }

        return $body;
    }

    /**
     * OAuth error redirect
     */
    private function oauth_error_redirect($error) {
        wp_safe_redirect(add_query_arg('oauth_error', urlencode($error), home_url('/portal/settings/')));
        exit;
    }

    /**
     * Generate OAuth authorization URL
     */
    public function get_auth_url($scopes = [], $redirect_to = '') {
        if (!$this->is_oauth_configured()) {
            return new WP_Error('not_configured', 'Google OAuth is not configured');
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'User must be logged in');
        }

        // Default scopes
        if (empty($scopes)) {
            $scopes = ['openid', 'email', 'profile'];
        }

        // Generate state
        $state = wp_generate_password(32, false);

        // Store state with user info
        set_transient('tbp_oauth_state_' . $state, [
            'user_id' => $user_id,
            'redirect_to' => $redirect_to ?: home_url('/portal/dashboard/'),
            'scopes' => $scopes,
        ], 600); // 10 minutes

        $params = [
            'client_id' => $this->settings['google_client_id'],
            'redirect_uri' => self::get_oauth_callback_url(),
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Generate Firebase custom token
     */
    public function generate_firebase_token($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return new WP_Error('not_logged_in', 'User must be logged in');
        }

        $service_account = $this->settings['firebase_service_account'];
        if (empty($service_account)) {
            return new WP_Error('not_configured', 'Firebase service account not configured');
        }

        $sa = json_decode($service_account, true);
        if (!$sa || !isset($sa['private_key']) || !isset($sa['client_email'])) {
            return new WP_Error('invalid_config', 'Invalid service account JSON');
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found');
        }

        // Create JWT
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $uid = 'wp_' . $user_id;
        $claims = [
            'iss' => $sa['client_email'],
            'sub' => $sa['client_email'],
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $now + 3600,
            'uid' => $uid,
            'claims' => [
                'wp_user_id' => $user_id,
                'email' => $user->user_email,
                'name' => $user->display_name,
                'role' => $this->get_user_portal_role($user_id),
            ],
        ];

        $token = $this->create_jwt($header, $claims, $sa['private_key']);

        return $token;
    }

    /**
     * Create JWT
     */
    private function create_jwt($header, $payload, $private_key) {
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $data = $header_encoded . '.' . $payload_encoded;

        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);

        return $data . '.' . $this->base64url_encode($signature);
    }

    /**
     * Base64 URL encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get user portal role
     */
    private function get_user_portal_role($user_id) {
        if (class_exists('TBP_Portal_Roles')) {
            return TBP_Portal_Roles::get_user_role($user_id);
        }

        $user = get_userdata($user_id);
        $roles = $user->roles ?? [];
        return $roles[0] ?? 'subscriber';
    }

    /**
     * AJAX: Initialize OAuth
     */
    public function ajax_oauth_init() {
        check_ajax_referer('tbp_google_nonce', 'nonce');

        $scopes = isset($_POST['scopes']) ? array_map('sanitize_text_field', (array)$_POST['scopes']) : [];
        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';

        $auth_url = $this->get_auth_url($scopes, $redirect_to);

        if (is_wp_error($auth_url)) {
            wp_send_json_error(['message' => $auth_url->get_error_message()]);
        }

        wp_send_json_success(['auth_url' => $auth_url]);
    }

    /**
     * AJAX: Refresh token
     */
    public function ajax_refresh_token() {
        check_ajax_referer('tbp_google_nonce', 'nonce');

        $tokens = $this->get_user_tokens();

        if (!$tokens) {
            wp_send_json_error(['message' => 'No tokens found']);
        }

        wp_send_json_success([
            'access_token' => $tokens['access_token'],
            'expires_at' => $tokens['expires_at'],
        ]);
    }

    /**
     * AJAX: Get Firebase custom token
     */
    public function ajax_get_firebase_token() {
        check_ajax_referer('tbp_google_nonce', 'nonce');

        $token = $this->generate_firebase_token();

        if (is_wp_error($token)) {
            wp_send_json_error(['message' => $token->get_error_message()]);
        }

        wp_send_json_success(['token' => $token]);
    }

    /**
     * Register REST routes
     */
    public function register_rest_routes() {
        register_rest_route('tbp/v1', '/google/firebase-token', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_firebase_token'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('tbp/v1', '/google/config', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_config'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);
    }

    /**
     * REST: Get Firebase token
     */
    public function rest_get_firebase_token() {
        $token = $this->generate_firebase_token();

        if (is_wp_error($token)) {
            return new WP_Error('token_error', $token->get_error_message(), ['status' => 400]);
        }

        return new WP_REST_Response(['token' => $token]);
    }

    /**
     * REST: Get Firebase config (public parts only)
     */
    public function rest_get_config() {
        return new WP_REST_Response([
            'firebase' => [
                'apiKey' => $this->settings['firebase_api_key'],
                'authDomain' => $this->settings['firebase_auth_domain'],
                'projectId' => $this->settings['firebase_project_id'],
                'storageBucket' => $this->settings['firebase_storage_bucket'],
                'messagingSenderId' => $this->settings['firebase_messaging_sender_id'],
                'appId' => $this->settings['firebase_app_id'],
            ],
            'configured' => [
                'firebase' => $this->is_firebase_configured(),
                'oauth' => $this->is_oauth_configured(),
            ],
        ]);
    }

    /**
     * Enqueue scripts on portal pages
     */
    public function enqueue_scripts() {
        $is_portal = false;

        if (class_exists('TBP_Portal_Layout') && method_exists('TBP_Portal_Layout', 'is_portal_page')) {
            $is_portal = TBP_Portal_Layout::is_portal_page();
        }

        if (!$is_portal) {
            return;
        }

        // Firebase SDK (only if configured)
        if ($this->is_firebase_configured()) {
            wp_enqueue_script(
                'firebase-app',
                'https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js',
                [],
                '10.7.1',
                true
            );

            wp_enqueue_script(
                'firebase-auth',
                'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js',
                ['firebase-app'],
                '10.7.1',
                true
            );

            wp_enqueue_script(
                'firebase-firestore',
                'https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js',
                ['firebase-app'],
                '10.7.1',
                true
            );
        }

        // Our integration script
        wp_enqueue_script(
            'tbp-google-integration',
            TBP_GOOGLE_URL . 'assets/js/google-integration.js',
            ['jquery', 'firebase-app', 'firebase-auth', 'firebase-firestore'],
            TBP_CORE_VERSION,
            true
        );

        wp_localize_script('tbp-google-integration', 'tbpGoogle', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('tbp/v1/google/'),
            'nonce' => wp_create_nonce('tbp_google_nonce'),
            'restNonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
            'firebase' => $this->is_firebase_configured() ? [
                'apiKey' => $this->settings['firebase_api_key'],
                'authDomain' => $this->settings['firebase_auth_domain'],
                'projectId' => $this->settings['firebase_project_id'],
                'storageBucket' => $this->settings['firebase_storage_bucket'],
                'messagingSenderId' => $this->settings['firebase_messaging_sender_id'],
                'appId' => $this->settings['firebase_app_id'],
            ] : null,
            'configured' => [
                'firebase' => $this->is_firebase_configured(),
                'oauth' => $this->is_oauth_configured(),
            ],
        ]);
    }
}

// Initialize
TBP_Google_Integration::instance();

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get Google Integration instance
 */
function tbp_google() {
    return TBP_Google_Integration::instance();
}

/**
 * Get Firebase custom token for current user
 */
function tbp_get_firebase_token($user_id = null) {
    return TBP_Google_Integration::instance()->generate_firebase_token($user_id);
}

/**
 * Get Google OAuth authorization URL
 */
function tbp_get_google_auth_url($scopes = [], $redirect_to = '') {
    return TBP_Google_Integration::instance()->get_auth_url($scopes, $redirect_to);
}

/**
 * Get user's Google tokens
 */
function tbp_get_google_tokens($user_id = null) {
    return TBP_Google_Integration::instance()->get_user_tokens($user_id);
}

/**
 * Check if user has connected Google account
 */
function tbp_user_has_google($user_id = null) {
    $tokens = tbp_get_google_tokens($user_id);
    return !empty($tokens);
}

/**
 * Get OAuth callback URL (for settings display)
 */
function tbp_get_oauth_callback_url() {
    return TBP_Google_Integration::get_oauth_callback_url();
}

/**
 * Get JavaScript origin (for settings display)
 */
function tbp_get_javascript_origin() {
    return TBP_Google_Integration::get_javascript_origin();
}
