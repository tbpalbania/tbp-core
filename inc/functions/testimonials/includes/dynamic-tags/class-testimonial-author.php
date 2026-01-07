<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Author_Tag extends Tag {

    public function get_name() {
        return 'tbp-testimonial-author';
    }

    public function get_title() {
        return __('Testimonial Author', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-testimonials';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'field',
            [
                'label'   => __('Field', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name'     => __('Name', 'tbp-core'),
                    'email'    => __('Email', 'tbp-core'),
                    'company'  => __('Company', 'tbp-core'),
                    'position' => __('Position', 'tbp-core'),
                ],
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
        $field = $settings['field'];
        $value = '';

        // Check if we're in Elementor Loop context with CPT
        $post_id = get_the_ID();
        if ($post_id && get_post_type($post_id) === 'tbp_testimonial') {
            switch ($field) {
                case 'name':
                    $value = get_post_meta($post_id, '_testimonial_author_name', true);
                    break;
                case 'email':
                    $value = get_post_meta($post_id, '_testimonial_author_email', true);
                    break;
                case 'company':
                    $value = get_post_meta($post_id, '_testimonial_author_company', true);
                    break;
                case 'position':
                    $value = get_post_meta($post_id, '_testimonial_author_position', true);
                    break;
            }
        } else {
            // Legacy: custom table
            $testimonial_id = !empty($settings['testimonial_id']) ? absint($settings['testimonial_id']) : $this->get_current_testimonial_id();

            if ($testimonial_id && class_exists('TBP_Testimonials')) {
                $testimonial = \TBP_Testimonials::get_testimonial($testimonial_id);
                if ($testimonial) {
                    switch ($field) {
                        case 'name':
                            $value = $testimonial->author_name;
                            break;
                        case 'email':
                            $value = $testimonial->author_email;
                            break;
                        case 'company':
                            $value = $testimonial->author_company;
                            break;
                        case 'position':
                            $value = $testimonial->author_position;
                            break;
                    }
                }
            }
        }

        echo esc_html($value ?: $settings['fallback']);
    }

    private function get_current_testimonial_id() {
        // Check for loop item context
        $current_id = apply_filters('tbp_current_testimonial_id', 0);
        if ($current_id) {
            return $current_id;
        }

        // Check query var
        if (isset($GLOBALS['tbp_current_testimonial'])) {
            return $GLOBALS['tbp_current_testimonial']->id;
        }

        return 0;
    }
}
