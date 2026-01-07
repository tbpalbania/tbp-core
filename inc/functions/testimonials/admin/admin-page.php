<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle single actions
if (isset($_GET['action']) && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
    $action = sanitize_text_field($_GET['action']);
    $id = absint($_GET['id']);

    if (wp_verify_nonce($_GET['_wpnonce'], 'tbp_testimonial_action_' . $id)) {
        switch ($action) {
            case 'approve':
                $wpdb->update(TBP_Testimonials::$table_testimonials, ['status' => 'approved'], ['id' => $id]);
                break;
            case 'reject':
                $wpdb->update(TBP_Testimonials::$table_testimonials, ['status' => 'rejected'], ['id' => $id]);
                break;
            case 'delete':
                $wpdb->delete(TBP_Testimonials::$table_testimonialsmeta, ['testimonial_id' => $id]);
                $wpdb->delete(TBP_Testimonials::$table_testimonials, ['id' => $id]);
                break;
            case 'feature':
                $wpdb->update(TBP_Testimonials::$table_testimonials, ['featured' => 1], ['id' => $id]);
                break;
            case 'unfeature':
                $wpdb->update(TBP_Testimonials::$table_testimonials, ['featured' => 0], ['id' => $id]);
                break;
        }
        wp_redirect(remove_query_arg(['action', 'id', '_wpnonce']));
        exit;
    }
}

// Get counts
$counts = [
    'all'      => TBP_Testimonials::get_count(),
    'pending'  => TBP_Testimonials::get_count('pending'),
    'approved' => TBP_Testimonials::get_count('approved'),
    'rejected' => TBP_Testimonials::get_count('rejected'),
];

// Current filter
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
$per_page = 20;

// Build query
$where = ['1=1'];
$values = [];

if (!empty($current_status)) {
    $where[] = 'status = %s';
    $values[] = $current_status;
}

if (!empty($search)) {
    $where[] = '(author_name LIKE %s OR author_email LIKE %s OR content LIKE %s)';
    $search_like = '%' . $wpdb->esc_like($search) . '%';
    $values[] = $search_like;
    $values[] = $search_like;
    $values[] = $search_like;
}

$total_sql = "SELECT COUNT(*) FROM " . TBP_Testimonials::$table_testimonials . " WHERE " . implode(' AND ', $where);
if (!empty($values)) {
    $total_sql = $wpdb->prepare($total_sql, $values);
}
$total_items = (int) $wpdb->get_var($total_sql);
$total_pages = ceil($total_items / $per_page);
$offset = ($paged - 1) * $per_page;

$sql = "SELECT * FROM " . TBP_Testimonials::$table_testimonials . " WHERE " . implode(' AND ', $where) . " ORDER BY date_created DESC LIMIT %d OFFSET %d";
$values[] = $per_page;
$values[] = $offset;

$testimonials = $wpdb->get_results($wpdb->prepare($sql, $values));

// Helper for stars
function tbp_render_stars($rating) {
    $output = '<span class="tbp-stars">';
    for ($i = 1; $i <= 5; $i++) {
        $class = $i <= $rating ? 'filled' : 'empty';
        $output .= '<span class="tbp-star tbp-star--' . $class . '">&#9733;</span>';
    }
    $output .= '</span>';
    return $output;
}
?>
<div class="wrap tbp-testimonials-admin">
    <h1 class="wp-heading-inline"><?php esc_html_e('Testimonials', 'tbp-core'); ?></h1>
    <button type="button" class="page-title-action" id="tbp-add-testimonial-btn">
        <span class="dashicons dashicons-plus-alt" style="font-size: 16px; line-height: 26px;"></span>
        <?php esc_html_e('Add New', 'tbp-core'); ?>
    </button>
    <button type="button" class="page-title-action" id="tbp-testimonials-settings-btn">
        <span class="dashicons dashicons-admin-generic" style="font-size: 16px; line-height: 26px;"></span>
        <?php esc_html_e('Settings', 'tbp-core'); ?>
    </button>
    <hr class="wp-header-end">

    <!-- Status filters -->
    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url(remove_query_arg(['status', 'paged'])); ?>" class="<?php echo empty($current_status) ? 'current' : ''; ?>">
                <?php esc_html_e('All', 'tbp-core'); ?> <span class="count">(<?php echo esc_html($counts['all']); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg(['status' => 'pending', 'paged' => 1])); ?>" class="<?php echo $current_status === 'pending' ? 'current' : ''; ?>">
                <?php esc_html_e('Pending', 'tbp-core'); ?> <span class="count">(<?php echo esc_html($counts['pending']); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg(['status' => 'approved', 'paged' => 1])); ?>" class="<?php echo $current_status === 'approved' ? 'current' : ''; ?>">
                <?php esc_html_e('Approved', 'tbp-core'); ?> <span class="count">(<?php echo esc_html($counts['approved']); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo esc_url(add_query_arg(['status' => 'rejected', 'paged' => 1])); ?>" class="<?php echo $current_status === 'rejected' ? 'current' : ''; ?>">
                <?php esc_html_e('Rejected', 'tbp-core'); ?> <span class="count">(<?php echo esc_html($counts['rejected']); ?>)</span>
            </a>
        </li>
    </ul>

    <!-- Search and bulk actions -->
    <form method="get" class="search-form">
        <input type="hidden" name="page" value="tbp-testimonials">
        <?php if (!empty($current_status)): ?>
            <input type="hidden" name="status" value="<?php echo esc_attr($current_status); ?>">
        <?php endif; ?>
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search testimonials...', 'tbp-core'); ?>">
            <input type="submit" class="button" value="<?php esc_attr_e('Search', 'tbp-core'); ?>">
        </p>
    </form>

    <form method="post" id="tbp-testimonials-form">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="bulk_action" id="bulk-action-selector-top">
                    <option value=""><?php esc_html_e('Bulk Actions', 'tbp-core'); ?></option>
                    <option value="approve"><?php esc_html_e('Approve', 'tbp-core'); ?></option>
                    <option value="reject"><?php esc_html_e('Reject', 'tbp-core'); ?></option>
                    <option value="feature"><?php esc_html_e('Mark as Featured', 'tbp-core'); ?></option>
                    <option value="unfeature"><?php esc_html_e('Remove from Featured', 'tbp-core'); ?></option>
                    <option value="delete"><?php esc_html_e('Delete', 'tbp-core'); ?></option>
                </select>
                <button type="button" class="button action" id="doaction"><?php esc_html_e('Apply', 'tbp-core'); ?></button>
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num"><?php printf(_n('%s item', '%s items', $total_items, 'tbp-core'), number_format_i18n($total_items)); ?></span>
                <?php if ($total_pages > 1): ?>
                    <span class="pagination-links">
                        <?php if ($paged > 1): ?>
                            <a class="prev-page button" href="<?php echo esc_url(add_query_arg('paged', $paged - 1)); ?>">
                                <span aria-hidden="true">&lsaquo;</span>
                            </a>
                        <?php endif; ?>
                        <span class="paging-input">
                            <?php echo esc_html($paged); ?> / <?php echo esc_html($total_pages); ?>
                        </span>
                        <?php if ($paged < $total_pages): ?>
                            <a class="next-page button" href="<?php echo esc_url(add_query_arg('paged', $paged + 1)); ?>">
                                <span aria-hidden="true">&rsaquo;</span>
                            </a>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all">
                    </td>
                    <th class="manage-column column-author"><?php esc_html_e('Author', 'tbp-core'); ?></th>
                    <th class="manage-column column-rating"><?php esc_html_e('Rating', 'tbp-core'); ?></th>
                    <th class="manage-column column-content"><?php esc_html_e('Content', 'tbp-core'); ?></th>
                    <th class="manage-column column-status"><?php esc_html_e('Status', 'tbp-core'); ?></th>
                    <th class="manage-column column-date"><?php esc_html_e('Date', 'tbp-core'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($testimonials)): ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e('No testimonials found.', 'tbp-core'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($testimonials as $item): ?>
                        <tr class="<?php echo $item->featured ? 'tbp-featured' : ''; ?>">
                            <th class="check-column">
                                <input type="checkbox" name="testimonial_ids[]" value="<?php echo esc_attr($item->id); ?>">
                            </th>
                            <td class="column-author">
                                <strong><?php echo esc_html($item->author_name); ?></strong>
                                <?php if ($item->featured): ?>
                                    <span class="dashicons dashicons-star-filled" style="color: #f0b849;" title="<?php esc_attr_e('Featured', 'tbp-core'); ?>"></span>
                                <?php endif; ?>
                                <br>
                                <span class="description"><?php echo esc_html($item->author_email); ?></span>
                                <?php if (!empty($item->author_company)): ?>
                                    <br><span class="description"><?php echo esc_html($item->author_company); ?></span>
                                <?php endif; ?>
                                <div class="row-actions">
                                    <?php
                                    $actions = [];
                                    $nonce = wp_create_nonce('tbp_testimonial_action_' . $item->id);
                                    $base_url = add_query_arg(['id' => $item->id, '_wpnonce' => $nonce], admin_url('admin.php?page=tbp-testimonials'));

                                    // Edit action
                                    $actions[] = '<a href="#" class="tbp-edit-testimonial" data-id="' . esc_attr($item->id) . '">' . __('Edit', 'tbp-core') . '</a>';

                                    if ($item->status !== 'approved') {
                                        $actions[] = '<a href="' . esc_url(add_query_arg('action', 'approve', $base_url)) . '">' . __('Approve', 'tbp-core') . '</a>';
                                    }
                                    if ($item->status !== 'rejected') {
                                        $actions[] = '<a href="' . esc_url(add_query_arg('action', 'reject', $base_url)) . '">' . __('Reject', 'tbp-core') . '</a>';
                                    }
                                    if ($item->featured) {
                                        $actions[] = '<a href="' . esc_url(add_query_arg('action', 'unfeature', $base_url)) . '">' . __('Unfeature', 'tbp-core') . '</a>';
                                    } else {
                                        $actions[] = '<a href="' . esc_url(add_query_arg('action', 'feature', $base_url)) . '">' . __('Feature', 'tbp-core') . '</a>';
                                    }

                                    // Reply action
                                    $actions[] = '<a href="#" class="tbp-reply-testimonial" data-id="' . esc_attr($item->id) . '">' . __('Reply', 'tbp-core') . '</a>';

                                    $actions[] = '<a href="' . esc_url(add_query_arg('action', 'delete', $base_url)) . '" class="delete" onclick="return confirm(\'' . esc_js(__('Are you sure?', 'tbp-core')) . '\');">' . __('Delete', 'tbp-core') . '</a>';

                                    echo implode(' | ', $actions);
                                    ?>
                                </div>
                            </td>
                            <td class="column-rating">
                                <?php echo tbp_render_stars($item->rating); ?>
                            </td>
                            <td class="column-content">
                                <?php echo esc_html(wp_trim_words($item->content, 20)); ?>
                            </td>
                            <td class="column-status">
                                <?php
                                $status_class = 'tbp-status--' . $item->status;
                                $status_labels = [
                                    'pending'  => __('Pending', 'tbp-core'),
                                    'approved' => __('Approved', 'tbp-core'),
                                    'rejected' => __('Rejected', 'tbp-core'),
                                ];
                                ?>
                                <span class="tbp-status <?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_labels[$item->status] ?? $item->status); ?>
                                </span>
                            </td>
                            <td class="column-date">
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->date_created))); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<!-- Settings Modal -->
<div id="tbp-testimonials-settings-modal" class="tbp-modal" style="display: none;">
    <div class="tbp-modal__overlay"></div>
    <div class="tbp-modal__container">
        <div class="tbp-modal__header">
            <h2><?php esc_html_e('Testimonials Settings', 'tbp-core'); ?></h2>
            <button type="button" class="tbp-modal__close">&times;</button>
        </div>
        <div class="tbp-modal__content">
            <form id="tbp-testimonials-settings-form">
                <!-- General Settings -->
                <h3><?php esc_html_e('General Settings', 'tbp-core'); ?></h3>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="allow_guests" value="true">
                        <?php esc_html_e('Allow testimonials from non-logged-in users', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="require_approval" value="true">
                        <?php esc_html_e('Require admin approval before publishing', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="allow_rating" value="true">
                        <?php esc_html_e('Enable star ratings', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row tbp-settings-row--inline">
                    <label for="min_rating"><?php esc_html_e('Minimum allowed rating:', 'tbp-core'); ?></label>
                    <select name="min_rating" id="min_rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php esc_html_e('star(s)', 'tbp-core'); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="tbp-settings-row tbp-settings-row--inline">
                    <label for="min_length"><?php esc_html_e('Minimum content length:', 'tbp-core'); ?></label>
                    <input type="number" name="min_length" id="min_length" min="0" max="1000" style="width: 80px;"> <?php esc_html_e('characters', 'tbp-core'); ?>
                </div>

                <div class="tbp-settings-row tbp-settings-row--inline">
                    <label for="max_length"><?php esc_html_e('Maximum content length:', 'tbp-core'); ?></label>
                    <input type="number" name="max_length" id="max_length" min="100" max="10000" style="width: 80px;"> <?php esc_html_e('characters', 'tbp-core'); ?>
                </div>

                <!-- Notification Settings -->
                <h3><?php esc_html_e('Notifications', 'tbp-core'); ?></h3>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="notify_admin" value="true">
                        <?php esc_html_e('Send email notification for new testimonials', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row">
                    <label for="notification_email"><?php esc_html_e('Notification email:', 'tbp-core'); ?></label>
                    <input type="email" name="notification_email" id="notification_email" class="regular-text" placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                </div>

                <!-- Anti-Spam Settings -->
                <h3><?php esc_html_e('Anti-Spam', 'tbp-core'); ?></h3>

                <div class="tbp-settings-row tbp-settings-row--inline">
                    <label for="rate_limit"><?php esc_html_e('Rate limit:', 'tbp-core'); ?></label>
                    <input type="number" name="rate_limit" id="rate_limit" min="0" max="100" style="width: 60px;">
                    <?php esc_html_e('testimonials per', 'tbp-core'); ?>
                    <input type="number" name="rate_period" id="rate_period" min="1" max="168" style="width: 60px;">
                    <?php esc_html_e('hours', 'tbp-core'); ?>
                </div>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="require_email_verification" value="true">
                        <?php esc_html_e('Require email verification (coming soon)', 'tbp-core'); ?>
                    </label>
                </div>

                <!-- Display Settings -->
                <h3><?php esc_html_e('Display Settings', 'tbp-core'); ?></h3>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="show_rating" value="true">
                        <?php esc_html_e('Show star rating', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="show_date" value="true">
                        <?php esc_html_e('Show submission date', 'tbp-core'); ?>
                    </label>
                </div>

                <div class="tbp-settings-row">
                    <label>
                        <input type="checkbox" name="show_company" value="true">
                        <?php esc_html_e('Show company/position', 'tbp-core'); ?>
                    </label>
                </div>
            </form>
        </div>
        <div class="tbp-modal__footer">
            <button type="button" class="button" id="tbp-settings-cancel"><?php esc_html_e('Cancel', 'tbp-core'); ?></button>
            <button type="button" class="button button-primary" id="tbp-settings-save"><?php esc_html_e('Save Settings', 'tbp-core'); ?></button>
        </div>
    </div>
</div>

<!-- Add/Edit Testimonial Modal -->
<div id="tbp-testimonial-modal" class="tbp-modal" style="display: none;">
    <div class="tbp-modal__overlay"></div>
    <div class="tbp-modal__container tbp-modal__container--wide">
        <div class="tbp-modal__header">
            <h2 id="tbp-testimonial-modal-title"><?php esc_html_e('Add Testimonial', 'tbp-core'); ?></h2>
            <button type="button" class="tbp-modal__close">&times;</button>
        </div>
        <div class="tbp-modal__content">
            <form id="tbp-testimonial-form">
                <input type="hidden" name="testimonial_id" id="tbp-testimonial-id" value="0">

                <div class="tbp-form-row tbp-form-row--2col">
                    <div class="tbp-form-field">
                        <label for="tbp-author-name"><?php esc_html_e('Author Name', 'tbp-core'); ?> <span class="required">*</span></label>
                        <input type="text" name="author_name" id="tbp-author-name" class="regular-text" required>
                    </div>
                    <div class="tbp-form-field">
                        <label for="tbp-author-email"><?php esc_html_e('Author Email', 'tbp-core'); ?> <span class="required">*</span></label>
                        <input type="email" name="author_email" id="tbp-author-email" class="regular-text" required>
                    </div>
                </div>

                <div class="tbp-form-row tbp-form-row--2col">
                    <div class="tbp-form-field">
                        <label for="tbp-author-company"><?php esc_html_e('Company', 'tbp-core'); ?></label>
                        <input type="text" name="author_company" id="tbp-author-company" class="regular-text">
                    </div>
                    <div class="tbp-form-field">
                        <label for="tbp-author-position"><?php esc_html_e('Position', 'tbp-core'); ?></label>
                        <input type="text" name="author_position" id="tbp-author-position" class="regular-text">
                    </div>
                </div>

                <div class="tbp-form-row tbp-form-row--2col">
                    <div class="tbp-form-field">
                        <label for="tbp-testimonial-rating"><?php esc_html_e('Rating', 'tbp-core'); ?></label>
                        <select name="rating" id="tbp-testimonial-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo _n('star', 'stars', $i, 'tbp-core'); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="tbp-form-field">
                        <label for="tbp-testimonial-status"><?php esc_html_e('Status', 'tbp-core'); ?></label>
                        <select name="status" id="tbp-testimonial-status">
                            <option value="approved"><?php esc_html_e('Approved', 'tbp-core'); ?></option>
                            <option value="pending"><?php esc_html_e('Pending', 'tbp-core'); ?></option>
                            <option value="rejected"><?php esc_html_e('Rejected', 'tbp-core'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="tbp-form-field">
                    <label for="tbp-testimonial-content"><?php esc_html_e('Testimonial Content', 'tbp-core'); ?> <span class="required">*</span></label>
                    <textarea name="content" id="tbp-testimonial-content" rows="5" required></textarea>
                </div>

                <div class="tbp-form-row tbp-form-row--2col">
                    <div class="tbp-form-field">
                        <label>
                            <input type="checkbox" name="featured" id="tbp-testimonial-featured" value="1">
                            <?php esc_html_e('Featured testimonial', 'tbp-core'); ?>
                        </label>
                    </div>
                    <div class="tbp-form-field">
                        <label for="tbp-testimonial-product"><?php esc_html_e('Product/Service ID', 'tbp-core'); ?></label>
                        <input type="number" name="product_id" id="tbp-testimonial-product" min="0" style="width: 100px;">
                    </div>
                </div>

                <div class="tbp-form-message" id="tbp-testimonial-message" style="display: none;"></div>
            </form>
        </div>
        <div class="tbp-modal__footer">
            <button type="button" class="button" id="tbp-testimonial-cancel"><?php esc_html_e('Cancel', 'tbp-core'); ?></button>
            <button type="button" class="button button-primary" id="tbp-testimonial-save"><?php esc_html_e('Save Testimonial', 'tbp-core'); ?></button>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div id="tbp-reply-modal" class="tbp-modal" style="display: none;">
    <div class="tbp-modal__overlay"></div>
    <div class="tbp-modal__container">
        <div class="tbp-modal__header">
            <h2><?php esc_html_e('Add Reply', 'tbp-core'); ?></h2>
            <button type="button" class="tbp-modal__close">&times;</button>
        </div>
        <div class="tbp-modal__content">
            <form id="tbp-reply-form">
                <input type="hidden" name="testimonial_id" id="tbp-reply-testimonial-id" value="0">

                <div class="tbp-reply-original" id="tbp-reply-original">
                    <!-- Original testimonial will be loaded here -->
                </div>

                <div class="tbp-form-field">
                    <label for="tbp-reply-content"><?php esc_html_e('Your Reply', 'tbp-core'); ?> <span class="required">*</span></label>
                    <textarea name="reply_content" id="tbp-reply-content" rows="4" required placeholder="<?php esc_attr_e('Write your reply to this testimonial...', 'tbp-core'); ?>"></textarea>
                </div>

                <div class="tbp-form-message" id="tbp-reply-message" style="display: none;"></div>
            </form>

            <div class="tbp-existing-replies" id="tbp-existing-replies">
                <!-- Existing replies will be loaded here -->
            </div>
        </div>
        <div class="tbp-modal__footer">
            <button type="button" class="button" id="tbp-reply-cancel"><?php esc_html_e('Cancel', 'tbp-core'); ?></button>
            <button type="button" class="button button-primary" id="tbp-reply-save"><?php esc_html_e('Add Reply', 'tbp-core'); ?></button>
        </div>
    </div>
</div>
