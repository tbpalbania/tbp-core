<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Slinky_Menu extends Widget_Base {

    public function get_name() {
        return 'tbp-slinky-menu';
    }

    public function get_title() {
        return __('Slinky Menu', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-nav-menu';
    }

    public function get_categories() {
        return ['theme-elements'];
    }

    public function get_keywords() {
        return ['menu', 'navigation', 'slinky', 'mobile', 'slide', 'nested', 'tabs'];
    }

    public function get_style_depends() {
        return ['tbp-slinky-menu'];
    }

    public function get_script_depends() {
        return ['tbp-slinky-menu'];
    }

    protected function register_controls() {
        // Tabs Section
        $this->start_controls_section(
            'section_tabs',
            [
                'label' => __('Menu Tabs', 'tbp-core'),
            ]
        );

        $this->add_control(
            'enable_tabs',
            [
                'label' => __('Enable Tabs', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Enable to show multiple menus in tabs', 'tbp-core'),
            ]
        );

        $menus = $this->get_available_menus();

        // Single menu (when tabs disabled)
        if (!empty($menus)) {
            $this->add_control(
                'menu_id',
                [
                    'label' => __('Select Menu', 'tbp-core'),
                    'type' => Controls_Manager::SELECT,
                    'options' => $menus,
                    'default' => array_key_first($menus),
                    'condition' => ['enable_tabs' => ''],
                ]
            );
        } else {
            $this->add_control(
                'menu_notice',
                [
                    'type' => Controls_Manager::RAW_HTML,
                    'raw' => sprintf(
                        '<strong>%s</strong><br>%s <a href="%s" target="_blank">%s</a>',
                        __('No menus found.', 'tbp-core'),
                        __('Go to', 'tbp-core'),
                        admin_url('nav-menus.php'),
                        __('Appearance → Menus', 'tbp-core')
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                ]
            );
        }

        // Tabs repeater
        $repeater = new Repeater();

        $repeater->add_control(
            'tab_label',
            [
                'label' => __('Tab Label', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Menu', 'tbp-core'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_icon',
            [
                'label' => __('Tab Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [],
            ]
        );

        if (!empty($menus)) {
            $repeater->add_control(
                'tab_menu_id',
                [
                    'label' => __('Select Menu', 'tbp-core'),
                    'type' => Controls_Manager::SELECT,
                    'options' => $menus,
                    'default' => array_key_first($menus),
                ]
            );
        }

        // Display Conditions
        $repeater->add_control(
            'display_conditions_heading',
            [
                'label' => __('Display Conditions', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'condition_type',
            [
                'label' => __('Show Tab', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'always',
                'options' => [
                    'always' => __('Always', 'tbp-core'),
                    'logged_in' => __('When Logged In', 'tbp-core'),
                    'logged_out' => __('When Logged Out', 'tbp-core'),
                    'user_role' => __('By User Role', 'tbp-core'),
                    'user_capability' => __('By User Capability', 'tbp-core'),
                ],
            ]
        );

        $repeater->add_control(
            'condition_roles',
            [
                'label' => __('User Roles', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_user_roles(),
                'default' => [],
                'condition' => ['condition_type' => 'user_role'],
                'description' => __('Show tab only for these roles', 'tbp-core'),
            ]
        );

        $repeater->add_control(
            'condition_capabilities',
            [
                'label' => __('User Capabilities', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_user_capabilities(),
                'default' => [],
                'condition' => ['condition_type' => 'user_capability'],
                'description' => __('Show tab only for users with these capabilities', 'tbp-core'),
            ]
        );

        // Additional conditions
        $repeater->add_control(
            'additional_conditions_heading',
            [
                'label' => __('Additional Conditions', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $repeater->add_control(
            'condition_page_type',
            [
                'label' => __('Page Type', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Any', 'tbp-core'),
                    'front_page' => __('Front Page', 'tbp-core'),
                    'single' => __('Single Post/Page', 'tbp-core'),
                    'archive' => __('Archive', 'tbp-core'),
                    'search' => __('Search Results', 'tbp-core'),
                    '404' => __('404 Page', 'tbp-core'),
                ],
            ]
        );

        $repeater->add_control(
            'condition_device',
            [
                'label' => __('Device', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Any', 'tbp-core'),
                    'mobile' => __('Mobile Only', 'tbp-core'),
                    'tablet' => __('Tablet Only', 'tbp-core'),
                    'desktop' => __('Desktop Only', 'tbp-core'),
                ],
                'description' => __('Device detection uses CSS media queries', 'tbp-core'),
            ]
        );

        $repeater->add_control(
            'condition_date_range',
            [
                'label' => __('Date Range', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $repeater->add_control(
            'condition_date_from',
            [
                'label' => __('From Date', 'tbp-core'),
                'type' => Controls_Manager::DATE_TIME,
                'condition' => ['condition_date_range' => 'yes'],
            ]
        );

        $repeater->add_control(
            'condition_date_to',
            [
                'label' => __('To Date', 'tbp-core'),
                'type' => Controls_Manager::DATE_TIME,
                'condition' => ['condition_date_range' => 'yes'],
            ]
        );

        $this->add_control(
            'tabs',
            [
                'label' => __('Tabs', 'tbp-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tab_label' => __('Main Menu', 'tbp-core'),
                        'condition_type' => 'always',
                    ],
                    [
                        'tab_label' => __('Account', 'tbp-core'),
                        'condition_type' => 'logged_in',
                    ],
                ],
                'title_field' => '{{{ tab_label }}}',
                'condition' => ['enable_tabs' => 'yes'],
            ]
        );

        $this->end_controls_section();

        // Header Section
        $this->start_controls_section(
            'section_header',
            [
                'label' => __('Header', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_header',
            [
                'label' => __('Show Menu Header', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'header_text',
            [
                'label' => __('Header Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Menu', 'tbp-core'),
                'condition' => [
                    'show_header' => 'yes',
                    'enable_tabs' => '',
                ],
            ]
        );

        $this->add_control(
            'header_text_tabs',
            [
                'label' => __('Header Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Navigation', 'tbp-core'),
                'condition' => [
                    'show_header' => 'yes',
                    'enable_tabs' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Behavior Section
        $this->start_controls_section(
            'section_behavior',
            [
                'label' => __('Behavior', 'tbp-core'),
            ]
        );

        $this->add_control(
            'animation_speed',
            [
                'label' => __('Animation Speed', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 800,
                        'step' => 50,
                    ],
                ],
                'default' => [
                    'size' => 300,
                ],
            ]
        );

        $this->add_control(
            'show_back_button',
            [
                'label' => __('Show Back Button', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'back_text',
            [
                'label' => __('Back Button Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Back', 'tbp-core'),
                'condition' => ['show_back_button' => 'yes'],
            ]
        );

        $this->add_control(
            'show_parent_link',
            [
                'label' => __('Show Parent as Link', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => __('Parent items with children can be clicked to navigate', 'tbp-core'),
            ]
        );

        $this->end_controls_section();

        // Icons Section
        $this->start_controls_section(
            'section_icons',
            [
                'label' => __('Icons', 'tbp-core'),
            ]
        );

        $this->add_control(
            'back_icon',
            [
                'label' => __('Back Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-chevron-left',
                    'library' => 'fa-solid',
                ],
                'condition' => ['show_back_button' => 'yes'],
            ]
        );

        $this->add_control(
            'next_icon',
            [
                'label' => __('Next Level Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-chevron-right',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Container
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __('Container', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'container_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .tbp-slinky',
            ]
        );

        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_shadow',
                'selector' => '{{WRAPPER}} .tbp-slinky',
            ]
        );

        $this->add_responsive_control(
            'container_max_height',
            [
                'label' => __('Max Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => ['min' => 100, 'max' => 1000],
                    'vh' => ['min' => 10, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky' => 'max-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Tabs
        $this->start_controls_section(
            'section_style_tabs',
            [
                'label' => __('Tabs', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['enable_tabs' => 'yes'],
            ]
        );

        $this->add_control(
            'tabs_layout',
            [
                'label' => __('Layout', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'fill',
                'options' => [
                    'fill' => __('Fill Container', 'tbp-core'),
                    'auto' => __('Auto Width', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'tabs_alignment',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tabs' => 'justify-content: {{VALUE}};',
                ],
                'condition' => ['tabs_layout' => 'auto'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tabs_typography',
                'selector' => '{{WRAPPER}} .tbp-slinky__tab',
            ]
        );

        $this->add_responsive_control(
            'tabs_gap',
            [
                'label' => __('Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 20],
                ],
                'default' => ['size' => 0, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tabs' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_icon_spacing',
            [
                'label' => __('Icon Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 20],
                ],
                'default' => ['size' => 6, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_style_tabs');

        $this->start_controls_tab(
            'tabs_normal',
            ['label' => __('Normal', 'tbp-core')]
        );

        $this->add_control(
            'tabs_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_hover',
            ['label' => __('Hover', 'tbp-core')]
        );

        $this->add_control(
            'tabs_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_background_hover',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tabs_active',
            ['label' => __('Active', 'tbp-core')]
        );

        $this->add_control(
            'tabs_color_active',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab--active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_background_active',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab--active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabs_indicator_color',
            [
                'label' => __('Indicator Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab--active::after' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'tabs_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Header
        $this->start_controls_section(
            'section_style_header',
            [
                'label' => __('Header', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_header' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'header_typography',
                'selector' => '{{WRAPPER}} .tbp-slinky__header-title',
            ]
        );

        $this->add_control(
            'header_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__header-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__header' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'header_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'header_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__header' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Menu Items
        $this->start_controls_section(
            'section_style_items',
            [
                'label' => __('Menu Items', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'item_typography',
                'selector' => '{{WRAPPER}} .tbp-slinky__item-link, {{WRAPPER}} .tbp-slinky__item-text',
            ]
        );

        $this->start_controls_tabs('item_tabs');

        $this->start_controls_tab(
            'item_normal',
            ['label' => __('Normal', 'tbp-core')]
        );

        $this->add_control(
            'item_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item-link, {{WRAPPER}} .tbp-slinky__item-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'item_hover',
            ['label' => __('Hover', 'tbp-core')]
        );

        $this->add_control(
            'item_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item:hover .tbp-slinky__item-link, {{WRAPPER}} .tbp-slinky__item:hover .tbp-slinky__item-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_hover',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'item_active',
            ['label' => __('Active', 'tbp-core')]
        );

        $this->add_control(
            'item_color_active',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item--current .tbp-slinky__item-link, {{WRAPPER}} .tbp-slinky__item--current .tbp-slinky__item-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_background_active',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item--current' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'item_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__item' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Back Button
        $this->start_controls_section(
            'section_style_back',
            [
                'label' => __('Back Button', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_back_button' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'back_typography',
                'selector' => '{{WRAPPER}} .tbp-slinky__back',
            ]
        );

        $this->add_control(
            'back_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'back_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'back_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'back_background_hover',
            [
                'label' => __('Hover Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'back_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Icons
        $this->start_controls_section(
            'section_style_icons',
            [
                'label' => __('Icons', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 32],
                ],
                'default' => ['size' => 14, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__next-icon, {{WRAPPER}} .tbp-slinky__back-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-slinky__next-icon svg, {{WRAPPER}} .tbp-slinky__back-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'next_icon_color',
            [
                'label' => __('Next Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__next-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-slinky__next-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'back_icon_color',
            [
                'label' => __('Back Icon Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-slinky__back-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-slinky__back-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_available_menus() {
        $menus = wp_get_nav_menus();
        $options = [];

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }

    private function get_user_roles() {
        global $wp_roles;
        $roles = [];

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($wp_roles->roles as $key => $role) {
            $roles[$key] = $role['name'];
        }

        return $roles;
    }

    private function get_user_capabilities() {
        global $wp_roles;
        $capabilities = [];

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        foreach ($wp_roles->roles as $role) {
            foreach ($role['capabilities'] as $cap => $granted) {
                if ($granted && !isset($capabilities[$cap])) {
                    $capabilities[$cap] = ucwords(str_replace('_', ' ', $cap));
                }
            }
        }

        ksort($capabilities);
        return $capabilities;
    }

    private function check_tab_condition($tab) {
        // Check primary condition
        $condition_type = $tab['condition_type'] ?? 'always';

        switch ($condition_type) {
            case 'logged_in':
                if (!is_user_logged_in()) {
                    return false;
                }
                break;

            case 'logged_out':
                if (is_user_logged_in()) {
                    return false;
                }
                break;

            case 'user_role':
                if (!is_user_logged_in()) {
                    return false;
                }
                $user = wp_get_current_user();
                $roles = $tab['condition_roles'] ?? [];
                if (!empty($roles) && !array_intersect($user->roles, $roles)) {
                    return false;
                }
                break;

            case 'user_capability':
                if (!is_user_logged_in()) {
                    return false;
                }
                $capabilities = $tab['condition_capabilities'] ?? [];
                if (!empty($capabilities)) {
                    $has_cap = false;
                    foreach ($capabilities as $cap) {
                        if (current_user_can($cap)) {
                            $has_cap = true;
                            break;
                        }
                    }
                    if (!$has_cap) {
                        return false;
                    }
                }
                break;
        }

        // Check page type condition
        $page_type = $tab['condition_page_type'] ?? '';
        if (!empty($page_type)) {
            switch ($page_type) {
                case 'front_page':
                    if (!is_front_page()) return false;
                    break;
                case 'single':
                    if (!is_singular()) return false;
                    break;
                case 'archive':
                    if (!is_archive()) return false;
                    break;
                case 'search':
                    if (!is_search()) return false;
                    break;
                case '404':
                    if (!is_404()) return false;
                    break;
            }
        }

        // Check date range
        $date_range = $tab['condition_date_range'] ?? '';
        if ($date_range === 'yes') {
            $now = current_time('timestamp');
            $from = $tab['condition_date_from'] ?? '';
            $to = $tab['condition_date_to'] ?? '';

            if (!empty($from)) {
                $from_ts = strtotime($from);
                if ($now < $from_ts) {
                    return false;
                }
            }

            if (!empty($to)) {
                $to_ts = strtotime($to);
                if ($now > $to_ts) {
                    return false;
                }
            }
        }

        return true;
    }

    private function get_device_class($device) {
        switch ($device) {
            case 'mobile':
                return 'tbp-slinky__tab--mobile-only';
            case 'tablet':
                return 'tbp-slinky__tab--tablet-only';
            case 'desktop':
                return 'tbp-slinky__tab--desktop-only';
            default:
                return '';
        }
    }

    private function build_menu_tree($menu_id) {
        $menu_items = wp_get_nav_menu_items($menu_id);

        if (!$menu_items) {
            return [];
        }

        // Build tree structure
        $menu_tree = [];
        $refs = [];

        foreach ($menu_items as $item) {
            $item_data = [
                'id' => $item->ID,
                'title' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'classes' => implode(' ', $item->classes),
                'current' => in_array('current-menu-item', $item->classes),
                'current_parent' => in_array('current-menu-parent', $item->classes),
                'current_ancestor' => in_array('current-menu-ancestor', $item->classes),
                'children' => [],
            ];

            $refs[$item->ID] = $item_data;

            if ($item->menu_item_parent == 0) {
                $menu_tree[] = &$refs[$item->ID];
            } else {
                if (isset($refs[$item->menu_item_parent])) {
                    $refs[$item->menu_item_parent]['children'][] = &$refs[$item->ID];
                }
            }
        }

        return $menu_tree;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $enable_tabs = !empty($settings['enable_tabs']) && $settings['enable_tabs'] === 'yes';

        if ($enable_tabs) {
            $this->render_tabbed_menu($settings);
        } else {
            $this->render_single_menu($settings);
        }
    }

    private function render_single_menu($settings) {
        if (empty($settings['menu_id'])) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-slinky tbp-slinky--placeholder">';
                echo '<p style="text-align:center;color:#6b7280;padding:20px;">' . esc_html__('Please select a menu from the widget settings.', 'tbp-core') . '</p>';
                echo '</div>';
            }
            return;
        }

        $menu_tree = $this->build_menu_tree($settings['menu_id']);

        if (empty($menu_tree)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-slinky tbp-slinky--placeholder">';
                echo '<p style="text-align:center;color:#6b7280;padding:20px;">' . esc_html__('The selected menu has no items.', 'tbp-core') . '</p>';
                echo '</div>';
            }
            return;
        }

        $show_header = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $animation_speed = !empty($settings['animation_speed']['size']) ? $settings['animation_speed']['size'] : 300;
        $show_parent_link = !empty($settings['show_parent_link']) && $settings['show_parent_link'] === 'yes';

        ?>
        <nav class="tbp-slinky"
             role="navigation"
             aria-label="<?php esc_attr_e('Mobile Navigation', 'tbp-core'); ?>"
             data-speed="<?php echo esc_attr($animation_speed); ?>"
             data-show-parent-link="<?php echo $show_parent_link ? 'true' : 'false'; ?>">

            <?php if ($show_header) : ?>
                <div class="tbp-slinky__header">
                    <span class="tbp-slinky__header-title"><?php echo esc_html($settings['header_text'] ?? __('Menu', 'tbp-core')); ?></span>
                </div>
            <?php endif; ?>

            <div class="tbp-slinky__wrapper">
                <ul class="tbp-slinky__list tbp-slinky__list--root" role="menubar">
                    <?php $this->render_menu_items($menu_tree, $settings, 0); ?>
                </ul>
            </div>
        </nav>
        <?php
    }

    private function render_tabbed_menu($settings) {
        $tabs = $settings['tabs'] ?? [];

        // Filter tabs based on conditions
        $visible_tabs = [];
        foreach ($tabs as $index => $tab) {
            // In editor, show all tabs
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $visible_tabs[] = $tab;
            } elseif ($this->check_tab_condition($tab)) {
                $visible_tabs[] = $tab;
            }
        }

        if (empty($visible_tabs)) {
            return;
        }

        $show_header = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $animation_speed = !empty($settings['animation_speed']['size']) ? $settings['animation_speed']['size'] : 300;
        $show_parent_link = !empty($settings['show_parent_link']) && $settings['show_parent_link'] === 'yes';
        $tabs_layout = $settings['tabs_layout'] ?? 'fill';

        ?>
        <nav class="tbp-slinky tbp-slinky--tabbed tbp-slinky--tabs-<?php echo esc_attr($tabs_layout); ?>"
             role="navigation"
             aria-label="<?php esc_attr_e('Mobile Navigation', 'tbp-core'); ?>"
             data-speed="<?php echo esc_attr($animation_speed); ?>"
             data-show-parent-link="<?php echo $show_parent_link ? 'true' : 'false'; ?>">

            <?php if ($show_header) : ?>
                <div class="tbp-slinky__header">
                    <span class="tbp-slinky__header-title"><?php echo esc_html($settings['header_text_tabs'] ?? __('Navigation', 'tbp-core')); ?></span>
                </div>
            <?php endif; ?>

            <?php if (count($visible_tabs) > 1) : ?>
                <div class="tbp-slinky__tabs" role="tablist">
                    <?php foreach ($visible_tabs as $index => $tab) :
                        $is_active = $index === 0;
                        $device_class = $this->get_device_class($tab['condition_device'] ?? '');
                        $tab_classes = ['tbp-slinky__tab'];
                        if ($is_active) $tab_classes[] = 'tbp-slinky__tab--active';
                        if ($device_class) $tab_classes[] = $device_class;
                        ?>
                        <button type="button"
                                class="<?php echo esc_attr(implode(' ', $tab_classes)); ?>"
                                role="tab"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                aria-controls="tbp-slinky-panel-<?php echo esc_attr($this->get_id() . '-' . $index); ?>"
                                id="tbp-slinky-tab-<?php echo esc_attr($this->get_id() . '-' . $index); ?>">
                            <?php if (!empty($tab['tab_icon']['value'])) : ?>
                                <span class="tbp-slinky__tab-icon">
                                    <?php Icons_Manager::render_icon($tab['tab_icon'], ['aria-hidden' => 'true']); ?>
                                </span>
                            <?php endif; ?>
                            <span class="tbp-slinky__tab-label"><?php echo esc_html($tab['tab_label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="tbp-slinky__panels">
                <?php foreach ($visible_tabs as $index => $tab) :
                    $is_active = $index === 0;
                    $menu_id = $tab['tab_menu_id'] ?? '';
                    $menu_tree = $menu_id ? $this->build_menu_tree($menu_id) : [];
                    $device_class = $this->get_device_class($tab['condition_device'] ?? '');
                    $panel_classes = ['tbp-slinky__panel'];
                    if ($is_active) $panel_classes[] = 'tbp-slinky__panel--active';
                    if ($device_class) $panel_classes[] = $device_class;
                    ?>
                    <div class="<?php echo esc_attr(implode(' ', $panel_classes)); ?>"
                         role="tabpanel"
                         id="tbp-slinky-panel-<?php echo esc_attr($this->get_id() . '-' . $index); ?>"
                         aria-labelledby="tbp-slinky-tab-<?php echo esc_attr($this->get_id() . '-' . $index); ?>"
                         <?php echo !$is_active ? 'hidden' : ''; ?>>

                        <div class="tbp-slinky__wrapper">
                            <?php if (!empty($menu_tree)) : ?>
                                <ul class="tbp-slinky__list tbp-slinky__list--root" role="menubar">
                                    <?php $this->render_menu_items($menu_tree, $settings, 0); ?>
                                </ul>
                            <?php else : ?>
                                <p class="tbp-slinky__empty"><?php esc_html_e('No menu items found.', 'tbp-core'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </nav>
        <?php
    }

    private function render_menu_items($items, $settings, $depth = 0) {
        $show_back = !empty($settings['show_back_button']) && $settings['show_back_button'] === 'yes';
        $show_parent_link = !empty($settings['show_parent_link']) && $settings['show_parent_link'] === 'yes';

        foreach ($items as $item) {
            $has_children = !empty($item['children']);
            $is_current = $item['current'] || $item['current_parent'] || $item['current_ancestor'];

            $item_classes = ['tbp-slinky__item'];
            if ($has_children) {
                $item_classes[] = 'tbp-slinky__item--has-children';
            }
            if ($is_current) {
                $item_classes[] = 'tbp-slinky__item--current';
            }
            if (!empty($item['classes'])) {
                $item_classes[] = $item['classes'];
            }

            ?>
            <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" role="none">
                <div class="tbp-slinky__item-inner">
                    <?php if ($has_children && !$show_parent_link) : ?>
                        <span class="tbp-slinky__item-text" role="menuitem" tabindex="0">
                            <?php echo esc_html($item['title']); ?>
                        </span>
                        <button type="button"
                                class="tbp-slinky__next"
                                aria-expanded="false"
                                aria-label="<?php echo esc_attr(sprintf(__('Open %s submenu', 'tbp-core'), $item['title'])); ?>">
                            <span class="tbp-slinky__next-icon">
                                <?php Icons_Manager::render_icon($settings['next_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        </button>
                    <?php elseif ($has_children && $show_parent_link) : ?>
                        <a href="<?php echo esc_url($item['url']); ?>"
                           class="tbp-slinky__item-link"
                           role="menuitem"
                           <?php echo !empty($item['target']) ? 'target="' . esc_attr($item['target']) . '"' : ''; ?>>
                            <?php echo esc_html($item['title']); ?>
                        </a>
                        <button type="button"
                                class="tbp-slinky__next"
                                aria-expanded="false"
                                aria-label="<?php echo esc_attr(sprintf(__('Open %s submenu', 'tbp-core'), $item['title'])); ?>">
                            <span class="tbp-slinky__next-icon">
                                <?php Icons_Manager::render_icon($settings['next_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        </button>
                    <?php else : ?>
                        <a href="<?php echo esc_url($item['url']); ?>"
                           class="tbp-slinky__item-link"
                           role="menuitem"
                           <?php echo !empty($item['target']) ? 'target="' . esc_attr($item['target']) . '"' : ''; ?>>
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($has_children) : ?>
                    <ul class="tbp-slinky__list tbp-slinky__list--sub" role="menu" aria-label="<?php echo esc_attr($item['title']); ?>">
                        <?php if ($show_back) : ?>
                            <li class="tbp-slinky__item tbp-slinky__item--back" role="none">
                                <button type="button" class="tbp-slinky__back" role="menuitem">
                                    <span class="tbp-slinky__back-icon">
                                        <?php Icons_Manager::render_icon($settings['back_icon'], ['aria-hidden' => 'true']); ?>
                                    </span>
                                    <span class="tbp-slinky__back-text"><?php echo esc_html($settings['back_text'] ?? __('Back', 'tbp-core')); ?></span>
                                </button>
                            </li>
                        <?php endif; ?>
                        <?php $this->render_menu_items($item['children'], $settings, $depth + 1); ?>
                    </ul>
                <?php endif; ?>
            </li>
            <?php
        }
    }

    protected function content_template() {
        ?>
        <nav class="tbp-slinky<# if (settings.enable_tabs === 'yes') { #> tbp-slinky--tabbed tbp-slinky--tabs-{{ settings.tabs_layout || 'fill' }}<# } #>" role="navigation">
            <# if (settings.show_header === 'yes') { #>
            <div class="tbp-slinky__header">
                <span class="tbp-slinky__header-title">
                    <# if (settings.enable_tabs === 'yes') { #>
                        {{ settings.header_text_tabs || '<?php esc_html_e('Navigation', 'tbp-core'); ?>' }}
                    <# } else { #>
                        {{ settings.header_text || '<?php esc_html_e('Menu', 'tbp-core'); ?>' }}
                    <# } #>
                </span>
            </div>
            <# } #>

            <# if (settings.enable_tabs === 'yes' && settings.tabs && settings.tabs.length > 1) { #>
            <div class="tbp-slinky__tabs" role="tablist">
                <# _.each(settings.tabs, function(tab, index) { #>
                <button type="button"
                        class="tbp-slinky__tab<# if (index === 0) { #> tbp-slinky__tab--active<# } #>"
                        role="tab"
                        aria-selected="<# if (index === 0) { #>true<# } else { #>false<# } #>">
                    <# if (tab.tab_icon && tab.tab_icon.value) { #>
                    <span class="tbp-slinky__tab-icon">
                        <i class="{{ tab.tab_icon.value }}"></i>
                    </span>
                    <# } #>
                    <span class="tbp-slinky__tab-label">{{ tab.tab_label }}</span>
                </button>
                <# }); #>
            </div>
            <# } #>

            <div class="<# if (settings.enable_tabs === 'yes') { #>tbp-slinky__panels<# } else { #>tbp-slinky__wrapper<# } #>">
                <# if (settings.enable_tabs === 'yes') { #>
                    <# _.each(settings.tabs, function(tab, index) { #>
                    <div class="tbp-slinky__panel<# if (index === 0) { #> tbp-slinky__panel--active<# } #>" role="tabpanel">
                        <div class="tbp-slinky__wrapper">
                            <ul class="tbp-slinky__list tbp-slinky__list--root" role="menubar">
                                <# for (var i = 0; i < 3; i++) { #>
                                <li class="tbp-slinky__item" role="none">
                                    <div class="tbp-slinky__item-inner">
                                        <a href="#" class="tbp-slinky__item-link" role="menuitem">
                                            {{ tab.tab_label }} - <?php esc_html_e('Item', 'tbp-core'); ?> {{ i + 1 }}
                                        </a>
                                    </div>
                                </li>
                                <# } #>
                            </ul>
                        </div>
                    </div>
                    <# }); #>
                <# } else { #>
                <ul class="tbp-slinky__list tbp-slinky__list--root" role="menubar">
                    <# for (var i = 0; i < 4; i++) { #>
                    <li class="tbp-slinky__item<# if (i === 1) { #> tbp-slinky__item--has-children<# } #>" role="none">
                        <div class="tbp-slinky__item-inner">
                            <# if (i === 1) { #>
                            <span class="tbp-slinky__item-text" role="menuitem">
                                <?php esc_html_e('Menu Item with Children', 'tbp-core'); ?>
                            </span>
                            <button type="button" class="tbp-slinky__next">
                                <span class="tbp-slinky__next-icon">
                                    <i class="{{ settings.next_icon.value }}"></i>
                                </span>
                            </button>
                            <# } else { #>
                            <a href="#" class="tbp-slinky__item-link" role="menuitem">
                                <?php esc_html_e('Menu Item', 'tbp-core'); ?> {{ i + 1 }}
                            </a>
                            <# } #>
                        </div>
                    </li>
                    <# } #>
                </ul>
                <# } #>
            </div>
        </nav>
        <?php
    }
}
