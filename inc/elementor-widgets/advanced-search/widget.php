<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Advanced_Search extends Widget_Base {

    public function get_name() {
        return 'tbp-advanced-search';
    }

    public function get_title() {
        return __('Advanced Search', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-search';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['search', 'find', 'ajax', 'multi', 'post type'];
    }

    public function get_style_depends() {
        return ['tbp-advanced-search'];
    }

    public function get_script_depends() {
        return ['tbp-advanced-search'];
    }

    protected function register_controls() {

        // Layout Section
        $this->start_controls_section(
            'section_search_layout',
            [
                'label' => __('Search Layout', 'tbp-core'),
            ]
        );

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'layout-1',
                'options' => [
                    'layout-1' => __('Layout 1 - Default', 'tbp-core'),
                    'layout-2' => __('Layout 2 - Modal', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'search_post_types',
            [
                'label' => __('Post Types', 'tbp-core'),
                'description' => __('Select one or more post types to search. Leave empty to search all.', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $this->get_post_types(),
                'default' => ['post'],
            ]
        );

        $this->add_control(
            'search_taxonomies_enabled',
            [
                'label' => __('Search Taxonomies', 'tbp-core'),
                'description' => __('Also search taxonomy terms (categories, tags, etc.)', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'search_taxonomies',
            [
                'label' => __('Taxonomies', 'tbp-core'),
                'description' => __('Select taxonomies to search.', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'options' => $this->get_taxonomies(),
                'default' => ['category'],
                'condition' => [
                    'search_taxonomies_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'group_post_types',
            [
                'label' => __('Group by Post Type', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'separator' => 'before',
                'condition' => [
                    'show_ajax_search' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'group_taxonomies',
            [
                'label' => __('Group by Taxonomy', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => [
                    'show_ajax_search' => 'yes',
                    'search_taxonomies_enabled' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'placeholder',
            [
                'label' => __('Placeholder', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'separator' => 'before',
                'default' => __('Search', 'tbp-core') . '...',
            ]
        );

        $this->add_control(
            'search_icon',
            [
                'label' => __('Search Icon', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'search_icon_select',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => 'fas fa-search',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'search_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'search_icon_flip',
            [
                'label' => __('Icon Flip', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'condition' => [
                    'search_icon' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'search_align',
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
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'search_width',
            [
                'label' => __('Search Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 150,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search-default' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'layout' => 'layout-1',
                ],
            ]
        );

        $this->add_control(
            'modal_trigger_icon',
            [
                'label' => __('Trigger Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-search',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'layout' => 'layout-2',
                ],
            ]
        );

        $this->add_control(
            'modal_close_icon',
            [
                'label' => __('Close Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-times',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'layout' => 'layout-2',
                ],
            ]
        );

        $this->add_control(
            'enable_keyboard_shortcut',
            [
                'label' => __('Keyboard Shortcut', 'tbp-core'),
                'description' => __('Enable CMD/CTRL + K to open modal', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'layout' => 'layout-2',
                ],
            ]
        );

        $this->add_control(
            'show_ajax_search',
            [
                'label' => __('Ajax Search', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'anchor_target',
            [
                'label' => __('Open in New Tab', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'show_ajax_search' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // AJAX Query Section
        $this->start_controls_section(
            'ajax_search_query',
            [
                'label' => __('Ajax Query', 'tbp-core'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'show_ajax_search' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Item Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 20,
            ]
        );

        $this->add_control(
            'show_thumbnail',
            [
                'label' => __('Show Thumbnail', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'excerpt_lines',
            [
                'label' => __('Excerpt Lines', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 2,
                'min' => 1,
                'max' => 10,
            ]
        );

        $this->add_control(
            'close_on_click_outside',
            [
                'label' => __('Close on Click Outside', 'tbp-core'),
                'description' => __('Close search results when clicking outside', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        // Style - Search Container
        $this->start_controls_section(
            'section_search_layout_style',
            [
                'label' => __('Search Container', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'search_container_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'search_container_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'search_container_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'search_container_shadow',
                'selector' => '{{WRAPPER}} .tbp-search-container .tbp-search',
            ]
        );

        $this->end_controls_section();

        // Style - Search Icon
        $this->start_controls_section(
            'section_search_icon_style',
            [
                'label' => __('Search Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'search_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search .tbp-search-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-search .tbp-search-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .tbp-search .tbp-search-icon i' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'icon_background',
                'label' => __('Icon Background', 'tbp-core'),
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}}.elementor-widget-tbp-advanced-search .tbp-search .tbp-search-icon',
            ]
        );

        $this->add_responsive_control(
            'search_icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search .tbp-search-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-search .tbp-search-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-search .tbp-search-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'search_icon_width',
            [
                'label' => __('Icon Container Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search .tbp-search-icon' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style - Input
        $this->start_controls_section(
            'section_search_style',
            [
                'label' => __('Input', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .tbp-search-input',
            ]
        );

        $this->start_controls_tabs('tabs_input_colors');

        $this->start_controls_tab(
            'tab_input_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_background_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search .tbp-search-input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_placeholder_color',
            [
                'label' => __('Placeholder Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'size' => 3,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input' => 'border-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-search .tbp-search-wrapper' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search .tbp-search-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_shadow',
                'selector' => '{{WRAPPER}} .tbp-search-input',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_input_focus',
            [
                'label' => __('Focus', 'tbp-core'),
            ]
        );

        $this->add_control(
            'input_text_color_focus',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_background_color_focus',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-container .tbp-search .tbp-search-input:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_color_focus',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-input:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_shadow_focus',
                'selector' => '{{WRAPPER}} .tbp-search-input:focus',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Style - Search Results
        $this->start_controls_section(
            'section_results_style',
            [
                'label' => __('Search Results', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_ajax_search' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_size',
            [
                'label' => __('Thumbnail Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 40,
                        'max' => 150,
                    ],
                ],
                'default' => [
                    'size' => 60,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-item-thumb' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_thumbnail' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_radius',
            [
                'label' => __('Thumbnail Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-item-thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_thumbnail' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'result_title_color',
            [
                'label' => __('Title Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'result_title_typography',
                'selector' => '{{WRAPPER}} .tbp-search-title',
            ]
        );

        $this->add_control(
            'result_excerpt_color',
            [
                'label' => __('Excerpt Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'result_excerpt_typography',
                'selector' => '{{WRAPPER}} .tbp-search-text',
            ]
        );

        $this->end_controls_section();

        // Style - Modal Trigger (Layout 2)
        $this->start_controls_section(
            'section_modal_trigger_style',
            [
                'label' => __('Modal Trigger', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'layout' => 'layout-2',
                ],
            ]
        );

        $this->add_control(
            'trigger_icon_color',
            [
                'label' => __('Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-search-modal-trigger svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'trigger_icon_color_hover',
            [
                'label' => __('Icon Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-search-modal-trigger:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'trigger_icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 24,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-search-modal-trigger svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'trigger_background',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'trigger_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'trigger_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'trigger_border',
                'selector' => '{{WRAPPER}} .tbp-search-modal-trigger',
            ]
        );

        $this->end_controls_section();

        // Style - Modal (Layout 2)
        $this->start_controls_section(
            'section_modal_style',
            [
                'label' => __('Modal', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'layout' => 'layout-2',
                ],
            ]
        );

        $this->add_control(
            'modal_overlay_color',
            [
                'label' => __('Overlay Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0,0,0,0.5)',
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-overlay' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'modal_close_color',
            [
                'label' => __('Close Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-close' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-search-modal-close svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'modal_close_size',
            [
                'label' => __('Close Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'size' => 30,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-search-modal-close' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-search-modal-close svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $layout = $settings['layout'] ?? 'layout-1';
        ?>
        <div class="tbp-search-container tbp-search-<?php echo esc_attr($layout); ?>">
            <?php if ($layout === 'layout-2') : ?>
                <?php $this->render_modal_search($settings); ?>
            <?php else : ?>
                <?php $this->render_search_form($settings); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function render_modal_search($settings) {
        $widget_id = $this->get_id();
        $enable_shortcut = !empty($settings['enable_keyboard_shortcut']) && $settings['enable_keyboard_shortcut'] === 'yes';
        ?>
        <button type="button" class="tbp-search-modal-trigger" data-modal-id="tbp-modal-<?php echo esc_attr($widget_id); ?>">
            <?php Icons_Manager::render_icon($settings['modal_trigger_icon'], ['aria-hidden' => 'true']); ?>
        </button>

        <div id="tbp-modal-<?php echo esc_attr($widget_id); ?>"
             class="tbp-search-modal"
             style="display:none;"
             data-keyboard-shortcut="<?php echo $enable_shortcut ? 'yes' : 'no'; ?>">
            <div class="tbp-search-modal-overlay"></div>
            <div class="tbp-search-modal-content">
                <button type="button" class="tbp-search-modal-close">
                    <?php Icons_Manager::render_icon($settings['modal_close_icon'], ['aria-hidden' => 'true']); ?>
                </button>
                <div class="tbp-search-modal-inner">
                    <?php $this->render_search_form($settings, true); ?>
                </div>
            </div>
        </div>
        <?php
    }

    protected function render_search_form($settings, $is_modal = false) {
        $form_class = $is_modal ? 'tbp-search tbp-search-modal-form' : 'tbp-search tbp-search-default';

        $this->add_render_attribute('search', [
            'class' => $form_class,
            'role' => 'search',
            'method' => 'get',
            'action' => esc_url(home_url('/')),
        ]);

        $this->add_render_attribute('input', [
            'placeholder' => $settings['placeholder'],
            'class' => 'tbp-search-input',
            'type' => 'search',
            'name' => 's',
            'title' => __('Search', 'tbp-core'),
            'value' => get_search_query(),
        ]);

        if ($settings['show_ajax_search']) {
            $post_types = !empty($settings['search_post_types']) ? $settings['search_post_types'] : ['post'];
            $search_taxonomies = !empty($settings['search_taxonomies_enabled']) && $settings['search_taxonomies_enabled'] === 'yes';
            $taxonomies = $search_taxonomies && !empty($settings['search_taxonomies']) ? $settings['search_taxonomies'] : [];

            $this->add_render_attribute('search', [
                'class' => 'tbp-ajax-search',
                'data-anchor-target' => $settings['anchor_target'] ? 'yes' : 'no',
                'autocomplete' => 'off',
                'data-settings' => wp_json_encode([
                    'post_types' => $post_types,
                    'per_page' => $settings['posts_per_page'] ?? 5,
                    'show_thumbnail' => !empty($settings['show_thumbnail']) && $settings['show_thumbnail'] === 'yes',
                    'excerpt_lines' => $settings['excerpt_lines'] ?? 2,
                    'search_taxonomies' => $search_taxonomies,
                    'taxonomies' => $taxonomies,
                    'group_post_types' => !empty($settings['group_post_types']) && $settings['group_post_types'] === 'yes',
                    'group_taxonomies' => !empty($settings['group_taxonomies']) && $settings['group_taxonomies'] === 'yes',
                    'close_on_click_outside' => !empty($settings['close_on_click_outside']) && $settings['close_on_click_outside'] === 'yes',
                ]),
            ]);
        }

        ?>
        <form <?php $this->print_render_attribute_string('search'); ?>>
            <div class="tbp-search-wrapper">
                <?php if ($settings['search_icon'] === 'yes' && !$is_modal) : ?>
                    <span class="tbp-search-icon <?php echo $settings['search_icon_flip'] === 'yes' ? 'tbp-icon-flip' : ''; ?>">
                        <?php Icons_Manager::render_icon($settings['search_icon_select'], ['aria-hidden' => 'true']); ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($settings['search_post_types'])) : ?>
                    <?php foreach ($settings['search_post_types'] as $post_type) : ?>
                        <input name="post_type[]" type="hidden" value="<?php echo esc_attr($post_type); ?>">
                    <?php endforeach; ?>
                <?php endif; ?>

                <input <?php $this->print_render_attribute_string('input'); ?>>

                <?php if ($settings['show_ajax_search']) : ?>
                    <div class="tbp-search-result" style="display:none;"></div>
                <?php endif; ?>
            </div>
        </form>
        <?php

        // Clear render attributes for next form if needed
        $this->remove_render_attribute('search');
        $this->remove_render_attribute('input');
    }

    private function get_post_types() {
        $post_types = get_post_types(['public' => true], 'objects');
        $post_types = wp_list_pluck($post_types, 'label', 'name');

        $ignore_post_types = [
            'elementor_library' => '',
            'attachment' => '',
        ];

        $post_types = array_diff_key($post_types, $ignore_post_types);

        return $post_types;
    }

    private function get_taxonomies() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomies = wp_list_pluck($taxonomies, 'label', 'name');

        $ignore_taxonomies = [
            'post_format' => '',
            'elementor_library_type' => '',
        ];

        $taxonomies = array_diff_key($taxonomies, $ignore_taxonomies);

        return $taxonomies;
    }
}
