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

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_filter('upgrader_post_install', [$this, 'post_install'], 10, 3);
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);
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
     * Get download URL from release
     */
    private function get_download_url($release) {
        // First, check for attached zip asset
        if (!empty($release['assets'])) {
            foreach ($release['assets'] as $asset) {
                if (strpos($asset['name'], '.zip') !== false) {
                    return $asset['browser_download_url'];
                }
            }
        }

        // Fallback to GitHub's auto-generated source zip
        return $release['zipball_url'];
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

        if (version_compare($remote_version, $current_version, '>')) {
            $transient->response[$this->plugin_file] = (object) [
                'slug' => $this->slug,
                'plugin' => $this->plugin_file,
                'new_version' => $remote_version,
                'url' => $release['html_url'],
                'package' => $this->get_download_url($release),
                'icons' => [],
                'banners' => [],
                'requires' => '5.0',
                'requires_php' => '7.4',
                'tested' => get_bloginfo('version'),
            ];
        } else {
            // No update available
            $transient->no_update[$this->plugin_file] = (object) [
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
        if ($action !== 'plugin_information' || $args->slug !== $this->slug) {
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
     * Handle post-install to fix folder naming
     */
    public function post_install($response, $hook_extra, $result) {
        global $wp_filesystem;

        if (!isset($hook_extra['plugin']) || $hook_extra['plugin'] !== $this->plugin_file) {
            return $response;
        }

        $plugin_folder = WP_PLUGIN_DIR . '/' . $this->slug;
        $wp_filesystem->move($result['destination'], $plugin_folder);
        $result['destination'] = $plugin_folder;

        // Clear update cache
        delete_transient($this->cache_key);

        // Re-activate plugin if it was active
        if (is_plugin_active($this->plugin_file)) {
            activate_plugin($this->plugin_file);
        }

        return $response;
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
     * Force check for updates (clears cache)
     */
    public function force_check() {
        delete_transient($this->cache_key);
        delete_site_transient('update_plugins');
    }
}
