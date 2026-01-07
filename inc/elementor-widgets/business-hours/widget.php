<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if (!defined('ABSPATH')) {
    exit;
}

class Business_Hours extends Widget_Base {

    public function get_name() {
        return 'tbp-business-hours';
    }

    public function get_title() {
        return __('Business Hours', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-clock-o';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_keywords() {
        return ['business', 'hours', 'schedule', 'time', 'opening', 'working'];
    }

    public function get_style_depends() {
        return ['tbp-business-hours'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section('section_content', [
            'label' => __('Business Hours', 'tbp-core'),
        ]);

        $repeater = new Repeater();

        $repeater->add_control('day', [
            'label'   => __('Day', 'tbp-core'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('Monday', 'tbp-core'),
        ]);

        $repeater->add_control('hours', [
            'label'   => __('Hours', 'tbp-core'),
            'type'    => Controls_Manager::TEXT,
            'default' => __('9:00 AM - 5:00 PM', 'tbp-core'),
        ]);

        $repeater->add_control('is_closed', [
            'label'        => __('Closed', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Yes', 'tbp-core'),
            'label_off'    => __('No', 'tbp-core'),
            'return_value' => 'yes',
            'default'      => '',
        ]);

        $repeater->add_control('closed_text', [
            'label'     => __('Closed Text', 'tbp-core'),
            'type'      => Controls_Manager::TEXT,
            'default'   => __('Closed', 'tbp-core'),
            'condition' => ['is_closed' => 'yes'],
        ]);

        $repeater->add_control('highlight', [
            'label'        => __('Highlight', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Yes', 'tbp-core'),
            'label_off'    => __('No', 'tbp-core'),
            'return_value' => 'yes',
            'default'      => '',
        ]);

        $this->add_control('hours_list', [
            'label'   => __('Schedule', 'tbp-core'),
            'type'    => Controls_Manager::REPEATER,
            'fields'  => $repeater->get_controls(),
            'default' => [
                ['day' => __('Monday', 'tbp-core'), 'hours' => __('9:00 AM - 5:00 PM', 'tbp-core')],
                ['day' => __('Tuesday', 'tbp-core'), 'hours' => __('9:00 AM - 5:00 PM', 'tbp-core')],
                ['day' => __('Wednesday', 'tbp-core'), 'hours' => __('9:00 AM - 5:00 PM', 'tbp-core')],
                ['day' => __('Thursday', 'tbp-core'), 'hours' => __('9:00 AM - 5:00 PM', 'tbp-core')],
                ['day' => __('Friday', 'tbp-core'), 'hours' => __('9:00 AM - 5:00 PM', 'tbp-core')],
                ['day' => __('Saturday', 'tbp-core'), 'hours' => __('10:00 AM - 2:00 PM', 'tbp-core')],
                ['day' => __('Sunday', 'tbp-core'), 'hours' => __('Closed', 'tbp-core'), 'is_closed' => 'yes', 'closed_text' => __('Closed', 'tbp-core')],
            ],
            'title_field' => '{{{ day }}}',
        ]);

        $this->end_controls_section();

        // Row Style Section
        $this->start_controls_section('section_row_style', [
            'label' => __('Row', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('row_padding', [
            'label'      => __('Padding', 'tbp-core'),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}} .tbp-bh-row' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('row_gap', [
            'label'      => __('Gap Between Rows', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 0, 'max' => 50]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-bh-list' => 'gap: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('row_divider', [
            'label'        => __('Divider', 'tbp-core'),
            'type'         => Controls_Manager::SWITCHER,
            'label_on'     => __('Yes', 'tbp-core'),
            'label_off'    => __('No', 'tbp-core'),
            'return_value' => 'yes',
            'default'      => 'yes',
        ]);

        $this->add_control('divider_color', [
            'label'     => __('Divider Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-bh-row' => 'border-bottom-color: {{VALUE}};',
            ],
            'condition' => ['row_divider' => 'yes'],
        ]);

        $this->add_control('divider_style', [
            'label'     => __('Divider Style', 'tbp-core'),
            'type'      => Controls_Manager::SELECT,
            'options'   => [
                'solid'  => __('Solid', 'tbp-core'),
                'dashed' => __('Dashed', 'tbp-core'),
                'dotted' => __('Dotted', 'tbp-core'),
            ],
            'default'   => 'solid',
            'selectors' => [
                '{{WRAPPER}} .tbp-bh-row' => 'border-bottom-style: {{VALUE}};',
            ],
            'condition' => ['row_divider' => 'yes'],
        ]);

        $this->add_responsive_control('divider_weight', [
            'label'      => __('Divider Weight', 'tbp-core'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range'      => ['px' => ['min' => 1, 'max' => 5]],
            'selectors'  => [
                '{{WRAPPER}} .tbp-bh-row' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
            ],
            'condition'  => ['row_divider' => 'yes'],
        ]);

        $this->end_controls_section();

        // Day Column Style
        $this->start_controls_section('section_day_style', [
            'label' => __('Day Column', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('day_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-bh-day' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'day_typography',
            'selector' => '{{WRAPPER}} .tbp-bh-day',
        ]);

        $this->end_controls_section();

        // Hours Column Style
        $this->start_controls_section('section_hours_style', [
            'label' => __('Hours Column', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('hours_color', [
            'label'     => __('Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-bh-hours' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(Group_Control_Typography::get_type(), [
            'name'     => 'hours_typography',
            'selector' => '{{WRAPPER}} .tbp-bh-hours',
        ]);

        $this->end_controls_section();

        // Highlight Style
        $this->start_controls_section('section_highlight_style', [
            'label' => __('Highlight', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('highlight_day_color', [
            'label'     => __('Day Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-highlight .tbp-bh-day' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('highlight_hours_color', [
            'label'     => __('Hours Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-highlight .tbp-bh-hours' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('highlight_bg_color', [
            'label'     => __('Background Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-highlight' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();

        // Closed Style
        $this->start_controls_section('section_closed_style', [
            'label' => __('Closed', 'tbp-core'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control('closed_day_color', [
            'label'     => __('Day Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-closed .tbp-bh-day' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('closed_hours_color', [
            'label'     => __('Hours Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-closed .tbp-bh-hours' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('closed_bg_color', [
            'label'     => __('Background Color', 'tbp-core'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbp-business-hours .tbp-bh-row.is-closed' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['hours_list'])) {
            return;
        }

        $has_divider = $settings['row_divider'] === 'yes';
        ?>
        <div class="tbp-business-hours">
            <div class="tbp-bh-list <?php echo $has_divider ? 'has-divider' : ''; ?>">
                <?php foreach ($settings['hours_list'] as $index => $item) :
                    $is_closed = $item['is_closed'] === 'yes';
                    $is_highlight = $item['highlight'] === 'yes';
                    $hours_text = $is_closed ? ($item['closed_text'] ?: __('Closed', 'tbp-core')) : $item['hours'];

                    $row_class = 'tbp-bh-row';
                    if ($is_closed) $row_class .= ' is-closed';
                    if ($is_highlight) $row_class .= ' is-highlight';
                ?>
                    <div class="<?php echo esc_attr($row_class); ?>">
                        <span class="tbp-bh-day"><?php echo esc_html($item['day']); ?></span>
                        <span class="tbp-bh-hours"><?php echo esc_html($hours_text); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        if (!settings.hours_list.length) return;

        var hasDivider = settings.row_divider === 'yes';
        #>
        <div class="tbp-business-hours">
            <div class="tbp-bh-list <# if (hasDivider) { #>has-divider<# } #>">
                <# _.each(settings.hours_list, function(item, index) {
                    var isClosed = item.is_closed === 'yes';
                    var isHighlight = item.highlight === 'yes';
                    var hoursText = isClosed ? (item.closed_text || 'Closed') : item.hours;

                    var rowClass = 'tbp-bh-row';
                    if (isClosed) rowClass += ' is-closed';
                    if (isHighlight) rowClass += ' is-highlight';
                #>
                    <div class="{{ rowClass }}">
                        <span class="tbp-bh-day">{{{ item.day }}}</span>
                        <span class="tbp-bh-hours">{{{ hoursText }}}</span>
                    </div>
                <# }); #>
            </div>
        </div>
        <?php
    }
}
