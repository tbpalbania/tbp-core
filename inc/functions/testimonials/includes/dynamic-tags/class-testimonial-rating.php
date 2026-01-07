<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Rating_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-rating';
    }

    public function get_title() {
        return __('Testimonial Rating', 'tbp-core');
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
                'default' => 'stars',
                'options' => [
                    'stars'      => __('Stars (HTML)', 'tbp-core'),
                    'number'     => __('Number (e.g., 4)', 'tbp-core'),
                    'fraction'   => __('Fraction (e.g., 4/5)', 'tbp-core'),
                    'percentage' => __('Percentage (e.g., 80%)', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'star_filled',
            [
                'label'     => __('Filled Star', 'tbp-core'),
                'type'      => Controls_Manager::TEXT,
                'default'   => '★',
                'condition' => ['format' => 'stars'],
            ]
        );

        $this->add_control(
            'star_empty',
            [
                'label'     => __('Empty Star', 'tbp-core'),
                'type'      => Controls_Manager::TEXT,
                'default'   => '☆',
                'condition' => ['format' => 'stars'],
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
                'type'    => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
    }

    public function render() {
        $settings = $this->get_settings();
        $rating = 0;

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            $rating = (int) get_post_meta($post_id, '_testimonial_rating', true);
        } else {
            // Legacy: custom table
            $testimonial_id = !empty($settings['testimonial_id']) ? absint($settings['testimonial_id']) : $this->get_current_testimonial_id();

            if ($testimonial_id && class_exists('TBP_Testimonials')) {
                $testimonial = \TBP_Testimonials::get_testimonial($testimonial_id);
                if ($testimonial) {
                    $rating = (int) $testimonial->rating;
                }
            }
        }

        if (!$rating) {
            echo esc_html($settings['fallback']);
            return;
        }

        $format = $settings['format'];

        switch ($format) {
            case 'stars':
                $filled = $settings['star_filled'] ?: '★';
                $empty = $settings['star_empty'] ?: '☆';
                $output = str_repeat($filled, $rating) . str_repeat($empty, 5 - $rating);
                echo '<span class="tbp-testimonial-stars">' . esc_html($output) . '</span>';
                break;

            case 'number':
                echo esc_html($rating);
                break;

            case 'fraction':
                echo esc_html($rating . '/5');
                break;

            case 'percentage':
                echo esc_html(($rating * 20) . '%');
                break;
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
