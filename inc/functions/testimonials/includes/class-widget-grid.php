<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
    exit;
}

class Testimonials_Grid_Widget extends Widget_Base {

    public function get_name() {
        return 'tbp-testimonials-grid';
    }

    public function get_title() {
        return __('Testimonials Grid', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-testimonial';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['testimonial', 'review', 'grid', 'carousel'];
    }

    public function get_style_depends() {
        return ['tbp-testimonials'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_query',
            [
                'label' => __('Query', 'tbp-core'),
            ]
        );

        $this->add_control(
            'count',
            [
                'label'   => __('Number of Testimonials', 'tbp-core'),
                'type'    => Controls_Manager::NUMBER,
                'default' => 6,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __('Order By', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date_created',
                'options' => [
                    'date_created' => __('Date', 'tbp-core'),
                    'rating'       => __('Rating', 'tbp-core'),
                    'author_name'  => __('Author Name', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => __('Order', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __('Descending', 'tbp-core'),
                    'ASC'  => __('Ascending', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'featured_only',
            [
                'label'   => __('Featured Only', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => '',
            ]
        );

        $this->add_control(
            'min_rating',
            [
                'label'   => __('Minimum Rating', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => '0',
                'options' => [
                    '0' => __('All', 'tbp-core'),
                    '3' => __('3+ Stars', 'tbp-core'),
                    '4' => __('4+ Stars', 'tbp-core'),
                    '5' => __('5 Stars Only', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label'       => __('Product ID', 'tbp-core'),
                'type'        => Controls_Manager::NUMBER,
                'description' => __('Show testimonials for a specific product only', 'tbp-core'),
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
                'label'   => __('Columns', 'tbp-core'),
                'type'    => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'selectors_dictionary' => [
                    '1' => 'repeat(1, 1fr)',
                    '2' => 'repeat(2, 1fr)',
                    '3' => 'repeat(3, 1fr)',
                    '4' => 'repeat(4, 1fr)',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonials-grid' => 'grid-template-columns: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'gap',
            [
                'label'      => __('Gap', 'tbp-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 0, 'max' => 60]],
                'default'    => ['size' => 24, 'unit' => 'px'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonials-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'show_rating',
            [
                'label'   => __('Show Rating', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_date',
            [
                'label'   => __('Show Date', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_company',
            [
                'label'   => __('Show Company/Position', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_avatar',
            [
                'label'   => __('Show Avatar', 'tbp-core'),
                'type'    => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Card Style
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => __('Card', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_background',
            [
                'label'     => __('Background', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => __('Padding', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label'      => __('Border Radius', 'tbp-core'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_shadow',
                'selector' => '{{WRAPPER}} .tbp-testimonial-card',
            ]
        );

        $this->end_controls_section();

        // Content Style
        $this->start_controls_section(
            'section_style_content',
            [
                'label' => __('Content', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label'     => __('Text Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-card__content' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'content_typography',
                'selector' => '{{WRAPPER}} .tbp-testimonial-card__content',
            ]
        );

        $this->end_controls_section();

        // Author Style
        $this->start_controls_section(
            'section_style_author',
            [
                'label' => __('Author', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'author_name_color',
            [
                'label'     => __('Name Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-card__name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'author_name_typography',
                'label'    => __('Name Typography', 'tbp-core'),
                'selector' => '{{WRAPPER}} .tbp-testimonial-card__name',
            ]
        );

        $this->add_control(
            'author_company_color',
            [
                'label'     => __('Company Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-card__company' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'avatar_size',
            [
                'label'      => __('Avatar Size', 'tbp-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 32, 'max' => 80]],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-card__avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Stars Style
        $this->start_controls_section(
            'section_style_stars',
            [
                'label' => __('Stars', 'tbp-core'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'stars_size',
            [
                'label'      => __('Size', 'tbp-core'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => ['px' => ['min' => 12, 'max' => 32]],
                'selectors'  => [
                    '{{WRAPPER}} .tbp-testimonial-card__rating' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'stars_color',
            [
                'label'     => __('Color', 'tbp-core'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-testimonial-card__rating' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        wp_enqueue_style('tbp-testimonials');

        $testimonials = \TBP_Testimonials::get_testimonials([
            'limit'         => $settings['count'],
            'order'         => $settings['order'],
            'orderby'       => $settings['orderby'],
            'featured_only' => $settings['featured_only'] === 'yes',
            'min_rating'    => (int) $settings['min_rating'],
            'product_id'    => (int) $settings['product_id'],
        ]);

        if (empty($testimonials)) {
            echo '<p>' . esc_html__('No testimonials found.', 'tbp-core') . '</p>';
            return;
        }

        $show_rating = $settings['show_rating'] === 'yes';
        $show_date = $settings['show_date'] === 'yes';
        $show_company = $settings['show_company'] === 'yes';
        $show_avatar = $settings['show_avatar'] === 'yes';
        ?>
        <div class="tbp-testimonials-grid">
            <?php foreach ($testimonials as $testimonial):
                $GLOBALS['tbp_current_testimonial'] = $testimonial;
                ?>
                <div class="tbp-testimonial-card <?php echo $testimonial->featured ? 'tbp-testimonial-card--featured' : ''; ?>">
                    <?php if ($testimonial->featured): ?>
                        <span class="tbp-testimonial-card__featured-badge">
                            <span>&#9733;</span> <?php esc_html_e('Featured', 'tbp-core'); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($show_rating): ?>
                        <div class="tbp-testimonial-card__rating">
                            <?php
                            $rating = (int) $testimonial->rating;
                            echo str_repeat('&#9733;', $rating) . str_repeat('&#9734;', 5 - $rating);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="tbp-testimonial-card__content">
                        <?php echo esc_html($testimonial->content); ?>
                    </div>

                    <div class="tbp-testimonial-card__author">
                        <?php if ($show_avatar): ?>
                            <div class="tbp-testimonial-card__avatar">
                                <?php
                                if ($testimonial->author_avatar) {
                                    echo wp_get_attachment_image($testimonial->author_avatar, 'thumbnail');
                                } else {
                                    echo esc_html(strtoupper(substr($testimonial->author_name, 0, 1)));
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <div class="tbp-testimonial-card__info">
                            <p class="tbp-testimonial-card__name"><?php echo esc_html($testimonial->author_name); ?></p>
                            <?php if ($show_company && (!empty($testimonial->author_company) || !empty($testimonial->author_position))): ?>
                                <p class="tbp-testimonial-card__company">
                                    <?php
                                    $parts = [];
                                    if (!empty($testimonial->author_position)) {
                                        $parts[] = $testimonial->author_position;
                                    }
                                    if (!empty($testimonial->author_company)) {
                                        $parts[] = $testimonial->author_company;
                                    }
                                    echo esc_html(implode(' at ', $parts));
                                    ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($show_date): ?>
                                <span class="tbp-testimonial-card__date">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($testimonial->date_created))); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach;
            unset($GLOBALS['tbp_current_testimonial']);
            ?>
        </div>
        <?php
    }
}
