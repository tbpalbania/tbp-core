<?php
/**
 * GitHub Updater for TBP Core
 *
 * Enables plugin updates via GitHub Releases using WordPress's native update system.
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_GitHub_Updater {

    private $slug;
    private $plugin_file;
    private $plugin_data;
    private $github_repo;
    private $github_api_url;
    private $cache_key;
    private $cache_expiry = 6 * HOUR_IN_SECONDS;

    public function __construct() {
        $this->slug = 'tbp-core';
        $this->plugin_file = 'tbp-core/tbp-core.php';
        $this->github_repo = 'tbpalbania/tbp-core';
        $this->github_api_url = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
        $this->cache_key = 'tbp_core_github_update';

        // Core update hooks
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);

        // Clear cache when WordPress does force-check
        add_action('load-update-core.php', [$this, 'maybe_clear_cache']);

        // Auto-updates support
        add_filter('auto_update_plugin', [$this, 'auto_update'], 10, 2);
        add_action('admin_init', [$this, 'register_auto_update_hooks']);
    }

    /**
     * Register auto-update related hooks
     */
    public function register_auto_update_hooks() {
        // Force the plugin to appear in the auto-updates UI
        add_filter('plugin_auto_update_setting_html', [$this, 'auto_update_setting_html'], 10, 3);
    }

    /**
     * Allow auto-updates for this plugin
     */
    public function auto_update($update, $item) {
        if (isset($item->slug) && $item->slug === $this->slug) {
            $auto_updates = (array) get_site_option('auto_update_plugins', []);
            return in_array($this->plugin_file, $auto_updates, true);
        }
        return $update;
    }

    /**
     * Enable auto-update toggle for this plugin
     */
    public function auto_update_setting_html($html, $plugin_file, $plugin_data) {
        if ($plugin_file !== $this->plugin_file) {
            return $html;
        }

        // Check if auto-updates are enabled for this plugin
        $auto_updates = (array) get_site_option('auto_update_plugins', []);
        $is_enabled = in_array($this->plugin_file, $auto_updates, true);

        if ($is_enabled) {
            $text = __('Disable auto-updates');
            $action = 'disable';
        } else {
            $text = __('Enable auto-updates');
            $action = 'enable';
        }

        $query_args = [
            'action' => $action . '-auto-update',
            'plugin' => $this->plugin_file,
            'paged' => isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : 1,
        ];
        $url = add_query_arg($query_args, 'plugins.php');

        return sprintf(
            '<a href="%s" class="toggle-auto-update aria-button-if-js" data-wp-action="%s"><span class="dashicons dashicons-update spin hidden" aria-hidden="true"></span><span class="label">%s</span></a>',
            wp_nonce_url($url, 'updates'),
            $action,
            esc_html($text)
        );
    }

    /**
     * Get plugin data
     */
    private function get_plugin_data() {
        if (empty($this->plugin_data)) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $this->plugin_data = get_plugin_data(TBP_CORE_PATH . 'tbp-core.php');
        }
        return $this->plugin_data;
    }

    /**
     * Fetch release info from GitHub API
     */
    private function get_github_release() {
        $cached = get_transient($this->cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get($this->github_api_url, [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'TBP-Core-Updater',
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $release = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($release) || !isset($release['tag_name'])) {
            return false;
        }

        set_transient($this->cache_key, $release, $this->cache_expiry);

        return $release;
    }

    /**
     * Get download URL from release - specifically looks for tbp-core.zip
     */
    private function get_download_url($release) {
        // Look specifically for tbp-core.zip asset
        if (!empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if ($asset['name'] === 'tbp-core.zip') {
                    return $asset['browser_download_url'];
                }
            }
        }

        // No proper zip found - return false to indicate update not available
        return false;
    }

    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_github_release();
        if (!$release) {
            return $transient;
        }

        $plugin_data = $this->get_plugin_data();
        $current_version = $plugin_data['Version'];
        $remote_version = ltrim($release['tag_name'], 'v');
        $download_url = $this->get_download_url($release);

        // Only show update if there's a proper tbp-core.zip attached
        if (version_compare($remote_version, $current_version, '>') && $download_url) {
            $transient->response[$this->plugin_file] = (object) [
                'id' => $this->plugin_file,
                'slug' => $this->slug,
                'plugin' => $this->plugin_file,
                'new_version' => $remote_version,
                'url' => $release['html_url'],
                'package' => $download_url,
                'icons' => [],
                'banners' => [],
                'banners_rtl' => [],
                'requires' => '5.0',
                'requires_php' => '7.4',
                'tested' => get_bloginfo('version'),
                'compatibility' => new stdClass(),
            ];
        }

        // Always register in no_update for auto-update UI to work
        if (!isset($transient->response[$this->plugin_file])) {
            $transient->no_update[$this->plugin_file] = (object) [
                'id' => $this->plugin_file,
                'slug' => $this->slug,
                'plugin' => $this->plugin_file,
                'new_version' => $current_version,
                'url' => 'https://github.com/' . $this->github_repo,
                'package' => '',
            ];
        }

        return $transient;
    }

    /**
     * Plugin information for the update modal
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || !isset($args->slug) || $args->slug !== $this->slug) {
            return $result;
        }

        $release = $this->get_github_release();
        if (!$release) {
            return $result;
        }

        $plugin_data = $this->get_plugin_data();
        $remote_version = ltrim($release['tag_name'], 'v');

        return (object) [
            'name' => $plugin_data['Name'],
            'slug' => $this->slug,
            'version' => $remote_version,
            'author' => $plugin_data['Author'],
            'author_profile' => $plugin_data['AuthorURI'],
            'homepage' => $plugin_data['PluginURI'],
            'requires' => '5.0',
            'requires_php' => '7.4',
            'tested' => get_bloginfo('version'),
            'downloaded' => 0,
            'last_updated' => $release['published_at'],
            'sections' => [
                'description' => $plugin_data['Description'],
                'changelog' => $this->parse_changelog($release['body']),
            ],
            'download_link' => $this->get_download_url($release),
        ];
    }

    /**
     * Parse release notes as changelog
     */
    private function parse_changelog($body) {
        if (empty($body)) {
            return '<p>No changelog available.</p>';
        }

        // Convert markdown to basic HTML
        $changelog = esc_html($body);
        $changelog = nl2br($changelog);

        return '<div class="tbp-changelog">' . $changelog . '</div>';
    }

    /**
     * Add GitHub link to plugin row meta
     */
    public function plugin_row_meta($links, $file) {
        if ($file !== $this->plugin_file) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            'https://github.com/' . $this->github_repo,
            __('View on GitHub', 'tbp-core')
        );

        return $links;
    }

    /**
     * Clear cache when WordPress does a force update check
     */
    public function maybe_clear_cache() {
        if (isset($_GET['force-check']) && $_GET['force-check'] == '1') {
            delete_transient($this->cache_key);
        }
    }

    /**
     * Force check for updates (clears cache)
     */
    public function force_check() {
        delete_transient($this->cache_key);
        delete_site_transient('update_plugins');
    }
}
