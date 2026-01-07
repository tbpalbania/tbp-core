<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class List_Childrens extends Widget_Base {

    public function get_name() {
        return 'tbp-list-childrens';
    }

    public function get_title() {
        return __('List Childrens', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['list', 'children', 'child', 'posts', 'dynamic', 'hierarchy'];
    }

    public function get_style_depends() {
        return ['tbp-list-childrens'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function register_content_controls() {
        // Query Section
        $this->start_controls_section(
            'section_query',
            [
                'label' => __('Query', 'tbp-core'),
            ]
        );

        $this->add_control(
            'posts_limit',
            [
                'label' => __('Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => -1,
                'min' => -1,
                'description' => __('-1 for all children', 'tbp-core'),
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'menu_order',
                'options' => [
                    'menu_order' => __('Menu Order', 'tbp-core'),
                    'title' => __('Title', 'tbp-core'),
                    'date' => __('Date', 'tbp-core'),
                    'modified' => __('Modified Date', 'tbp-core'),
                    'ID' => __('ID', 'tbp-core'),
                    'rand' => __('Random', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => __('Ascending', 'tbp-core'),
                    'DESC' => __('Descending', 'tbp-core'),
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Section
        $this->start_controls_section(
            'section_icon',
            [
                'label' => __('Icon', 'tbp-core'),
            ]
        );

        $this->add_control(
            'icon_source',
            [
                'label' => __('Icon Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'static',
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'static' => __('Static Icon', 'tbp-core'),
                    'dynamic' => __('Dynamic (ACF Field)', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'static_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-chevron-right',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'icon_source' => 'static',
                ],
            ]
        );

        // Get ACF icon fields
        $icon_fields = $this->get_acf_icon_fields();

        $this->add_control(
            'acf_icon_field',
            [
                'label' => __('ACF Icon Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $icon_fields,
                'default' => '',
                'condition' => [
                    'icon_source' => 'dynamic',
                ],
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
                'condition' => [
                    'icon_source!' => 'none',
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
            'list_align',
            [
                'label' => __('Alignment', 'tbp-core'),
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

        $this->end_controls_section();

        // Link Section
        $this->start_controls_section(
            'section_link',
            [
                'label' => __('Link', 'tbp-core'),
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label' => __('Link To', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'post',
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'post' => __('Post Permalink', 'tbp-core'),
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
                    'link_to' => 'post',
                ],
            ]
        );

        $this->end_controls_section();

        // No Results Section
        $this->start_controls_section(
            'section_no_results',
            [
                'label' => __('No Results', 'tbp-core'),
            ]
        );

        $this->add_control(
            'no_results_text',
            [
                'label' => __('No Results Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('No items found.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'hide_if_empty',
            [
                'label' => __('Hide If Empty', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
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
                    '{{WRAPPER}} .tbp-list-childrens-items:not(.tbp-inline-items) .tbp-list-childrens-item:not(:last-child)' => 'padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-list-childrens-items:not(.tbp-inline-items) .tbp-list-childrens-item:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-list-childrens-items.tbp-inline-items .tbp-list-childrens-item' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
                    '{{WRAPPER}} .tbp-list-childrens-items.tbp-inline-items' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
                ],
            ]
        );

        $this->add_responsive_control(
            'vertical_align',
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
                    '{{WRAPPER}} .tbp-list-childrens-item' => 'align-items: {{VALUE}};',
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
                    '{{WRAPPER}} .tbp-list-childrens-items:not(.tbp-inline-items) .tbp-list-childrens-item:not(:last-child):after' => 'border-top-style: {{VALUE}}',
                    '{{WRAPPER}} .tbp-list-childrens-items.tbp-inline-items .tbp-list-childrens-item:not(:last-child):after' => 'border-left-style: {{VALUE}}',
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
                    '{{WRAPPER}} .tbp-list-childrens-items:not(.tbp-inline-items) .tbp-list-childrens-item:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .tbp-list-childrens-items.tbp-inline-items .tbp-list-childrens-item:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
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
                    '{{WRAPPER}} .tbp-list-childrens-item:not(:last-child):after' => 'border-color: {{VALUE}}',
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
                'condition' => [
                    'icon_source!' => 'none',
                ],
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

        $this->start_controls_tabs('icon_colors');

        $this->start_controls_tab(
            'icon_colors_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'icon_primary_color',
            [
                'label' => __('Primary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-icon' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-icon i, {{WRAPPER}} .tbp-icon-view-default .tbp-list-childrens-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-icon svg, {{WRAPPER}} .tbp-icon-view-default .tbp-list-childrens-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_secondary_color',
            [
                'label' => __('Secondary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_colors_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'icon_primary_color_hover',
            [
                'label' => __('Primary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-item:hover .tbp-list-childrens-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-item:hover .tbp-list-childrens-icon' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-item:hover .tbp-list-childrens-icon i, {{WRAPPER}} .tbp-icon-view-default .tbp-list-childrens-item:hover .tbp-list-childrens-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-item:hover .tbp-list-childrens-icon svg, {{WRAPPER}} .tbp-icon-view-default .tbp-list-childrens-item:hover .tbp-list-childrens-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_secondary_color_hover',
            [
                'label' => __('Secondary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'icon_view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-view-framed .tbp-list-childrens-item:hover .tbp-list-childrens-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-item:hover .tbp-list-childrens-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-icon-view-stacked .tbp-list-childrens-item:hover .tbp-list-childrens-icon svg' => 'fill: {{VALUE}};',
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
                    '{{WRAPPER}} .tbp-list-childrens-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-list-childrens-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-list-childrens-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .tbp-list-childrens-icon' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-left .tbp-list-childrens-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-icon-right .tbp-list-childrens-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
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

        $this->start_controls_tabs('text_colors');

        $this->start_controls_tab(
            'text_colors_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-list-childrens-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'text_colors_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'text_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-list-childrens-item:hover .tbp-list-childrens-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .tbp-list-childrens-text',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get ACF icon fields
     */
    private function get_acf_icon_fields() {
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
                // Accept image, icon_picker, file, or text fields
                if (in_array($field['type'], ['image', 'file', 'icon_picker', 'text', 'url'])) {
                    $fields[$field['name']] = $group['title'] . ' - ' . $field['label'];
                }
            }
        }

        return $fields;
    }

    /**
     * Get children posts
     */
    private function get_children_posts($settings) {
        $parent_id = get_the_ID();

        if (!$parent_id) {
            return [];
        }

        $args = [
            'post_type' => get_post_type($parent_id),
            'post_parent' => $parent_id,
            'posts_per_page' => $settings['posts_limit'] ?? -1,
            'orderby' => $settings['orderby'] ?? 'menu_order',
            'order' => $settings['order'] ?? 'ASC',
            'post_status' => 'publish',
        ];

        $query = new \WP_Query($args);

        return $query->posts;
    }

    /**
     * Get icon for a post
     */
    private function get_post_icon($post_id, $settings) {
        if ($settings['icon_source'] === 'static') {
            return ['type' => 'elementor', 'icon' => $settings['static_icon']];
        }

        if ($settings['icon_source'] === 'dynamic' && !empty($settings['acf_icon_field'])) {
            $field_name = $settings['acf_icon_field'];

            // Get raw value first to handle icon_picker
            $raw_value = function_exists('get_field') ? get_field($field_name, $post_id, false) : null;
            $value = function_exists('get_field') ? get_field($field_name, $post_id) : null;

            if (empty($value) && empty($raw_value)) {
                return null;
            }

            // Check if it's an ACF icon_picker field (raw value is array with type)
            if (is_array($raw_value) && isset($raw_value['type'])) {
                if ($raw_value['type'] === 'icon') {
                    return ['type' => 'class', 'class' => $raw_value['value']];
                } elseif ($raw_value['type'] === 'media') {
                    $media_value = $raw_value['value'];
                    if (is_numeric($media_value)) {
                        return ['type' => 'url', 'url' => wp_get_attachment_url($media_value)];
                    } elseif (is_string($media_value)) {
                        return ['type' => 'url', 'url' => $media_value];
                    }
                }
            }

            // Handle other field types
            if (is_array($value)) {
                // Image array
                if (isset($value['url'])) {
                    return ['type' => 'url', 'url' => $value['url']];
                }
            } elseif (is_numeric($value)) {
                // Attachment ID
                return ['type' => 'url', 'url' => wp_get_attachment_url($value)];
            } elseif (is_string($value)) {
                if (filter_var($value, FILTER_VALIDATE_URL) || strpos($value, '/') !== false) {
                    return ['type' => 'url', 'url' => $value];
                } else {
                    return ['type' => 'class', 'class' => $value];
                }
            }
        }

        return null;
    }

    /**
     * Render icon
     */
    private function render_icon($icon_data) {
        if (empty($icon_data)) {
            return;
        }

        echo '<span class="tbp-list-childrens-icon">';

        if ($icon_data['type'] === 'url' && !empty($icon_data['url'])) {
            echo '<img src="' . esc_url($icon_data['url']) . '" alt="" />';
        } elseif ($icon_data['type'] === 'class' && !empty($icon_data['class'])) {
            echo '<i class="' . esc_attr($icon_data['class']) . '"></i>';
        } elseif ($icon_data['type'] === 'elementor' && !empty($icon_data['icon']['value'])) {
            Icons_Manager::render_icon($icon_data['icon'], ['aria-hidden' => 'true']);
        }

        echo '</span>';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $children = $this->get_children_posts($settings);

        // No results handling
        if (empty($children)) {
            if ($settings['hide_if_empty'] === 'yes') {
                return;
            }

            if (!empty($settings['no_results_text'])) {
                echo '<p class="tbp-list-childrens-no-results">' . esc_html($settings['no_results_text']) . '</p>';
            }
            return;
        }

        $icon_position = $settings['icon_position'] ?? 'left';
        $icon_view = $settings['icon_view'] ?? 'default';
        $icon_shape = $settings['icon_shape'] ?? 'circle';
        $has_link = $settings['link_to'] === 'post';
        $new_tab = !empty($settings['link_new_tab']) && $settings['link_new_tab'] === 'yes';

        // Build list class
        $list_classes = ['tbp-list-childrens-items'];
        if ($settings['view'] === 'inline') {
            $list_classes[] = 'tbp-inline-items';
        }

        // Build item class
        $item_classes = [
            'tbp-list-childrens-item',
            'tbp-icon-' . $icon_position,
            'tbp-icon-view-' . $icon_view,
        ];
        if ($icon_view !== 'default') {
            $item_classes[] = 'tbp-icon-shape-' . $icon_shape;
        }

        ?>
        <ul class="<?php echo esc_attr(implode(' ', $list_classes)); ?>">
            <?php foreach ($children as $child) :
                $icon_data = $settings['icon_source'] !== 'none' ? $this->get_post_icon($child->ID, $settings) : null;
                $permalink = get_permalink($child->ID);
                ?>
                <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
                    <?php if ($has_link) : ?>
                        <a href="<?php echo esc_url($permalink); ?>"<?php echo $new_tab ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php endif; ?>

                    <?php if ($icon_position === 'left' && $icon_data) : ?>
                        <?php $this->render_icon($icon_data); ?>
                    <?php endif; ?>

                    <span class="tbp-list-childrens-text"><?php echo esc_html($child->post_title); ?></span>

                    <?php if ($icon_position === 'right' && $icon_data) : ?>
                        <?php $this->render_icon($icon_data); ?>
                    <?php endif; ?>

                    <?php if ($has_link) : ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var iconPosition = settings.icon_position || 'left';
        var iconView = settings.icon_view || 'default';
        var iconShape = settings.icon_shape || 'circle';

        var listClass = 'tbp-list-childrens-items';
        if (settings.view === 'inline') {
            listClass += ' tbp-inline-items';
        }

        var itemClass = 'tbp-list-childrens-item tbp-icon-' + iconPosition + ' tbp-icon-view-' + iconView;
        if (iconView !== 'default') {
            itemClass += ' tbp-icon-shape-' + iconShape;
        }

        function getIconHtml() {
            if (settings.icon_source === 'none') return '';
            if (settings.icon_source === 'static' && settings.static_icon && settings.static_icon.value) {
                var iconValue = settings.static_icon.value;
                if (typeof iconValue === 'object' && iconValue.url) {
                    return '<span class="tbp-list-childrens-icon"><img src="' + iconValue.url + '" /></span>';
                }
                return '<span class="tbp-list-childrens-icon"><i class="' + iconValue + '"></i></span>';
            }
            return '<span class="tbp-list-childrens-icon"><i class="fas fa-image"></i></span>';
        }
        #>
        <div class="tbp-list-childrens-placeholder">
            <p><?php _e('Children posts will be loaded dynamically.', 'tbp-core'); ?></p>
            <p><?php _e('Preview:', 'tbp-core'); ?></p>
            <ul class="{{ listClass }}">
                <# for (var i = 1; i <= 3; i++) { #>
                <li class="{{ itemClass }}">
                    <# if (iconPosition === 'left') { #>{{{ getIconHtml() }}}<# } #>
                    <span class="tbp-list-childrens-text"><?php _e('Child Post', 'tbp-core'); ?> {{ i }}</span>
                    <# if (iconPosition === 'right') { #>{{{ getIconHtml() }}}<# } #>
                </li>
                <# } #>
            </ul>
        </div>
        <?php
    }
}
