<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Company_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-company';
    }

    public function get_title() {
        return __('Testimonial Company/Position', 'tbp-core');
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
                'default' => 'both',
                'options' => [
                    'both'     => __('Position at Company', 'tbp-core'),
                    'company'  => __('Company Only', 'tbp-core'),
                    'position' => __('Position Only', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'separator',
            [
                'label'     => __('Separator', 'tbp-core'),
                'type'      => Controls_Manager::TEXT,
                'default'   => ' at ',
                'condition' => ['format' => 'both'],
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
        $format = $settings['format'];
        $separator = $settings['separator'] ?: ' at ';
        $company = '';
        $position = '';

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            $company = get_post_meta($post_id, '_testimonial_author_company', true);
            $position = get_post_meta($post_id, '_testimonial_author_position', true);
        } else {
            // Legacy: custom table
            $testimonial_id = !empty($settings['testimonial_id']) ? absint($settings['testimonial_id']) : $this->get_current_testimonial_id();

            if ($testimonial_id && class_exists('TBP_Testimonials')) {
                $testimonial = \TBP_Testimonials::get_testimonial($testimonial_id);
                if ($testimonial) {
                    $company = $testimonial->author_company;
                    $position = $testimonial->author_position;
                }
            }
        }

        $output = '';

        switch ($format) {
            case 'both':
                if (!empty($position) && !empty($company)) {
                    $output = $position . $separator . $company;
                } elseif (!empty($position)) {
                    $output = $position;
                } elseif (!empty($company)) {
                    $output = $company;
                }
                break;

            case 'company':
                $output = $company;
                break;

            case 'position':
                $output = $position;
                break;
        }

        echo esc_html($output ?: $settings['fallback']);
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
