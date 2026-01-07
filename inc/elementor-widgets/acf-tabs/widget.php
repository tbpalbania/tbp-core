<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if (!defined('ABSPATH')) {
    exit;
}

class ACF_Tabs extends Widget_Base {

    public function get_name() {
        return 'tbp-acf-tabs';
    }

    public function get_title() {
        return __('ACF Tabs', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['acf', 'tabs', 'fields', 'custom', 'dynamic'];
    }

    public function get_style_depends() {
        return ['tbp-acf-tabs'];
    }

    public function get_script_depends() {
        return ['tbp-acf-tabs'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_tabs_style_controls();
        $this->register_title_style_controls();
        $this->register_content_style_controls();
    }

    protected function register_content_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Settings', 'tbp-core'),
            ]
        );

        $this->add_control(
            'source',
            [
                'label' => __('Preview Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current' => __('Current Post', 'tbp-core'),
                    'custom' => __('Custom Post (Editor Only)', 'tbp-core'),
                ],
                'description' => __('On frontend, always uses current post. Custom is for editor preview only.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'custom_post_id',
            [
                'label' => __('Preview Post ID', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('Enter Post ID', 'tbp-core'),
                'description' => __('Enter a Post ID to preview ACF tabs in the editor. Ignored on frontend.', 'tbp-core'),
                'condition' => [
                    'source' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'field_group',
            [
                'label' => __('Field Group', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_field_groups_options(),
                'default' => 'auto',
                'description' => __('Select a field group or auto-detect from current post.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'tab_layout',
            [
                'label' => __('Position', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', 'tbp-core'),
                    'vertical' => __('Vertical', 'tbp-core'),
                ],
                'prefix_class' => 'tbp-acf-tabs-view-',
            ]
        );

        $this->add_control(
            'tabs_align_horizontal',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => __('Start', 'tbp-core'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => __('End', 'tbp-core'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'stretch' => [
                        'title' => __('Stretch', 'tbp-core'),
                        'icon' => 'eicon-h-align-stretch',
                    ],
                ],
                'default' => 'start',
                'condition' => [
                    'tab_layout' => 'horizontal',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-nav' => 'justify-content: {{VALUE}};',
                ],
                'selectors_dictionary' => [
                    'start' => 'flex-start',
                    'end' => 'flex-end',
                ],
            ]
        );

        $this->add_control(
            'tabs_position_vertical',
            [
                'label' => __('Position', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'left',
                'condition' => [
                    'tab_layout' => 'vertical',
                ],
                'prefix_class' => 'tbp-acf-tabs-position-',
            ]
        );

        $this->add_control(
            'tabs_sticky',
            [
                'label' => __('Sticky Tabs', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'description' => __('Make tabs sticky while scrolling content.', 'tbp-core'),
                'condition' => [
                    'tab_layout' => 'vertical',
                ],
                'prefix_class' => 'tbp-acf-tabs-sticky-',
            ]
        );

        $this->add_control(
            'sticky_offset',
            [
                'label' => __('Sticky Offset', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'condition' => [
                    'tab_layout' => 'vertical',
                    'tabs_sticky' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-nav' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_repeater_settings',
            [
                'label' => __('Repeater Fields Display', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'repeater_layout',
            [
                'label' => __('Repeater Layout', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'table',
                'options' => [
                    'table' => __('Table', 'tbp-core'),
                    'cards' => __('Cards', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'repeater_responsive',
            [
                'label' => __('Mobile Behavior', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'stack',
                'options' => [
                    'stack' => __('Stack Rows', 'tbp-core'),
                    'scroll' => __('Horizontal Scroll', 'tbp-core'),
                ],
                'condition' => [
                    'repeater_layout' => 'table',
                ],
                'prefix_class' => 'tbp-acf-repeater-responsive-',
            ]
        );

        $this->add_control(
            'show_field_tooltips',
            [
                'label' => __('Show Helpful Information', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'tbp-core'),
                'label_off' => __('No', 'tbp-core'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Show tooltip icon with field instructions', 'tbp-core'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'exclude_tabs',
            [
                'label' => __('Exclude Tabs', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_tabs_options(),
                'default' => [],
                'description' => __('Select tabs to exclude from display.', 'tbp-core'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'custom_order',
            [
                'label' => __('Custom Tab Order', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'separator' => 'before',
            ]
        );

        $tab_options = $this->get_tabs_options();
        $this->add_control(
            'tabs_order',
            [
                'label' => __('Tab Order', 'tbp-core'),
                'type' => Controls_Manager::REPEATER,
                'fields' => [
                    [
                        'name' => 'tab_key',
                        'label' => __('Tab', 'tbp-core'),
                        'type' => Controls_Manager::SELECT2,
                        'options' => $tab_options,
                        'default' => '',
                        'label_block' => true,
                    ],
                ],
                'default' => [],
                'title_field' => '<# var tabOptions = ' . wp_json_encode($tab_options) . '; #>{{{ tabOptions[tab_key] || tab_key }}}',
                'condition' => [
                    'custom_order' => 'yes',
                ],
                'description' => __('Add tabs in the order you want them displayed. Drag to reorder.', 'tbp-core'),
            ]
        );

        $this->end_controls_section();
    }

    protected function register_tabs_style_controls() {
        $this->start_controls_section(
            'section_tabs_style',
            [
                'label' => __('Tabs', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'tabs_nav_width',
            [
                'label' => __('Navigation Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'condition' => [
                    'tab_layout' => 'vertical',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-nav' => 'width: {{SIZE}}{{UNIT}}; flex: 0 0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tabs_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}.tbp-acf-tabs-view-horizontal .tbp-acf-tabs-nav' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.tbp-acf-tabs-view-vertical .tbp-acf-tabs-nav' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tabs_border',
                'selector' => '{{WRAPPER}} .tbp-acf-tabs-nav',
            ]
        );

        $this->add_control(
            'tabs_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'tabs_background',
                'selector' => '{{WRAPPER}} .tbp-acf-tabs-nav',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_title_style_controls() {
        $this->start_controls_section(
            'section_title_style',
            [
                'label' => __('Title', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .tbp-acf-tab-title',
            ]
        );

        $this->add_responsive_control(
            'title_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('title_tabs');

        $this->start_controls_tab(
            'title_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_background',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'title_border',
                'selector' => '{{WRAPPER}} .tbp-acf-tab-title',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_background',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_active',
            [
                'label' => __('Active', 'tbp-core'),
            ]
        );

        $this->add_control(
            'title_active_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_active_background',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_active_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title.active' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'title_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'title_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-acf-tab-title',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_content_style_controls() {
        $this->start_controls_section(
            'section_content_style',
            [
                'label' => __('Content', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .tbp-acf-tab-content',
            ]
        );

        $this->add_control(
            'content_background',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-content' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'content_border',
                'selector' => '{{WRAPPER}} .tbp-acf-tabs-content',
            ]
        );

        $this->add_control(
            'content_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-tabs-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'content_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-acf-tabs-content',
            ]
        );

        $this->end_controls_section();

        // Field Label Style Controls
        $this->start_controls_section(
            'section_field_label_style',
            [
                'label' => __('Field Labels', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'field_label_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-field-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'field_label_typography',
                'selector' => '{{WRAPPER}} .tbp-acf-field-label',
            ]
        );

        $this->add_responsive_control(
            'field_label_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-field-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_spacing',
            [
                'label' => __('Field Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-acf-field' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get field groups options
     */
    private function get_field_groups_options() {
        $options = [
            'auto' => __('Auto-detect', 'tbp-core'),
        ];

        if (function_exists('acf_get_field_groups')) {
            $groups = acf_get_field_groups();
            foreach ($groups as $group) {
                $options[$group['key']] = $group['title'];
            }
        }

        return $options;
    }

    /**
     * Get tabs options for exclude control
     */
    private function get_tabs_options() {
        $options = [];

        if (!function_exists('acf_get_field_groups')) {
            return $options;
        }

        // Get all field groups to list all possible tabs
        $field_groups = acf_get_field_groups();

        foreach ($field_groups as $group) {
            if (!$group) continue;

            $fields = acf_get_fields($group['key']);
            if (!$fields) continue;

            foreach ($fields as $field) {
                if ($field['type'] === 'tab') {
                    $options[$field['key']] = $field['label'];
                }
            }
        }

        return $options;
    }

    /**
     * Get the post ID to use (handles templates, preview mode, custom selection)
     */
    private function get_post_id($settings = []) {
        $is_editor = class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode();
        $is_preview = class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode();

        // Frontend: Always use current post ID
        if (!$is_editor && !$is_preview) {
            return get_the_ID();
        }

        // Editor/Preview mode: Check for custom post ID setting (for template preview)
        if (!empty($settings['source']) && $settings['source'] === 'custom' && !empty($settings['custom_post_id'])) {
            return absint($settings['custom_post_id']);
        }

        // Try to get current post ID
        $post_id = get_the_ID();

        // If in Elementor editor/preview and no valid post ID, try alternatives
        if (!$post_id || $this->is_template_context()) {
            // Check for Elementor's preview post
            if (class_exists('\Elementor\Plugin')) {
                $document = \Elementor\Plugin::$instance->documents->get_current();
                if ($document) {
                    $preview_id = $document->get_settings('preview_id');
                    if ($preview_id) {
                        return absint($preview_id);
                    }
                }
            }

            // Try from GET parameter
            if (isset($_GET['post'])) {
                return absint($_GET['post']);
            }
        }

        return $post_id;
    }

    /**
     * Check if we're in a template editing context
     */
    private function is_template_context() {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }

        $document = \Elementor\Plugin::$instance->documents->get_current();
        if (!$document) {
            return false;
        }

        // Check if document is a template type
        $template_types = ['single', 'archive', 'single-post', 'single-page', 'header', 'footer', 'section'];
        $doc_type = $document->get_name();

        return in_array($doc_type, $template_types) || strpos($doc_type, 'single') !== false;
    }

    /**
     * Get ACF tabs structure for current post
     */
    private function get_tabs_structure($field_group_key = 'auto', $post_id = null) {
        if (!$post_id) {
            $post_id = $this->get_post_id();
        }

        if (!$post_id || !function_exists('acf_get_field_groups')) {
            return [];
        }

        // Get field groups for this post
        if ($field_group_key === 'auto') {
            $field_groups = acf_get_field_groups(['post_id' => $post_id]);
        } else {
            $field_groups = [acf_get_field_group($field_group_key)];
        }

        $tabs = [];
        $current_tab_index = -1;

        foreach ($field_groups as $group) {
            if (!$group) continue;

            $fields = acf_get_fields($group['key']);
            if (!$fields) continue;

            foreach ($fields as $field) {
                if ($field['type'] === 'tab') {
                    // Start a new tab
                    $tabs[] = [
                        'key' => $field['key'],
                        'label' => $field['label'],
                        'fields' => [],
                    ];
                    $current_tab_index = count($tabs) - 1;
                } elseif ($current_tab_index >= 0) {
                    // Add field to current tab
                    $tabs[$current_tab_index]['fields'][] = $field;
                }
            }
        }

        return $tabs;
    }

    /**
     * Render field value based on type
     */
    private function render_field($field, $post_id, $settings = []) {
        $value = get_field($field['name'], $post_id);

        // For repeaters, empty array is valid "no data" state
        if ($value === null || $value === '' || $value === false || (is_array($value) && empty($value))) {
            // Debug in editor mode for repeaters
            if ($field['type'] === 'repeater' && class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-acf-field tbp-acf-field-repeater">';
                echo '<div class="tbp-acf-field-label">' . esc_html($field['label']) . '</div>';
                echo '<div class="tbp-acf-tabs-empty" style="font-size: 12px; padding: 10px;">';
                echo sprintf(__('No data in repeater. (Post ID: %d, Field: %s)', 'tbp-core'), $post_id, esc_html($field['name']));
                echo '</div></div>';
            }
            return;
        }

        echo '<div class="tbp-acf-field tbp-acf-field-' . esc_attr($field['type']) . '">';

        if (!empty($field['label'])) {
            echo '<div class="tbp-acf-field-label">';
            echo esc_html($field['label']);

            // Add tooltip icon if field has instructions and tooltips are enabled
            $show_tooltips = isset($settings['show_field_tooltips']) ? $settings['show_field_tooltips'] === 'yes' : true;
            if ($show_tooltips && !empty($field['instructions'])) {
                echo '<span class="tbp-acf-field-tooltip">';
                echo '<span class="tbp-acf-field-tooltip-icon">?</span>';
                echo '<span class="tbp-acf-field-tooltip-overlay"></span>';
                echo '<span class="tbp-acf-field-tooltip-content">';
                echo '<span class="tbp-acf-field-tooltip-close">&times;</span>';
                echo wp_kses_post($field['instructions']);
                echo '</span>';
                echo '</span>';
            }

            echo '</div>';
        }

        echo '<div class="tbp-acf-field-value">';

        switch ($field['type']) {
            case 'text':
            case 'number':
            case 'range':
            case 'email':
            case 'url':
                echo esc_html($value);
                break;

            case 'textarea':
                // Check if value contains HTML (auto paragraphs enabled in ACF)
                if (strpos($value, '<p>') !== false || strpos($value, '<br') !== false) {
                    echo wp_kses_post($value);
                } else {
                    echo nl2br(esc_html($value));
                }
                break;

            case 'wysiwyg':
                echo wp_kses_post($value);
                break;

            case 'image':
                $this->render_image_field($value, $field);
                break;

            case 'gallery':
                $this->render_gallery_field($value, $field);
                break;

            case 'file':
                $this->render_file_field($value, $field);
                break;

            case 'link':
                $this->render_link_field($value, $field);
                break;

            case 'select':
            case 'radio':
            case 'button_group':
                $this->render_choice_field($value, $field);
                break;

            case 'checkbox':
                $this->render_checkbox_field($value, $field);
                break;

            case 'true_false':
                echo $value ? __('Yes', 'tbp-core') : __('No', 'tbp-core');
                break;

            case 'date_picker':
            case 'date_time_picker':
            case 'time_picker':
                echo esc_html($value);
                break;

            case 'color_picker':
                echo '<span class="tbp-acf-color-swatch" style="background-color: ' . esc_attr($value) . '"></span>';
                echo '<span class="tbp-acf-color-value">' . esc_html($value) . '</span>';
                break;

            case 'oembed':
                echo $value;
                break;

            case 'post_object':
                $this->render_post_object_field($value, $field);
                break;

            case 'relationship':
                $this->render_relationship_field($value, $field);
                break;

            case 'taxonomy':
                $this->render_taxonomy_field($value, $field);
                break;

            case 'user':
                $this->render_user_field($value, $field);
                break;

            case 'repeater':
                $this->render_repeater_field($value, $field, $settings);
                break;

            case 'group':
                $this->render_group_field($value, $field);
                break;

            case 'google_map':
                $this->render_map_field($value, $field);
                break;

            case 'tbp_dropzone':
                $this->render_dropzone_field($value, $field);
                break;

            default:
                if (is_array($value)) {
                    echo '<pre>' . esc_html(print_r($value, true)) . '</pre>';
                } else {
                    echo esc_html($value);
                }
        }

        echo '</div></div>';
    }

    private function render_image_field($value, $field) {
        if (is_array($value)) {
            $url = $value['url'] ?? '';
            $alt = $value['alt'] ?? '';
        } else {
            $url = wp_get_attachment_image_url($value, 'large');
            $alt = get_post_meta($value, '_wp_attachment_image_alt', true);
        }

        if ($url) {
            echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="tbp-acf-image">';
        }
    }

    private function render_gallery_field($value, $field) {
        if (!is_array($value) || empty($value)) return;

        echo '<div class="tbp-acf-gallery">';
        foreach ($value as $image) {
            $url = is_array($image) ? ($image['url'] ?? '') : wp_get_attachment_image_url($image, 'medium');
            $alt = is_array($image) ? ($image['alt'] ?? '') : '';
            if ($url) {
                echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="tbp-acf-gallery-item">';
            }
        }
        echo '</div>';
    }

    private function render_file_field($value, $field) {
        if (is_array($value)) {
            $url = $value['url'] ?? '';
            $filename = $value['filename'] ?? '';
        } else {
            $url = wp_get_attachment_url($value);
            $filename = basename($url);
        }

        if ($url) {
            echo '<a href="' . esc_url($url) . '" target="_blank" download class="tbp-acf-file">';
            echo '<svg class="tbp-acf-file-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
            echo '<span class="tbp-acf-file-name">' . esc_html($filename) . '</span>';
            echo '</a>';
        }
    }

    private function render_link_field($value, $field) {
        if (!is_array($value)) return;

        $url = $value['url'] ?? '';
        $title = $value['title'] ?? $url;
        $target = $value['target'] ?? '_self';

        if ($url) {
            echo '<a href="' . esc_url($url) . '" target="' . esc_attr($target) . '" class="tbp-acf-link">';
            echo esc_html($title);
            echo '</a>';
        }
    }

    private function render_choice_field($value, $field) {
        if (is_array($value)) {
            echo implode(', ', array_map('esc_html', $value));
        } else {
            // Get label from choices if available
            if (!empty($field['choices'][$value])) {
                echo esc_html($field['choices'][$value]);
            } else {
                echo esc_html($value);
            }
        }
    }

    private function render_checkbox_field($value, $field) {
        if (!is_array($value)) return;

        echo '<ul class="tbp-acf-checkbox-list">';
        foreach ($value as $item) {
            $label = !empty($field['choices'][$item]) ? $field['choices'][$item] : $item;
            echo '<li>' . esc_html($label) . '</li>';
        }
        echo '</ul>';
    }

    private function render_post_object_field($value, $field) {
        if (!$value) return;

        if (is_array($value)) {
            echo '<ul class="tbp-acf-posts-list">';
            foreach ($value as $post) {
                $post_obj = is_object($post) ? $post : get_post($post);
                if ($post_obj) {
                    echo '<li><a href="' . esc_url(get_permalink($post_obj)) . '">' . esc_html($post_obj->post_title) . '</a></li>';
                }
            }
            echo '</ul>';
        } else {
            $post_obj = is_object($value) ? $value : get_post($value);
            if ($post_obj) {
                echo '<a href="' . esc_url(get_permalink($post_obj)) . '">' . esc_html($post_obj->post_title) . '</a>';
            }
        }
    }

    private function render_relationship_field($value, $field) {
        $this->render_post_object_field($value, $field);
    }

    private function render_taxonomy_field($value, $field) {
        if (!$value) return;

        if (!is_array($value)) {
            $value = [$value];
        }

        echo '<ul class="tbp-acf-terms-list">';
        foreach ($value as $term) {
            $term_obj = is_object($term) ? $term : get_term($term);
            if ($term_obj && !is_wp_error($term_obj)) {
                echo '<li><a href="' . esc_url(get_term_link($term_obj)) . '">' . esc_html($term_obj->name) . '</a></li>';
            }
        }
        echo '</ul>';
    }

    private function render_user_field($value, $field) {
        if (!$value) return;

        if (!is_array($value)) {
            $value = [$value];
        }

        echo '<ul class="tbp-acf-users-list">';
        foreach ($value as $user) {
            $user_obj = is_object($user) ? $user : get_userdata($user);
            if ($user_obj) {
                echo '<li>' . esc_html($user_obj->display_name) . '</li>';
            }
        }
        echo '</ul>';
    }

    private function render_repeater_field($value, $field, $settings = []) {
        // Debug: Check if value is empty
        if (!is_array($value) || empty($value)) {
            if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-acf-tabs-empty" style="font-size: 12px; padding: 10px;">';
                echo sprintf(__('Repeater "%s" has no data or returned invalid format.', 'tbp-core'), esc_html($field['label'] ?? $field['name']));
                echo '</div>';
            }
            return;
        }

        $layout = $settings['repeater_layout'] ?? 'table';
        $sub_fields = $field['sub_fields'] ?? [];

        // If sub_fields not in field array, try to get them from the field key
        if (empty($sub_fields) && !empty($field['key'])) {
            $full_field = acf_get_field($field['key']);
            if ($full_field && !empty($full_field['sub_fields'])) {
                $sub_fields = $full_field['sub_fields'];
            }
        }

        // Still no sub_fields? Try to infer from first row data
        if (empty($sub_fields) && !empty($value[0]) && is_array($value[0])) {
            foreach ($value[0] as $key => $val) {
                $sub_fields[] = [
                    'name' => $key,
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'type' => is_array($val) ? 'array' : 'text',
                ];
            }
        }

        if (empty($sub_fields)) {
            // Debug output in editor mode
            if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-acf-tabs-empty">' . __('Repeater field found but no sub-fields detected.', 'tbp-core') . '</div>';
            }
            return;
        }

        if ($layout === 'cards') {
            $this->render_repeater_cards($value, $sub_fields);
        } else {
            $this->render_repeater_table($value, $sub_fields);
        }
    }

    /**
     * Render repeater as responsive table
     */
    private function render_repeater_table($rows, $sub_fields) {
        echo '<div class="tbp-acf-repeater tbp-acf-repeater-table">';
        echo '<table class="tbp-acf-table">';

        // Table header
        echo '<thead><tr>';
        foreach ($sub_fields as $sub_field) {
            echo '<th data-label="' . esc_attr($sub_field['label']) . '">' . esc_html($sub_field['label']) . '</th>';
        }
        echo '</tr></thead>';

        // Table body
        echo '<tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($sub_fields as $sub_field) {
                $sub_value = $row[$sub_field['name']] ?? null;
                echo '<td data-label="' . esc_attr($sub_field['label']) . '">';
                if ($sub_value !== null && $sub_value !== '') {
                    $this->render_sub_field_value($sub_value, $sub_field);
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';
        echo '</div>';
    }

    /**
     * Render repeater as cards
     */
    private function render_repeater_cards($rows, $sub_fields) {
        echo '<div class="tbp-acf-repeater tbp-acf-repeater-cards">';
        foreach ($rows as $index => $row) {
            echo '<div class="tbp-acf-card">';
            foreach ($sub_fields as $sub_field) {
                $sub_value = $row[$sub_field['name']] ?? null;
                if ($sub_value !== null && $sub_value !== '') {
                    echo '<div class="tbp-acf-card-field">';
                    echo '<span class="tbp-acf-card-label">' . esc_html($sub_field['label']) . '</span>';
                    echo '<span class="tbp-acf-card-value">';
                    $this->render_sub_field_value($sub_value, $sub_field);
                    echo '</span>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_group_field($value, $field) {
        if (!is_array($value)) return;

        echo '<div class="tbp-acf-group">';
        if (!empty($field['sub_fields'])) {
            foreach ($field['sub_fields'] as $sub_field) {
                $sub_value = $value[$sub_field['name']] ?? null;
                if ($sub_value !== null && $sub_value !== '') {
                    echo '<div class="tbp-acf-group-field">';
                    echo '<span class="tbp-acf-group-label">' . esc_html($sub_field['label']) . ':</span> ';
                    echo '<span class="tbp-acf-group-value">';
                    $this->render_sub_field_value($sub_value, $sub_field);
                    echo '</span>';
                    echo '</div>';
                }
            }
        }
        echo '</div>';
    }

    private function render_sub_field_value($value, $field) {
        switch ($field['type']) {
            case 'image':
                if (is_array($value) && !empty($value['url'])) {
                    echo '<img src="' . esc_url($value['url']) . '" alt="" style="max-width: 100px; height: auto;">';
                } elseif (is_numeric($value)) {
                    $url = wp_get_attachment_image_url($value, 'thumbnail');
                    if ($url) {
                        echo '<img src="' . esc_url($url) . '" alt="" style="max-width: 100px; height: auto;">';
                    }
                }
                break;

            case 'link':
                if (is_array($value) && !empty($value['url'])) {
                    echo '<a href="' . esc_url($value['url']) . '">' . esc_html($value['title'] ?? $value['url']) . '</a>';
                }
                break;

            case 'wysiwyg':
                echo wp_kses_post($value);
                break;

            default:
                if (is_array($value)) {
                    echo esc_html(implode(', ', $value));
                } else {
                    echo esc_html($value);
                }
        }
    }

    private function render_map_field($value, $field) {
        if (!is_array($value) || empty($value['lat']) || empty($value['lng'])) return;

        $lat = $value['lat'];
        $lng = $value['lng'];
        $address = $value['address'] ?? '';

        echo '<div class="tbp-acf-map" data-lat="' . esc_attr($lat) . '" data-lng="' . esc_attr($lng) . '">';
        echo '<a href="https://www.google.com/maps?q=' . esc_attr($lat) . ',' . esc_attr($lng) . '" target="_blank">';
        if ($address) {
            echo esc_html($address);
        } else {
            echo __('View on Map', 'tbp-core');
        }
        echo '</a>';
        echo '</div>';
    }

    private function render_dropzone_field($value, $field) {
        if (empty($value)) return;

        // Normalize to array
        if (!is_array($value)) {
            $value = [$value];
        }

        echo '<div class="tbp-acf-dropzone-files">';

        foreach ($value as $file) {
            // Handle different return formats
            if (is_array($file)) {
                // Array format - full attachment data
                $url = $file['url'] ?? '';
                $filename = $file['filename'] ?? basename($url);
                $file_id = $file['ID'] ?? $file['id'] ?? 0;
                $mime = $file['mime_type'] ?? $file['mime'] ?? '';
            } elseif (is_numeric($file)) {
                // ID format
                $file_id = intval($file);
                $url = wp_get_attachment_url($file_id);
                $filename = basename(get_attached_file($file_id));
                $mime = get_post_mime_type($file_id);
            } else {
                // URL format
                $url = $file;
                $filename = basename($url);
                $file_id = attachment_url_to_postid($url);
                $mime = $file_id ? get_post_mime_type($file_id) : '';
            }

            if (empty($url)) continue;

            $is_image = $file_id ? wp_attachment_is_image($file_id) : preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $url);

            echo '<div class="tbp-acf-dropzone-file">';

            if ($is_image) {
                // Render as image with lightbox potential
                $thumb_url = $file_id ? wp_get_attachment_image_url($file_id, 'medium') : $url;
                echo '<a href="' . esc_url($url) . '" target="_blank" class="tbp-acf-dropzone-image-link">';
                echo '<img src="' . esc_url($thumb_url) . '" alt="' . esc_attr($filename) . '" class="tbp-acf-dropzone-image">';
                echo '</a>';
            } else {
                // Render as downloadable file
                echo '<a href="' . esc_url($url) . '" target="_blank" download class="tbp-acf-file">';
                echo '<svg class="tbp-acf-file-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
                echo '<span class="tbp-acf-file-name">' . esc_html($filename) . '</span>';
                echo '</a>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Check if a field has a non-empty value
     */
    private function field_has_value($field, $post_id) {
        $value = get_field($field['name'], $post_id);

        if ($value === null || $value === '' || $value === false) {
            return false;
        }

        // For arrays (repeaters, galleries, etc.), check if not empty
        if (is_array($value)) {
            return !empty($value);
        }

        return true;
    }

    /**
     * Check if a tab has any fields with values
     */
    private function tab_has_content($tab, $post_id) {
        if (empty($tab['fields'])) {
            return false;
        }

        foreach ($tab['fields'] as $field) {
            if ($this->field_has_value($field, $post_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter tabs to only include those with content
     */
    private function filter_tabs_with_content($tabs, $post_id) {
        return array_values(array_filter($tabs, function($tab) use ($post_id) {
            return $this->tab_has_content($tab, $post_id);
        }));
    }

    /**
     * Filter tab fields to only include those with values
     */
    private function filter_tab_fields_with_values($tab, $post_id) {
        $tab['fields'] = array_filter($tab['fields'], function($field) use ($post_id) {
            return $this->field_has_value($field, $post_id);
        });
        return $tab;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $post_id = $this->get_post_id($settings);
        $tabs = $this->get_tabs_structure($settings['field_group'], $post_id);

        if (empty($tabs)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $message = __('No ACF tabs found.', 'tbp-core');
                if ($settings['source'] === 'current') {
                    $message .= ' ' . __('When using templates, select "Custom Post" and enter a Post ID to preview.', 'tbp-core');
                }
                echo '<div class="tbp-acf-tabs-empty">' . $message . '</div>';
            }
            return;
        }

        // Filter excluded tabs
        if (!empty($settings['exclude_tabs']) && is_array($settings['exclude_tabs'])) {
            $excluded_keys = $settings['exclude_tabs'];

            $tabs = array_filter($tabs, function($tab) use ($excluded_keys) {
                return !in_array($tab['key'], $excluded_keys);
            });

            $tabs = array_values($tabs); // Re-index array
        }

        // Apply custom tab order
        if (!empty($settings['custom_order']) && $settings['custom_order'] === 'yes' && !empty($settings['tabs_order'])) {
            $ordered_tabs = [];
            $tabs_by_key = [];

            // Index tabs by key for quick lookup
            foreach ($tabs as $tab) {
                $tabs_by_key[$tab['key']] = $tab;
            }

            // Build ordered array based on user selection
            foreach ($settings['tabs_order'] as $order_item) {
                if (!empty($order_item['tab_key']) && isset($tabs_by_key[$order_item['tab_key']])) {
                    $ordered_tabs[] = $tabs_by_key[$order_item['tab_key']];
                    unset($tabs_by_key[$order_item['tab_key']]); // Remove to avoid duplicates
                }
            }

            // Only use ordered tabs if we have any (otherwise keep original)
            if (!empty($ordered_tabs)) {
                $tabs = $ordered_tabs;
            }
        }

        if (empty($tabs)) {
            return;
        }

        // Filter tabs to only include those with content
        $tabs = $this->filter_tabs_with_content($tabs, $post_id);

        // Filter each tab's fields to only include those with values
        $tabs = array_map(function($tab) use ($post_id) {
            return $this->filter_tab_fields_with_values($tab, $post_id);
        }, $tabs);

        // Re-check if any tabs remain after filtering
        if (empty($tabs)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="tbp-acf-tabs-empty">' . __('All tabs are empty (no fields have values).', 'tbp-core') . '</div>';
            }
            return;
        }

        $id_prefix = 'tbp-acf-tabs-' . $this->get_id();
        ?>
        <div class="tbp-acf-tabs-wrapper">
            <div class="tbp-acf-tabs-nav" role="tablist">
                <?php foreach ($tabs as $index => $tab) :
                    $active_class = $index === 0 ? ' active' : '';
                    ?>
                    <button class="tbp-acf-tab-title<?php echo $active_class; ?>"
                            role="tab"
                            id="<?php echo esc_attr($id_prefix . '-tab-' . $index); ?>"
                            aria-controls="<?php echo esc_attr($id_prefix . '-content-' . $index); ?>"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                            data-tab="<?php echo esc_attr($index); ?>">
                        <?php echo esc_html($tab['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="tbp-acf-tabs-content">
                <?php foreach ($tabs as $index => $tab) :
                    $active_class = $index === 0 ? ' active' : '';
                    ?>
                    <div class="tbp-acf-tab-content<?php echo $active_class; ?>"
                         role="tabpanel"
                         id="<?php echo esc_attr($id_prefix . '-content-' . $index); ?>"
                         aria-labelledby="<?php echo esc_attr($id_prefix . '-tab-' . $index); ?>"
                         data-tab="<?php echo esc_attr($index); ?>">
                        <?php
                        if (!empty($tab['fields'])) {
                            foreach ($tab['fields'] as $field) {
                                $this->render_field($field, $post_id, $settings);
                            }
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="tbp-acf-tabs-dots">
                <?php foreach ($tabs as $index => $tab) :
                    $active_class = $index === 0 ? ' active' : '';
                    ?>
                    <button class="tbp-acf-tabs-dot<?php echo $active_class; ?>"
                            data-tab="<?php echo esc_attr($index); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Go to %s', 'tbp-core'), $tab['label'])); ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="tbp-acf-tabs-wrapper">
            <div class="tbp-acf-tabs-nav" role="tablist">
                <button class="tbp-acf-tab-title active" role="tab">Tab 1</button>
                <button class="tbp-acf-tab-title" role="tab">Tab 2</button>
                <button class="tbp-acf-tab-title" role="tab">Tab 3</button>
            </div>
            <div class="tbp-acf-tabs-content">
                <div class="tbp-acf-tab-content active" role="tabpanel">
                    <p><?php _e('ACF tab content will appear here.', 'tbp-core'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
}
