<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;

if (!defined('ABSPATH')) {
    exit;
}

class Search_Results_Grid extends Widget_Base {

    public function get_name() {
        return 'tbp-search-results-grid';
    }

    public function get_title() {
        return __('Search Results Grid', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-posts-grid';
    }

    public function get_categories() {
        return ['theme-elements'];
    }

    public function get_keywords() {
        return ['search', 'results', 'grid', 'posts', 'archive', 'query'];
    }

    public function get_style_depends() {
        return ['tbp-search-results-grid'];
    }

    protected function register_controls() {
        // Query Section
        $this->start_controls_section(
            'section_query',
            [
                'label' => __('Query', 'tbp-core'),
            ]
        );

        $this->add_control(
            'post_types',
            [
                'label' => __('Post Types', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_post_types_options(),
                'default' => ['post', 'page'],
                'description' => __('Select which post types to include in search results', 'tbp-core'),
            ]
        );

        $this->add_control(
            'group_by_post_type',
            [
                'label' => __('Group by Post Type', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => __('Display results grouped under post type headings', 'tbp-core'),
            ]
        );

        $this->add_control(
            'posts_per_group',
            [
                'label' => __('Posts per Group', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 50,
                'condition' => ['group_by_post_type' => 'yes'],
                'description' => __('Maximum posts to show per post type group', 'tbp-core'),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts per Page', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
                'condition' => ['group_by_post_type' => ''],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'relevance',
                'options' => [
                    'relevance' => __('Relevance', 'tbp-core'),
                    'date' => __('Date', 'tbp-core'),
                    'title' => __('Title', 'tbp-core'),
                    'modified' => __('Modified Date', 'tbp-core'),
                    'rand' => __('Random', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __('Descending', 'tbp-core'),
                    'ASC' => __('Ascending', 'tbp-core'),
                ],
                'condition' => ['orderby!' => 'relevance'],
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
            'columns',
            [
                'label' => __('Columns', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
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
                    'px' => ['min' => 0, 'max' => 60],
                    'em' => ['min' => 0, 'max' => 4],
                ],
                'default' => ['size' => 24, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'equal_height',
            [
                'label' => __('Equal Height Cards', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Card Content Section
        $this->start_controls_section(
            'section_card_content',
            [
                'label' => __('Card Content', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_thumbnail',
            [
                'label' => __('Show Thumbnail', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'default' => 'medium',
                'condition' => ['show_thumbnail' => 'yes'],
            ]
        );

        $this->add_control(
            'thumbnail_ratio',
            [
                'label' => __('Thumbnail Ratio', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => '56.25',
                'options' => [
                    '56.25' => '16:9',
                    '75' => '4:3',
                    '100' => '1:1',
                    '133' => '3:4',
                    '150' => '2:3',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__thumb' => 'padding-bottom: {{VALUE}}%;',
                ],
                'condition' => ['show_thumbnail' => 'yes'],
            ]
        );

        $this->add_control(
            'show_post_type_badge',
            [
                'label' => __('Show Post Type Badge', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => __('Shows badge on card indicating post type', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'title_tag',
            [
                'label' => __('Title HTML Tag', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'h3',
                'options' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                ],
                'condition' => ['show_title' => 'yes'],
            ]
        );

        $this->add_control(
            'title_length',
            [
                'label' => __('Title Length', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('0 for no limit', 'tbp-core'),
                'condition' => ['show_title' => 'yes'],
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Excerpt', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 120,
                'min' => 0,
                'description' => __('Number of characters', 'tbp-core'),
                'condition' => ['show_excerpt' => 'yes'],
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_author',
            [
                'label' => __('Show Author', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'show_read_more',
            [
                'label' => __('Show Read More', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'read_more_text',
            [
                'label' => __('Read More Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Read More', 'tbp-core'),
                'condition' => ['show_read_more' => 'yes'],
            ]
        );

        $this->end_controls_section();

        // Group Headers Section
        $this->start_controls_section(
            'section_group_headers',
            [
                'label' => __('Group Headers', 'tbp-core'),
                'condition' => ['group_by_post_type' => 'yes'],
            ]
        );

        $this->add_control(
            'group_header_tag',
            [
                'label' => __('Header HTML Tag', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'h2',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'div' => 'div',
                ],
            ]
        );

        $this->add_control(
            'show_result_count',
            [
                'label' => __('Show Result Count', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_view_all_link',
            [
                'label' => __('Show View All Link', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Links to post type archive with search query', 'tbp-core'),
            ]
        );

        $this->add_control(
            'view_all_text',
            [
                'label' => __('View All Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('View All', 'tbp-core'),
                'condition' => ['show_view_all_link' => 'yes'],
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
            'no_results_title',
            [
                'label' => __('Title', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('No results found', 'tbp-core'),
            ]
        );

        $this->add_control(
            'no_results_message',
            [
                'label' => __('Message', 'tbp-core'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Sorry, no results were found for your search. Please try different keywords.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_search_form',
            [
                'label' => __('Show Search Form', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style: Group Header
        $this->start_controls_section(
            'section_style_group_header',
            [
                'label' => __('Group Header', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['group_by_post_type' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'group_header_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__group-title',
            ]
        );

        $this->add_control(
            'group_header_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__group-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'group_header_margin',
            [
                'label' => __('Margin', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__group-header' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'result_count_color',
            [
                'label' => __('Count Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__group-count' => 'color: {{VALUE}};',
                ],
                'condition' => ['show_result_count' => 'yes'],
            ]
        );

        $this->end_controls_section();

        // Style: Card
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => __('Card', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'selector' => '{{WRAPPER}} .tbp-srg__card',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .tbp-srg__card',
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-srg__thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-srg__card',
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => __('Content Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_hover_heading',
            [
                'label' => __('Hover', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'card_hover_transform',
            [
                'label' => __('Hover Effect', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'translate-up',
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'translate-up' => __('Move Up', 'tbp-core'),
                    'scale' => __('Scale', 'tbp-core'),
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow_hover',
                'selector' => '{{WRAPPER}} .tbp-srg__card:hover',
            ]
        );

        $this->end_controls_section();

        // Style: Badge
        $this->start_controls_section(
            'section_style_badge',
            [
                'label' => __('Post Type Badge', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_post_type_badge' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__badge',
            ]
        );

        $this->add_control(
            'badge_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_background',
            [
                'label' => __('Background', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Title
        $this->start_controls_section(
            'section_style_title',
            [
                'label' => __('Title', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_title' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__title a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Excerpt
        $this->start_controls_section(
            'section_style_excerpt',
            [
                'label' => __('Excerpt', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_excerpt' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__excerpt',
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__excerpt' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Meta
        $this->start_controls_section(
            'section_style_meta',
            [
                'label' => __('Meta', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__meta',
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Read More
        $this->start_controls_section(
            'section_style_read_more',
            [
                'label' => __('Read More', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_read_more' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'read_more_typography',
                'selector' => '{{WRAPPER}} .tbp-srg__read-more',
            ]
        );

        $this->start_controls_tabs('read_more_tabs');

        $this->start_controls_tab(
            'read_more_normal',
            ['label' => __('Normal', 'tbp-core')]
        );

        $this->add_control(
            'read_more_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__read-more' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'read_more_hover',
            ['label' => __('Hover', 'tbp-core')]
        );

        $this->add_control(
            'read_more_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__read-more:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Style: No Results
        $this->start_controls_section(
            'section_style_no_results',
            [
                'label' => __('No Results', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'no_results_align',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => ['title' => __('Left', 'tbp-core'), 'icon' => 'eicon-text-align-left'],
                    'center' => ['title' => __('Center', 'tbp-core'), 'icon' => 'eicon-text-align-center'],
                    'right' => ['title' => __('Right', 'tbp-core'), 'icon' => 'eicon-text-align-right'],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__no-results' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'no_results_title_typography',
                'label' => __('Title Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-srg__no-results-title',
            ]
        );

        $this->add_control(
            'no_results_title_color',
            [
                'label' => __('Title Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__no-results-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'no_results_message_typography',
                'label' => __('Message Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-srg__no-results-message',
            ]
        );

        $this->add_control(
            'no_results_message_color',
            [
                'label' => __('Message Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-srg__no-results-message' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_post_types_options() {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $post_type) {
            if ($post_type->name === 'attachment') {
                continue;
            }
            $options[$post_type->name] = $post_type->labels->singular_name;
        }

        return $options;
    }

    private function get_search_results($settings) {
        $search_query = get_search_query();

        // In editor, use sample query
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() && empty($search_query)) {
            $search_query = 'sample';
        }

        $post_types = !empty($settings['post_types']) ? $settings['post_types'] : ['post'];
        $group_by_type = !empty($settings['group_by_post_type']) && $settings['group_by_post_type'] === 'yes';

        $results = [];

        if ($group_by_type) {
            // Query each post type separately
            foreach ($post_types as $post_type) {
                $args = [
                    'post_type' => $post_type,
                    's' => $search_query,
                    'posts_per_page' => !empty($settings['posts_per_group']) ? (int) $settings['posts_per_group'] : 6,
                    'post_status' => 'publish',
                ];

                if ($settings['orderby'] !== 'relevance') {
                    $args['orderby'] = $settings['orderby'];
                    $args['order'] = $settings['order'] ?? 'DESC';
                }

                $query = new \WP_Query($args);

                if ($query->have_posts()) {
                    $post_type_obj = get_post_type_object($post_type);
                    $results[$post_type] = [
                        'label' => $post_type_obj->labels->name,
                        'singular' => $post_type_obj->labels->singular_name,
                        'posts' => $query->posts,
                        'total' => $query->found_posts,
                        'archive_url' => get_post_type_archive_link($post_type),
                    ];
                }

                wp_reset_postdata();
            }
        } else {
            // Single query for all post types
            $args = [
                'post_type' => $post_types,
                's' => $search_query,
                'posts_per_page' => !empty($settings['posts_per_page']) ? (int) $settings['posts_per_page'] : 12,
                'post_status' => 'publish',
            ];

            if ($settings['orderby'] !== 'relevance') {
                $args['orderby'] = $settings['orderby'];
                $args['order'] = $settings['order'] ?? 'DESC';
            }

            $query = new \WP_Query($args);

            if ($query->have_posts()) {
                $results['all'] = [
                    'label' => __('Results', 'tbp-core'),
                    'posts' => $query->posts,
                    'total' => $query->found_posts,
                ];
            }

            wp_reset_postdata();
        }

        return $results;
    }

    private function truncate_text($text, $length) {
        if ($length <= 0 || strlen($text) <= $length) {
            return $text;
        }
        return rtrim(substr($text, 0, $length)) . '…';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $results = $this->get_search_results($settings);

        $group_by_type = !empty($settings['group_by_post_type']) && $settings['group_by_post_type'] === 'yes';
        $equal_height = !empty($settings['equal_height']) && $settings['equal_height'] === 'yes';
        $hover_effect = $settings['card_hover_transform'] ?? 'none';

        $show_thumbnail = !empty($settings['show_thumbnail']) && $settings['show_thumbnail'] === 'yes';
        $show_badge = !empty($settings['show_post_type_badge']) && $settings['show_post_type_badge'] === 'yes';
        $show_title = !empty($settings['show_title']) && $settings['show_title'] === 'yes';
        $show_excerpt = !empty($settings['show_excerpt']) && $settings['show_excerpt'] === 'yes';
        $show_date = !empty($settings['show_date']) && $settings['show_date'] === 'yes';
        $show_author = !empty($settings['show_author']) && $settings['show_author'] === 'yes';
        $show_read_more = !empty($settings['show_read_more']) && $settings['show_read_more'] === 'yes';
        $show_result_count = !empty($settings['show_result_count']) && $settings['show_result_count'] === 'yes';
        $show_view_all = !empty($settings['show_view_all_link']) && $settings['show_view_all_link'] === 'yes';

        $title_tag = $settings['title_tag'] ?? 'h3';
        $title_length = !empty($settings['title_length']) ? (int) $settings['title_length'] : 0;
        $excerpt_length = !empty($settings['excerpt_length']) ? (int) $settings['excerpt_length'] : 120;
        $group_header_tag = $settings['group_header_tag'] ?? 'h2';

        // No results
        if (empty($results)) {
            $this->render_no_results($settings);
            return;
        }

        $container_class = 'tbp-srg';
        if ($equal_height) {
            $container_class .= ' tbp-srg--equal-height';
        }
        if ($hover_effect !== 'none') {
            $container_class .= ' tbp-srg--hover-' . $hover_effect;
        }

        ?>
        <div class="<?php echo esc_attr($container_class); ?>" role="region" aria-label="<?php esc_attr_e('Search Results', 'tbp-core'); ?>">
            <?php foreach ($results as $type_key => $group) : ?>
                <?php if ($group_by_type && $type_key !== 'all') : ?>
                    <section class="tbp-srg__group" aria-labelledby="group-<?php echo esc_attr($type_key); ?>">
                        <header class="tbp-srg__group-header">
                            <<?php echo esc_html($group_header_tag); ?> class="tbp-srg__group-title" id="group-<?php echo esc_attr($type_key); ?>">
                                <?php echo esc_html($group['label']); ?>
                                <?php if ($show_result_count) : ?>
                                    <span class="tbp-srg__group-count">(<?php echo esc_html($group['total']); ?>)</span>
                                <?php endif; ?>
                            </<?php echo esc_html($group_header_tag); ?>>
                            <?php if ($show_view_all && !empty($group['archive_url']) && $group['total'] > count($group['posts'])) : ?>
                                <a href="<?php echo esc_url(add_query_arg('s', get_search_query(), $group['archive_url'])); ?>" class="tbp-srg__view-all">
                                    <?php echo esc_html($settings['view_all_text'] ?? __('View All', 'tbp-core')); ?>
                                    <span class="screen-reader-text"><?php echo esc_html($group['label']); ?></span>
                                </a>
                            <?php endif; ?>
                        </header>
                <?php endif; ?>

                <div class="tbp-srg__grid" role="list">
                    <?php foreach ($group['posts'] as $post_item) : ?>
                        <?php
                        $post_type_obj = get_post_type_object($post_item->post_type);
                        $permalink = get_permalink($post_item->ID);
                        $thumbnail_id = get_post_thumbnail_id($post_item->ID);
                        ?>
                        <article class="tbp-srg__card" role="listitem">
                            <a href="<?php echo esc_url($permalink); ?>" class="tbp-srg__link" aria-labelledby="post-title-<?php echo esc_attr($post_item->ID); ?>">
                                <?php if ($show_thumbnail) : ?>
                                    <div class="tbp-srg__thumb">
                                        <?php if ($thumbnail_id) :
                                            echo wp_get_attachment_image($thumbnail_id, $settings['thumbnail_size'] ?? 'medium', false, ['class' => 'tbp-srg__img', 'loading' => 'lazy']);
                                        else : ?>
                                            <div class="tbp-srg__no-thumb" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <path d="M21 15l-5-5L5 21"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($show_badge) : ?>
                                            <span class="tbp-srg__badge"><?php echo esc_html($post_type_obj->labels->singular_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="tbp-srg__content">
                                    <?php if ($show_title) :
                                        $title = $title_length > 0 ? $this->truncate_text($post_item->post_title, $title_length) : $post_item->post_title;
                                    ?>
                                        <<?php echo esc_html($title_tag); ?> class="tbp-srg__title" id="post-title-<?php echo esc_attr($post_item->ID); ?>">
                                            <?php echo esc_html($title); ?>
                                        </<?php echo esc_html($title_tag); ?>>
                                    <?php endif; ?>

                                    <?php if ($show_date || $show_author) : ?>
                                        <div class="tbp-srg__meta">
                                            <?php if ($show_date) : ?>
                                                <time datetime="<?php echo esc_attr(get_the_date('c', $post_item->ID)); ?>">
                                                    <?php echo esc_html(get_the_date('', $post_item->ID)); ?>
                                                </time>
                                            <?php endif; ?>
                                            <?php if ($show_date && $show_author) : ?>
                                                <span class="tbp-srg__meta-sep" aria-hidden="true">•</span>
                                            <?php endif; ?>
                                            <?php if ($show_author) : ?>
                                                <span class="tbp-srg__author"><?php echo esc_html(get_the_author_meta('display_name', $post_item->post_author)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($show_excerpt) :
                                        $excerpt = !empty($post_item->post_excerpt) ? $post_item->post_excerpt : wp_strip_all_tags($post_item->post_content);
                                        $excerpt = $this->truncate_text($excerpt, $excerpt_length);
                                    ?>
                                        <p class="tbp-srg__excerpt"><?php echo esc_html($excerpt); ?></p>
                                    <?php endif; ?>

                                    <?php if ($show_read_more) : ?>
                                        <span class="tbp-srg__read-more">
                                            <?php echo esc_html($settings['read_more_text'] ?? __('Read More', 'tbp-core')); ?>
                                            <span class="screen-reader-text">: <?php echo esc_html($post_item->post_title); ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($group_by_type && $type_key !== 'all') : ?>
                    </section>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_no_results($settings) {
        ?>
        <div class="tbp-srg__no-results" role="status">
            <?php if (!empty($settings['no_results_title'])) : ?>
                <h2 class="tbp-srg__no-results-title"><?php echo esc_html($settings['no_results_title']); ?></h2>
            <?php endif; ?>

            <?php if (!empty($settings['no_results_message'])) : ?>
                <p class="tbp-srg__no-results-message"><?php echo esc_html($settings['no_results_message']); ?></p>
            <?php endif; ?>

            <?php if (!empty($settings['show_search_form']) && $settings['show_search_form'] === 'yes') : ?>
                <div class="tbp-srg__search-form">
                    <?php get_search_form(); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var groupByType = settings.group_by_post_type === 'yes';
        var equalHeight = settings.equal_height === 'yes';
        var hoverEffect = settings.card_hover_transform || 'none';

        var containerClass = 'tbp-srg';
        if (equalHeight) containerClass += ' tbp-srg--equal-height';
        if (hoverEffect !== 'none') containerClass += ' tbp-srg--hover-' + hoverEffect;
        #>
        <div class="{{ containerClass }}">
            <# if (groupByType) { #>
            <section class="tbp-srg__group">
                <header class="tbp-srg__group-header">
                    <{{ settings.group_header_tag || 'h2' }} class="tbp-srg__group-title">
                        <?php esc_html_e('Posts', 'tbp-core'); ?>
                        <# if (settings.show_result_count === 'yes') { #>
                        <span class="tbp-srg__group-count">(3)</span>
                        <# } #>
                    </{{ settings.group_header_tag || 'h2' }}>
                </header>
            <# } #>

            <div class="tbp-srg__grid" role="list">
                <# for (var i = 0; i < 3; i++) { #>
                <article class="tbp-srg__card" role="listitem">
                    <a href="#" class="tbp-srg__link">
                        <# if (settings.show_thumbnail === 'yes') { #>
                        <div class="tbp-srg__thumb">
                            <div class="tbp-srg__no-thumb">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="M21 15l-5-5L5 21"/>
                                </svg>
                            </div>
                            <# if (settings.show_post_type_badge === 'yes') { #>
                            <span class="tbp-srg__badge"><?php esc_html_e('Post', 'tbp-core'); ?></span>
                            <# } #>
                        </div>
                        <# } #>

                        <div class="tbp-srg__content">
                            <# if (settings.show_title === 'yes') { #>
                            <{{ settings.title_tag || 'h3' }} class="tbp-srg__title">
                                <?php esc_html_e('Sample Post Title', 'tbp-core'); ?>
                            </{{ settings.title_tag || 'h3' }}>
                            <# } #>

                            <# if (settings.show_date === 'yes' || settings.show_author === 'yes') { #>
                            <div class="tbp-srg__meta">
                                <# if (settings.show_date === 'yes') { #>
                                <time><?php echo esc_html(date_i18n(get_option('date_format'))); ?></time>
                                <# } #>
                                <# if (settings.show_date === 'yes' && settings.show_author === 'yes') { #>
                                <span class="tbp-srg__meta-sep">•</span>
                                <# } #>
                                <# if (settings.show_author === 'yes') { #>
                                <span class="tbp-srg__author"><?php esc_html_e('Author Name', 'tbp-core'); ?></span>
                                <# } #>
                            </div>
                            <# } #>

                            <# if (settings.show_excerpt === 'yes') { #>
                            <p class="tbp-srg__excerpt"><?php esc_html_e('This is a sample excerpt text that shows how the content will look in the search results grid.', 'tbp-core'); ?></p>
                            <# } #>

                            <# if (settings.show_read_more === 'yes') { #>
                            <span class="tbp-srg__read-more">{{ settings.read_more_text || '<?php esc_html_e('Read More', 'tbp-core'); ?>' }}</span>
                            <# } #>
                        </div>
                    </a>
                </article>
                <# } #>
            </div>

            <# if (groupByType) { #>
            </section>
            <# } #>
        </div>
        <?php
    }
}
