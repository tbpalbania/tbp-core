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

class Staff_Timeline_Widget extends Widget_Base {

    public function get_name() {
        return 'tbp-staff-timeline';
    }

    public function get_title() {
        return __('Staff Timeline', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-time-line';
    }

    public function get_categories() {
        return ['tbp-staff'];
    }

    public function get_keywords() {
        return ['staff', 'timeline', 'education', 'experience', 'resume', 'cv'];
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_fields_controls();
        $this->register_style_controls();
        $this->register_item_style_controls();
        $this->register_typography_controls();
    }

    protected function register_content_controls() {
        $this->start_controls_section('section_content', [
            'label' => __('Content', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('data_type', [
            'label'   => __('Data Type', 'tbp-core'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'education',
            'options' => [
                'education'  => __('Education', 'tbp-core'),
                'experience' => __('Experience', 'tbp-core'),
            ],
        ]);

        $this->add_control('source', [
            'label'   => __('Source', 'tbp-core'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'current',
            'options' => [
                'current'  => __('Current Post', 'tbp-core'),
                'specific' => __('Specific Staff', 'tbp-core'),
            ],
        ]);

        $this->add_control('staff_id', [
            'label'     => __('Select Staff', 'tbp-core'),
            'type'      => Controls_Manager::SELECT2,
            'options'   => $this->get_staff_options(),
            'condition' => ['source' => 'specific'],
        ]);

        $this->add_control('layout', [
            'label'   => __('Layout', 'tbp-core'),
            'type'    => Controls_Manager::SELECT,
            'default' => 'card',
            'options' => [
                'card'     => __('Cards', 'tbp-core'),
                'timeline' => __('Timeline', 'tbp-core'),
                'list'     => __('Simple List', 'tbp-core'),
            ],
        ]);

        $this->add_responsive_control('columns', [
            'label'     => __('Columns', 'tbp-core'),
            'type'      => Controls_Manager::SELECT,
            'default'   => '1',
            'options'   => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
            ],
            'condition' => ['layout' => 'card'],
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline--card' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
            ],
        ]);

        $this->add_control('limit', [
            'label'       => __('Limit', 'tbp-core'),
            'type'        => Controls_Manager::NUMBER,
            'default'     => 0,
            'min'         => 0,
            'description' => __('0 = show all', 'tbp-core'),
        ]);

        $this->add_control('present_text', [
            'label'   => __('Present Text', 'tbp-core'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Present', 'tbp-core'),
        ]);

        $this->add_control('empty_message', [
            'label'   => __('Empty Message', 'tbp-core'),
            'type'    => Controls_Manager::TEXT,
            'default' => '',
        ]);

        $this->end_controls_section();
    }

    protected function register_fields_controls() {
        // Education Fields
        $this->start_controls_section('section_education_fields', [
            'label'     => __('Education Fields', 'tbp-core'),
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => ['data_type' => 'education'],
        ]);

        $this->add_control('show_degree', [
            'label'        => __('Show Degree', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_institution', [
            'label'        => __('Show Institution', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_edu_dates', [
            'label'        => __('Show Dates', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_edu_duration', [
            'label'        => __('Show Duration', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => '',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_edu_description', [
            'label'        => __('Show Description', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->end_controls_section();

        // Experience Fields
        $this->start_controls_section('section_experience_fields', [
            'label'     => __('Experience Fields', 'tbp-core'),
            'tab'       => Controls_Manager::TAB_CONTENT,
            'condition' => ['data_type' => 'experience'],
        ]);

        $this->add_control('show_job_title', [
            'label'        => __('Show Job Title', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_company', [
            'label'        => __('Show Company', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_exp_dates', [
            'label'        => __('Show Dates', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_exp_duration', [
            'label'        => __('Show Duration', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => '',
            'return_value' => 'yes',
        ]);

        $this->add_control('show_exp_description', [
            'label'        => __('Show Description', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'return_value' => 'yes',
        ]);

        $this->end_controls_section();
    }

    protected function register_style_controls() {
        $this->start_controls_section('section_style_container', [
            'label' => __('Container', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('item_gap', [
            'label'      => __('Items Gap', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em'],
            'range'      => [
                'px' => ['min' => 0, 'max' => 60],
                'em' => ['min' => 0, 'max' => 4],
            ],
            'default'    => ['size' => 20, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // Timeline Line Style
        $this->start_controls_section('section_style_timeline', [
            'label'     => __('Timeline Line', 'tbp-core'),
            'tab'       => Controls_Manager::TAB_STYLE,
            'condition' => ['layout' => 'timeline'],
        ]);

        $this->add_control('timeline_line_color', [
            'label'     => __('Line Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#e0e0e0',
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline--timeline::before' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('timeline_line_width', [
            'label'      => __('Line Width', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 1, 'max' => 10]],
            'default'    => ['size' => 2, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline--timeline::before' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('timeline_dot_color', [
            'label'     => __('Dot Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#2271b1',
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__dot' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('timeline_dot_size', [
            'label'      => __('Dot Size', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 8, 'max' => 24]],
            'default'    => ['size' => 12, 'unit' => 'px'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function register_item_style_controls() {
        $this->start_controls_section('section_style_item', [
            'label' => __('Item Box', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(Group_Control_Background::get_type(), [
            'name'     => 'item_background',
            'label'    => __('Background', 'tbp-core'),
            'types'    => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .tbp-timeline__item',
        ]);

        $this->add_group_control(Group_Control_Border::get_type(), [
            'name'     => 'item_border',
            'label'    => __('Border', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__item',
        ]);

        $this->add_responsive_control('item_border_radius', [
            'label'      => __('Border Radius', 'tbp-core'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Box_Shadow::get_type(), [
            'name'     => 'item_box_shadow',
            'label'    => __('Box Shadow', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__item',
        ]);

        $this->add_responsive_control('item_padding', [
            'label'      => __('Padding', 'tbp-core'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function register_typography_controls() {
        // Title Style (Degree/Job Title)
        $this->start_controls_section('section_style_title', [
            'label' => __('Title', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('title_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'title_typography',
            'label'    => __('Typography', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__title',
        ]);

        $this->add_responsive_control('title_spacing', [
            'label'      => __('Spacing', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // Subtitle Style (Institution/Company)
        $this->start_controls_section('section_style_subtitle', [
            'label' => __('Subtitle', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('subtitle_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__subtitle' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'subtitle_typography',
            'label'    => __('Typography', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__subtitle',
        ]);

        $this->add_responsive_control('subtitle_spacing', [
            'label'      => __('Spacing', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // Dates Style
        $this->start_controls_section('section_style_dates', [
            'label' => __('Dates', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('dates_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__dates' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('dates_background', [
            'label'     => __('Background', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__dates' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'dates_typography',
            'label'    => __('Typography', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__dates',
        ]);

        $this->add_responsive_control('dates_padding', [
            'label'      => __('Padding', 'tbp-core'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__dates' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('dates_border_radius', [
            'label'      => __('Border Radius', 'tbp-core'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__dates' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('dates_spacing', [
            'label'      => __('Spacing', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__dates' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();

        // Duration Style
        $this->start_controls_section('section_style_duration', [
            'label' => __('Duration', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('duration_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__duration' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'duration_typography',
            'label'    => __('Typography', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__duration',
        ]);

        $this->end_controls_section();

        // Description Style
        $this->start_controls_section('section_style_description', [
            'label' => __('Description', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('description_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-timeline__description' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'description_typography',
            'label'    => __('Typography', 'tbp-core'),
            'selector' => '{{WRAPPER}} .tbp-timeline__description',
        ]);

        $this->add_responsive_control('description_spacing', [
            'label'      => __('Spacing', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 30]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-timeline__description' => 'margin-top: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_controls_section();
    }

    private function get_staff_options() {
        $options = [];
        $staff = get_posts([
            'post_type'      => 'staff',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        foreach ($staff as $member) {
            $options[$member->ID] = $member->post_title;
        }

        return $options;
    }

    private function format_date($date) {
        if (empty($date)) {
            return '';
        }
        $timestamp = strtotime($date . '-01');
        return date_i18n('M Y', $timestamp);
    }

    private function calculate_duration($start, $end, $is_current) {
        if (empty($start)) {
            return '';
        }

        $start_date = strtotime($start . '-01');
        $end_date = $is_current ? time() : ($end ? strtotime($end . '-01') : time());

        $diff = abs($end_date - $start_date);
        $years = floor($diff / (365 * 24 * 60 * 60));
        $months = floor(($diff % (365 * 24 * 60 * 60)) / (30 * 24 * 60 * 60));

        $parts = [];
        if ($years > 0) {
            $parts[] = sprintf(_n('%d year', '%d years', $years, 'tbp-core'), $years);
        }
        if ($months > 0) {
            $parts[] = sprintf(_n('%d month', '%d months', $months, 'tbp-core'), $months);
        }

        return implode(' ', $parts);
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Get post ID
        $post_id = null;
        if ($settings['source'] === 'current') {
            $post_id = get_the_ID();
        } elseif ($settings['source'] === 'specific' && !empty($settings['staff_id'])) {
            $post_id = intval($settings['staff_id']);
        }

        if (!$post_id || get_post_type($post_id) !== 'staff') {
            if (!empty($settings['empty_message'])) {
                echo '<p class="tbp-timeline__empty">' . esc_html($settings['empty_message']) . '</p>';
            }
            return;
        }

        // Get data
        $data_type = $settings['data_type'];
        $data = [];

        if ($data_type === 'education') {
            $data = \TBP_Staff::get_education($post_id);
        } else {
            $data = \TBP_Staff::get_experience($post_id);
        }

        if (empty($data)) {
            if (!empty($settings['empty_message'])) {
                echo '<p class="tbp-timeline__empty">' . esc_html($settings['empty_message']) . '</p>';
            }
            return;
        }

        // Apply limit
        $limit = intval($settings['limit']);
        if ($limit > 0) {
            $data = array_slice($data, 0, $limit);
        }

        $layout = $settings['layout'];
        $present_text = $settings['present_text'] ?: __('Present', 'tbp-core');

        ?>
        <div class="tbp-timeline tbp-timeline--<?php echo esc_attr($layout); ?> tbp-timeline--<?php echo esc_attr($data_type); ?>">
            <?php foreach ($data as $index => $item) : ?>
                <?php $this->render_item($item, $data_type, $settings, $present_text, $layout); ?>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_item($item, $data_type, $settings, $present_text, $layout) {
        $is_current = !empty($item['current']);
        $start_date = $item['start_date'] ?? '';
        $end_date = $item['end_date'] ?? '';

        // Determine field visibility
        if ($data_type === 'education') {
            $show_title = $settings['show_degree'] === 'yes';
            $show_subtitle = $settings['show_institution'] === 'yes';
            $show_dates = $settings['show_edu_dates'] === 'yes';
            $show_duration = $settings['show_edu_duration'] === 'yes';
            $show_description = $settings['show_edu_description'] === 'yes';
            $title = $item['degree'] ?? '';
            $subtitle = $item['institution'] ?? '';
        } else {
            $show_title = $settings['show_job_title'] === 'yes';
            $show_subtitle = $settings['show_company'] === 'yes';
            $show_dates = $settings['show_exp_dates'] === 'yes';
            $show_duration = $settings['show_exp_duration'] === 'yes';
            $show_description = $settings['show_exp_description'] === 'yes';
            $title = $item['job_title'] ?? '';
            $subtitle = $item['company'] ?? '';
        }

        $description = $item['description'] ?? '';
        ?>
        <div class="tbp-timeline__item">
            <?php if ($layout === 'timeline') : ?>
                <div class="tbp-timeline__dot"></div>
            <?php endif; ?>

            <div class="tbp-timeline__content">
                <?php if ($show_title && !empty($title)) : ?>
                    <h4 class="tbp-timeline__title"><?php echo esc_html($title); ?></h4>
                <?php endif; ?>

                <?php if ($show_subtitle && !empty($subtitle)) : ?>
                    <div class="tbp-timeline__subtitle"><?php echo esc_html($subtitle); ?></div>
                <?php endif; ?>

                <?php if ($show_dates && !empty($start_date)) : ?>
                    <div class="tbp-timeline__dates">
                        <?php
                        echo esc_html($this->format_date($start_date));
                        echo ' — ';
                        echo $is_current ? esc_html($present_text) : esc_html($this->format_date($end_date));
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_duration && !empty($start_date)) : ?>
                    <div class="tbp-timeline__duration">
                        <?php echo esc_html($this->calculate_duration($start_date, $end_date, $is_current)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_description && !empty($description)) : ?>
                    <div class="tbp-timeline__description"><?php echo esc_html($description); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
