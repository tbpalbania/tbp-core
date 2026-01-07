<?php
namespace TBP_Core\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;

if (!defined('ABSPATH')) {
    exit;
}

class Dynamic_Icon extends Widget_Base {

    public function get_name() {
        return 'tbp-dynamic-icon';
    }

    public function get_title() {
        return __('Dynamic Icon', 'tbp-core');
    }

    public function get_icon() {
        return 'eicon-favorite';
    }

    public function get_categories() {
        return ['tbp-core'];
    }

    public function get_keywords() {
        return ['icon', 'dynamic', 'acf', 'field'];
    }

    public function get_style_depends() {
        return ['tbp-dynamic-icon'];
    }

    protected function register_controls() {
        // Icon Section
        $this->start_controls_section(
            'section_icon',
            [
                'label' => __('Icon', 'tbp-core'),
            ]
        );

        $this->add_control(
            'icon_source',
            [
                'label' => __('Icon Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'static',
                'options' => [
                    'static' => __('Static', 'tbp-core'),
                    'dynamic' => __('Dynamic (ACF Field)', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'icon_source' => 'static',
                ],
            ]
        );

        // Get ACF icon fields
        $icon_fields = $this->get_acf_icon_fields();

        $this->add_control(
            'acf_icon_field',
            [
                'label' => __('ACF Icon Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => $icon_fields,
                'default' => '',
                'condition' => [
                    'icon_source' => 'dynamic',
                ],
            ]
        );

        $this->add_control(
            'view',
            [
                'label' => __('View', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'default' => __('Default', 'tbp-core'),
                    'stacked' => __('Stacked', 'tbp-core'),
                    'framed' => __('Framed', 'tbp-core'),
                ],
                'default' => 'default',
                'prefix_class' => 'tbp-view-',
            ]
        );

        $this->add_control(
            'shape',
            [
                'label' => __('Shape', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'square' => __('Square', 'tbp-core'),
                    'rounded' => __('Rounded', 'tbp-core'),
                    'circle' => __('Circle', 'tbp-core'),
                ],
                'default' => 'circle',
                'condition' => [
                    'view!' => 'default',
                ],
                'prefix_class' => 'tbp-shape-',
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => __('Link', 'tbp-core'),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style_icon',
            [
                'label' => __('Icon', 'tbp-core'),
                'tab' => Controls_Manager::TAB_STYLE,
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
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('icon_colors');

        $this->start_controls_tab(
            'icon_colors_normal',
            [
                'label' => __('Normal', 'tbp-core'),
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}.tbp-view-stacked .tbp-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}}.tbp-view-framed .tbp-icon, {{WRAPPER}}.tbp-view-default .tbp-icon' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
            ]
        );

        $this->add_control(
            'secondary_color',
            [
                'label' => __('Secondary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'condition' => [
                    'view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}}.tbp-view-framed .tbp-icon' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}}.tbp-view-stacked .tbp-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'svg_colors_heading',
            [
                'label' => __('SVG Colors', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'svg_fill_color',
            [
                'label' => __('Fill Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon svg' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg path' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg circle' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg rect' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg polygon' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg ellipse' => 'fill: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'svg_stroke_color',
            [
                'label' => __('Stroke Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon svg' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg path' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg circle' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg rect' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg line' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon svg polyline' => 'stroke: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'svg_stroke_width',
            [
                'label' => __('Stroke Width', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon svg' => 'stroke-width: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .tbp-icon svg path' => 'stroke-width: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .tbp-icon svg line' => 'stroke-width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_colors_hover',
            [
                'label' => __('Hover', 'tbp-core'),
            ]
        );

        $this->add_control(
            'hover_primary_color',
            [
                'label' => __('Primary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}.tbp-view-stacked .tbp-icon:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}}.tbp-view-framed .tbp-icon:hover, {{WRAPPER}}.tbp-view-default .tbp-icon:hover' => 'color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_secondary_color',
            [
                'label' => __('Secondary Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'condition' => [
                    'view!' => 'default',
                ],
                'selectors' => [
                    '{{WRAPPER}}.tbp-view-framed .tbp-icon:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}}.tbp-view-stacked .tbp-icon:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_svg_colors_heading',
            [
                'label' => __('SVG Colors', 'tbp-core'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'hover_svg_fill_color',
            [
                'label' => __('Fill Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon:hover svg' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg path' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg circle' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg rect' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg polygon' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg ellipse' => 'fill: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'hover_svg_stroke_color',
            [
                'label' => __('Stroke Color', 'tbp-core'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon:hover svg' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg path' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg circle' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg rect' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg line' => 'stroke: {{VALUE}} !important;',
                    '{{WRAPPER}} .tbp-icon:hover svg polyline' => 'stroke: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Hover Animation', 'tbp-core'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'size',
            [
                'label' => __('Size', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 6,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-icon svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tbp-icon img' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'icon_padding',
            [
                'label' => __('Padding', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon' => 'padding: {{SIZE}}{{UNIT}};',
                ],
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                ],
                'condition' => [
                    'view!' => 'default',
                ],
            ]
        );

        $this->add_responsive_control(
            'rotate',
            [
                'label' => __('Rotate', 'tbp-core'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['deg', 'grad', 'rad', 'turn'],
                'default' => [
                    'unit' => 'deg',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon i, {{WRAPPER}} .tbp-icon svg, {{WRAPPER}} .tbp-icon img' => 'transform: rotate({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->add_control(
            'border_width',
            [
                'label' => __('Border Width', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'view' => 'framed',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'tbp-core'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tbp-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'view!' => 'default',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get ACF icon/image fields
     */
    private function get_acf_icon_fields() {
        $fields = ['' => __('Select a field', 'tbp-core')];

        if (!function_exists('acf_get_field_groups')) {
            return $fields;
        }

        $field_groups = acf_get_field_groups();

        foreach ($field_groups as $group) {
            $group_fields = acf_get_fields($group['key']);

            if (!is_array($group_fields)) {
                continue;
            }

            foreach ($group_fields as $field) {
                // Include icon picker, image, and file fields
                if (in_array($field['type'], ['icon_picker', 'image', 'file', 'text', 'url'])) {
                    $fields[$field['name']] = $group['title'] . ' - ' . $field['label'];
                }
            }
        }

        return $fields;
    }

    /**
     * Get icon data from ACF field
     */
    private function get_dynamic_icon($field_name) {
        if (empty($field_name) || !function_exists('get_field')) {
            return null;
        }

        // Get current post ID
        $post_id = get_the_ID();

        // First try to get the raw value to avoid ACF formatting issues
        $icon_value = get_field($field_name, $post_id, false);

        if (empty($icon_value)) {
            return null;
        }

        // Get the field object to determine field type
        $field_object = get_field_object($field_name, $post_id, false, false);
        $field_type = $field_object ? $field_object['type'] : '';

        // Handle ACF Icon Picker field (raw value is serialized or array)
        if ($field_type === 'icon_picker') {
            // Raw value might be serialized
            if (is_string($icon_value) && strpos($icon_value, '{') === 0) {
                $icon_value = json_decode($icon_value, true);
            } elseif (is_string($icon_value)) {
                $icon_value = maybe_unserialize($icon_value);
            }

            if (is_array($icon_value)) {
                if (isset($icon_value['type'])) {
                    if ($icon_value['type'] === 'dashicons' || $icon_value['type'] === 'icon') {
                        return ['type' => 'class', 'value' => $icon_value['value']];
                    } elseif ($icon_value['type'] === 'media') {
                        $media_id = $icon_value['value'];
                        if (is_numeric($media_id)) {
                            $url = wp_get_attachment_url($media_id);
                            if ($url) {
                                return ['type' => 'url', 'value' => $url];
                            }
                        }
                    }
                }
            }
            return null;
        }

        // Handle other field types with formatted value
        $formatted_value = get_field($field_name, $post_id, true);

        if (empty($formatted_value)) {
            return null;
        }

        // Handle different ACF field formats
        if (is_array($formatted_value)) {
            // ACF Icon Picker with type (formatted)
            if (isset($formatted_value['type'])) {
                if ($formatted_value['type'] === 'dashicons' || $formatted_value['type'] === 'icon') {
                    return ['type' => 'class', 'value' => $formatted_value['value']];
                } elseif ($formatted_value['type'] === 'media') {
                    $media = $formatted_value['value'];
                    if (is_numeric($media)) {
                        return ['type' => 'url', 'value' => wp_get_attachment_url($media)];
                    } elseif (is_array($media) && !empty($media['url'])) {
                        return ['type' => 'url', 'value' => $media['url']];
                    } elseif (is_string($media)) {
                        return ['type' => 'url', 'value' => $media];
                    }
                }
            }
            // ACF Image field (returns array)
            if (!empty($formatted_value['url'])) {
                return ['type' => 'url', 'value' => $formatted_value['url']];
            }
        } elseif (is_string($formatted_value)) {
            // Check if URL or class
            if (filter_var($formatted_value, FILTER_VALIDATE_URL) || strpos($formatted_value, '/') !== false) {
                return ['type' => 'url', 'value' => $formatted_value];
            } else {
                return ['type' => 'class', 'value' => $formatted_value];
            }
        } elseif (is_numeric($formatted_value)) {
            // Attachment ID
            return ['type' => 'url', 'value' => wp_get_attachment_url($formatted_value)];
        }

        return null;
    }

    /**
     * Get inline SVG from URL
     */
    private function get_inline_svg($url) {
        // Check if it's an SVG
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if ($ext !== 'svg') {
            return false;
        }

        // Try to get attachment ID from URL
        $attachment_id = attachment_url_to_postid($url);

        if ($attachment_id) {
            $file_path = get_attached_file($attachment_id);
        } else {
            // Try to get file path from URL for local files
            $upload_dir = wp_upload_dir();
            if (strpos($url, $upload_dir['baseurl']) !== false) {
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
            } else {
                return false;
            }
        }

        if (!$file_path || !file_exists($file_path)) {
            return false;
        }

        $svg_content = file_get_contents($file_path);

        if (empty($svg_content)) {
            return false;
        }

        // Clean up SVG - remove XML declaration and doctype
        $svg_content = preg_replace('/<\?xml[^>]*\?>/i', '', $svg_content);
        $svg_content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $svg_content);

        // Add aria-hidden attribute
        $svg_content = preg_replace('/<svg/i', '<svg aria-hidden="true"', $svg_content, 1);

        return trim($svg_content);
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $icon_html = '';
        $has_icon = false;

        if ($settings['icon_source'] === 'dynamic') {
            $dynamic_icon = $this->get_dynamic_icon($settings['acf_icon_field']);

            if ($dynamic_icon) {
                $has_icon = true;
                if ($dynamic_icon['type'] === 'url') {
                    // Try to inline SVG for styling support
                    $inline_svg = $this->get_inline_svg($dynamic_icon['value']);
                    if ($inline_svg) {
                        $icon_html = $inline_svg;
                    } else {
                        // Fallback to img tag for non-SVG or external URLs
                        $icon_html = '<img src="' . esc_url($dynamic_icon['value']) . '" alt="" />';
                    }
                } else {
                    $icon_html = '<i class="' . esc_attr($dynamic_icon['value']) . '" aria-hidden="true"></i>';
                }
            }
        } else {
            if (!empty($settings['selected_icon']['value'])) {
                $has_icon = true;
            }
        }

        if (!$has_icon && $settings['icon_source'] === 'static' && empty($settings['selected_icon']['value'])) {
            return;
        }

        $this->add_render_attribute('wrapper', 'class', 'tbp-icon-wrapper');
        $this->add_render_attribute('icon-wrapper', 'class', 'tbp-icon');

        if (!empty($settings['hover_animation'])) {
            $this->add_render_attribute('icon-wrapper', 'class', 'elementor-animation-' . $settings['hover_animation']);
        }

        $icon_tag = 'div';

        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('icon-wrapper', $settings['link']);
            $icon_tag = 'a';
        }

        ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <<?php Utils::print_unescaped_internal_string($icon_tag . ' ' . $this->get_render_attribute_string('icon-wrapper')); ?>>
            <?php
            if ($settings['icon_source'] === 'dynamic' && $icon_html) {
                echo $icon_html;
            } elseif ($settings['icon_source'] === 'static' && !empty($settings['selected_icon']['value'])) {
                Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']);
            }
            ?>
            </<?php Utils::print_unescaped_internal_string($icon_tag); ?>>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var iconTag = settings.link?.url ? 'a' : 'div';
        var linkAttr = settings.link?.url ? 'href="' + elementor.helpers.sanitizeUrl(settings.link?.url) + '"' : '';

        view.addRenderAttribute('icon', 'class', 'tbp-icon');

        if ('' !== settings.hover_animation) {
            view.addRenderAttribute('icon', 'class', 'elementor-animation-' + settings.hover_animation);
        }

        if (settings.icon_source === 'static' && settings.selected_icon?.value) {
            var iconHTML = elementor.helpers.renderIcon(view, settings.selected_icon, { 'aria-hidden': true }, 'i', 'object');
        #>
        <div class="tbp-icon-wrapper">
            <{{{ iconTag }}} {{{ view.getRenderAttributeString('icon') }}} {{{ linkAttr }}}>
                <# if (iconHTML && iconHTML.rendered) { #>
                    {{{ iconHTML.value }}}
                <# } #>
            </{{{ iconTag }}}>
        </div>
        <# } else if (settings.icon_source === 'dynamic') { #>
        <div class="tbp-icon-wrapper">
            <div class="tbp-icon tbp-dynamic-placeholder">
                <i class="eicon-favorite" aria-hidden="true"></i>
            </div>
            <small style="display:block;text-align:center;opacity:0.6;margin-top:5px;"><?php _e('Dynamic icon from ACF field', 'tbp-core'); ?></small>
        </div>
        <# } #>
        <?php
    }
}
