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

class Alphabet_Filter extends Widget_Base {

    public function get_name() {
        return 'tbp-alphabet-filter';
    }

    public function get_title() {
        return __('Alphabet Filter', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-filter';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['alphabet', 'filter', 'a-z', 'letters', 'catalog', 'archive', 'search'];
    }

    public function get_style_depends() {
        return ['tbp-alphabet-filter'];
    }

    public function get_script_depends() {
        return ['tbp-alphabet-filter'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function register_content_controls() {
        // General Section
        $this->start_controls_section(
            'section_general',
            [
                'label' => __('General', 'tbp-core'),
            ]
        );

        $this->add_control(
            'filter_method',
            [
                'label' => __('Filter Method', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'url',
                'options' => [
                    'url' => __('URL Parameters (Page Reload)', 'tbp-core'),
                    'ajax' => __('AJAX (No Reload)', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'ajax_target',
            [
                'label' => __('AJAX Target Selector', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '.elementor-posts-container',
                'placeholder' => '.your-posts-container',
                'description' => __('CSS selector for the container to update with filtered posts', 'tbp-core'),
                'condition' => [
                    'filter_method' => 'ajax',
                ],
            ]
        );

        $this->add_control(
            'show_inactive',
            [
                'label' => __('Show Inactive Letters', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => __('Show letters that have no posts', 'tbp-core'),
            ]
        );

        $this->end_controls_section();

        // Search Section
        $this->start_controls_section(
            'section_search',
            [
                'label' => __('Search', 'tbp-core'),
            ]
        );

        $this->add_control(
            'show_search',
            [
                'label' => __('Show Search', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'search_placeholder',
            [
                'label' => __('Placeholder', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Search...', 'tbp-core'),
                'condition' => [
                    'show_search' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Buttons Section
        $this->start_controls_section(
            'section_buttons',
            [
                'label' => __('Buttons', 'tbp-core'),
            ]
        );

        $this->add_control(
            'all_button_text',
            [
                'label' => __('All Button Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('All', 'tbp-core'),
            ]
        );

        $this->add_control(
            'clear_button_text',
            [
                'label' => __('Clear Button Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Clear', 'tbp-core'),
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
                'default' => '4',
                'tablet_default' => '6',
                'mobile_default' => '7',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '13' => '13',
                    '14' => '14',
                    '27' => '27 (Single Row)',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
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
                    '{{WRAPPER}} .tbp-alphabet-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
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
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-filter' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // Letter Style Section
        $this->start_controls_section(
            'section_letter_style',
            [
                'label' => __('Letters', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'letter_typography',
                'selector' => '{{WRAPPER}} .tbp-alphabet-letter',
            ]
        );

        $this->add_responsive_control(
            'letter_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'letter_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('letter_tabs');

        // Normal State
        $this->start_controls_tab(
            'letter_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'letter_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive)' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'letter_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'letter_border',
                'selector' => '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive)',
            ]
        );

        $this->end_controls_tab();

        // Hover State
        $this->start_controls_tab(
            'letter_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'letter_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive):hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'letter_bg_color_hover',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive):hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'letter_border_color_hover',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter:not(.is-inactive):hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Active State
        $this->start_controls_tab(
            'letter_active',
            [
                'label' => __('Active', 'tbp-core'),
            ]
        );

        $this->add_control(
            'letter_color_active',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'letter_bg_color_active',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'letter_border_color_active',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-active' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'letter_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-alphabet-letter',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        // Inactive Letters Style Section
        $this->start_controls_section(
            'section_inactive_style',
            [
                'label' => __('Inactive Letters', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_inactive' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'inactive_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ccc',
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-inactive' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'inactive_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-inactive' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'inactive_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-inactive' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'inactive_opacity',
            [
                'label' => __('Opacity', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 0.5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-letter.is-inactive' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Search Style Section
        $this->start_controls_section(
            'section_search_style',
            [
                'label' => __('Search', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_search' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'search_typography',
                'selector' => '{{WRAPPER}} .tbp-alphabet-search input',
            ]
        );

        $this->add_responsive_control(
            'search_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-search input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'search_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-search input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'search_placeholder_color',
            [
                'label' => __('Placeholder Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-search input::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'search_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-search input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'search_border',
                'selector' => '{{WRAPPER}} .tbp-alphabet-search input',
            ]
        );

        $this->add_responsive_control(
            'search_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-search input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Buttons Style Section (All & Clear)
        $this->start_controls_section(
            'section_buttons_style',
            [
                'label' => __('Buttons (All & Clear)', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'buttons_typography',
                'selector' => '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear',
            ]
        );

        $this->add_responsive_control(
            'buttons_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('buttons_tabs');

        $this->start_controls_tab(
            'buttons_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'buttons_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'buttons_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'buttons_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'buttons_color_hover',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all:hover, {{WRAPPER}} .tbp-alphabet-clear:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'buttons_bg_color_hover',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all:hover, {{WRAPPER}} .tbp-alphabet-clear:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'buttons_active',
            [
                'label' => __('Active', 'tbp-core'),
            ]
        );

        $this->add_control(
            'buttons_color_active',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all.is-active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'buttons_bg_color_active',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all.is-active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'buttons_border',
                'selector' => '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'buttons_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-alphabet-all, {{WRAPPER}} .tbp-alphabet-clear' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get active letters (letters that have posts)
     */
    private function get_active_letters() {
        global $wpdb;

        $post_type = get_post_type();
        if (!$post_type) {
            $post_type = 'post';
        }

        // Handle archive pages
        if (is_post_type_archive()) {
            $post_type = get_query_var('post_type');
        }

        $query = $wpdb->prepare(
            "SELECT DISTINCT UPPER(SUBSTRING(post_title, 1, 1)) as first_letter
             FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = 'publish'
             ORDER BY first_letter",
            $post_type
        );

        $results = $wpdb->get_col($query);

        return array_filter($results, function($letter) {
            return ctype_alpha($letter);
        });
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $alphabet = range('A', 'Z');
        $active_letters = $this->get_active_letters();
        $current_letter = isset($_GET['letter']) ? strtoupper(sanitize_text_field($_GET['letter'])) : '';
        $current_search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $show_inactive = !empty($settings['show_inactive']) && $settings['show_inactive'] === 'yes';
        $show_search = !empty($settings['show_search']) && $settings['show_search'] === 'yes';
        $filter_method = $settings['filter_method'] ?? 'url';

        // Get current URL for building filter links
        $current_url = remove_query_arg(['letter', 'search']);

        // Check if any filter is active
        $has_active_filter = !empty($current_letter) || !empty($current_search);

        // Data attributes for JS
        $data_attrs = [
            'data-method="' . esc_attr($filter_method) . '"',
        ];

        if ($filter_method === 'ajax') {
            $data_attrs[] = 'data-target="' . esc_attr($settings['ajax_target'] ?? '.elementor-posts-container') . '"';
            $data_attrs[] = 'data-post-type="' . esc_attr(get_post_type() ?: 'post') . '"';

            // Add taxonomy info if on taxonomy archive
            if (is_tax() || is_category() || is_tag()) {
                $queried_object = get_queried_object();
                if ($queried_object) {
                    $data_attrs[] = 'data-taxonomy="' . esc_attr($queried_object->taxonomy) . '"';
                    $data_attrs[] = 'data-term="' . esc_attr($queried_object->slug) . '"';
                }
            }
        }

        ?>
        <div class="tbp-alphabet-filter" <?php echo implode(' ', $data_attrs); ?>>
            <?php // Search Input (Full Width) ?>
            <?php if ($show_search) : ?>
                <div class="tbp-alphabet-search">
                    <input type="text"
                           name="search"
                           placeholder="<?php echo esc_attr($settings['search_placeholder'] ?? __('Search...', 'tbp-core')); ?>"
                           value="<?php echo esc_attr($current_search); ?>"
                           autocomplete="off">
                </div>
            <?php endif; ?>

            <?php // All Button (Full Width) ?>
            <a href="<?php echo esc_url($current_url); ?>"
               class="tbp-alphabet-all<?php echo !$has_active_filter ? ' is-active' : ''; ?>"
               data-letter="">
                <?php echo esc_html($settings['all_button_text'] ?? __('All', 'tbp-core')); ?>
            </a>

            <?php // Letters Grid (4 Columns) ?>
            <div class="tbp-alphabet-grid">
                <?php foreach ($alphabet as $letter) :
                    $is_active = in_array($letter, $active_letters);
                    $is_current = ($letter === $current_letter);

                    if (!$is_active && !$show_inactive) {
                        continue;
                    }

                    $classes = ['tbp-alphabet-letter'];
                    if (!$is_active) {
                        $classes[] = 'is-inactive';
                    }
                    if ($is_current) {
                        $classes[] = 'is-active';
                    }

                    $href = $is_active ? add_query_arg('letter', strtolower($letter), $current_url) : '#';

                    // Preserve search if exists
                    if ($is_active && !empty($current_search)) {
                        $href = add_query_arg('search', $current_search, $href);
                    }

                    $tag = $is_active ? 'a' : 'span';
                    ?>
                    <?php if ($tag === 'a') : ?>
                        <a href="<?php echo esc_url($href); ?>"
                           class="<?php echo esc_attr(implode(' ', $classes)); ?>"
                           data-letter="<?php echo esc_attr($letter); ?>">
                            <?php echo esc_html($letter); ?>
                        </a>
                    <?php else : ?>
                        <span class="<?php echo esc_attr(implode(' ', $classes)); ?>">
                            <?php echo esc_html($letter); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php // Clear Button (Full Width) ?>
            <a href="<?php echo esc_url($current_url); ?>" class="tbp-alphabet-clear">
                <?php echo esc_html($settings['clear_button_text'] ?? __('Clear', 'tbp-core')); ?>
            </a>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
        var allText = settings.all_button_text || 'All';
        var clearText = settings.clear_button_text || 'Clear';
        var showSearch = settings.show_search === 'yes';
        var searchPlaceholder = settings.search_placeholder || 'Search...';
        #>
        <div class="tbp-alphabet-filter">
            <# if (showSearch) { #>
                <div class="tbp-alphabet-search">
                    <input type="text" placeholder="{{ searchPlaceholder }}" autocomplete="off">
                </div>
            <# } #>

            <a href="#" class="tbp-alphabet-all is-active">{{{ allText }}}</a>

            <div class="tbp-alphabet-grid">
                <# _.each(alphabet, function(letter) { #>
                    <a href="#" class="tbp-alphabet-letter" data-letter="{{ letter }}">{{{ letter }}}</a>
                <# }); #>
            </div>

            <a href="#" class="tbp-alphabet-clear">{{{ clearText }}}</a>
        </div>
        <?php
    }
}
