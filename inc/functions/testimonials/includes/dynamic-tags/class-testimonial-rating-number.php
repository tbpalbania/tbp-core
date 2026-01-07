<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Rating_Number_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-rating-number';
    }

    public function get_title() {
        return __('Testimonial Rating (Number)', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-testimonials';
    }

    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls() {
        $this->add_control(
            'fallback',
            [
                'label'   => __('Fallback', 'tbp-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 0,
                'min'     => 0,
                'max'     => 5,
                'step'    => 0.1,
            ]
        );
    }

    public function render() {
        $settings = $this->get_settings();
        $rating = 0;

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            $rating = (float) get_post_meta($post_id, '_testimonial_rating', true);
        }

        if (!$rating && isset($settings['fallback'])) {
            $rating = (float) $settings['fallback'];
        }

        echo $rating;
    }
}
