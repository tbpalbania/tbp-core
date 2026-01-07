<?php
namespace TBP_Core\DynamicTags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Staff_Text_Tag extends Tag {

    public function get_name() {
        return 'tbp-staff-text';
    }

    public function get_title() {
        return __('Staff Field', 'tbp-core');
    }

    public function get_group() {
        return 'tbp-staff';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    protected function register_controls() {
        $this->add_control(
            'field',
            [
                'label' => __('Field', 'tbp-core'),
                'type' => Controls_Manager::SELECT,
                'default' => 'full_name',
                'options' => [
                    'full_name'   => __('Full Name', 'tbp-core'),
                    'first_name'  => __('First Name', 'tbp-core'),
                    'last_name'   => __('Last Name', 'tbp-core'),
                    'designation' => __('Designation', 'tbp-core'),
                    'position'    => __('Position', 'tbp-core'),
                ],
            ]
        );

        $this->add_control(
            'include_designation',
            [
                'label'        => __('Include Designation', 'tbp-core'),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => '',
                'return_value' => 'yes',
                'description'  => __('Prepend designation (e.g., Dr.) before the name', 'tbp-core'),
                'condition'    => ['field' => 'full_name'],
            ]
        );

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

    public function render() {
        $settings = $this->get_settings();
        $field = $settings['field'];
        $source = $settings['source'];
        $fallback = $settings['fallback'];
        $include_designation = $settings['include_designation'] === 'yes';

        // Determine post ID
        $post_id = null;
        if ($source === 'current') {
            $post_id = get_the_ID();
        } elseif ($source === 'specific' && !empty($settings['staff_id'])) {
            $post_id = intval($settings['staff_id']);
        }

        if (!$post_id) {
            echo esc_html($fallback);
            return;
        }

        // Check if it's a staff post
        if (get_post_type($post_id) !== 'staff') {
            echo esc_html($fallback);
            return;
        }

        $value = '';

        switch ($field) {
            case 'full_name':
                $value = \TBP_Staff::get_full_name($post_id, $include_designation);
                break;
            case 'first_name':
                $value = \TBP_Staff::get_first_name($post_id);
                break;
            case 'last_name':
                $value = \TBP_Staff::get_last_name($post_id);
                break;
            case 'designation':
                $value = \TBP_Staff::get_designation($post_id);
                break;
            case 'position':
                $value = \TBP_Staff::get_position($post_id);
                break;
        }

        if (empty($value)) {
            $value = $fallback;
        }

        echo esc_html($value);
    }
}
