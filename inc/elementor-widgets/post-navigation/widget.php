<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Post_Navigation extends Widget_Base {

    public function get_name() {
        return 'tbp-post-navigation';
    }

    public function get_title() {
        return __('Post Navigation', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-post-navigation';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['post', 'navigation', 'next', 'previous', 'prev', 'link'];
    }

    public function get_style_depends() {
        return ['tbp-post-navigation'];
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
            'show_label',
            [
                'label' => __('Show Label', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'prev_label',
            [
                'label' => __('Previous Label', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Previous', 'tbp-core'),
                'condition' => ['show_label' => 'yes'],
            ]
        );

        $this->add_control(
            'next_label',
            [
                'label' => __('Next Label', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Next', 'tbp-core'),
                'condition' => ['show_label' => 'yes'],
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Post Title', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'title_length',
            [
                'label' => __('Title Length', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 50,
                'min' => 10,
                'max' => 200,
                'description' => __('Maximum characters for title.', 'tbp-core'),
                'condition' => ['show_title' => 'yes'],
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

        $this->add_control(
            'thumbnail_size',
            [
                'label' => __('Thumbnail Size', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'thumbnail',
                'options' => $this->get_image_sizes(),
                'condition' => ['show_thumbnail' => 'yes'],
            ]
        );

        $this->add_control(
            'show_arrow',
            [
                'label' => __('Show Arrows', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'prev_icon',
            [
                'label' => __('Previous Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-left',
                    'library' => 'fa-solid',
                ],
                'condition' => ['show_arrow' => 'yes'],
            ]
        );

        $this->add_control(
            'next_icon',
            [
                'label' => __('Next Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'fa-solid',
                ],
                'condition' => ['show_arrow' => 'yes'],
            ]
        );

        $this->add_control(
            'in_same_term',
            [
                'label' => __('Same Taxonomy', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Navigate within the same taxonomy term.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomy', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'category',
                'options' => $this->get_taxonomies_options(),
                'condition' => ['in_same_term' => 'yes'],
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
                'default' => 'sides',
                'options' => [
                    'sides' => __('Side by Side', 'tbp-core'),
                    'stacked' => __('Stacked', 'tbp-core'),
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
                    'px' => ['min' => 0, 'max' => 100],
                ],
                'default' => ['size' => 20, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'stretch_items',
            [
                'label' => __('Stretch Items', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => ['layout' => 'sides'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__item' => 'flex: 1;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        // Container Style
        $this->start_controls_section(
            'section_container_style',
            [
                'label' => __('Container', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .tbp-pn',
            ]
        );

        $this->end_controls_section();

        // Item Style
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
                'default' => [
                    'top' => 16,
                    'right' => 20,
                    'bottom' => 16,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('item_tabs');

        $this->start_controls_tab('item_normal', ['label' => __('Normal', 'tbp-core')]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .tbp-pn__link',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'selector' => '{{WRAPPER}} .tbp-pn__link',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_shadow',
                'selector' => '{{WRAPPER}} .tbp-pn__link',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('item_hover', ['label' => __('Hover', 'tbp-core')]);

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .tbp-pn__link:hover',
            ]
        );

        $this->add_control(
            'item_border_color_hover',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_shadow_hover',
                'selector' => '{{WRAPPER}} .tbp-pn__link:hover',
            ]
        );

        $this->add_control(
            'item_transform_hover',
            [
                'label' => __('Transform', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'tbp-core'),
                    'translate-up' => __('Move Up', 'tbp-core'),
                    'scale' => __('Scale', 'tbp-core'),
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
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Thumbnail Style
        $this->start_controls_section(
            'section_thumbnail_style',
            [
                'label' => __('Thumbnail', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_thumbnail' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_width',
            [
                'label' => __('Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 40, 'max' => 200],
                    '%' => ['min' => 10, 'max' => 50],
                ],
                'default' => ['size' => 80, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__thumb' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_height',
            [
                'label' => __('Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 40, 'max' => 200],
                ],
                'default' => ['size' => 80, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__thumb' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__thumb' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'thumbnail_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 40],
                ],
                'default' => ['size' => 16, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__item--prev .tbp-pn__thumb' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-pn__item--next .tbp-pn__thumb' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Label Style
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => __('Label', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_label' => 'yes'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .tbp-pn__label',
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'label_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link:hover .tbp-pn__label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 20],
                ],
                'default' => ['size' => 4, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'section_title_style',
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
                'selector' => '{{WRAPPER}} .tbp-pn__title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link:hover .tbp-pn__title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Arrow Style
        $this->start_controls_section(
            'section_arrow_style',
            [
                'label' => __('Arrow', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['show_arrow' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'arrow_size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 10, 'max' => 50],
                ],
                'default' => ['size' => 16, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__arrow' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-pn__arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_color',
            [
                'label' => __('Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6366f1',
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__arrow' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-pn__arrow svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'arrow_color_hover',
            [
                'label' => __('Hover Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__link:hover .tbp-pn__arrow' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-pn__link:hover .tbp-pn__arrow svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'arrow_spacing',
            [
                'label' => __('Spacing', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 30],
                ],
                'default' => ['size' => 12, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-pn__item--prev .tbp-pn__arrow' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-pn__item--next .tbp-pn__arrow' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_image_sizes() {
        $sizes = get_intermediate_image_sizes();
        $options = [];
        foreach ($sizes as $size) {
            $options[$size] = ucwords(str_replace(['_', '-'], ' ', $size));
        }
        return $options;
    }

    private function get_taxonomies_options() {
        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $options = [];
        foreach ($taxonomies as $taxonomy) {
            $options[$taxonomy->name] = $taxonomy->label;
        }
        return $options;
    }

    private function truncate_text($text, $length = 50) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return rtrim(substr($text, 0, $length)) . '…';
    }

    /**
     * Custom adjacent post query that bypasses WordPress filters
     */
    private function get_adjacent_post_custom($direction = 'previous', $in_same_term = false, $taxonomy = 'category') {
        global $post, $wpdb;

        if (!$post || empty($post->post_date)) {
            return null;
        }

        $current_post_date = $post->post_date;
        $post_type = $post->post_type;

        // Build the query
        if ($direction === 'previous') {
            $op = '<';
            $order = 'DESC';
        } else {
            $op = '>';
            $order = 'ASC';
        }

        $join = '';
        $where_term = '';

        // Handle same term filtering
        if ($in_same_term && $taxonomy) {
            $term_ids = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
            if (!empty($term_ids) && !is_wp_error($term_ids)) {
                $term_ids_string = implode(',', array_map('intval', $term_ids));
                $join = " INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id";
                $where_term = " AND tr.term_taxonomy_id IN ({$term_ids_string})";
            }
        }

        $query = $wpdb->prepare(
            "SELECT p.* FROM {$wpdb->posts} p
            {$join}
            WHERE p.post_date {$op} %s
            AND p.post_type = %s
            AND p.post_status = 'publish'
            AND p.ID != %d
            {$where_term}
            ORDER BY p.post_date {$order}
            LIMIT 1",
            $current_post_date,
            $post_type,
            $post->ID
        );

        $result = $wpdb->get_row($query);

        return $result ? $result : null;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        global $post;

        // Get navigation settings
        $in_same_term = !empty($settings['in_same_term']) && $settings['in_same_term'] === 'yes';
        $taxonomy = $in_same_term ? ($settings['taxonomy'] ?? 'category') : '';

        // Use custom queries instead of get_previous_post/get_next_post
        // as those can be filtered by themes/plugins
        $prev_post = $this->get_adjacent_post_custom('previous', $in_same_term, $taxonomy);
        $next_post = $this->get_adjacent_post_custom('next', $in_same_term, $taxonomy);

        // If no posts found, show message
        if (!$prev_post && !$next_post) {
            // Show placeholder in editor or if no adjacent posts exist
            if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
                echo '<div class="tbp-pn tbp-pn--placeholder" style="background:#f9fafb;border:2px dashed #e5e7eb;border-radius:8px;padding:20px;text-align:center;">';
                echo '<p style="color:#6b7280;margin:0;">' . esc_html__('Post navigation will appear here when there are adjacent posts.', 'tbp-core') . '</p>';
                echo '</div>';
            }
            // On frontend, simply return nothing if no adjacent posts
            return;
        }

        $layout = $settings['layout'] ?? 'sides';
        $show_label = !empty($settings['show_label']) && $settings['show_label'] === 'yes';
        $show_title = !empty($settings['show_title']) && $settings['show_title'] === 'yes';
        $show_thumbnail = !empty($settings['show_thumbnail']) && $settings['show_thumbnail'] === 'yes';
        $show_arrow = !empty($settings['show_arrow']) && $settings['show_arrow'] === 'yes';
        $title_length = !empty($settings['title_length']) ? (int) $settings['title_length'] : 50;
        $hover_transform = $settings['item_transform_hover'] ?? 'none';

        $container_class = 'tbp-pn tbp-pn--' . $layout;
        if ($hover_transform !== 'none') {
            $container_class .= ' tbp-pn--hover-' . $hover_transform;
        }

        ?>
        <nav class="<?php echo esc_attr($container_class); ?>" aria-label="<?php esc_attr_e('Post navigation', 'tbp-core'); ?>">
            <?php if ($prev_post) : ?>
                <div class="tbp-pn__item tbp-pn__item--prev">
                    <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" class="tbp-pn__link" rel="prev">
                        <?php if ($show_arrow && !empty($settings['prev_icon']['value'])) : ?>
                            <span class="tbp-pn__arrow" aria-hidden="true">
                                <?php Icons_Manager::render_icon($settings['prev_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($show_thumbnail && has_post_thumbnail($prev_post)) : ?>
                            <span class="tbp-pn__thumb" aria-hidden="true">
                                <?php echo get_the_post_thumbnail($prev_post, $settings['thumbnail_size'] ?? 'thumbnail'); ?>
                            </span>
                        <?php endif; ?>

                        <span class="tbp-pn__content">
                            <?php if ($show_label) : ?>
                                <span class="tbp-pn__label"><?php echo esc_html($settings['prev_label'] ?? __('Previous', 'tbp-core')); ?></span>
                            <?php endif; ?>

                            <?php if ($show_title) : ?>
                                <span class="tbp-pn__title"><?php echo esc_html($this->truncate_text(get_the_title($prev_post), $title_length)); ?></span>
                            <?php endif; ?>
                        </span>

                        <span class="screen-reader-text"><?php printf(esc_html__('Previous post: %s', 'tbp-core'), get_the_title($prev_post)); ?></span>
                    </a>
                </div>
            <?php elseif ($layout === 'sides') : ?>
                <div class="tbp-pn__item tbp-pn__item--prev tbp-pn__item--empty"></div>
            <?php endif; ?>

            <?php if ($next_post) : ?>
                <div class="tbp-pn__item tbp-pn__item--next">
                    <a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="tbp-pn__link" rel="next">
                        <span class="tbp-pn__content">
                            <?php if ($show_label) : ?>
                                <span class="tbp-pn__label"><?php echo esc_html($settings['next_label'] ?? __('Next', 'tbp-core')); ?></span>
                            <?php endif; ?>

                            <?php if ($show_title) : ?>
                                <span class="tbp-pn__title"><?php echo esc_html($this->truncate_text(get_the_title($next_post), $title_length)); ?></span>
                            <?php endif; ?>
                        </span>

                        <?php if ($show_thumbnail && has_post_thumbnail($next_post)) : ?>
                            <span class="tbp-pn__thumb" aria-hidden="true">
                                <?php echo get_the_post_thumbnail($next_post, $settings['thumbnail_size'] ?? 'thumbnail'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($show_arrow && !empty($settings['next_icon']['value'])) : ?>
                            <span class="tbp-pn__arrow" aria-hidden="true">
                                <?php Icons_Manager::render_icon($settings['next_icon'], ['aria-hidden' => 'true']); ?>
                            </span>
                        <?php endif; ?>

                        <span class="screen-reader-text"><?php printf(esc_html__('Next post: %s', 'tbp-core'), get_the_title($next_post)); ?></span>
                    </a>
                </div>
            <?php elseif ($layout === 'sides') : ?>
                <div class="tbp-pn__item tbp-pn__item--next tbp-pn__item--empty"></div>
            <?php endif; ?>
        </nav>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var layoutClass = 'tbp-pn tbp-pn--' + (settings.layout || 'sides');
        if (settings.item_transform_hover && settings.item_transform_hover !== 'none') {
            layoutClass += ' tbp-pn--hover-' + settings.item_transform_hover;
        }
        var showLabel = settings.show_label === 'yes';
        var showTitle = settings.show_title === 'yes';
        var showThumbnail = settings.show_thumbnail === 'yes';
        var showArrow = settings.show_arrow === 'yes';
        #>
        <nav class="{{ layoutClass }}">
            <div class="tbp-pn__item tbp-pn__item--prev">
                <a href="#" class="tbp-pn__link">
                    <# if (showArrow && settings.prev_icon.value) { #>
                        <span class="tbp-pn__arrow">
                            <i class="{{ settings.prev_icon.value }}"></i>
                        </span>
                    <# } #>

                    <# if (showThumbnail) { #>
                        <span class="tbp-pn__thumb" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;">
                            <i class="eicon-image" style="font-size:24px;color:#9ca3af;"></i>
                        </span>
                    <# } #>

                    <span class="tbp-pn__content">
                        <# if (showLabel) { #>
                            <span class="tbp-pn__label">{{{ settings.prev_label || 'Previous' }}}</span>
                        <# } #>
                        <# if (showTitle) { #>
                            <span class="tbp-pn__title">Previous Post Title</span>
                        <# } #>
                    </span>
                </a>
            </div>

            <div class="tbp-pn__item tbp-pn__item--next">
                <a href="#" class="tbp-pn__link">
                    <span class="tbp-pn__content">
                        <# if (showLabel) { #>
                            <span class="tbp-pn__label">{{{ settings.next_label || 'Next' }}}</span>
                        <# } #>
                        <# if (showTitle) { #>
                            <span class="tbp-pn__title">Next Post Title</span>
                        <# } #>
                    </span>

                    <# if (showThumbnail) { #>
                        <span class="tbp-pn__thumb" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;">
                            <i class="eicon-image" style="font-size:24px;color:#9ca3af;"></i>
                        </span>
                    <# } #>

                    <# if (showArrow && settings.next_icon.value) { #>
                        <span class="tbp-pn__arrow">
                            <i class="{{ settings.next_icon.value }}"></i>
                        </span>
                    <# } #>
                </a>
            </div>
        </nav>
        <?php
    }
}
