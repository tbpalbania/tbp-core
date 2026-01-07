<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap tbp-testimonials-settings">
    <h1><?php esc_html_e('Testimonials Settings', 'tbp-core'); ?></h1>

    <form id="tbp-testimonials-settings-form" class="tbp-settings-form">
        <div class="tbp-settings-sections">
            <!-- General Settings -->
            <div class="tbp-settings-section">
                <h2><?php esc_html_e('General Settings', 'tbp-core'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Guest Submissions', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="allow_guests" value="true">
                                <?php esc_html_e('Allow testimonials from non-logged-in users', 'tbp-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Approval', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="require_approval" value="true">
                                <?php esc_html_e('Require admin approval before publishing', 'tbp-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Star Ratings', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="allow_rating" value="true">
                                <?php esc_html_e('Enable star ratings', 'tbp-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Minimum Rating', 'tbp-core'); ?></th>
                        <td>
                            <select name="min_rating" id="min_rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php esc_html_e('star(s)', 'tbp-core'); ?></option>
                                <?php endfor; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Minimum allowed rating for submissions', 'tbp-core'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Content Length', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <?php esc_html_e('Min:', 'tbp-core'); ?>
                                <input type="number" name="min_length" id="min_length" min="0" max="1000" style="width: 80px;">
                            </label>
                            &nbsp;&nbsp;
                            <label>
                                <?php esc_html_e('Max:', 'tbp-core'); ?>
                                <input type="number" name="max_length" id="max_length" min="100" max="10000" style="width: 80px;">
                            </label>
                            <span><?php esc_html_e('characters', 'tbp-core'); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Notification Settings -->
            <div class="tbp-settings-section">
                <h2><?php esc_html_e('Notifications', 'tbp-core'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Email Notifications', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="notify_admin" value="true">
                                <?php esc_html_e('Send email notification for new testimonials', 'tbp-core'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Notification Email', 'tbp-core'); ?></th>
                        <td>
                            <input type="email" name="notification_email" id="notification_email" class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                            <p class="description"><?php esc_html_e('Leave empty to use the site admin email', 'tbp-core'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Anti-Spam Settings -->
            <div class="tbp-settings-section">
                <h2><?php esc_html_e('Anti-Spam', 'tbp-core'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Rate Limiting', 'tbp-core'); ?></th>
                        <td>
                            <label>
                                <input type="number" name="rate_limit" id="rate_limit" min="0" max="100" style="width: 60px;">
                                <?php esc_html_e('testimonials per', 'tbp-core'); ?>
                                <input type="number" name="rate_period" id="rate_period" min="1" max="168" style="width: 60px;">
                                <?php esc_html_e('hours per IP', 'tbp-core'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Set to 0 to disable rate limiting', 'tbp-core'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Display Settings -->
            <div class="tbp-settings-section">
                <h2><?php esc_html_e('Display Settings', 'tbp-core'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Show Elements', 'tbp-core'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_rating" value="true">
                                    <?php esc_html_e('Show star rating', 'tbp-core'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="show_date" value="true">
                                    <?php esc_html_e('Show submission date', 'tbp-core'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="show_company" value="true">
                                    <?php esc_html_e('Show company/position', 'tbp-core'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="submit">
            <button type="button" class="button button-primary" id="tbp-settings-save">
                <?php esc_html_e('Save Settings', 'tbp-core'); ?>
            </button>
            <span class="spinner"></span>
            <span class="tbp-settings-saved" style="display: none; color: green; margin-left: 10px;">
                <?php esc_html_e('Settings saved!', 'tbp-core'); ?>
            </span>
        </p>
    </form>
</div>

<style>
.tbp-testimonials-settings .tbp-settings-sections {
    max-width: 800px;
}
.tbp-testimonials-settings .tbp-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    margin-bottom: 20px;
    padding: 0 20px 20px;
}
.tbp-testimonials-settings .tbp-settings-section h2 {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin: 20px 0 15px;
}
.tbp-testimonials-settings .form-table th {
    width: 200px;
}
.tbp-testimonials-settings .spinner {
    float: none;
    margin-top: 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load current settings
    $.ajax({
        url: tbpTestimonials.ajaxurl,
        type: 'POST',
        data: {
            action: 'tbp_testimonials_get_settings',
            nonce: tbpTestimonials.nonce
        },
        success: function(response) {
            if (response.success) {
                var settings = response.data;
                $('[name="allow_guests"]').prop('checked', settings.allow_guests);
                $('[name="require_approval"]').prop('checked', settings.require_approval);
                $('[name="allow_rating"]').prop('checked', settings.allow_rating);
                $('[name="min_rating"]').val(settings.min_rating);
                $('[name="min_length"]').val(settings.min_length);
                $('[name="max_length"]').val(settings.max_length);
                $('[name="notify_admin"]').prop('checked', settings.notify_admin);
                $('[name="notification_email"]').val(settings.notification_email);
                $('[name="rate_limit"]').val(settings.rate_limit);
                $('[name="rate_period"]').val(settings.rate_period);
                $('[name="show_rating"]').prop('checked', settings.show_rating);
                $('[name="show_date"]').prop('checked', settings.show_date);
                $('[name="show_company"]').prop('checked', settings.show_company);
            }
        }
    });

    // Save settings
    $('#tbp-settings-save').on('click', function() {
        var $btn = $(this);
        var $spinner = $btn.siblings('.spinner');
        var $saved = $btn.siblings('.tbp-settings-saved');

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        $saved.hide();

        $.ajax({
            url: tbpTestimonials.ajaxurl,
            type: 'POST',
            data: {
                action: 'tbp_testimonials_save_settings',
                nonce: tbpTestimonials.nonce,
                allow_guests: $('[name="allow_guests"]').is(':checked').toString(),
                require_approval: $('[name="require_approval"]').is(':checked').toString(),
                allow_rating: $('[name="allow_rating"]').is(':checked').toString(),
                min_rating: $('[name="min_rating"]').val(),
                min_length: $('[name="min_length"]').val(),
                max_length: $('[name="max_length"]').val(),
                notify_admin: $('[name="notify_admin"]').is(':checked').toString(),
                notification_email: $('[name="notification_email"]').val(),
                rate_limit: $('[name="rate_limit"]').val(),
                rate_period: $('[name="rate_period"]').val(),
                show_rating: $('[name="show_rating"]').is(':checked').toString(),
                show_date: $('[name="show_date"]').is(':checked').toString(),
                show_company: $('[name="show_company"]').is(':checked').toString()
            },
            success: function(response) {
                $btn.prop('disabled', false);
                $spinner.removeClass('is-active');
                if (response.success) {
                    $saved.fadeIn().delay(2000).fadeOut();
                } else {
                    alert(response.data.message || tbpTestimonials.i18n.error);
                }
            },
            error: function() {
                $btn.prop('disabled', false);
                $spinner.removeClass('is-active');
                alert(tbpTestimonials.i18n.error);
            }
        });
    });
});
</script>
