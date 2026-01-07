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

class Taxonomy_Badges extends Widget_Base {

    public function get_name() {
        return 'tbp-taxonomy-badges';
    }

    public function get_title() {
        return __('Taxonomy Badges', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-tags';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['taxonomy', 'badges', 'tags', 'categories', 'terms'];
    }

    public function get_style_depends() {
        return ['tbp-taxonomy-badges'];
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

        // Get available taxonomies
        $taxonomies = $this->get_taxonomies_options();

        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomy', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $taxonomies,
                'default' => 'category',
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => -1,
                'min' => -1,
                'description' => __('-1 for unlimited', 'tbp-core'),
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
            'orderby',
            [
                'label' => __('Order By', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => __('Name', 'tbp-core'),
                    'count' => __('Count', 'tbp-core'),
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
            'hide_empty_message',
            [
                'label' => __('Hide If Empty', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
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
                    '{{WRAPPER}} .tbp-taxonomy-badges' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label' => __('Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'size' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badges' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // Badge Style Section
        $this->start_controls_section(
            'section_badge_style',
            [
                'label' => __('Badge', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .tbp-taxonomy-badge',
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => 5,
                    'right' => 12,
                    'bottom' => 5,
                    'left' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('badge_tabs');

        $this->start_controls_tab(
            'badge_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'badge_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'badge_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'badge_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_bg_color_hover',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_border_color_hover',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'badge_border',
                'selector' => '{{WRAPPER}} .tbp-taxonomy-badge',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'badge_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-taxonomy-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'badge_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-taxonomy-badge',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get taxonomies options
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
     * Get terms for current post
     */
    private function get_post_terms($settings) {
        $post_id = get_the_ID();

        if (!$post_id) {
            return [];
        }

        $taxonomy = $settings['taxonomy'] ?? 'category';
        $limit = isset($settings['limit']) ? (int) $settings['limit'] : -1;
        $orderby = $settings['orderby'] ?? 'name';
        $order = $settings['order'] ?? 'ASC';

        $terms = wp_get_post_terms($post_id, $taxonomy, [
            'orderby' => $orderby,
            'order' => $order,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return [];
        }

        // Apply limit
        if ($limit > 0) {
            $terms = array_slice($terms, 0, $limit);
        }

        return $terms;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $terms = $this->get_post_terms($settings);

        // Hide if empty
        if (empty($terms)) {
            if (!empty($settings['hide_empty_message']) && $settings['hide_empty_message'] === 'yes') {
                return;
            }
            echo '<p class="tbp-taxonomy-badges-empty">' . __('No terms found.', 'tbp-core') . '</p>';
            return;
        }

        $link_to = $settings['link_to'] ?? 'archive';
        $new_tab = !empty($settings['open_new_tab']) && $settings['open_new_tab'] === 'yes';
        $tag = $link_to === 'archive' ? 'a' : 'span';

        ?>
        <div class="tbp-taxonomy-badges">
            <?php foreach ($terms as $term) :
                $attrs = '';
                if ($tag === 'a') {
                    $attrs = 'href="' . esc_url(get_term_link($term)) . '"';
                    if ($new_tab) {
                        $attrs .= ' target="_blank" rel="noopener noreferrer"';
                    }
                }
                ?>
                <<?php echo $tag; ?> class="tbp-taxonomy-badge" <?php echo $attrs; ?>>
                    <?php echo esc_html($term->name); ?>
                </<?php echo $tag; ?>>
            <?php endforeach; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <div class="tbp-taxonomy-badges">
            <span class="tbp-taxonomy-badge">Term 1</span>
            <span class="tbp-taxonomy-badge">Term 2</span>
            <span class="tbp-taxonomy-badge">Term 3</span>
        </div>
        <?php
    }
}
