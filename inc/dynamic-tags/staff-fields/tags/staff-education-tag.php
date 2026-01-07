<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Staff_Education_Tag extends Tag {

    public function get_name() {
        return 'tbp-staff-education';
    }

    public function get_title() {
        return __('Staff Education', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-staff';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'source',
            [
                'label' => __('Source', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current'  => __('Current Post', 'tbp-core'),
                    'specific' => __('Specific Staff', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'staff_id',
            [
                'label' => __('Select Staff', 'tbp-core'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->get_staff_options(),
                'condition' => ['source' => 'specific'],
            ]
        );

        $this->add_control(
            'display',
            [
                'label' => __('Display', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'list'   => __('Formatted List', 'tbp-core'),
                    'single' => __('Single Item Field', 'tbp-core'),
                    'count'  => __('Count Only', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'item_index',
            [
                'label' => __('Item Index', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'condition' => ['display' => 'single'],
                'description' => __('0 = first item, 1 = second item, etc.', 'tbp-core'),
            ]
        );

        $this->add_control(
            'single_field',
            [
                'label' => __('Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'degree',
                'options' => [
                    'degree'      => __('Degree', 'tbp-core'),
                    'institution' => __('Institution', 'tbp-core'),
                    'start_date'  => __('From Date', 'tbp-core'),
                    'end_date'    => __('To Date', 'tbp-core'),
                    'duration'    => __('Duration', 'tbp-core'),
                    'description' => __('Description', 'tbp-core'),
                ],
                'condition' => ['display' => 'single'],
            ]
        );

        $this->add_control(
            'current_text',
            [
                'label' => __('Present Text', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Present', 'tbp-core'),
            ]
        );

        $this->add_control(
            'list_format',
            [
                'label' => __('Format', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ul',
                'options' => [
                    'ul'    => __('Unordered List', 'tbp-core'),
                    'ol'    => __('Ordered List', 'tbp-core'),
                    'div'   => __('Div Blocks', 'tbp-core'),
                ],
                'condition' => ['display' => 'list'],
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => __('Show Description', 'tbp-core'),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'condition' => ['display' => 'list'],
            ]
        );

        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'tbp-core'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('0 = show all', 'tbp-core'),
                'condition' => ['display' => 'list'],
            ]
        );

        $this->add_control(
            'fallback',
            [
                'label' => __('Fallback', 'tbp-core'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
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

        // Convert YYYY-MM to readable format
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

    public function render() {
        $settings = $this->get_settings();
        $source = $settings['source'];
        $display = $settings['display'];
        $fallback = $settings['fallback'];
        $current_text = $settings['current_text'] ?: __('Present', 'tbp-core');

        // Determine post ID
        $post_id = null;
        if ($source === 'current') {
            $post_id = get_the_ID();
        } elseif ($source === 'specific' && !empty($settings['staff_id'])) {
            $post_id = intval($settings['staff_id']);
        }

        if (!$post_id || get_post_type($post_id) !== 'staff') {
            echo esc_html($fallback);
            return;
        }

        $education = \TBP_Staff::get_education($post_id);

        if (empty($education)) {
            echo esc_html($fallback);
            return;
        }

        switch ($display) {
            case 'count':
                echo count($education);
                break;

            case 'single':
                $index = intval($settings['item_index']);
                $field = $settings['single_field'];

                if (!isset($education[$index])) {
                    echo esc_html($fallback);
                    return;
                }

                $item = $education[$index];

                switch ($field) {
                    case 'degree':
                    case 'institution':
                    case 'description':
                        echo esc_html($item[$field] ?? $fallback);
                        break;
                    case 'start_date':
                        echo esc_html($this->format_date($item['start_date'] ?? ''));
                        break;
                    case 'end_date':
                        if (!empty($item['current'])) {
                            echo esc_html($current_text);
                        } else {
                            echo esc_html($this->format_date($item['end_date'] ?? ''));
                        }
                        break;
                    case 'duration':
                        echo esc_html($this->calculate_duration(
                            $item['start_date'] ?? '',
                            $item['end_date'] ?? '',
                            !empty($item['current'])
                        ));
                        break;
                    default:
                        echo esc_html($fallback);
                }
                break;

            case 'list':
                $format = $settings['list_format'];
                $show_desc = $settings['show_description'] === 'yes';
                $limit = intval($settings['limit']);

                if ($limit > 0) {
                    $education = array_slice($education, 0, $limit);
                }

                $tag = $format === 'div' ? 'div' : $format;
                $item_tag = $format === 'div' ? 'div' : 'li';

                echo '<' . $tag . ' class="tbp-staff-education-list">';
                foreach ($education as $item) {
                    $end_display = !empty($item['current']) ? $current_text : $this->format_date($item['end_date'] ?? '');

                    echo '<' . $item_tag . ' class="tbp-staff-education-item">';
                    echo '<strong class="tbp-staff-education-degree">' . esc_html($item['degree']) . '</strong>';

                    if (!empty($item['institution'])) {
                        echo ' <span class="tbp-staff-education-separator">-</span> ';
                        echo '<span class="tbp-staff-education-institution">' . esc_html($item['institution']) . '</span>';
                    }

                    if (!empty($item['start_date'])) {
                        echo ' <span class="tbp-staff-education-dates">';
                        echo '(' . esc_html($this->format_date($item['start_date']));
                        echo ' - ' . esc_html($end_display) . ')';
                        echo '</span>';
                    }

                    if ($show_desc && !empty($item['description'])) {
                        echo '<p class="tbp-staff-education-description">' . esc_html($item['description']) . '</p>';
                    }
                    echo '</' . $item_tag . '>';
                }
                echo '</' . $tag . '>';
                break;
        }
    }
}
