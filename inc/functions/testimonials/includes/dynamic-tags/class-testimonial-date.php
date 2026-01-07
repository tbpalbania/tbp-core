<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Date_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-date';
    }

    public function get_title() {
        return __('Testimonial Date', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-testimonials';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'date_format',
            [
                'label'   => __('Date Format', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'  => __('Default (Site Settings)', 'tbp-core'),
                    'relative' => __('Relative (e.g., 2 days ago)', 'tbp-core'),
                    'custom'   => __('Custom', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'custom_format',
            [
                'label'       => __('Custom Format', 'tbp-core'),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'F j, Y',
                'description' => __('PHP date format', 'tbp-core'),
                'condition'   => ['date_format' => 'custom'],
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
        $timestamp = 0;

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            $timestamp = get_post_timestamp($post_id);
        } else {
            // Legacy: custom table
            $testimonial_id = !empty($settings['testimonial_id']) ? absint($settings['testimonial_id']) : $this->get_current_testimonial_id();

            if ($testimonial_id && class_exists('TBP_Testimonials')) {
                $testimonial = \TBP_Testimonials::get_testimonial($testimonial_id);
                if ($testimonial && !empty($testimonial->date_created)) {
                    $timestamp = strtotime($testimonial->date_created);
                }
            }
        }

        if (!$timestamp) {
            echo esc_html($settings['fallback']);
            return;
        }

        $format = $settings['date_format'];

        switch ($format) {
            case 'relative':
                echo esc_html(human_time_diff($timestamp, current_time('timestamp')) . ' ' . __('ago', 'tbp-core'));
                break;

            case 'custom':
                $custom_format = $settings['custom_format'] ?: 'F j, Y';
                echo esc_html(date_i18n($custom_format, $timestamp));
                break;

            default:
                echo esc_html(date_i18n(get_option('date_format'), $timestamp));
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
