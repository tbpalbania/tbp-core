<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class Button extends Widget_Base {

    public function get_name() {
        return 'tbp-button';
    }

    public function get_title() {
        return __('Button', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['button', 'link', 'cta', 'call to action'];
    }

    public function get_style_depends() {
        return ['tbp-button'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'section_button',
            [
                'label' => __('Button', 'tbp-core'),
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'default' => __('Click Here', 'tbp-core'),
                'placeholder' => __('Button text', 'tbp-core'),
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __('Link', 'tbp-core'),
                'type' => Controls_Manager::URL,
                'dynamic' => ['active' => true],
                'default' => ['url' => '#'],
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'small' => __('Small', 'tbp-core'),
                    'base' => __('Base', 'tbp-core'),
                    'large' => __('Large', 'tbp-core'),
                ],
                'default' => 'base',
            ]
        );

        $this->add_control(
            'variant',
            [
                'label' => __('Variant', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'filled' => __('Filled', 'tbp-core'),
                    'outlined' => __('Outlined', 'tbp-core'),
                    'ghost' => __('Ghost', 'tbp-core'),
                ],
                'default' => 'filled',
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alignment', 'tbp-core'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'tbp-core'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'tbp-core'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'tbp-core'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justify', 'tbp-core'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'full_width',
            [
                'label' => __('Full Width', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'width: 100%;',
                ],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'left' => __('Before', 'tbp-core'),
                    'right' => __('After', 'tbp-core'),
                ],
                'default' => 'left',
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'button_id',
            [
                'label' => __('Button ID', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => ['active' => true],
                'default' => '',
                'separator' => 'before',
                'description' => __('Add a custom ID attribute to the button.', 'tbp-core'),
            ]
        );

        $this->end_controls_section();

        // Style Section - Button
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Button', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'override_styles',
            [
                'label' => __('Override Global Styles', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Enable to override Site Settings defaults for this button.', 'tbp-core'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .tbp-button',
                'condition' => [
                    'override_styles' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'override_styles' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_min_height',
            [
                'label' => __('Min Height', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 20, 'max' => 150],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'override_styles' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'custom_border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'override_styles' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs(
            'button_styles',
            [
                'condition' => [
                    'override_styles' => 'yes',
                ],
            ]
        );

        // Normal Tab
        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-button',
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Text Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tbp-button:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_bg_color',
            [
                'label' => __('Background Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .tbp-button:hover',
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Hover Animation', 'tbp-core'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->add_control(
            'hover_transition',
            [
                'label' => __('Transition Duration', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms'],
                'default' => [
                    'unit' => 's',
                    'size' => 0.3,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button' => 'transition-duration: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Icon Style Section
        $this->start_controls_section(
            'section_icon_style',
            [
                'label' => __('Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'override_icon_styles',
            [
                'label' => __('Override Global Styles', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'description' => __('Enable to override Site Settings icon defaults.', 'tbp-core'),
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'override_icon_styles' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_gap',
            [
                'label' => __('Icon Gap', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-button-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'override_icon_styles' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Build button classes
        $button_classes = [
            'tbp-button',
            'tbp-button-size-' . $settings['size'],
            'tbp-button-variant-' . $settings['variant'],
        ];

        if (!empty($settings['hover_animation'])) {
            $button_classes[] = 'elementor-animation-' . $settings['hover_animation'];
        }

        $this->add_render_attribute('button', [
            'class' => $button_classes,
            'role' => 'button',
        ]);

        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('button', $settings['link']);
        }

        if (!empty($settings['button_id'])) {
            $this->add_render_attribute('button', 'id', $settings['button_id']);
        }

        $has_icon = !empty($settings['selected_icon']['value']);
        $icon_position = $settings['icon_position'];
        ?>
        <a <?php $this->print_render_attribute_string('button'); ?>>
            <span class="tbp-button-content">
                <?php if ($has_icon && $icon_position === 'left') : ?>
                    <span class="tbp-button-icon tbp-button-icon-left">
                        <?php Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']); ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($settings['text'])) : ?>
                    <span class="tbp-button-text"><?php echo esc_html($settings['text']); ?></span>
                <?php endif; ?>

                <?php if ($has_icon && $icon_position === 'right') : ?>
                    <span class="tbp-button-icon tbp-button-icon-right">
                        <?php Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']); ?>
                    </span>
                <?php endif; ?>
            </span>
        </a>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var buttonClasses = [
            'tbp-button',
            'tbp-button-size-' + settings.size,
            'tbp-button-variant-' + settings.variant
        ];

        if (settings.hover_animation) {
            buttonClasses.push('elementor-animation-' + settings.hover_animation);
        }

        view.addRenderAttribute('button', {
            'class': buttonClasses.join(' '),
            'role': 'button'
        });

        if (settings.link?.url) {
            view.addRenderAttribute('button', 'href', settings.link.url);
        }

        if (settings.button_id) {
            view.addRenderAttribute('button', 'id', settings.button_id);
        }

        var hasIcon = settings.selected_icon?.value;
        var iconHTML = hasIcon ? elementor.helpers.renderIcon(view, settings.selected_icon, { 'aria-hidden': true }, 'i', 'object') : null;
        #>
        <a {{{ view.getRenderAttributeString('button') }}}>
            <span class="tbp-button-content">
                <# if (hasIcon && settings.icon_position === 'left') { #>
                    <span class="tbp-button-icon tbp-button-icon-left">
                        <# if (iconHTML?.rendered) { #>
                            {{{ iconHTML.value }}}
                        <# } #>
                    </span>
                <# } #>

                <# if (settings.text) { #>
                    <span class="tbp-button-text">{{{ settings.text }}}</span>
                <# } #>

                <# if (hasIcon && settings.icon_position === 'right') { #>
                    <span class="tbp-button-icon tbp-button-icon-right">
                        <# if (iconHTML?.rendered) { #>
                            {{{ iconHTML.value }}}
                        <# } #>
                    </span>
                <# } #>
            </span>
        </a>
        <?php
    }
}
