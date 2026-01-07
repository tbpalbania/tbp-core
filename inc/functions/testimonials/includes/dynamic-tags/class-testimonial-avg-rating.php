<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Avg_Rating_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-avg-rating';
    }

    public function get_title() {
        return __('Testimonial Average Rating', 'tbp-core');
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
            'decimals',
            [
                'label'   => __('Decimal Places', 'tbp-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 1,
                'min'     => 0,
                'max'     => 2,
            ]
        );

        $this->add_control(
            'min_rating',
            [
                'label'       => __('Minimum Rating Filter', 'tbp-core'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 0,
                'min'         => 0,
                'max'         => 5,
                'description' => __('Only include testimonials with this rating or higher', 'tbp-core'),
            ]
        );

        $this->add_control(
            'featured_only',
            [
                'label'        => __('Featured Only', 'tbp-core'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'tbp-core'),
                'label_off'    => __('No', 'tbp-core'),
                'return_value' => 'yes',
                'default'      => '',
            ]
        );

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
        $decimals = isset($settings['decimals']) ? (int) $settings['decimals'] : 1;
        $min_rating = isset($settings['min_rating']) ? (int) $settings['min_rating'] : 0;
        $featured_only = $settings['featured_only'] === 'yes';
        $fallback = isset($settings['fallback']) ? (float) $settings['fallback'] : 0;

        // Build query args
        $query_args = [
            'post_type'      => 'tbp_testimonial',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ];

        $meta_query = [];

        if ($min_rating > 0) {
            $meta_query[] = [
                'key'     => '_testimonial_rating',
                'value'   => $min_rating,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        }

        if ($featured_only) {
            $meta_query[] = [
                'key'   => '_testimonial_featured',
                'value' => '1',
            ];
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        $query = new \WP_Query($query_args);
        $post_ids = $query->posts;

        if (empty($post_ids)) {
            echo $fallback;
            return;
        }

        // Calculate average
        $total = 0;
        $count = 0;

        foreach ($post_ids as $post_id) {
            $rating = (float) get_post_meta($post_id, '_testimonial_rating', true);
            if ($rating > 0) {
                $total += $rating;
                $count++;
            }
        }

        if ($count === 0) {
            echo $fallback;
            return;
        }

        $average = $total / $count;
        echo number_format($average, $decimals, '.', '');
    }
}
