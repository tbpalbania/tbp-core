<?php
/**
 * User Profile Fields for Academic Taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Academic_User_Fields {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Add fields to user profile
        add_action('show_user_profile', [$this, 'render_fields']);
        add_action('edit_user_profile', [$this, 'render_fields']);

        // Save fields
        add_action('personal_options_update', [$this, 'save_fields']);
        add_action('edit_user_profile_update', [$this, 'save_fields']);

        // Add fields to user registration (if needed)
        add_action('user_new_form', [$this, 'render_fields_admin_new']);
        add_action('user_register', [$this, 'save_fields']);
    }

    /**
     * Check if user should have academic fields
     */
    private function user_should_have_fields($user) {
        if (!$user || !isset($user->roles)) {
            return false;
        }

        // Administrators don't get these fields
        if (in_array('administrator', $user->roles)) {
            return false;
        }

        return true;
    }

    /**
     * Render fields on user profile
     */
    public function render_fields($user) {
        if (!$this->user_should_have_fields($user)) {
            return;
        }

        $values = TBP_Chain_Selector::get_user_meta($user->ID);
        ?>
        <h2><?php _e('Academic Information', 'tbp-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php _e('Academic Assignment', 'tbp-core'); ?></th>
                <td>
                    <?php TBP_Chain_Selector::render([], $values, 'user'); ?>
                    <p class="description"><?php _e('Select the faculty, department, cycle, and profile for this user.', 'tbp-core'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render fields on add new user form
     */
    public function render_fields_admin_new($type) {
        if ($type !== 'add-new-user') {
            return;
        }
        ?>
        <h2><?php _e('Academic Information', 'tbp-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php _e('Academic Assignment', 'tbp-core'); ?></th>
                <td>
                    <?php TBP_Chain_Selector::render([], [], 'user'); ?>
                    <p class="description"><?php _e('Select the faculty, department, cycle, and profile for this user.', 'tbp-core'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save fields
     */
    public function save_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $user = get_userdata($user_id);
        if (!$this->user_should_have_fields($user)) {
            return;
        }

        TBP_Chain_Selector::save_user_meta($user_id);
    }
}
