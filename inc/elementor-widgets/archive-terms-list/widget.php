<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Archive_Terms_List extends Widget_Base {

    public function get_name() {
        return 'tbp-archive-terms-list';
    }

    public function get_title() {
        return __('Archive Terms List', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-bullet-list';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['archive', 'terms', 'list', 'taxonomy', 'categories', 'tags', 'icon list'];
    }

    public function get_style_depends() {
        return ['tbp-archive-terms-list'];
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
                'default' => 'current_archive',
                'options' => [
                    'current_archive' => __('Current Archive Taxonomy', 'tbp-core'),
                    'specific' => __('Specific Taxonomy', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomy', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_taxonomies_options(),
                'default' => 'category',
                'condition' => [
                    'source' => 'specific',
                ],
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => __('Show Icon', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-check',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label' => __('Link To', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'archive',
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'archive' => __('Term Archive', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'open_new_tab',
            [
                'label' => __('Open in New Tab', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => [
                    'link_to' => 'archive',
                ],
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label' => __('Show Post Count', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'count_format',
            [
                'label' => __('Count Format', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '(%s)',
                'description' => __('Use %s as placeholder for count number.', 'tbp-core'),
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => __('Name', 'tbp-core'),
                    'count' => __('Post Count', 'tbp-core'),
                    'term_id' => __('Term ID', 'tbp-core'),
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

        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('0 for no limit.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'hide_empty',
            [
                'label' => __('Hide If Empty', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
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
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'vertical' => [
                        'title' => __('Vertical', 'tbp-core'),
                        'icon' => 'eicon-editor-list-ul',
                    ],
                    'horizontal' => [
                        'title' => __('Horizontal', 'tbp-core'),
                        'icon' => 'eicon-ellipsis-h',
                    ],
                ],
                'default' => 'vertical',
                'toggle' => false,
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Start', 'tbp-core'),
                        'icon' => 'eicon-align-start-h',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-align-center-h',
                    ],
                    'flex-end' => [
                        'title' => __('End', 'tbp-core'),
                        'icon' => 'eicon-align-end-h',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .tbp-atl--horizontal' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'show_divider',
            [
                'label' => __('Show Divider', 'tbp-core'),
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
            'list_gap',
            [
                'label' => __('Space Between Items', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                    'em' => ['min' => 0, 'max' => 3],
                ],
                'default' => ['size' => 10, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl--vertical' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-atl--horizontal' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Item Style Section
        $this->start_controls_section(
            'section_item_style',
            [
                'label' => __('Item', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'item_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('item_tabs');

        $this->start_controls_tab('item_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'item_bg_color',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('item_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'item_bg_color_hover',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'item_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 6, 'max' => 50],
                    'em' => ['min' => 0.5, 'max' => 3],
                ],
                'default' => ['size' => 14, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-atl__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 30],
                ],
                'default' => ['size' => 8, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('icon_tabs');

        $this->start_controls_tab('icon_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'icon_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-atl__icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('icon_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'icon_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item:hover .tbp-atl__icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-atl__item:hover .tbp-atl__icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Text Style Section
        $this->start_controls_section(
            'section_text_style',
            [
                'label' => __('Text', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .tbp-atl__text',
            ]
        );

        $this->start_controls_tabs('text_tabs');

        $this->start_controls_tab('text_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_control(
            'text_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('text_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_control(
            'text_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__item:hover .tbp-atl__text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .tbp-atl__text',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        // Count Style Section
        $this->start_controls_section(
            'section_count_style',
            [
                'label' => __('Count', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'count_typography',
                'selector' => '{{WRAPPER}} .tbp-atl__count',
            ]
        );

        $this->add_control(
            'count_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__count' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'count_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 20],
                ],
                'default' => ['size' => 5, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl__count' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Divider Style Section
        $this->start_controls_section(
            'section_divider_style',
            [
                'label' => __('Divider', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_divider' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'divider_style',
            [
                'label' => __('Style', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'solid' => __('Solid', 'tbp-core'),
                    'dashed' => __('Dashed', 'tbp-core'),
                    'dotted' => __('Dotted', 'tbp-core'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl--vertical .tbp-atl__item:not(:last-child)' => 'border-bottom-style: {{VALUE}};',
                    '{{WRAPPER}} .tbp-atl--horizontal .tbp-atl__item:not(:last-child)' => 'border-right-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#e5e7eb',
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl--vertical .tbp-atl__item:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-atl--horizontal .tbp-atl__item:not(:last-child)' => 'border-right-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_weight',
            [
                'label' => __('Weight', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 1, 'max' => 5],
                ],
                'default' => ['size' => 1, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-atl--vertical .tbp-atl__item:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-atl--horizontal .tbp-atl__item:not(:last-child)' => 'border-right-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get available taxonomies
     */
    private function get_taxonomies_options() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = [];

        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy->name] = $taxonomy->label;
        }

        return $options;
    }

    /**
     * Get terms based on source setting
     */
    private function get_archive_terms($settings) {
        $source = $settings['source'] ?? 'current_archive';
        $orderby = $settings['orderby'] ?? 'name';
        $order = $settings['order'] ?? 'ASC';
        $limit = !empty($settings['limit']) ? (int) $settings['limit'] : 0;

        // Determine taxonomy
        $taxonomy = '';

        if ($source === 'current_archive') {
            // Get taxonomy from current archive
            $queried_object = get_queried_object();

            if ($queried_object instanceof \WP_Term) {
                $taxonomy = $queried_object->taxonomy;
            } elseif (is_tax() || is_category() || is_tag()) {
                $taxonomy = get_query_var('taxonomy');
                if (!$taxonomy && is_category()) {
                    $taxonomy = 'category';
                } elseif (!$taxonomy && is_tag()) {
                    $taxonomy = 'post_tag';
                }
            }

            // For editor preview, use category as fallback
            if (empty($taxonomy) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $taxonomy = 'category';
            }
        } else {
            // Use specific taxonomy
            $taxonomy = $settings['taxonomy'] ?? 'category';
        }

        if (empty($taxonomy)) {
            return [];
        }

        $args = [
            'taxonomy' => $taxonomy,
            'orderby' => $orderby,
            'order' => $order,
            'hide_empty' => true,
        ];

        if ($limit > 0) {
            $args['number'] = $limit;
        }

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            return [];
        }

        return $terms;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $terms = $this->get_archive_terms($settings);

        // Hide if empty
        if (empty($terms)) {
            if (!empty($settings['hide_empty']) && $settings['hide_empty'] === 'yes') {
                return;
            }
            echo '<p class="tbp-atl__empty">' . esc_html__('No terms found.', 'tbp-core') . '</p>';
            return;
        }

        $layout = $settings['layout'] ?? 'vertical';
        $show_icon = !empty($settings['show_icon']) && $settings['show_icon'] === 'yes';
        $show_count = !empty($settings['show_count']) && $settings['show_count'] === 'yes';
        $count_format = $settings['count_format'] ?? '(%s)';
        $link_to = $settings['link_to'] ?? 'archive';
        $new_tab = !empty($settings['open_new_tab']) && $settings['open_new_tab'] === 'yes';
        $show_divider = !empty($settings['show_divider']) && $settings['show_divider'] === 'yes';

        $list_class = 'tbp-atl tbp-atl--' . $layout;
        if ($show_divider) {
            $list_class .= ' tbp-atl--has-divider';
        }

        ?>
        <ul class="<?php echo esc_attr($list_class); ?>" role="list" aria-label="<?php esc_attr_e('Archive terms', 'tbp-core'); ?>">
            <?php foreach ($terms as $term) :
                $item_tag = $link_to === 'archive' ? 'a' : 'span';
                $item_attrs = '';

                if ($link_to === 'archive') {
                    $item_attrs = 'href="' . esc_url(get_term_link($term)) . '"';
                    if ($new_tab) {
                        $item_attrs .= ' target="_blank" rel="noopener noreferrer"';
                    }
                }
            ?>
                <li class="tbp-atl__item-wrapper">
                    <<?php echo $item_tag; ?> class="tbp-atl__item" <?php echo $item_attrs; ?>>
                        <?php if ($show_icon && !empty($settings['icon']['value'])) : ?>
                            <span class="tbp-atl__icon" aria-hidden="true">
                                <?php Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="tbp-atl__text"><?php echo esc_html($term->name); ?></span>
                        <?php if ($show_count) : ?>
                            <span class="tbp-atl__count"><?php echo esc_html(sprintf($count_format, $term->count)); ?></span>
                        <?php endif; ?>
                    </<?php echo $item_tag; ?>>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var layoutClass = 'tbp-atl tbp-atl--' + (settings.layout || 'vertical');
        if (settings.show_divider === 'yes') {
            layoutClass += ' tbp-atl--has-divider';
        }
        var showIcon = settings.show_icon === 'yes' && settings.icon.value;
        var showCount = settings.show_count === 'yes';
        var countFormat = settings.count_format || '(%s)';
        var itemTag = settings.link_to === 'archive' ? 'a' : 'span';

        var sampleTerms = [
            { name: 'Term One', count: 12 },
            { name: 'Term Two', count: 8 },
            { name: 'Term Three', count: 5 },
        ];
        #>
        <ul class="{{ layoutClass }}" role="list">
            <# _.each(sampleTerms, function(term) { #>
                <li class="tbp-atl__item-wrapper">
                    <{{{ itemTag }}} class="tbp-atl__item" <# if (settings.link_to === 'archive') { #>href="#"<# } #>>
                        <# if (showIcon) { #>
                            <span class="tbp-atl__icon" aria-hidden="true">
                                <i class="{{ settings.icon.value }}"></i>
                            </span>
                        <# } #>
                        <span class="tbp-atl__text">{{{ term.name }}}</span>
                        <# if (showCount) { #>
                            <span class="tbp-atl__count">{{{ countFormat.replace('%s', term.count) }}}</span>
                        <# } #>
                    </{{{ itemTag }}}>
                </li>
            <# }); #>
        </ul>
        <?php
    }
}
