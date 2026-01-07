<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Staff_Biography_Tag extends Tag {

    public function get_name() {
        return 'tbp-staff-biography';
    }

    public function get_title() {
        return __('Staff Biography', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-staff';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'source',
            [
                'label' => __('Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current'  => __('Current Post', 'tbp-core'),
                    'specific' => __('Specific Staff', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'staff_id',
            [
                'label' => __('Select Staff', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_staff_options(),
                'condition' => ['source' => 'specific'],
            ]
        );

        $this->add_control(
            'format',
            [
                'label' => __('Format', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'full',
                'options' => [
                    'full'    => __('Full (with HTML)', 'tbp-core'),
                    'excerpt' => __('Excerpt (trimmed)', 'tbp-core'),
                    'plain'   => __('Plain Text', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'word_limit',
            [
                'label' => __('Word Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 55,
                'min' => 10,
                'max' => 500,
                'condition' => ['format' => 'excerpt'],
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label' => __('Fallback', 'tbp-core'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => '',
            ]
        );
    }

    private function get_staff_options() {
        $options = [];
        $staff = get_posts([
            'post_type'      => 'staff',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        foreach ($staff as $member) {
            $options[$member->ID] = $member->post_title;
        }

        return $options;
    }

    public function render() {
        $settings = $this->get_settings();
        $source = $settings['source'];
        $format = $settings['format'];
        $fallback = $settings['fallback'];

        // Determine post ID
        $post_id = null;
        if ($source === 'current') {
            $post_id = get_the_ID();
        } elseif ($source === 'specific' && !empty($settings['staff_id'])) {
            $post_id = intval($settings['staff_id']);
        }

        if (!$post_id || get_post_type($post_id) !== 'staff') {
            echo wp_kses_post($fallback);
            return;
        }

        $biography = \TBP_Staff::get_biography($post_id);

        if (empty($biography)) {
            echo wp_kses_post($fallback);
            return;
        }

        switch ($format) {
            case 'full':
                // Apply wpautop for paragraph formatting and allow shortcodes
                echo wp_kses_post(wpautop(do_shortcode($biography)));
                break;
            case 'excerpt':
                $word_limit = intval($settings['word_limit']) ?: 55;
                echo esc_html(wp_trim_words(wp_strip_all_tags($biography), $word_limit, '...'));
                break;
            case 'plain':
                echo esc_html(wp_strip_all_tags($biography));
                break;
        }
    }
}
