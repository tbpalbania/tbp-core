<?php
if (!defined('ABSPATH')) {
    exit;
}

$allow_guests = (bool) get_option('tbp_testimonials_allow_guests', true);
$allow_rating = (bool) get_option('tbp_testimonials_allow_rating', true);
$product_id = isset($atts['product_id']) ? absint($atts['product_id']) : 0;

if (!$allow_guests && !is_user_logged_in()) {
    echo '<p class="tbp-testimonial-form__login-notice">' . esc_html__('Please log in to submit a testimonial.', 'tbp-core') . '</p>';
    return;
}

$current_user = wp_get_current_user();
?>
<div class="tbp-testimonial-form">
    <form class="tbp-testimonial-form__form" data-user-logged-in="<?php echo is_user_logged_in() ? 'true' : 'false'; ?>">
        <?php if ($product_id): ?>
            <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
        <?php endif; ?>

        <?php if ($allow_rating): ?>
            <div class="tbp-testimonial-form__field">
                <label class="tbp-testimonial-form__label"><?php esc_html_e('Rating', 'tbp-core'); ?> <span class="required">*</span></label>
                <div class="tbp-testimonial-form__rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star-<?php echo $i; ?>" <?php checked($i, 5); ?>>
                        <label for="star-<?php echo $i; ?>" title="<?php printf(esc_attr__('%d star(s)', 'tbp-core'), $i); ?>">&#9733;</label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php else: ?>
            <input type="hidden" name="rating" value="5">
        <?php endif; ?>

        <div class="tbp-testimonial-form__row">
            <div class="tbp-testimonial-form__field">
                <label for="author_name" class="tbp-testimonial-form__label"><?php esc_html_e('Name', 'tbp-core'); ?> <span class="required">*</span></label>
                <input type="text"
                       name="author_name"
                       id="author_name"
                       class="tbp-testimonial-form__input"
                       required
                       value="<?php echo esc_attr(is_user_logged_in() ? $current_user->display_name : ''); ?>">
            </div>
            <div class="tbp-testimonial-form__field">
                <label for="author_email" class="tbp-testimonial-form__label"><?php esc_html_e('Email', 'tbp-core'); ?> <span class="required">*</span></label>
                <input type="email"
                       name="author_email"
                       id="author_email"
                       class="tbp-testimonial-form__input"
                       required
                       value="<?php echo esc_attr(is_user_logged_in() ? $current_user->user_email : ''); ?>">
            </div>
        </div>

        <div class="tbp-testimonial-form__row">
            <div class="tbp-testimonial-form__field">
                <label for="author_company" class="tbp-testimonial-form__label"><?php esc_html_e('Company', 'tbp-core'); ?></label>
                <input type="text"
                       name="author_company"
                       id="author_company"
                       class="tbp-testimonial-form__input">
            </div>
            <div class="tbp-testimonial-form__field">
                <label for="author_position" class="tbp-testimonial-form__label"><?php esc_html_e('Position', 'tbp-core'); ?></label>
                <input type="text"
                       name="author_position"
                       id="author_position"
                       class="tbp-testimonial-form__input">
            </div>
        </div>

        <div class="tbp-testimonial-form__field">
            <label for="content" class="tbp-testimonial-form__label"><?php esc_html_e('Your Testimonial', 'tbp-core'); ?> <span class="required">*</span></label>
            <textarea name="content"
                      id="content"
                      class="tbp-testimonial-form__textarea"
                      required
                      minlength="<?php echo esc_attr(get_option('tbp_testimonials_min_length', 20)); ?>"
                      maxlength="<?php echo esc_attr(get_option('tbp_testimonials_max_length', 1000)); ?>"></textarea>
        </div>

        <!-- Honeypot -->
        <div class="tbp-testimonial-form__honeypot">
            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
        </div>

        <div class="tbp-testimonial-form__field">
            <button type="submit" class="tbp-testimonial-form__submit">
                <?php esc_html_e('Submit Testimonial', 'tbp-core'); ?>
            </button>
        </div>
    </form>
</div>
