<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Content_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-content';
    }

    public function get_title() {
        return __('Testimonial Content', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-testimonials';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'format',
            [
                'label'   => __('Format', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'full',
                'options' => [
                    'full'    => __('Full', 'tbp-core'),
                    'excerpt' => __('Excerpt', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'word_limit',
            [
                'label'     => __('Word Limit', 'tbp-core'),
                'type'      => Controls_Manager::NUMBER,
                'default'   => 30,
                'min'       => 5,
                'max'       => 200,
                'condition' => ['format' => 'excerpt'],
            ]
        );

        $this->add_control(
            'testimonial_id',
            [
                'label'       => __('Testimonial ID', 'tbp-core'),
                'type'        => Controls_Manager::NUMBER,
                'description' => __('Leave empty to use current loop item', 'tbp-core'),
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label'   => __('Fallback', 'tbp-core'),
                'type'    => Controls_Manager::TEXTAREA,
                'default' => '',
            ]
        );
    }

    public function render() {
        $settings = $this->get_settings();
        $content = '';

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            $post = get_post($post_id);
            $content = $post ? $post->post_content : '';
        } else {
            // Legacy: custom table
            $testimonial_id = !empty($settings['testimonial_id']) ? absint($settings['testimonial_id']) : $this->get_current_testimonial_id();

            if ($testimonial_id && class_exists('TBP_Testimonials')) {
                $testimonial = \TBP_Testimonials::get_testimonial($testimonial_id);
                if ($testimonial) {
                    $content = $testimonial->content;
                }
            }
        }

        if (empty($content)) {
            echo wp_kses_post($settings['fallback']);
            return;
        }

        if ($settings['format'] === 'excerpt') {
            $word_limit = intval($settings['word_limit']) ?: 30;
            echo esc_html(wp_trim_words($content, $word_limit, '...'));
        } else {
            echo wp_kses_post(wpautop($content));
        }
    }

    private function get_current_testimonial_id() {
        $current_id = apply_filters('tbp_current_testimonial_id', 0);
        if ($current_id) {
            return $current_id;
        }

        if (isset($GLOBALS['tbp_current_testimonial'])) {
            return $GLOBALS['tbp_current_testimonial']->id;
        }

        return 0;
    }
}
