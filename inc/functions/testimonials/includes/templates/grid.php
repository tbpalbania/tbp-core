<?php
if (!defined('ABSPATH')) {
    exit;
}

$columns = isset($atts['columns']) ? (int) $atts['columns'] : 3;
$show_rating = (bool) get_option('tbp_testimonials_show_rating', true);
$show_date = (bool) get_option('tbp_testimonials_show_date', true);
$show_company = (bool) get_option('tbp_testimonials_show_company', true);

if (empty($testimonials)) {
    echo '<p>' . esc_html__('No testimonials found.', 'tbp-core') . '</p>';
    return;
}
?>
<div class="tbp-testimonials-grid tbp-testimonials-grid--cols-<?php echo esc_attr($columns); ?>">
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
                <div class="tbp-testimonial-card__avatar">
                    <?php
                    if ($testimonial->author_avatar) {
                        echo wp_get_attachment_image($testimonial->author_avatar, 'thumbnail');
                    } else {
                        echo esc_html(strtoupper(substr($testimonial->author_name, 0, 1)));
                    }
                    ?>
                </div>
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
