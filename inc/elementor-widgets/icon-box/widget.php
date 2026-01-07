<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class Icon_Box extends Widget_Base {

    public function get_name() {
        return 'tbp-icon-box';
    }

    /**
     * Resolve global color to actual color value
     */
    private function get_color_value($color_key) {
        $settings = $this->get_settings_for_display();

        // Check if global color is set
        $globals = $this->get_settings('__globals__');
        if (!empty($globals[$color_key])) {
            // Global color format: globals/colors?id=primary
            $global_value = $globals[$color_key];
            preg_match('/id=(\w+)/', $global_value, $matches);
            if (!empty($matches[1])) {
                $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
                $system_colors = $kit->get_settings_for_display('system_colors');
                $custom_colors = $kit->get_settings_for_display('custom_colors');

                // Check system colors
                if (is_array($system_colors)) {
                    foreach ($system_colors as $color) {
                        if ($color['_id'] === $matches[1]) {
                            return $color['color'];
                        }
                    }
                }

                // Check custom colors
                if (is_array($custom_colors)) {
                    foreach ($custom_colors as $color) {
                        if ($color['_id'] === $matches[1]) {
                            return $color['color'];
                        }
                    }
                }
            }
        }

        // Return direct color value
        return $settings[$color_key] ?? '';
    }

    public function get_title() {
        return __('Icon Box', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-icon-box';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['icon box', 'icon', 'box', 'gradient'];
    }

    public function get_style_depends() {
        return ['tbp-icon-box'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_icon',
            [
                'label' => __('Icon Box', 'tbp-core'),
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'view',
            [
                'label' => __('View', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'default' => __('Default', 'tbp-core'),
                    'stacked' => __('Stacked', 'tbp-core'),
                    'framed' => __('Framed', 'tbp-core'),
                ],
                'default' => 'default',
                'prefix_class' => 'tbp-icon-box-view-',
            ]
        );

        $this->add_control(
            'shape',
            [
                'label' => __('Shape', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'square' => __('Square', 'tbp-core'),
                    'rounded' => __('Rounded', 'tbp-core'),
                    'circle' => __('Circle', 'tbp-core'),
                ],
                'default' => 'circle',
                'condition' => [
                    'view!' => 'default',
                ],
                'prefix_class' => 'tbp-icon-box-shape-',
            ]
        );

        $this->add_control(
            'title_text',
            [
                'label' => __('Title', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => __('This is the heading', 'tbp-core'),
                'placeholder' => __('Enter your title', 'tbp-core'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'description_text',
            [
                'label' => __('Description', 'tbp-core'),
                'type' => Controls_Manager::TEXTAREA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'tbp-core'),
                'placeholder' => __('Enter your description', 'tbp-core'),
                'rows' => 10,
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __('Link', 'tbp-core'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'title_size',
            [
                'label' => __('Title HTML Tag', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h3',
            ]
        );

        $this->end_controls_section();

        // Box Style Section
        $this->start_controls_section(
            'section_style_box',
            [
                'label' => __('Box', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'position',
            [
                'label' => __('Icon Position', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'top',
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'top' => [
                        'title' => __('Top', 'tbp-core'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'right' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class' => 'tbp-icon-box-position-',
            ]
        );

        $this->add_responsive_control(
            'content_vertical_alignment',
            [
                'label' => __('Vertical Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => __('Top', 'tbp-core'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'middle' => [
                        'title' => __('Middle', 'tbp-core'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'bottom' => [
                        'title' => __('Bottom', 'tbp-core'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'top',
                'selectors_dictionary' => [
                    'top' => 'flex-start',
                    'middle' => 'center',
                    'bottom' => 'flex-end',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-wrapper' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'position' => ['left', 'right'],
                ],
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_space',
            [
                'label' => __('Icon Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'size' => 15,
                ],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.tbp-icon-box-position-top .tbp-icon-box-icon' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.tbp-icon-box-position-left .tbp-icon-box-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.tbp-icon-box-position-right .tbp-icon-box-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_bottom_space',
            [
                'label' => __('Title Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Style Section
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('icon_colors');

        // Normal Tab
        $this->start_controls_tab(
            'icon_colors_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-box-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_bg_type',
            [
                'label' => __('Background Type', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'solid' => [
                        'title' => __('Solid', 'tbp-core'),
                        'icon' => 'eicon-paint-brush',
                    ],
                    'gradient' => [
                        'title' => __('Gradient', 'tbp-core'),
                        'icon' => 'eicon-barcode',
                    ],
                ],
                'default' => 'solid',
                'toggle' => false,
                'condition' => [
                    'view' => ['stacked', 'framed'],
                ],
            ]
        );

        $this->add_control(
            'icon_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'icon_gradient_color_1',
            [
                'label' => __('Gradient Color 1', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'icon_gradient_color_2',
            [
                'label' => __('Gradient Color 2', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#8b5cf6',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'icon_gradient_type',
            [
                'label' => __('Gradient Type', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'linear' => __('Linear', 'tbp-core'),
                    'radial' => __('Radial', 'tbp-core'),
                ],
                'default' => 'linear',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'icon_gradient_angle',
            [
                'label' => __('Gradient Angle', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 360,
                    ],
                ],
                'default' => [
                    'size' => 135,
                ],
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'gradient',
                    'icon_gradient_type' => 'linear',
                ],
            ]
        );

        $this->add_control(
            'icon_gradient_position',
            [
                'label' => __('Gradient Position', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'center center' => __('Center Center', 'tbp-core'),
                    'center left' => __('Center Left', 'tbp-core'),
                    'center right' => __('Center Right', 'tbp-core'),
                    'top center' => __('Top Center', 'tbp-core'),
                    'top left' => __('Top Left', 'tbp-core'),
                    'top right' => __('Top Right', 'tbp-core'),
                    'bottom center' => __('Bottom Center', 'tbp-core'),
                    'bottom left' => __('Bottom Left', 'tbp-core'),
                    'bottom right' => __('Bottom Right', 'tbp-core'),
                ],
                'default' => 'center center',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'icon_bg_type' => 'gradient',
                    'icon_gradient_type' => 'radial',
                ],
            ]
        );

        $this->add_control(
            'icon_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'view' => 'framed',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'icon_colors_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'hover_icon_color',
            [
                'label' => __('Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .tbp-icon-box-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}}:hover .tbp-icon-box-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_bg_type',
            [
                'label' => __('Background Type', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'solid' => [
                        'title' => __('Solid', 'tbp-core'),
                        'icon' => 'eicon-paint-brush',
                    ],
                    'gradient' => [
                        'title' => __('Gradient', 'tbp-core'),
                        'icon' => 'eicon-barcode',
                    ],
                ],
                'default' => 'solid',
                'toggle' => false,
                'condition' => [
                    'view' => ['stacked', 'framed'],
                ],
            ]
        );

        $this->add_control(
            'hover_icon_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .tbp-icon-box-icon' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_gradient_color_1',
            [
                'label' => __('Gradient Color 1', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#8b5cf6',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_gradient_color_2',
            [
                'label' => __('Gradient Color 2', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_gradient_type',
            [
                'label' => __('Gradient Type', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'linear' => __('Linear', 'tbp-core'),
                    'radial' => __('Radial', 'tbp-core'),
                ],
                'default' => 'linear',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'gradient',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_gradient_angle',
            [
                'label' => __('Gradient Angle', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 360,
                    ],
                ],
                'default' => [
                    'size' => 135,
                ],
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'gradient',
                    'hover_icon_gradient_type' => 'linear',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_gradient_position',
            [
                'label' => __('Gradient Position', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'center center' => __('Center Center', 'tbp-core'),
                    'center left' => __('Center Left', 'tbp-core'),
                    'center right' => __('Center Right', 'tbp-core'),
                    'top center' => __('Top Center', 'tbp-core'),
                    'top left' => __('Top Left', 'tbp-core'),
                    'top right' => __('Top Right', 'tbp-core'),
                    'bottom center' => __('Bottom Center', 'tbp-core'),
                    'bottom left' => __('Bottom Left', 'tbp-core'),
                    'bottom right' => __('Bottom Right', 'tbp-core'),
                ],
                'default' => 'center center',
                'condition' => [
                    'view' => ['stacked', 'framed'],
                    'hover_icon_bg_type' => 'gradient',
                    'hover_icon_gradient_type' => 'radial',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .tbp-icon-box-icon' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'view' => 'framed',
                ],
            ]
        );

        $this->add_control(
            'hover_icon_border_color_stacked',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .tbp-icon-box-icon' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'view' => 'stacked',
                    'icon_border_style!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Hover Animation', 'tbp-core'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->add_control(
            'hover_transition_duration',
            [
                'label' => __('Transition Duration', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms'],
                'default' => [
                    'unit' => 's',
                    'size' => 0.3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-icon-box-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'icon_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'padding: {{SIZE}}{{UNIT}};',
                ],
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'view!' => 'default',
                ],
            ]
        );

        $this->add_responsive_control(
            'rotate',
            [
                'label' => __('Rotate', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['deg', 'grad', 'rad', 'turn'],
                'default' => [
                    'unit' => 'deg',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon i, {{WRAPPER}} .tbp-icon-box-icon svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->add_control(
            'icon_border_heading',
            [
                'label' => __('Border', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'view!' => 'default',
                ],
            ]
        );

        $this->add_control(
            'icon_border_style',
            [
                'label' => __('Border Style', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'solid' => __('Solid', 'tbp-core'),
                    'double' => __('Double', 'tbp-core'),
                    'dotted' => __('Dotted', 'tbp-core'),
                    'dashed' => __('Dashed', 'tbp-core'),
                    'groove' => __('Groove', 'tbp-core'),
                ],
                'default' => 'none',
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-style: {{VALUE}};',
                ],
                'condition' => [
                    'view' => 'stacked',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_border_width_stacked',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'view' => 'stacked',
                    'icon_border_style!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'icon_border_color_stacked',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'view' => 'stacked',
                    'icon_border_style!' => 'none',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'view' => 'framed',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'view!' => 'default',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Style Section
        $this->start_controls_section(
            'section_style_content',
            [
                'label' => __('Content', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_title',
            [
                'label' => __('Title', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .tbp-icon-box-title, {{WRAPPER}} .tbp-icon-box-title a',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-title, {{WRAPPER}} .tbp-icon-box-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}:hover .tbp-icon-box-title, {{WRAPPER}}:hover .tbp-icon-box-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_description',
            [
                'label' => __('Description', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .tbp-icon-box-description',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-box-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $has_link = !empty($settings['link']['url']);
        $html_tag = $has_link ? 'a' : 'span';

        $has_icon = !empty($settings['selected_icon']['value']);
        $has_content = !empty($settings['title_text']) || !empty($settings['description_text']);

        if (!$has_icon && !$has_content) {
            return;
        }

        $this->add_render_attribute('wrapper', 'class', 'tbp-icon-box-wrapper');

        if ($has_link) {
            $this->add_link_attributes('link', $settings['link']);
        }

        $this->add_render_attribute('icon', 'class', 'tbp-icon-box-icon');

        if (!empty($settings['hover_animation'])) {
            $this->add_render_attribute('icon', 'class', 'elementor-animation-' . $settings['hover_animation']);
        }

        // Build inline styles for icon background
        $icon_styles = [];
        $icon_hover_styles = [];

        if (in_array($settings['view'], ['stacked', 'framed'])) {
            if ($settings['icon_bg_type'] === 'gradient') {
                $color1 = $this->get_color_value('icon_gradient_color_1') ?: '#6366f1';
                $color2 = $this->get_color_value('icon_gradient_color_2') ?: '#8b5cf6';

                if ($settings['icon_gradient_type'] === 'linear') {
                    $angle = isset($settings['icon_gradient_angle']['size']) ? $settings['icon_gradient_angle']['size'] : 135;
                    $icon_styles[] = "background: linear-gradient({$angle}deg, {$color1} 0%, {$color2} 100%)";
                } else {
                    $position = $settings['icon_gradient_position'] ?: 'center center';
                    $icon_styles[] = "background: radial-gradient(at {$position}, {$color1} 0%, {$color2} 100%)";
                }
            } else {
                $bg_color = $this->get_color_value('icon_bg_color') ?: '#6366f1';
                $icon_styles[] = "background-color: {$bg_color}";
            }

            // Hover styles
            if ($settings['hover_icon_bg_type'] === 'gradient') {
                $hover_color1 = $this->get_color_value('hover_icon_gradient_color_1') ?: '#8b5cf6';
                $hover_color2 = $this->get_color_value('hover_icon_gradient_color_2') ?: '#6366f1';

                if ($settings['hover_icon_gradient_type'] === 'linear') {
                    $hover_angle = isset($settings['hover_icon_gradient_angle']['size']) ? $settings['hover_icon_gradient_angle']['size'] : 135;
                    $icon_hover_styles[] = "background: linear-gradient({$hover_angle}deg, {$hover_color1} 0%, {$hover_color2} 100%)";
                } else {
                    $hover_position = $settings['hover_icon_gradient_position'] ?: 'center center';
                    $icon_hover_styles[] = "background: radial-gradient(at {$hover_position}, {$hover_color1} 0%, {$hover_color2} 100%)";
                }
            } elseif (!empty($this->get_color_value('hover_icon_bg_color'))) {
                $hover_bg = $this->get_color_value('hover_icon_bg_color');
                $icon_hover_styles[] = "background-color: {$hover_bg}";
            }
        }

        if (!empty($icon_styles)) {
            $this->add_render_attribute('icon', 'style', implode('; ', $icon_styles));
        }

        // Add hover styles via CSS variable for JS
        if (!empty($icon_hover_styles)) {
            $this->add_render_attribute('icon', 'data-hover-bg', implode('; ', $icon_hover_styles));
        }

        $title_tag = Utils::validate_html_tag($settings['title_size']);

        // Output hover CSS if needed
        if (!empty($icon_hover_styles)) {
            $unique_id = $this->get_id();
            ?>
            <style>
                .elementor-element-<?php echo esc_attr($unique_id); ?>:hover .tbp-icon-box-icon {
                    <?php echo esc_html(implode('; ', $icon_hover_styles)); ?> !important;
                }
            </style>
            <?php
        }
        ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <?php if ($has_icon) : ?>
            <div class="tbp-icon-box-icon-wrapper">
                <<?php echo esc_html($html_tag); ?> <?php $this->print_render_attribute_string('link'); ?> <?php $this->print_render_attribute_string('icon'); ?>>
                    <?php Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']); ?>
                </<?php echo esc_html($html_tag); ?>>
            </div>
            <?php endif; ?>

            <?php if ($has_content) : ?>
            <div class="tbp-icon-box-content">
                <?php if (!empty($settings['title_text'])) : ?>
                <<?php echo esc_html($title_tag); ?> class="tbp-icon-box-title">
                    <?php if ($has_link) : ?>
                    <a <?php $this->print_render_attribute_string('link'); ?>>
                        <?php echo wp_kses_post($settings['title_text']); ?>
                    </a>
                    <?php else : ?>
                        <?php echo wp_kses_post($settings['title_text']); ?>
                    <?php endif; ?>
                </<?php echo esc_html($title_tag); ?>>
                <?php endif; ?>

                <?php if (!empty($settings['description_text'])) : ?>
                <p class="tbp-icon-box-description">
                    <?php echo wp_kses_post($settings['description_text']); ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var hasIcon = settings.selected_icon?.value;
        var hasContent = settings.title_text || settings.description_text;

        if (!hasIcon && !hasContent) {
            return;
        }

        var hasLink = settings.link?.url;
        var htmlTag = hasLink ? 'a' : 'span';
        var titleTag = elementor.helpers.validateHTMLTag(settings.title_size);
        var linkAttr = hasLink ? 'href="' + elementor.helpers.sanitizeUrl(settings.link?.url) + '"' : '';

        view.addRenderAttribute('icon', 'class', 'tbp-icon-box-icon');

        if ('' !== settings.hover_animation) {
            view.addRenderAttribute('icon', 'class', 'elementor-animation-' + settings.hover_animation);
        }

        // Helper function to get color value (handles global colors)
        function getColorValue(colorKey) {
            var globals = settings.__globals__ || {};
            if (globals[colorKey]) {
                // Global color format: globals/colors?id=primary
                var match = globals[colorKey].match(/id=(\w+)/);
                if (match && match[1]) {
                    var globalId = match[1];
                    var kit = elementor.config.kit;
                    if (kit) {
                        var systemColors = kit.settings?.system_colors || [];
                        var customColors = kit.settings?.custom_colors || [];

                        for (var i = 0; i < systemColors.length; i++) {
                            if (systemColors[i]._id === globalId) {
                                return systemColors[i].color;
                            }
                        }
                        for (var i = 0; i < customColors.length; i++) {
                            if (customColors[i]._id === globalId) {
                                return customColors[i].color;
                            }
                        }
                    }
                }
            }
            return settings[colorKey] || '';
        }

        // Build icon background styles
        var iconStyles = [];

        if (settings.view === 'stacked' || settings.view === 'framed') {
            if (settings.icon_bg_type === 'gradient') {
                var color1 = getColorValue('icon_gradient_color_1') || '#6366f1';
                var color2 = getColorValue('icon_gradient_color_2') || '#8b5cf6';

                if (settings.icon_gradient_type === 'linear') {
                    var angle = settings.icon_gradient_angle?.size || 135;
                    iconStyles.push('background: linear-gradient(' + angle + 'deg, ' + color1 + ' 0%, ' + color2 + ' 100%)');
                } else {
                    var position = settings.icon_gradient_position || 'center center';
                    iconStyles.push('background: radial-gradient(at ' + position + ', ' + color1 + ' 0%, ' + color2 + ' 100%)');
                }
            } else {
                var bgColor = getColorValue('icon_bg_color') || '#6366f1';
                iconStyles.push('background-color: ' + bgColor);
            }
        }

        if (iconStyles.length > 0) {
            view.addRenderAttribute('icon', 'style', iconStyles.join('; '));
        }

        var iconHTML = hasIcon ? elementor.helpers.renderIcon(view, settings.selected_icon, { 'aria-hidden': true }, 'i', 'object') : '';
        #>
        <div class="tbp-icon-box-wrapper">
            <# if (hasIcon) { #>
            <div class="tbp-icon-box-icon-wrapper">
                <{{{ htmlTag }}} {{{ linkAttr }}} {{{ view.getRenderAttributeString('icon') }}}>
                    <# if (iconHTML && iconHTML.rendered) { #>
                        {{{ iconHTML.value }}}
                    <# } #>
                </{{{ htmlTag }}}>
            </div>
            <# } #>

            <# if (hasContent) { #>
            <div class="tbp-icon-box-content">
                <# if (settings.title_text) { #>
                <{{{ titleTag }}} class="tbp-icon-box-title">
                    <# if (hasLink) { #>
                    <a {{{ linkAttr }}}>
                        {{{ settings.title_text }}}
                    </a>
                    <# } else { #>
                        {{{ settings.title_text }}}
                    <# } #>
                </{{{ titleTag }}}>
                <# } #>

                <# if (settings.description_text) { #>
                <p class="tbp-icon-box-description">{{{ settings.description_text }}}</p>
                <# } #>
            </div>
            <# } #>
        </div>
        <?php
    }
}
