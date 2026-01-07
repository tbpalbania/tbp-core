<?php
namespace TBP_Core\Settings;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Tab_Base;
use Elementor\Group_Control_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class Button_Settings extends Tab_Base {

    public function get_id() {
        return 'tbp-buttons';
    }

    public function get_title() {
        return __('TBP Buttons', 'tbp-core');
    }

    public function get_group() {
        return 'theme-style';
    }

    public function get_icon() {
        return 'eicon-button';
    }

    protected function register_tab_controls() {
        // Size Defaults Section
        $this->start_controls_section(
            'section_button_sizes',
            [
                'label' => __('Button Sizes', 'tbp-core'),
                'tab' => $this->get_id(),
            ]
        );

        $this->add_control(
            'button_sizes_description',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __('Define default dimensions for each button size. These will be inherited by all TBP Button widgets.', 'tbp-core'),
                'content_classes' => 'elementor-descriptor',
            ]
        );

        // Small Size
        $this->add_control(
            'button_size_small_heading',
            [
                'label' => __('Small', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_small_typography',
                'label' => __('Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-button-size-small',
                'fields_options' => [
                    'font_size' => ['default' => ['size' => 13, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                    'line_height' => ['default' => ['size' => 1.2, 'unit' => 'em']],
                ],
            ]
        );

        $this->add_responsive_control(
            'button_small_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '8',
                    'right' => '16',
                    'bottom' => '8',
                    'left' => '16',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-small' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_small_min_height',
            [
                'label' => __('Min Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 20, 'max' => 100],
                ],
                'default' => ['size' => 32, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-small' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_small_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '6',
                    'right' => '6',
                    'bottom' => '6',
                    'left' => '6',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-small' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_small_icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 50],
                ],
                'default' => ['size' => 14, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-small .tbp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-button-size-small .tbp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_small_icon_gap',
            [
                'label' => __('Icon Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 30],
                ],
                'default' => ['size' => 6, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-small .tbp-button-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Base Size
        $this->add_control(
            'button_size_base_heading',
            [
                'label' => __('Base', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_base_typography',
                'label' => __('Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-button-size-base',
                'fields_options' => [
                    'font_size' => ['default' => ['size' => 15, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                    'line_height' => ['default' => ['size' => 1.2, 'unit' => 'em']],
                ],
            ]
        );

        $this->add_responsive_control(
            'button_base_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '12',
                    'right' => '24',
                    'bottom' => '12',
                    'left' => '24',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-base' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_base_min_height',
            [
                'label' => __('Min Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 20, 'max' => 100],
                ],
                'default' => ['size' => 44, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-base' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_base_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '8',
                    'right' => '8',
                    'bottom' => '8',
                    'left' => '8',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-base' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_base_icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 50],
                ],
                'default' => ['size' => 18, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-base .tbp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-button-size-base .tbp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_base_icon_gap',
            [
                'label' => __('Icon Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 30],
                ],
                'default' => ['size' => 8, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-base .tbp-button-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Large Size
        $this->add_control(
            'button_size_large_heading',
            [
                'label' => __('Large', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_large_typography',
                'label' => __('Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-button-size-large',
                'fields_options' => [
                    'font_size' => ['default' => ['size' => 17, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                    'line_height' => ['default' => ['size' => 1.2, 'unit' => 'em']],
                ],
            ]
        );

        $this->add_responsive_control(
            'button_large_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => '16',
                    'right' => '32',
                    'bottom' => '16',
                    'left' => '32',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-large' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_large_min_height',
            [
                'label' => __('Min Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 20, 'max' => 100],
                ],
                'default' => ['size' => 52, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-large' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_large_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '10',
                    'right' => '10',
                    'bottom' => '10',
                    'left' => '10',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-large' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_large_icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 50],
                ],
                'default' => ['size' => 22, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-large .tbp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-button-size-large .tbp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_large_icon_gap',
            [
                'label' => __('Icon Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 30],
                ],
                'default' => ['size' => 10, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-size-large .tbp-button-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Variant Defaults Section
        $this->start_controls_section(
            'section_button_variants',
            [
                'label' => __('Button Variants', 'tbp-core'),
                'tab' => $this->get_id(),
            ]
        );

        $this->add_control(
            'button_variants_description',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __('Define default colors for each button variant. These will be inherited by all TBP Button widgets.', 'tbp-core'),
                'content_classes' => 'elementor-descriptor',
            ]
        );

        // Filled Variant
        $this->add_control(
            'button_variant_filled_heading',
            [
                'label' => __('Filled', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_filled_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-filled' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_filled_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-filled' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-filled svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_filled_hover_bg_color',
            [
                'label' => __('Hover Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4f46e5',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-filled:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_filled_hover_text_color',
            [
                'label' => __('Hover Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-filled:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-filled:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        // Outlined Variant
        $this->add_control(
            'button_variant_outlined_heading',
            [
                'label' => __('Outlined', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_outlined_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_outlined_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-outlined svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_outlined_border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 1, 'max' => 10],
                ],
                'default' => ['size' => 2, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_outlined_hover_bg_color',
            [
                'label' => __('Hover Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_outlined_hover_text_color',
            [
                'label' => __('Hover Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-outlined:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_outlined_hover_border_color',
            [
                'label' => __('Hover Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-outlined:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        // Ghost Variant
        $this->add_control(
            'button_variant_ghost_heading',
            [
                'label' => __('Ghost', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_ghost_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-ghost' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-ghost svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_ghost_hover_bg_color',
            [
                'label' => __('Hover Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(99, 102, 241, 0.1)',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-ghost:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_ghost_hover_text_color',
            [
                'label' => __('Hover Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4f46e5',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-variant-ghost:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button-variant-ghost:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
}
