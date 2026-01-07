<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;

if (!defined('ABSPATH')) {
    exit;
}

class Author_Long_Bio extends Tag {

    public function get_name() {
        return 'tbp-author-long-bio';
    }

    public function get_title() {
        return __('Author Long Bio', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-author';
    }

    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'author_source',
            [
                'label' => __('Author Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current' => __('Current Author', 'tbp-core'),
                    'queried' => __('Queried Author (Archive)', 'tbp-core'),
                    'custom' => __('Specific User', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'author_source_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __('<strong>Current Author:</strong> Post author on single posts<br><strong>Queried Author:</strong> Author on author archive pages<br><strong>Specific User:</strong> Choose a specific user', 'tbp-core'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'custom_user',
            [
                'label' => __('Select User', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_users_options(),
                'condition' => [
                    'author_source' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label' => __('Fallback', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Text to display if no long biography is set', 'tbp-core'),
            ]
        );

        $this->add_control(
            'use_short_bio_fallback',
            [
                'label' => __('Fallback to Short Bio', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('If no long bio, use the WordPress short bio instead', 'tbp-core'),
            ]
        );
    }

    private function get_users_options() {
        $users = get_users(['fields' => ['ID', 'display_name']]);
        $options = [];
        foreach ($users as $user) {
            $options[$user->ID] = $user->display_name;
        }
        return $options;
    }

    public function render() {
        $settings = $this->get_settings();
        $author_id = $this->get_author_id($settings);

        if (!$author_id) {
            echo wp_kses_post($settings['fallback']);
            return;
        }

        // Get long biography
        $long_bio = '';
        if (class_exists('TBP_User_Profile_Fields')) {
            $long_bio = \TBP_User_Profile_Fields::get_user_long_biography($author_id, true);
        }

        // Fallback to short bio if enabled and no long bio
        if (empty($long_bio) && $settings['use_short_bio_fallback'] === 'yes') {
            $user = get_userdata($author_id);
            if ($user && !empty($user->description)) {
                $long_bio = wpautop($user->description);
            }
        }

        // Use fallback text if still empty
        if (empty($long_bio)) {
            echo wp_kses_post($settings['fallback']);
            return;
        }

        echo wp_kses_post($long_bio);
    }

    private function get_author_id($settings) {
        $source = $settings['author_source'] ?? 'current';

        switch ($source) {
            case 'queried':
                // For author archive pages
                if (is_author()) {
                    $author = get_queried_object();
                    return $author ? $author->ID : 0;
                }
                // Fall through to current if not on author archive
                // no break intentional

            case 'current':
                // Current post author
                if (is_singular()) {
                    return get_post_field('post_author', get_the_ID());
                }
                // On author archive, get the queried author
                if (is_author()) {
                    $author = get_queried_object();
                    return $author ? $author->ID : 0;
                }
                // Fallback to global post
                global $post;
                return $post ? $post->post_author : 0;

            case 'custom':
                return !empty($settings['custom_user']) ? (int) $settings['custom_user'] : 0;

            default:
                return 0;
        }
    }
}
