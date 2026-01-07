<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class Breadcrumbs extends Widget_Base {

    public function get_name() {
        return 'tbp-breadcrumbs';
    }

    public function get_title() {
        return __('Breadcrumbs', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-post-navigation';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['breadcrumbs', 'navigation', 'path', 'trail', 'seo'];
    }

    public function get_style_depends() {
        return ['tbp-breadcrumbs'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_breadcrumbs',
            [
                'label' => __('Breadcrumbs', 'tbp-core'),
            ]
        );

        $this->add_control(
            'source',
            [
                'label' => __('Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'auto',
                'options' => [
                    'auto' => __('Auto (RankMath if available)', 'tbp-core'),
                    'custom' => __('Custom', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'show_home',
            [
                'label' => __('Show Home', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'home_text',
            [
                'label' => __('Home Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Home', 'tbp-core'),
                'condition' => [
                    'show_home' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'home_icon',
            [
                'label' => __('Home Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-home',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_home' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'home_icon_only',
            [
                'label' => __('Icon Only (Hide Text)', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => [
                    'show_home' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'separator_type',
            [
                'label' => __('Separator Type', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'icon',
                'options' => [
                    'text' => __('Text', 'tbp-core'),
                    'icon' => __('Icon', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'separator_text',
            [
                'label' => __('Separator', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '/',
                'condition' => [
                    'separator_type' => 'text',
                ],
            ]
        );

        $this->add_control(
            'separator_icon',
            [
                'label' => __('Separator Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-chevron-right',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'separator_type' => 'icon',
                ],
            ]
        );

        $this->add_control(
            'schema_markup',
            [
                'label' => __('Schema Markup', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => __('Add structured data for SEO', 'tbp-core'),
            ]
        );

        $this->end_controls_section();

        // Truncation Section
        $this->start_controls_section(
            'section_truncation',
            [
                'label' => __('Truncation (Ellipsis)', 'tbp-core'),
            ]
        );

        $this->add_control(
            'enable_truncation',
            [
                'label' => __('Enable Truncation', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'max_characters',
            [
                'label' => __('Max Characters', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 5,
                'max' => 100,
                'condition' => [
                    'enable_truncation' => 'yes',
                ],
                'description' => __('Including spaces', 'tbp-core'),
            ]
        );

        $this->add_control(
            'truncate_apply',
            [
                'label' => __('Apply To', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'middle',
                'options' => [
                    'all' => __('All Items', 'tbp-core'),
                    'middle' => __('Middle Items Only', 'tbp-core'),
                    'last' => __('Last Item Only', 'tbp-core'),
                ],
                'condition' => [
                    'enable_truncation' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Style - General
        $this->start_controls_section(
            'section_style_general',
            [
                'label' => __('General', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'alignment',
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
                    '{{WRAPPER}} .tbp-breadcrumbs' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label' => __('Item Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumbs' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style - Links
        $this->start_controls_section(
            'section_style_links',
            [
                'label' => __('Links', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'links_typography',
                'selector' => '{{WRAPPER}} .tbp-breadcrumb-item a',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->start_controls_tabs('tabs_links_style');

        $this->start_controls_tab(
            'tab_links_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'links_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-item a' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_links_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'links_hover_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-item a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Style - Current
        $this->start_controls_section(
            'section_style_current',
            [
                'label' => __('Current Item', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'current_typography',
                'selector' => '{{WRAPPER}} .tbp-breadcrumb-item.is-current span',
            ]
        );

        $this->add_control(
            'current_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-item.is-current span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style - Separator
        $this->start_controls_section(
            'section_style_separator',
            [
                'label' => __('Separator', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'separator_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-separator' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'separator_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 30,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-separator' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style - Home Icon
        $this->start_controls_section(
            'section_style_home_icon',
            [
                'label' => __('Home Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_home' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'home_icon_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 40,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-home-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-breadcrumb-home-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'home_icon_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-breadcrumb-home-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'home_icon_only' => '',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Truncate text with ellipsis
     */
    private function truncate_text($text, $max_chars) {
        if (mb_strlen($text) <= $max_chars) {
            return $text;
        }
        return mb_substr($text, 0, $max_chars) . '...';
    }

    /**
     * Get breadcrumbs from RankMath
     */
    private function get_rankmath_breadcrumbs() {
        if (!function_exists('rank_math_get_breadcrumbs')) {
            return null;
        }

        $crumbs = rank_math_get_breadcrumbs();
        if (empty($crumbs)) {
            return null;
        }

        $items = [];
        foreach ($crumbs as $crumb) {
            $items[] = [
                'text' => $crumb[0] ?? '',
                'url' => $crumb[1] ?? '',
            ];
        }

        return $items;
    }

    /**
     * Build custom breadcrumbs
     */
    private function get_custom_breadcrumbs($settings) {
        $items = [];

        // Home
        if ($settings['show_home'] === 'yes') {
            $items[] = [
                'text' => $settings['home_text'],
                'url' => home_url('/'),
                'is_home' => true,
            ];
        }

        if (is_front_page()) {
            return $items;
        }

        // Blog page
        if (is_home() && !is_front_page()) {
            $blog_page_id = get_option('page_for_posts');
            if ($blog_page_id) {
                $items[] = [
                    'text' => get_the_title($blog_page_id),
                    'url' => '',
                ];
            }
            return $items;
        }

        // Single post
        if (is_single()) {
            $post_type = get_post_type();

            // Post type archive
            if ($post_type !== 'post') {
                $post_type_obj = get_post_type_object($post_type);
                if ($post_type_obj && $post_type_obj->has_archive) {
                    $items[] = [
                        'text' => $post_type_obj->labels->name,
                        'url' => get_post_type_archive_link($post_type),
                    ];
                }
            }

            // Categories (for posts)
            if ($post_type === 'post') {
                $categories = get_the_category();
                if (!empty($categories)) {
                    $cat = $categories[0];
                    // Parent categories
                    if ($cat->parent) {
                        $parent_cats = get_ancestors($cat->term_id, 'category');
                        $parent_cats = array_reverse($parent_cats);
                        foreach ($parent_cats as $parent_id) {
                            $parent = get_term($parent_id, 'category');
                            $items[] = [
                                'text' => $parent->name,
                                'url' => get_term_link($parent),
                            ];
                        }
                    }
                    $items[] = [
                        'text' => $cat->name,
                        'url' => get_term_link($cat),
                    ];
                }
            }

            // Primary taxonomy for CPT
            if ($post_type !== 'post') {
                $taxonomies = get_object_taxonomies($post_type, 'objects');
                foreach ($taxonomies as $tax_slug => $tax) {
                    if (!$tax->hierarchical) continue;
                    $terms = get_the_terms(get_the_ID(), $tax_slug);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        $term = $terms[0];
                        if ($term->parent) {
                            $parent_terms = get_ancestors($term->term_id, $tax_slug);
                            $parent_terms = array_reverse($parent_terms);
                            foreach ($parent_terms as $parent_id) {
                                $parent = get_term($parent_id, $tax_slug);
                                $items[] = [
                                    'text' => $parent->name,
                                    'url' => get_term_link($parent),
                                ];
                            }
                        }
                        $items[] = [
                            'text' => $term->name,
                            'url' => get_term_link($term),
                        ];
                        break;
                    }
                }
            }

            // Current post
            $items[] = [
                'text' => get_the_title(),
                'url' => '',
            ];
        }

        // Page
        if (is_page() && !is_front_page()) {
            $page_id = get_the_ID();
            $ancestors = get_ancestors($page_id, 'page');

            if (!empty($ancestors)) {
                $ancestors = array_reverse($ancestors);
                foreach ($ancestors as $ancestor_id) {
                    $items[] = [
                        'text' => get_the_title($ancestor_id),
                        'url' => get_permalink($ancestor_id),
                    ];
                }
            }

            $items[] = [
                'text' => get_the_title(),
                'url' => '',
            ];
        }

        // Category/Tag/Taxonomy archive
        if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();

            if (is_tax()) {
                $taxonomy = get_taxonomy($term->taxonomy);
                if ($taxonomy) {
                    // Check if taxonomy is attached to a CPT
                    $post_types = $taxonomy->object_type;
                    if (!empty($post_types)) {
                        $post_type = $post_types[0];
                        if ($post_type !== 'post') {
                            $post_type_obj = get_post_type_object($post_type);
                            if ($post_type_obj && $post_type_obj->has_archive) {
                                $items[] = [
                                    'text' => $post_type_obj->labels->name,
                                    'url' => get_post_type_archive_link($post_type),
                                ];
                            }
                        }
                    }
                }
            }

            // Parent terms
            if ($term->parent) {
                $parent_terms = get_ancestors($term->term_id, $term->taxonomy);
                $parent_terms = array_reverse($parent_terms);
                foreach ($parent_terms as $parent_id) {
                    $parent = get_term($parent_id, $term->taxonomy);
                    $items[] = [
                        'text' => $parent->name,
                        'url' => get_term_link($parent),
                    ];
                }
            }

            $items[] = [
                'text' => $term->name,
                'url' => '',
            ];
        }

        // Post type archive
        if (is_post_type_archive()) {
            $post_type = get_queried_object();
            $items[] = [
                'text' => $post_type->labels->name,
                'url' => '',
            ];
        }

        // Author archive
        if (is_author()) {
            $items[] = [
                'text' => get_the_author(),
                'url' => '',
            ];
        }

        // Date archives
        if (is_date()) {
            if (is_year()) {
                $items[] = [
                    'text' => get_the_date('Y'),
                    'url' => '',
                ];
            } elseif (is_month()) {
                $items[] = [
                    'text' => get_the_date('Y'),
                    'url' => get_year_link(get_the_date('Y')),
                ];
                $items[] = [
                    'text' => get_the_date('F'),
                    'url' => '',
                ];
            } elseif (is_day()) {
                $items[] = [
                    'text' => get_the_date('Y'),
                    'url' => get_year_link(get_the_date('Y')),
                ];
                $items[] = [
                    'text' => get_the_date('F'),
                    'url' => get_month_link(get_the_date('Y'), get_the_date('m')),
                ];
                $items[] = [
                    'text' => get_the_date('d'),
                    'url' => '',
                ];
            }
        }

        // Search
        if (is_search()) {
            $items[] = [
                'text' => sprintf(__('Search: %s', 'tbp-core'), get_search_query()),
                'url' => '',
            ];
        }

        // 404
        if (is_404()) {
            $items[] = [
                'text' => __('Page Not Found', 'tbp-core'),
                'url' => '',
            ];
        }

        return $items;
    }

    /**
     * Render separator
     */
    private function render_separator($settings) {
        echo '<span class="tbp-breadcrumb-separator">';
        if ($settings['separator_type'] === 'icon' && !empty($settings['separator_icon']['value'])) {
            \Elementor\Icons_Manager::render_icon($settings['separator_icon'], ['aria-hidden' => 'true']);
        } else {
            echo esc_html($settings['separator_text']);
        }
        echo '</span>';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Get breadcrumbs
        $items = null;

        if ($settings['source'] === 'auto' && function_exists('rank_math_get_breadcrumbs')) {
            $items = $this->get_rankmath_breadcrumbs();
        }

        if (empty($items)) {
            $items = $this->get_custom_breadcrumbs($settings);
        }

        if (empty($items)) {
            return;
        }

        $enable_truncation = $settings['enable_truncation'] === 'yes';
        $max_chars = intval($settings['max_characters']);
        $truncate_apply = $settings['truncate_apply'];
        $total_items = count($items);

        // Schema markup
        $schema = $settings['schema_markup'] === 'yes';

        ?>
        <nav class="tbp-breadcrumbs-wrapper" aria-label="<?php esc_attr_e('Breadcrumb', 'tbp-core'); ?>">
            <?php if ($schema) : ?>
            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [
                    <?php
                    $schema_items = [];
                    foreach ($items as $index => $item) {
                        $position = $index + 1;
                        $schema_item = '{
                            "@type": "ListItem",
                            "position": ' . $position . ',
                            "name": "' . esc_js($item['text']) . '"';
                        if (!empty($item['url'])) {
                            $schema_item .= ',
                            "item": "' . esc_url($item['url']) . '"';
                        }
                        $schema_item .= '}';
                        $schema_items[] = $schema_item;
                    }
                    echo implode(",\n", $schema_items);
                    ?>
                ]
            }
            </script>
            <?php endif; ?>

            <ol class="tbp-breadcrumbs">
                <?php foreach ($items as $index => $item) :
                    $is_first = $index === 0;
                    $is_last = $index === $total_items - 1;
                    $is_middle = !$is_first && !$is_last;
                    $is_home = !empty($item['is_home']);
                    $has_url = !empty($item['url']);

                    // Determine if truncation applies to this item
                    $should_truncate = false;
                    if ($enable_truncation) {
                        if ($truncate_apply === 'all') {
                            $should_truncate = true;
                        } elseif ($truncate_apply === 'middle' && $is_middle) {
                            $should_truncate = true;
                        } elseif ($truncate_apply === 'last' && $is_last) {
                            $should_truncate = true;
                        }
                    }

                    $display_text = $item['text'];
                    $full_text = $item['text'];

                    if ($should_truncate) {
                        $display_text = $this->truncate_text($item['text'], $max_chars);
                    }

                    $item_class = 'tbp-breadcrumb-item';
                    if ($is_last) {
                        $item_class .= ' is-current';
                    }
                ?>
                    <?php if (!$is_first) : ?>
                        <?php $this->render_separator($settings); ?>
                    <?php endif; ?>

                    <li class="<?php echo esc_attr($item_class); ?>">
                        <?php if ($has_url) : ?>
                            <a href="<?php echo esc_url($item['url']); ?>"<?php echo ($should_truncate && $display_text !== $full_text) ? ' title="' . esc_attr($full_text) . '"' : ''; ?>>
                        <?php else : ?>
                            <span<?php echo ($should_truncate && $display_text !== $full_text) ? ' title="' . esc_attr($full_text) . '"' : ''; ?>>
                        <?php endif; ?>

                        <?php if ($is_home && !empty($settings['home_icon']['value'])) : ?>
                            <span class="tbp-breadcrumb-home-icon">
                                <?php \Elementor\Icons_Manager::render_icon($settings['home_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                            <?php if ($settings['home_icon_only'] !== 'yes') : ?>
                                <?php echo esc_html($display_text); ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php echo esc_html($display_text); ?>
                        <?php endif; ?>

                        <?php if ($has_url) : ?>
                            </a>
                        <?php else : ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }

    protected function content_template() {
        ?>
        <nav class="tbp-breadcrumbs-wrapper">
            <ol class="tbp-breadcrumbs">
                <li class="tbp-breadcrumb-item">
                    <a href="#">
                        <# if (settings.show_home === 'yes' && settings.home_icon?.value) { #>
                            <span class="tbp-breadcrumb-home-icon">
                                <i class="{{ settings.home_icon.value }}" aria-hidden="true"></i>
                            </span>
                        <# } #>
                        <# if (settings.home_icon_only !== 'yes') { #>
                            {{{ settings.home_text || 'Home' }}}
                        <# } #>
                    </a>
                </li>
                <span class="tbp-breadcrumb-separator">
                    <# if (settings.separator_type === 'icon' && settings.separator_icon?.value) { #>
                        <i class="{{ settings.separator_icon.value }}" aria-hidden="true"></i>
                    <# } else { #>
                        {{{ settings.separator_text || '/' }}}
                    <# } #>
                </span>
                <li class="tbp-breadcrumb-item">
                    <a href="#"><?php _e('Parent Page', 'tbp-core'); ?></a>
                </li>
                <span class="tbp-breadcrumb-separator">
                    <# if (settings.separator_type === 'icon' && settings.separator_icon?.value) { #>
                        <i class="{{ settings.separator_icon.value }}" aria-hidden="true"></i>
                    <# } else { #>
                        {{{ settings.separator_text || '/' }}}
                    <# } #>
                </span>
                <li class="tbp-breadcrumb-item is-current">
                    <span><?php _e('Current Page', 'tbp-core'); ?></span>
                </li>
            </ol>
        </nav>
        <?php
    }
}
