<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonial_Form_Widget extends Widget_Base {

    public function get_name() {
        return 'tbp-testimonial-form';
    }

    public function get_title() {
        return __('Testimonial Form', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['testimonial', 'review', 'form', 'feedback'];
    }

    public function get_style_depends() {
        return ['tbp-testimonials'];
    }

    public function get_script_depends() {
        return ['tbp-testimonials'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Form Settings', 'tbp-core'),
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label'       => __('Product ID', 'tbp-core'),
                'type'        => Controls_Manager::NUMBER,
                'description' => __('Associate testimonials with a specific product (optional)', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label'   => __('Show Title', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'title_text',
            [
                'label'     => __('Title', 'tbp-core'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('Share Your Experience', 'tbp-core'),
                'condition' => ['show_title' => 'yes'],
            ]
        );

        $this->end_controls_section();

        // Style Section - Form
        $this->start_controls_section(
            'section_style_form',
            [
                'label' => __('Form', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_max_width',
            [
                'label'      => __('Max Width', 'tbp-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => ['min' => 200, 'max' => 1000],
                    '%'  => ['min' => 50, 'max' => 100],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_padding',
            [
                'label'      => __('Padding', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_background',
            [
                'label'     => __('Background Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_border',
                'selector' => '{{WRAPPER}} .tbp-testimonial-form',
            ]
        );

        $this->add_control(
            'form_border_radius',
            [
                'label'      => __('Border Radius', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'form_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-testimonial-form',
            ]
        );

        $this->end_controls_section();

        // Style Section - Labels
        $this->start_controls_section(
            'section_style_labels',
            [
                'label' => __('Labels', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => __('Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .tbp-testimonial-form__label',
            ]
        );

        $this->end_controls_section();

        // Style Section - Inputs
        $this->start_controls_section(
            'section_style_inputs',
            [
                'label' => __('Inputs', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_background',
            [
                'label'     => __('Background', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__input, {{WRAPPER}} .tbp-testimonial-form__textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => __('Text Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__input, {{WRAPPER}} .tbp-testimonial-form__textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_color',
            [
                'label'     => __('Border Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__input, {{WRAPPER}} .tbp-testimonial-form__textarea' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_focus_border_color',
            [
                'label'     => __('Focus Border Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__input:focus, {{WRAPPER}} .tbp-testimonial-form__textarea:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label'      => __('Padding', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form__input, {{WRAPPER}} .tbp-testimonial-form__textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label'      => __('Border Radius', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form__input, {{WRAPPER}} .tbp-testimonial-form__textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Button
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __('Submit Button', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .tbp-testimonial-form__submit',
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab('button_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'button_text_color',
            [
                'label'     => __('Text Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label'     => __('Background', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('button_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'button_hover_text_color',
            [
                'label'     => __('Text Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label'     => __('Background', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => __('Padding', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'separator'  => 'before',
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label'      => __('Border Radius', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form__submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Stars
        $this->start_controls_section(
            'section_style_stars',
            [
                'label' => __('Star Rating', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'stars_size',
            [
                'label'      => __('Size', 'tbp-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 16, 'max' => 48]],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-form__rating label' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'stars_color',
            [
                'label'     => __('Inactive Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__rating label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'stars_active_color',
            [
                'label'     => __('Active Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-form__rating label:hover, {{WRAPPER}} .tbp-testimonial-form__rating label:hover ~ label, {{WRAPPER}} .tbp-testimonial-form__rating input:checked ~ label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        wp_enqueue_style('tbp-testimonials');
        wp_enqueue_script('tbp-testimonials');

        $atts = [
            'product_id' => !empty($settings['product_id']) ? absint($settings['product_id']) : 0,
        ];

        if ($settings['show_title'] === 'yes' && !empty($settings['title_text'])) {
            echo '<h3 class="tbp-testimonial-form__title">' . esc_html($settings['title_text']) . '</h3>';
        }

        include TBP_TESTIMONIALS_PATH . 'includes/templates/form.php';
    }
}
