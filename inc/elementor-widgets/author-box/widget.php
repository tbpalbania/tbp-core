<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Author_Box extends Widget_Base {

    public function get_name() {
        return 'tbp-author-box';
    }

    public function get_title() {
        return __('Author Box', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['author', 'box', 'profile', 'bio', 'avatar', 'user'];
    }

    public function get_style_depends() {
        return ['tbp-author-box'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function register_content_controls() {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'tbp-core'),
            ]
        );

        $this->add_control(
            'source',
            [
                'label' => __('Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current_post',
                'options' => [
                    'current_post' => __('Current Post Author', 'tbp-core'),
                    'custom' => __('Custom User', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'custom_user',
            [
                'label' => __('Select User', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_users_options(),
                'condition' => ['source' => 'custom'],
            ]
        );

        $this->add_control(
            'show_avatar',
            [
                'label' => __('Show Avatar', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'avatar_size',
            [
                'label' => __('Avatar Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 32,
                        'max' => 300,
                    ],
                ],
                'default' => [
                    'size' => 96,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => 80,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => 64,
                    'unit' => 'px',
                ],
                'condition' => ['show_avatar' => 'yes'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__avatar img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'show_name',
            [
                'label' => __('Show Name', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'name_tag',
            [
                'label' => __('Name HTML Tag', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'h3',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'p' => 'p',
                    'span' => 'span',
                ],
                'condition' => ['show_name' => 'yes'],
            ]
        );

        $this->add_control(
            'show_role',
            [
                'label' => __('Show Role/Title', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Displays the user role or custom title.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'custom_role_label',
            [
                'label' => __('Custom Title', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('e.g., Senior Editor', 'tbp-core'),
                'condition' => ['show_role' => 'yes'],
            ]
        );

        $this->add_control(
            'show_bio',
            [
                'label' => __('Show Bio', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'bio_length',
            [
                'label' => __('Bio Length', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('0 for full bio.', 'tbp-core'),
                'condition' => ['show_bio' => 'yes'],
            ]
        );

        $this->add_control(
            'show_post_count',
            [
                'label' => __('Show Post Count', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'post_count_text',
            [
                'label' => __('Post Count Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('%d Articles', 'tbp-core'),
                'description' => __('Use %d for count number.', 'tbp-core'),
                'condition' => ['show_post_count' => 'yes'],
            ]
        );

        $this->add_control(
            'show_archive_link',
            [
                'label' => __('Show Archive Link', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'archive_link_text',
            [
                'label' => __('Archive Link Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('View All Posts', 'tbp-core'),
                'condition' => ['show_archive_link' => 'yes'],
            ]
        );

        $this->end_controls_section();

        // Social Links Section
        $this->start_controls_section(
            'section_social',
            [
                'label' => __('Social Links', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_social',
            [
                'label' => __('Show Social Links', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'social_source',
            [
                'label' => __('Social Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'user_meta',
                'options' => [
                    'user_meta' => __('User Profile', 'tbp-core'),
                    'custom' => __('Custom (Define Here)', 'tbp-core'),
                ],
                'condition' => ['show_social' => 'yes'],
            ]
        );

        $this->add_control(
            'social_meta_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => __('Uses social links from user profile. Go to Users → Edit User → Social Links to add social networks.', 'tbp-core'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition' => [
                    'show_social' => 'yes',
                    'social_source' => 'user_meta',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'social_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fab fa-twitter',
                    'library' => 'fa-brands',
                ],
            ]
        );

        $repeater->add_control(
            'social_url',
            [
                'label' => __('URL', 'tbp-core'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://twitter.com/username',
            ]
        );

        $repeater->add_control(
            'social_label',
            [
                'label' => __('Label (for screen readers)', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Twitter',
            ]
        );

        $this->add_control(
            'social_links',
            [
                'label' => __('Social Links', 'tbp-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    ['social_label' => 'Twitter', 'social_icon' => ['value' => 'fab fa-twitter', 'library' => 'fa-brands']],
                    ['social_label' => 'LinkedIn', 'social_icon' => ['value' => 'fab fa-linkedin', 'library' => 'fa-brands']],
                ],
                'title_field' => '{{{ social_label }}}',
                'condition' => [
                    'show_social' => 'yes',
                    'social_source' => 'custom',
                ],
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
            'layout',
            [
                'label' => __('Layout', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'tbp-core'),
                    'vertical' => __('Vertical (Centered)', 'tbp-core'),
                ],
            ]
        );

        $this->add_responsive_control(
            'alignment',
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
                'default' => 'left',
                'condition' => ['layout' => 'horizontal'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab--horizontal .tbp-ab__content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // Box Style
        $this->start_controls_section(
            'section_box_style',
            [
                'label' => __('Box', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .tbp-ab',
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => 24,
                    'right' => 24,
                    'bottom' => 24,
                    'left' => 24,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'selector' => '{{WRAPPER}} .tbp-ab',
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .tbp-ab',
            ]
        );

        $this->end_controls_section();

        // Avatar Style
        $this->start_controls_section(
            'section_avatar_style',
            [
                'label' => __('Avatar', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_avatar' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'avatar_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 50]],
                'default' => ['size' => 20, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab--horizontal .tbp-ab__avatar' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-ab--vertical .tbp-ab__avatar' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'avatar_border',
                'selector' => '{{WRAPPER}} .tbp-ab__avatar img',
            ]
        );

        $this->add_responsive_control(
            'avatar_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 50,
                    'right' => 50,
                    'bottom' => 50,
                    'left' => 50,
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'avatar_shadow',
                'selector' => '{{WRAPPER}} .tbp-ab__avatar img',
            ]
        );

        $this->end_controls_section();

        // Name Style
        $this->start_controls_section(
            'section_name_style',
            [
                'label' => __('Name', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_name' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .tbp-ab__name',
            ]
        );

        $this->add_control(
            'name_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__name' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-ab__name a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'name_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__name a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'name_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 30]],
                'default' => ['size' => 4, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Role Style
        $this->start_controls_section(
            'section_role_style',
            [
                'label' => __('Role/Title', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_role' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'role_typography',
                'selector' => '{{WRAPPER}} .tbp-ab__role',
            ]
        );

        $this->add_control(
            'role_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__role' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'role_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 30]],
                'default' => ['size' => 8, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__role' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Bio Style
        $this->start_controls_section(
            'section_bio_style',
            [
                'label' => __('Bio', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_bio' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'bio_typography',
                'selector' => '{{WRAPPER}} .tbp-ab__bio',
            ]
        );

        $this->add_control(
            'bio_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4b5563',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__bio' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'bio_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 30]],
                'default' => ['size' => 16, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__bio' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Social Style
        $this->start_controls_section(
            'section_social_style',
            [
                'label' => __('Social Links', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_social' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'social_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 12, 'max' => 40]],
                'default' => ['size' => 18, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__social a' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-ab__social svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'social_gap',
            [
                'label' => __('Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 30]],
                'default' => ['size' => 12, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__social' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('social_tabs');

        $this->start_controls_tab('social_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'social_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__social a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-ab__social svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('social_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'social_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__social a:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-ab__social a:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'social_spacing',
            [
                'label' => __('Top Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 30]],
                'default' => ['size' => 16, 'unit' => 'px'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__social' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style
        $this->start_controls_section(
            'section_button_style',
            [
                'label' => __('Archive Button', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_archive_link' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .tbp-ab__button',
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab('button_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'button_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('button_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'button_color_hover',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color_hover',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#4f46e5',
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'separator' => 'before',
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 6,
                    'right' => 6,
                    'bottom' => 6,
                    'left' => 6,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_spacing',
            [
                'label' => __('Top Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 40]],
                'default' => ['size' => 16, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-ab__button' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_users_options() {
        $users = get_users(['fields' => ['ID', 'display_name']]);
        $options = [];
        foreach ($users as $user) {
            $options[$user->ID] = $user->display_name;
        }
        return $options;
    }

    private function get_author_data($settings) {
        $source = $settings['source'] ?? 'current_post';

        if ($source === 'custom' && !empty($settings['custom_user'])) {
            $user_id = (int) $settings['custom_user'];
        } else {
            $user_id = get_post_field('post_author', get_the_ID());
        }

        if (!$user_id) {
            return null;
        }

        $user = get_userdata($user_id);
        if (!$user) {
            return null;
        }

        // Get social links from new user profile fields module
        $social_links = [];
        if (class_exists('TBP_User_Profile_Fields')) {
            $social_links = \TBP_User_Profile_Fields::get_user_social_links($user_id);
        }

        // Fallback to legacy user meta if no new social links
        if (empty($social_links)) {
            $legacy_social = [
                ['network' => 'website', 'url' => $user->user_url, 'label' => 'Website', 'icon' => 'fas fa-globe'],
                ['network' => 'twitter', 'url' => get_user_meta($user_id, 'twitter', true), 'label' => 'Twitter', 'icon' => 'fab fa-x-twitter'],
                ['network' => 'facebook', 'url' => get_user_meta($user_id, 'facebook', true), 'label' => 'Facebook', 'icon' => 'fab fa-facebook'],
                ['network' => 'instagram', 'url' => get_user_meta($user_id, 'instagram', true), 'label' => 'Instagram', 'icon' => 'fab fa-instagram'],
                ['network' => 'linkedin', 'url' => get_user_meta($user_id, 'linkedin', true), 'label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
            ];
            foreach ($legacy_social as $link) {
                if (!empty($link['url'])) {
                    $social_links[] = $link;
                }
            }
        }

        // Get custom avatar URL if available
        $custom_avatar = '';
        if (class_exists('TBP_User_Profile_Fields')) {
            $custom_avatar = get_user_meta($user_id, 'tbp_custom_avatar', true);
        }

        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'bio' => $user->description,
            'email' => $user->user_email,
            'url' => $user->user_url,
            'archive_url' => get_author_posts_url($user->ID),
            'post_count' => count_user_posts($user->ID),
            'role' => !empty($user->roles) ? ucfirst($user->roles[0]) : '',
            'social' => $social_links,
            'custom_avatar' => $custom_avatar,
        ];
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $author = $this->get_author_data($settings);

        if (!$author) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-ab tbp-ab--placeholder">';
                echo '<p style="text-align:center;color:#6b7280;padding:20px;">' . esc_html__('Author box will appear here on single posts.', 'tbp-core') . '</p>';
                echo '</div>';
            }
            return;
        }

        $layout = $settings['layout'] ?? 'horizontal';
        $show_avatar = !empty($settings['show_avatar']) && $settings['show_avatar'] === 'yes';
        $show_name = !empty($settings['show_name']) && $settings['show_name'] === 'yes';
        $show_role = !empty($settings['show_role']) && $settings['show_role'] === 'yes';
        $show_bio = !empty($settings['show_bio']) && $settings['show_bio'] === 'yes';
        $show_post_count = !empty($settings['show_post_count']) && $settings['show_post_count'] === 'yes';
        $show_archive_link = !empty($settings['show_archive_link']) && $settings['show_archive_link'] === 'yes';
        $show_social = !empty($settings['show_social']) && $settings['show_social'] === 'yes';

        $avatar_size = 300; // High quality, CSS handles actual display size
        $name_tag = $settings['name_tag'] ?? 'h3';
        $bio_length = !empty($settings['bio_length']) ? (int) $settings['bio_length'] : 0;

        $role_text = !empty($settings['custom_role_label']) ? $settings['custom_role_label'] : $author['role'];

        ?>
        <div class="tbp-ab tbp-ab--<?php echo esc_attr($layout); ?>" itemscope itemtype="https://schema.org/Person">
            <?php if ($show_avatar) : ?>
                <div class="tbp-ab__avatar">
                    <?php echo get_avatar($author['id'], $avatar_size, '', $author['name'], ['itemprop' => 'image']); ?>
                </div>
            <?php endif; ?>

            <div class="tbp-ab__content">
                <?php if ($show_name) : ?>
                    <<?php echo esc_html($name_tag); ?> class="tbp-ab__name" itemprop="name">
                        <a href="<?php echo esc_url($author['archive_url']); ?>"><?php echo esc_html($author['name']); ?></a>
                    </<?php echo esc_html($name_tag); ?>>
                <?php endif; ?>

                <?php if ($show_role && $role_text) : ?>
                    <div class="tbp-ab__role" itemprop="jobTitle"><?php echo esc_html($role_text); ?></div>
                <?php endif; ?>

                <?php if ($show_post_count) : ?>
                    <div class="tbp-ab__post-count">
                        <?php echo esc_html(sprintf($settings['post_count_text'] ?? __('%d Articles', 'tbp-core'), $author['post_count'])); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_bio && $author['bio']) :
                    $bio = $author['bio'];
                    if ($bio_length > 0 && strlen($bio) > $bio_length) {
                        $bio = rtrim(substr($bio, 0, $bio_length)) . '…';
                    }
                ?>
                    <div class="tbp-ab__bio" itemprop="description"><?php echo esc_html($bio); ?></div>
                <?php endif; ?>

                <?php if ($show_social && !empty($author['social'])) : ?>
                    <div class="tbp-ab__social" aria-label="<?php esc_attr_e('Social links', 'tbp-core'); ?>">
                        <?php if ($settings['social_source'] === 'user_meta') :
                            foreach ($author['social'] as $link) :
                                if (empty($link['url'])) continue;
                        ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($link['label']); ?>">
                                <i class="<?php echo esc_attr($link['icon']); ?>" aria-hidden="true"></i>
                                <span class="screen-reader-text"><?php echo esc_html($link['label']); ?></span>
                            </a>
                        <?php endforeach;
                        else :
                            foreach ($settings['social_links'] as $link) :
                                if (empty($link['social_url']['url'])) continue;
                        ?>
                            <a href="<?php echo esc_url($link['social_url']['url']); ?>"
                               <?php echo !empty($link['social_url']['is_external']) ? 'target="_blank"' : ''; ?>
                               <?php echo !empty($link['social_url']['nofollow']) ? 'rel="nofollow noopener"' : 'rel="noopener"'; ?>
                               aria-label="<?php echo esc_attr($link['social_label']); ?>">
                                <?php Icons_Manager::render_icon($link['social_icon'], ['aria-hidden' => 'true']); ?>
                                <span class="screen-reader-text"><?php echo esc_html($link['social_label']); ?></span>
                            </a>
                        <?php endforeach;
                        endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_archive_link) : ?>
                    <a href="<?php echo esc_url($author['archive_url']); ?>" class="tbp-ab__button">
                        <?php echo esc_html($settings['archive_link_text'] ?? __('View All Posts', 'tbp-core')); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var layout = settings.layout || 'horizontal';
        var showAvatar = settings.show_avatar === 'yes';
        var showName = settings.show_name === 'yes';
        var showRole = settings.show_role === 'yes';
        var showBio = settings.show_bio === 'yes';
        var showPostCount = settings.show_post_count === 'yes';
        var showArchiveLink = settings.show_archive_link === 'yes';
        var showSocial = settings.show_social === 'yes';
        var nameTag = settings.name_tag || 'h3';
        #>
        <div class="tbp-ab tbp-ab--{{ layout }}">
            <# if (showAvatar) {
                var avatarSize = settings.avatar_size && settings.avatar_size.size ? settings.avatar_size.size : 96;
            #>
                <div class="tbp-ab__avatar">
                    <img src="https://www.gravatar.com/avatar/?d=mp&s=300" alt="Author" style="width:{{ avatarSize }}px;height:{{ avatarSize }}px;">
                </div>
            <# } #>

            <div class="tbp-ab__content">
                <# if (showName) { #>
                    <{{{ nameTag }}} class="tbp-ab__name">
                        <a href="#">Author Name</a>
                    </{{{ nameTag }}}>
                <# } #>

                <# if (showRole) { #>
                    <div class="tbp-ab__role">{{{ settings.custom_role_label || 'Author' }}}</div>
                <# } #>

                <# if (showPostCount) { #>
                    <div class="tbp-ab__post-count">{{{ (settings.post_count_text || '%d Articles').replace('%d', '42') }}}</div>
                <# } #>

                <# if (showBio) { #>
                    <div class="tbp-ab__bio">This is a sample author biography. The actual bio will be displayed from the author's profile.</div>
                <# } #>

                <# if (showSocial) { #>
                    <div class="tbp-ab__social">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                <# } #>

                <# if (showArchiveLink) { #>
                    <a href="#" class="tbp-ab__button">{{{ settings.archive_link_text || 'View All Posts' }}}</a>
                <# } #>
            </div>
        </div>
        <?php
    }
}
