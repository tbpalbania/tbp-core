<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Repeater;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Repeater_List extends Widget_Base {

    public function get_name() {
        return 'tbp-repeater-list';
    }

    public function get_title() {
        return __('Repeater List', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-bullet-list';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['list', 'icon', 'repeater', 'acf', 'dynamic'];
    }

    public function get_style_depends() {
        return ['tbp-repeater-list'];
    }

    public function get_script_depends() {
        return ['tbp-repeater-list'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function register_content_controls() {
        // Data Source Section
        $this->start_controls_section(
            'section_data_source',
            [
                'label' => __('Data Source', 'tbp-core'),
            ]
        );

        $this->add_control(
            'data_source',
            [
                'label' => __('Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'static',
                'options' => [
                    'static' => __('Static (Manual)', 'tbp-core'),
                    'dynamic' => __('Dynamic (ACF Repeater)', 'tbp-core'),
                ],
            ]
        );

        $this->end_controls_section();

        // Static Items Section
        $this->start_controls_section(
            'section_static_items',
            [
                'label' => __('List Items', 'tbp-core'),
                'condition' => [
                    'data_source' => 'static',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'text',
            [
                'label' => __('Title', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => __('List Item', 'tbp-core'),
                'default' => __('List Item', 'tbp-core'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $repeater->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-check',
                    'library' => 'fa-solid',
                ],
                'fa4compatibility' => 'icon',
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label' => __('Link', 'tbp-core'),
                'type' => Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'tbp-core'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'icon_list',
            [
                'label' => __('Items', 'tbp-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'text' => __('List Item #1', 'tbp-core'),
                        'selected_icon' => [
                            'value' => 'fas fa-check',
                            'library' => 'fa-solid',
                        ],
                    ],
                    [
                        'text' => __('List Item #2', 'tbp-core'),
                        'selected_icon' => [
                            'value' => 'fas fa-times',
                            'library' => 'fa-solid',
                        ],
                    ],
                    [
                        'text' => __('List Item #3', 'tbp-core'),
                        'selected_icon' => [
                            'value' => 'fas fa-dot-circle',
                            'library' => 'fa-solid',
                        ],
                    ],
                ],
                'title_field' => '{{{ elementor.helpers.renderIcon( this, selected_icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}} {{{ text }}}',
            ]
        );

        $this->end_controls_section();

        // Dynamic Source Section
        $this->start_controls_section(
            'section_dynamic_source',
            [
                'label' => __('Repeater Source', 'tbp-core'),
                'condition' => [
                    'data_source' => 'dynamic',
                ],
            ]
        );

        $this->add_control(
            'repeater_source',
            [
                'label' => __('Get Repeater From', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current_post',
                'options' => [
                    'current_post' => __('Current Post', 'tbp-core'),
                    'options_page' => __('Options Page', 'tbp-core'),
                ],
            ]
        );

        // Get available ACF repeater fields
        $repeater_fields = $this->get_acf_repeater_fields();

        $this->add_control(
            'acf_repeater_field',
            [
                'label' => __('Repeater Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $repeater_fields,
                'default' => '',
                'description' => __('Select an ACF repeater field', 'tbp-core'),
            ]
        );

        $this->add_control(
            'options_page_name',
            [
                'label' => __('Options Page Name', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => 'options',
                'description' => __('Enter the options page name (e.g., "options", "theme-settings")', 'tbp-core'),
                'condition' => [
                    'repeater_source' => 'options_page',
                ],
            ]
        );

        $this->end_controls_section();

        // Field Mapping Section
        $this->start_controls_section(
            'section_field_mapping',
            [
                'label' => __('Field Mapping', 'tbp-core'),
                'condition' => [
                    'data_source' => 'dynamic',
                ],
            ]
        );

        // Get all sub-fields from all repeaters for selection
        $sub_fields = $this->get_all_repeater_sub_fields();

        $this->add_control(
            'map_title_field',
            [
                'label' => __('Title Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $sub_fields,
                'default' => '',
            ]
        );

        $this->add_control(
            'map_icon_source',
            [
                'label' => __('Icon Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'static',
                'options' => [
                    'static' => __('Static Icon', 'tbp-core'),
                    'dynamic' => __('From Field', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'static_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-check',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'map_icon_source' => 'static',
                ],
            ]
        );

        $this->add_control(
            'map_icon_field',
            [
                'label' => __('Icon Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $sub_fields,
                'default' => '',
                'condition' => [
                    'map_icon_source' => 'dynamic',
                ],
            ]
        );

        $this->add_control(
            'map_link_source',
            [
                'label' => __('Link Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('No Link', 'tbp-core'),
                    'dynamic' => __('From Field', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'map_link_field',
            [
                'label' => __('Link Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $sub_fields,
                'default' => '',
                'condition' => [
                    'map_link_source' => 'dynamic',
                ],
            ]
        );

        $this->add_control(
            'link_new_tab',
            [
                'label' => __('Open in New Tab', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => [
                    'map_link_source' => 'dynamic',
                ],
            ]
        );

        $this->end_controls_section();

        // Before/After Text Section
        $this->start_controls_section(
            'section_before_after',
            [
                'label' => __('Before/After Text', 'tbp-core'),
            ]
        );

        $this->add_control(
            'title_before',
            [
                'label' => __('Title Before', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Text before title', 'tbp-core'),
            ]
        );

        $this->add_control(
            'title_after',
            [
                'label' => __('Title After', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Text after title', 'tbp-core'),
            ]
        );

        $this->add_control(
            'link_before',
            [
                'label' => __('Link Before', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Text before link', 'tbp-core'),
            ]
        );

        $this->add_control(
            'link_after',
            [
                'label' => __('Link After', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Text after link', 'tbp-core'),
            ]
        );

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Layout', 'tbp-core'),
            ]
        );

        $this->add_control(
            'view',
            [
                'label' => __('Layout', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'traditional',
                'options' => [
                    'traditional' => [
                        'title' => __('Default', 'tbp-core'),
                        'icon' => 'eicon-editor-list-ul',
                    ],
                    'inline' => [
                        'title' => __('Inline', 'tbp-core'),
                        'icon' => 'eicon-ellipsis-h',
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_align',
            [
                'label' => __('Icon Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class' => 'elementor%s-align-',
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Left', 'tbp-core'),
                    'right' => __('Right', 'tbp-core'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // List Style Section
        $this->start_controls_section(
            'section_list_style',
            [
                'label' => __('List', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'space_between',
            [
                'label' => __('Space Between', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-items:not(.tbp-inline-items) .tbp-repeater-list-item:not(:last-child)' => 'padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-repeater-list-items:not(.tbp-inline-items) .tbp-repeater-list-item:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-repeater-list-items.tbp-inline-items .tbp-repeater-list-item' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-repeater-list-items.tbp-inline-items' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_align_vertical',
            [
                'label' => __('Vertical Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Top', 'tbp-core'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'flex-end' => [
                        'title' => __('Bottom', 'tbp-core'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-item' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'divider',
            [
                'label' => __('Divider', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'divider_style',
            [
                'label' => __('Style', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'solid' => __('Solid', 'tbp-core'),
                    'double' => __('Double', 'tbp-core'),
                    'dotted' => __('Dotted', 'tbp-core'),
                    'dashed' => __('Dashed', 'tbp-core'),
                ],
                'default' => 'solid',
                'condition' => [
                    'divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-items:not(.tbp-inline-items) .tbp-repeater-list-item:not(:last-child):after' => 'border-top-style: {{VALUE}}',
                    '{{WRAPPER}} .tbp-repeater-list-items.tbp-inline-items .tbp-repeater-list-item:not(:last-child):after' => 'border-left-style: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'divider_weight',
            [
                'label' => __('Weight', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 20,
                    ],
                ],
                'condition' => [
                    'divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-items:not(.tbp-inline-items) .tbp-repeater-list-item:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .tbp-repeater-list-items.tbp-inline-items .tbp-repeater-list-item:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'divider_width',
            [
                'label' => __('Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'condition' => [
                    'divider' => 'yes',
                    'view!' => 'inline',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-item:not(:last-child):after' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'divider_height',
            [
                'label' => __('Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'default' => [
                    'unit' => '%',
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'divider' => 'yes',
                    'view' => 'inline',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-item:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ddd',
                'condition' => [
                    'divider' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-item:not(:last-child):after' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Style Section
        $this->start_controls_section(
            'section_icon_style',
            [
                'label' => __('Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_view',
            [
                'label' => __('View', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'default' => __('Default', 'tbp-core'),
                    'stacked' => __('Stacked', 'tbp-core'),
                    'framed' => __('Framed', 'tbp-core'),
                ],
                'default' => 'default',
            ]
        );

        $this->add_control(
            'icon_shape',
            [
                'label' => __('Shape', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'circle' => __('Circle', 'tbp-core'),
                    'square' => __('Square', 'tbp-core'),
                ],
                'default' => 'circle',
                'condition' => [
                    'icon_view!' => 'default',
                ],
            ]
        );

        $this->add_control(
            'icon_primary_color',
            [
                'label' => __('Primary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-icon' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-icon i, {{WRAPPER}} .tbp-icon-view-default .tbp-repeater-list-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-icon svg, {{WRAPPER}} .tbp-icon-view-default .tbp-repeater-list-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-icon img, {{WRAPPER}} .tbp-icon-view-default .tbp-repeater-list-icon img' => 'filter: drop-shadow(0 0 0 {{VALUE}});',
                ],
            ]
        );

        $this->add_control(
            'icon_secondary_color',
            [
                'label' => __('Secondary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_primary_color_hover',
            [
                'label' => __('Primary Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-item:hover .tbp-repeater-list-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-item:hover .tbp-repeater-list-icon' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-item:hover .tbp-repeater-list-icon i, {{WRAPPER}} .tbp-icon-view-default .tbp-repeater-list-item:hover .tbp-repeater-list-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-item:hover .tbp-repeater-list-icon svg, {{WRAPPER}} .tbp-icon-view-default .tbp-repeater-list-item:hover .tbp-repeater-list-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_secondary_color_hover',
            [
                'label' => __('Secondary Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-repeater-list-item:hover .tbp-repeater-list-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-item:hover .tbp-repeater-list-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-repeater-list-item:hover .tbp-repeater-list-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'separator' => 'before',
                'default' => [
                    'size' => 14,
                ],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-repeater-list-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-repeater-list-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-icon' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'condition' => [
                    'icon_view' => 'framed',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-icon' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'separator' => 'before',
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-left .tbp-repeater-list-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-icon-right .tbp-repeater-list-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Text Style Section
        $this->start_controls_section(
            'section_text_style',
            [
                'label' => __('Text', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-repeater-list-item:hover .tbp-repeater-list-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .tbp-repeater-list-text',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .tbp-repeater-list-text',
            ]
        );

        $this->end_controls_section();

        // Before/After Text Style
        $this->start_controls_section(
            'section_before_after_style',
            [
                'label' => __('Before/After Text', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'before_after_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-text-before, {{WRAPPER}} .tbp-text-after' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'before_after_typography',
                'selector' => '{{WRAPPER}} .tbp-text-before, {{WRAPPER}} .tbp-text-after',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get ACF repeater fields
     */
    private function get_acf_repeater_fields() {
        $fields = ['' => __('Select a field', 'tbp-core')];

        if (!function_exists('acf_get_field_groups')) {
            return $fields;
        }

        $field_groups = acf_get_field_groups();

        foreach ($field_groups as $group) {
            $group_fields = acf_get_fields($group['key']);

            if (!is_array($group_fields)) {
                continue;
            }

            foreach ($group_fields as $field) {
                if ($field['type'] === 'repeater') {
                    $fields[$field['name']] = $group['title'] . ' - ' . $field['label'];
                }
            }
        }

        return $fields;
    }

    /**
     * Get all sub-fields from all repeater fields
     */
    private function get_all_repeater_sub_fields() {
        $sub_fields = ['' => __('-- None --', 'tbp-core')];

        if (!function_exists('acf_get_field_groups')) {
            return $sub_fields;
        }

        $field_groups = acf_get_field_groups();

        foreach ($field_groups as $group) {
            $group_fields = acf_get_fields($group['key']);

            if (!is_array($group_fields)) {
                continue;
            }

            foreach ($group_fields as $field) {
                if ($field['type'] === 'repeater' && !empty($field['sub_fields'])) {
                    foreach ($field['sub_fields'] as $sub_field) {
                        $key = $sub_field['name'];
                        $label = $field['label'] . ' → ' . $sub_field['label'];
                        $sub_fields[$key] = $label;
                    }
                }
            }
        }

        return $sub_fields;
    }

    /**
     * Get repeater data based on source
     */
    private function get_repeater_data($settings) {
        $items = [];

        if ($settings['data_source'] === 'static') {
            return $settings['icon_list'];
        }

        if (!function_exists('get_field')) {
            return $items;
        }

        $field_name = $settings['acf_repeater_field'];

        if (empty($field_name)) {
            return $items;
        }

        if ($settings['repeater_source'] === 'options_page') {
            $option_name = !empty($settings['options_page_name']) ? $settings['options_page_name'] : 'options';
            $repeater_data = get_field($field_name, $option_name);
        } else {
            $repeater_data = get_field($field_name);
        }

        if (empty($repeater_data) || !is_array($repeater_data)) {
            return $items;
        }

        $title_field = $settings['map_title_field'];
        $icon_field = $settings['map_icon_field'];
        $link_field = $settings['map_link_field'];

        foreach ($repeater_data as $row) {
            $item = [
                'text' => isset($row[$title_field]) ? $row[$title_field] : '',
                'selected_icon' => null,
                'link' => null,
            ];

            // Handle icon
            if ($settings['map_icon_source'] === 'static') {
                $item['selected_icon'] = $settings['static_icon'];
            } elseif (!empty($icon_field) && isset($row[$icon_field])) {
                $icon_value = $row[$icon_field];

                // ACF Icon Picker can return different formats
                if (is_array($icon_value)) {
                    if (isset($icon_value['type'])) {
                        if ($icon_value['type'] === 'icon') {
                            // ACF Icon Picker - font icon format
                            $item['acf_icon_class'] = $icon_value['value'];
                        } elseif ($icon_value['type'] === 'media') {
                            // ACF Icon Picker - uploaded SVG/image
                            $media_value = $icon_value['value'];
                            if (is_numeric($media_value)) {
                                // Attachment ID
                                $item['acf_icon_url'] = wp_get_attachment_url($media_value);
                            } elseif (is_array($media_value) && !empty($media_value['url'])) {
                                // Array with url
                                $item['acf_icon_url'] = $media_value['url'];
                            } elseif (is_string($media_value)) {
                                // Direct URL
                                $item['acf_icon_url'] = $media_value;
                            }
                        }
                    } elseif (isset($icon_value['url'])) {
                        // Image array format with url key
                        $item['acf_icon_url'] = $icon_value['url'];
                    } elseif (isset($icon_value['value'])) {
                        // Standard Elementor icon format
                        $item['selected_icon'] = $icon_value;
                    }
                } elseif (is_string($icon_value)) {
                    // Check if it's a URL or a CSS class
                    if (filter_var($icon_value, FILTER_VALIDATE_URL) || strpos($icon_value, '/') !== false) {
                        // It's a URL
                        $item['acf_icon_url'] = $icon_value;
                    } else {
                        // It's a CSS class
                        $item['acf_icon_class'] = $icon_value;
                    }
                } elseif (is_numeric($icon_value)) {
                    // Attachment ID
                    $item['acf_icon_url'] = wp_get_attachment_url($icon_value);
                }
            }

            // Handle link
            if ($settings['map_link_source'] === 'dynamic' && !empty($link_field) && isset($row[$link_field])) {
                $link_value = $row[$link_field];
                $new_tab = !empty($settings['link_new_tab']) && $settings['link_new_tab'] === 'yes';

                if (is_array($link_value)) {
                    // ACF Link field format - use widget setting to override
                    $item['link'] = [
                        'url' => $link_value['url'] ?? '',
                        'is_external' => $new_tab,
                        'nofollow' => false,
                    ];
                } elseif (is_string($link_value)) {
                    // Plain URL string
                    $item['link'] = [
                        'url' => $link_value,
                        'is_external' => $new_tab,
                        'nofollow' => false,
                    ];
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $items = $this->get_repeater_data($settings);

        if (empty($items)) {
            return;
        }

        $this->add_render_attribute('list', 'class', 'tbp-repeater-list-items');

        if ($settings['view'] === 'inline') {
            $this->add_render_attribute('list', 'class', 'tbp-inline-items');
        }

        $icon_position = $settings['icon_position'];
        $icon_view = $settings['icon_view'] ?? 'default';
        $icon_shape = $settings['icon_shape'] ?? 'circle';

        // Build item class
        $item_classes = [
            'tbp-repeater-list-item',
            'tbp-icon-' . $icon_position,
            'tbp-icon-view-' . $icon_view,
        ];

        if ($icon_view !== 'default') {
            $item_classes[] = 'tbp-icon-shape-' . $icon_shape;
        }

        ?>
        <ul <?php echo $this->get_render_attribute_string('list'); ?>>
            <?php foreach ($items as $index => $item) :
                $has_link = !empty($item['link']['url']);
                $link_key = 'link_' . $index;

                if ($has_link) {
                    $this->add_link_attributes($link_key, $item['link']);
                }

                $title_text = '';

                // Before text
                if (!empty($settings['title_before'])) {
                    $title_text .= '<span class="tbp-text-before">' . esc_html($settings['title_before']) . '</span> ';
                }

                $title_text .= esc_html($item['text']);

                // After text
                if (!empty($settings['title_after'])) {
                    $title_text .= ' <span class="tbp-text-after">' . esc_html($settings['title_after']) . '</span>';
                }
                ?>
                <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
                    <?php if ($has_link) : ?>
                        <a <?php echo $this->get_render_attribute_string($link_key); ?>>
                            <?php if (!empty($settings['link_before'])) : ?>
                                <span class="tbp-text-before"><?php echo esc_html($settings['link_before']); ?></span>
                            <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($icon_position === 'left') : ?>
                        <?php $this->render_icon($item, $settings); ?>
                    <?php endif; ?>

                    <span class="tbp-repeater-list-text"><?php echo $title_text; ?></span>

                    <?php if ($icon_position === 'right') : ?>
                        <?php $this->render_icon($item, $settings); ?>
                    <?php endif; ?>

                    <?php if ($has_link) : ?>
                            <?php if (!empty($settings['link_after'])) : ?>
                                <span class="tbp-text-after"><?php echo esc_html($settings['link_after']); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Render icon
     */
    private function render_icon($item, $settings) {
        // Check for ACF uploaded icon/SVG URL first
        if (!empty($item['acf_icon_url'])) {
            echo '<span class="tbp-repeater-list-icon"><img src="' . esc_url($item['acf_icon_url']) . '" alt="" /></span>';
            return;
        }

        // Check for ACF icon class (font icons)
        if (!empty($item['acf_icon_class'])) {
            echo '<span class="tbp-repeater-list-icon"><i class="' . esc_attr($item['acf_icon_class']) . '"></i></span>';
            return;
        }

        // Elementor icon format
        if (!empty($item['selected_icon']['value'])) {
            echo '<span class="tbp-repeater-list-icon">';
            Icons_Manager::render_icon($item['selected_icon'], ['aria-hidden' => 'true']);
            echo '</span>';
        }
    }

    protected function content_template() {
        ?>
        <#
        var iconPosition = settings.icon_position;
        var iconView = settings.icon_view || 'default';
        var iconShape = settings.icon_shape || 'circle';

        function getIconHtml(icon) {
            if (!icon || !icon.value) return '';

            var iconValue = icon.value;
            if (typeof iconValue === 'object' && iconValue.url) {
                return '<img src="' + iconValue.url + '" aria-hidden="true" />';
            }
            return '<i class="' + iconValue + '" aria-hidden="true"></i>';
        }

        // Build item class
        var itemClass = 'tbp-repeater-list-item tbp-icon-' + iconPosition + ' tbp-icon-view-' + iconView;
        if (iconView !== 'default') {
            itemClass += ' tbp-icon-shape-' + iconShape;
        }

        if (settings.data_source === 'static' && settings.icon_list) {
            var listClass = 'tbp-repeater-list-items';
            if (settings.view === 'inline') {
                listClass += ' tbp-inline-items';
            }
            #>
            <ul class="{{ listClass }}">
            <# _.each(settings.icon_list, function(item, index) {
                var hasLink = item.link && item.link.url;
                var titleBefore = settings.title_before ? '<span class="tbp-text-before">' + _.escape(settings.title_before) + '</span> ' : '';
                var titleAfter = settings.title_after ? ' <span class="tbp-text-after">' + _.escape(settings.title_after) + '</span>' : '';
                var iconHtml = getIconHtml(item.selected_icon);
                #>
                <li class="{{ itemClass }}">
                    <# if (hasLink) { #>
                        <a href="{{ item.link.url }}">
                            <# if (settings.link_before) { #>
                                <span class="tbp-text-before">{{{ settings.link_before }}}</span>
                            <# } #>
                    <# } #>

                    <# if (iconPosition === 'left' && iconHtml) { #>
                        <span class="tbp-repeater-list-icon">{{{ iconHtml }}}</span>
                    <# } #>

                    <span class="tbp-repeater-list-text">{{{ titleBefore }}}{{{ item.text }}}{{{ titleAfter }}}</span>

                    <# if (iconPosition === 'right' && iconHtml) { #>
                        <span class="tbp-repeater-list-icon">{{{ iconHtml }}}</span>
                    <# } #>

                    <# if (hasLink) { #>
                            <# if (settings.link_after) { #>
                                <span class="tbp-text-after">{{{ settings.link_after }}}</span>
                            <# } #>
                        </a>
                    <# } #>
                </li>
            <# }); #>
            </ul>
        <# } else if (settings.data_source === 'dynamic') { #>
            <div class="tbp-repeater-list-dynamic-placeholder">
                <p><?php _e('Dynamic content will be loaded from ACF repeater.', 'tbp-core'); ?></p>
            </div>
        <# } #>
        <?php
    }
}
