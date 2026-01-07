<?php
/**
 * @module-title: User Profile Fields
 * @module-version: 1.0.0
 * @module-description: Adds custom avatar and social links repeater to user profiles
 * @module-usage: Activate module to add custom fields to user profiles
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TBP User Profile Fields
 */
class TBP_User_Profile_Fields {

    private static $instance = null;

    /**
     * Available social networks
     */
    private $social_networks = [
        'website'   => ['label' => 'Website', 'icon' => 'fas fa-globe'],
        'twitter'   => ['label' => 'Twitter / X', 'icon' => 'fab fa-x-twitter'],
        'facebook'  => ['label' => 'Facebook', 'icon' => 'fab fa-facebook'],
        'instagram' => ['label' => 'Instagram', 'icon' => 'fab fa-instagram'],
        'linkedin'  => ['label' => 'LinkedIn', 'icon' => 'fab fa-linkedin'],
        'youtube'   => ['label' => 'YouTube', 'icon' => 'fab fa-youtube'],
        'tiktok'    => ['label' => 'TikTok', 'icon' => 'fab fa-tiktok'],
        'pinterest' => ['label' => 'Pinterest', 'icon' => 'fab fa-pinterest'],
        'github'    => ['label' => 'GitHub', 'icon' => 'fab fa-github'],
        'dribbble'  => ['label' => 'Dribbble', 'icon' => 'fab fa-dribbble'],
        'behance'   => ['label' => 'Behance', 'icon' => 'fab fa-behance'],
        'medium'    => ['label' => 'Medium', 'icon' => 'fab fa-medium'],
        'telegram'  => ['label' => 'Telegram', 'icon' => 'fab fa-telegram'],
        'whatsapp'  => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'],
        'discord'   => ['label' => 'Discord', 'icon' => 'fab fa-discord'],
        'twitch'    => ['label' => 'Twitch', 'icon' => 'fab fa-twitch'],
        'spotify'   => ['label' => 'Spotify', 'icon' => 'fab fa-spotify'],
        'soundcloud'=> ['label' => 'SoundCloud', 'icon' => 'fab fa-soundcloud'],
        'custom'    => ['label' => 'Custom Link', 'icon' => 'fas fa-link'],
    ];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Profile fields
        add_action('show_user_profile', [$this, 'render_profile_fields']);
        add_action('edit_user_profile', [$this, 'render_profile_fields']);

        // Save fields
        add_action('personal_options_update', [$this, 'save_profile_fields']);
        add_action('edit_user_profile_update', [$this, 'save_profile_fields']);

        // Custom avatar filter
        add_filter('get_avatar_url', [$this, 'custom_avatar_url'], 10, 3);
        add_filter('get_avatar', [$this, 'custom_avatar'], 10, 6);

        // Admin scripts
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);

        // AJAX handlers
        add_action('wp_ajax_tbp_get_social_row', [$this, 'ajax_get_social_row']);
    }

    /**
     * Render profile fields
     */
    public function render_profile_fields($user) {
        $custom_avatar = get_user_meta($user->ID, 'tbp_custom_avatar', true);
        $social_links = get_user_meta($user->ID, 'tbp_social_links', true);
        $long_bio = get_user_meta($user->ID, 'tbp_long_biography', true);

        if (!is_array($social_links)) {
            $social_links = [];
        }

        wp_nonce_field('tbp_user_profile_fields', 'tbp_user_profile_nonce');
        ?>
        <h2><?php esc_html_e('Long Biography', 'tbp-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="tbp_long_biography"><?php esc_html_e('Full Biography', 'tbp-core'); ?></label></th>
                <td>
                    <?php
                    wp_editor($long_bio, 'tbp_long_biography', [
                        'textarea_name' => 'tbp_long_biography',
                        'textarea_rows' => 12,
                        'media_buttons' => true,
                        'teeny' => false,
                        'quicktags' => true,
                        'tinymce' => [
                            'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_fullscreen,wp_adv',
                            'toolbar2' => 'styleselect,fontselect,fontsizeselect,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        ],
                    ]);
                    ?>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e('Write a detailed biography. This can be displayed using the "Author Long Bio" dynamic tag in Elementor. The short bio above is kept for quick descriptions.', 'tbp-core'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Custom Avatar', 'tbp-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="tbp_custom_avatar"><?php esc_html_e('Profile Picture', 'tbp-core'); ?></label></th>
                <td>
                    <div class="tbp-avatar-field">
                        <div class="tbp-avatar-preview" style="margin-bottom: 10px;">
                            <?php if ($custom_avatar) : ?>
                                <img src="<?php echo esc_url($custom_avatar); ?>" alt="" style="max-width: 150px; height: auto; border-radius: 50%; display: block;">
                            <?php else : ?>
                                <?php echo get_avatar($user->ID, 150); ?>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="tbp_custom_avatar" id="tbp_custom_avatar" value="<?php echo esc_attr($custom_avatar); ?>">
                        <button type="button" class="button tbp-upload-avatar"><?php esc_html_e('Upload Image', 'tbp-core'); ?></button>
                        <?php if ($custom_avatar) : ?>
                            <button type="button" class="button tbp-remove-avatar" style="color: #a00;"><?php esc_html_e('Remove', 'tbp-core'); ?></button>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('Upload a custom profile picture. If not set, the default Gravatar will be used.', 'tbp-core'); ?></p>
                    </div>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Social Links', 'tbp-core'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><?php esc_html_e('Social Networks', 'tbp-core'); ?></th>
                <td>
                    <div class="tbp-social-links-wrapper">
                        <div class="tbp-social-links-list" id="tbp-social-links-list">
                            <?php
                            if (!empty($social_links)) :
                                foreach ($social_links as $index => $link) :
                                    $this->render_social_row($index, $link);
                                endforeach;
                            endif;
                            ?>
                        </div>
                        <button type="button" class="button tbp-add-social" style="margin-top: 10px;">
                            <span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Add Social Link', 'tbp-core'); ?>
                        </button>
                        <p class="description" style="margin-top: 10px;"><?php esc_html_e('Add your social media profiles. These can be displayed in the Author Box widget.', 'tbp-core'); ?></p>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render a single social link row
     */
    private function render_social_row($index, $link = []) {
        $network = isset($link['network']) ? $link['network'] : '';
        $url = isset($link['url']) ? $link['url'] : '';
        $label = isset($link['label']) ? $link['label'] : '';
        ?>
        <div class="tbp-social-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center; padding: 12px; background: #f9f9f9; border-radius: 6px;">
            <select name="tbp_social_links[<?php echo esc_attr($index); ?>][network]" style="min-width: 150px;">
                <option value=""><?php esc_html_e('Select Network', 'tbp-core'); ?></option>
                <?php foreach ($this->social_networks as $key => $data) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($network, $key); ?>>
                        <?php echo esc_html($data['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="url"
                   name="tbp_social_links[<?php echo esc_attr($index); ?>][url]"
                   value="<?php echo esc_url($url); ?>"
                   placeholder="<?php esc_attr_e('https://...', 'tbp-core'); ?>"
                   style="flex: 1;">
            <input type="text"
                   name="tbp_social_links[<?php echo esc_attr($index); ?>][label]"
                   value="<?php echo esc_attr($label); ?>"
                   placeholder="<?php esc_attr_e('Label (optional)', 'tbp-core'); ?>"
                   style="width: 150px;"
                   class="tbp-social-label <?php echo $network === 'custom' ? '' : 'hidden'; ?>">
            <button type="button" class="button tbp-remove-social" style="color: #a00;" title="<?php esc_attr_e('Remove', 'tbp-core'); ?>">
                <span class="dashicons dashicons-trash" style="vertical-align: middle;"></span>
            </button>
        </div>
        <?php
    }

    /**
     * Save profile fields
     */
    public function save_profile_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        if (!isset($_POST['tbp_user_profile_nonce']) || !wp_verify_nonce($_POST['tbp_user_profile_nonce'], 'tbp_user_profile_fields')) {
            return;
        }

        // Save custom avatar
        if (isset($_POST['tbp_custom_avatar'])) {
            $avatar_url = esc_url_raw($_POST['tbp_custom_avatar']);
            if (empty($avatar_url)) {
                delete_user_meta($user_id, 'tbp_custom_avatar');
            } else {
                update_user_meta($user_id, 'tbp_custom_avatar', $avatar_url);
            }
        }

        // Save social links
        if (isset($_POST['tbp_social_links']) && is_array($_POST['tbp_social_links'])) {
            $social_links = [];
            foreach ($_POST['tbp_social_links'] as $link) {
                if (!empty($link['network']) && !empty($link['url'])) {
                    $social_links[] = [
                        'network' => sanitize_key($link['network']),
                        'url'     => esc_url_raw($link['url']),
                        'label'   => isset($link['label']) ? sanitize_text_field($link['label']) : '',
                    ];
                }
            }

            if (empty($social_links)) {
                delete_user_meta($user_id, 'tbp_social_links');
            } else {
                update_user_meta($user_id, 'tbp_social_links', $social_links);
            }
        } else {
            delete_user_meta($user_id, 'tbp_social_links');
        }

        // Save long biography
        if (isset($_POST['tbp_long_biography'])) {
            $long_bio = wp_kses_post($_POST['tbp_long_biography']);
            if (empty($long_bio)) {
                delete_user_meta($user_id, 'tbp_long_biography');
            } else {
                update_user_meta($user_id, 'tbp_long_biography', $long_bio);
            }
        }
    }

    /**
     * Filter avatar URL to use custom avatar
     */
    public function custom_avatar_url($url, $id_or_email, $args) {
        $user_id = $this->get_user_id($id_or_email);

        if (!$user_id) {
            return $url;
        }

        $custom_avatar = get_user_meta($user_id, 'tbp_custom_avatar', true);

        if (!empty($custom_avatar)) {
            return $custom_avatar;
        }

        return $url;
    }

    /**
     * Filter avatar HTML to use custom avatar
     */
    public function custom_avatar($avatar, $id_or_email, $size, $default, $alt, $args = []) {
        $user_id = $this->get_user_id($id_or_email);

        if (!$user_id) {
            return $avatar;
        }

        $custom_avatar = get_user_meta($user_id, 'tbp_custom_avatar', true);

        if (empty($custom_avatar)) {
            return $avatar;
        }

        // Build custom avatar HTML
        $class = isset($args['class']) ? $args['class'] : ['avatar', 'avatar-' . $size, 'photo'];
        if (is_array($class)) {
            $class = implode(' ', $class);
        }

        $avatar = sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d" class="%s" loading="lazy" decoding="async">',
            esc_url($custom_avatar),
            esc_attr($alt),
            (int) $size,
            (int) $size,
            esc_attr($class)
        );

        return $avatar;
    }

    /**
     * Get user ID from various formats
     */
    private function get_user_id($id_or_email) {
        if (is_numeric($id_or_email)) {
            return (int) $id_or_email;
        }

        if (is_object($id_or_email)) {
            if (isset($id_or_email->user_id) && $id_or_email->user_id) {
                return (int) $id_or_email->user_id;
            }
            if (isset($id_or_email->ID)) {
                return (int) $id_or_email->ID;
            }
            if (isset($id_or_email->comment_author_email)) {
                $user = get_user_by('email', $id_or_email->comment_author_email);
                return $user ? $user->ID : 0;
            }
            return 0;
        }

        if (is_string($id_or_email) && is_email($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            return $user ? $user->ID : 0;
        }

        return 0;
    }

    /**
     * Admin scripts and styles
     */
    public function admin_scripts($hook) {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }

        wp_enqueue_media();

        wp_add_inline_style('wp-admin', '
            .tbp-social-row.hidden .tbp-social-label,
            .tbp-social-label.hidden {
                display: none !important;
            }
            .tbp-social-row {
                transition: background-color 0.2s ease;
            }
            .tbp-social-row:hover {
                background-color: #f0f0f1;
            }
        ');

        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Media uploader for avatar
                var avatarFrame;
                $(".tbp-upload-avatar").on("click", function(e) {
                    e.preventDefault();

                    if (avatarFrame) {
                        avatarFrame.open();
                        return;
                    }

                    avatarFrame = wp.media({
                        title: "' . esc_js(__('Select Profile Picture', 'tbp-core')) . '",
                        button: { text: "' . esc_js(__('Use as Avatar', 'tbp-core')) . '" },
                        multiple: false,
                        library: { type: "image" }
                    });

                    avatarFrame.on("select", function() {
                        var attachment = avatarFrame.state().get("selection").first().toJSON();
                        $("#tbp_custom_avatar").val(attachment.url);
                        $(".tbp-avatar-preview").html("<img src=\"" + attachment.url + "\" alt=\"\" style=\"max-width: 150px; height: auto; border-radius: 50%; display: block;\">");

                        if ($(".tbp-remove-avatar").length === 0) {
                            $(".tbp-upload-avatar").after(" <button type=\"button\" class=\"button tbp-remove-avatar\" style=\"color: #a00;\">' . esc_js(__('Remove', 'tbp-core')) . '</button>");
                        }
                    });

                    avatarFrame.open();
                });

                // Remove avatar
                $(document).on("click", ".tbp-remove-avatar", function(e) {
                    e.preventDefault();
                    $("#tbp_custom_avatar").val("");
                    $(".tbp-avatar-preview").html("' . addslashes(get_avatar(get_current_user_id(), 150)) . '");
                    $(this).remove();
                });

                // Add social link
                var socialIndex = ' . (count(get_user_meta(get_current_user_id(), 'tbp_social_links', true) ?: []) + 1) . ';
                $(".tbp-add-social").on("click", function() {
                    var row = $("<div>", {
                        class: "tbp-social-row",
                        style: "display: flex; gap: 10px; margin-bottom: 10px; align-items: center; padding: 12px; background: #f9f9f9; border-radius: 6px;"
                    });

                    var networks = ' . json_encode($this->social_networks) . ';
                    var select = $("<select>", {
                        name: "tbp_social_links[" + socialIndex + "][network]",
                        style: "min-width: 150px;"
                    }).append($("<option>", { value: "", text: "' . esc_js(__('Select Network', 'tbp-core')) . '" }));

                    $.each(networks, function(key, data) {
                        select.append($("<option>", { value: key, text: data.label }));
                    });

                    var urlInput = $("<input>", {
                        type: "url",
                        name: "tbp_social_links[" + socialIndex + "][url]",
                        placeholder: "https://...",
                        style: "flex: 1;"
                    });

                    var labelInput = $("<input>", {
                        type: "text",
                        name: "tbp_social_links[" + socialIndex + "][label]",
                        placeholder: "' . esc_js(__('Label (optional)', 'tbp-core')) . '",
                        style: "width: 150px;",
                        class: "tbp-social-label hidden"
                    });

                    var removeBtn = $("<button>", {
                        type: "button",
                        class: "button tbp-remove-social",
                        style: "color: #a00;",
                        title: "' . esc_js(__('Remove', 'tbp-core')) . '"
                    }).html("<span class=\"dashicons dashicons-trash\" style=\"vertical-align: middle;\"></span>");

                    row.append(select, urlInput, labelInput, removeBtn);
                    $("#tbp-social-links-list").append(row);
                    socialIndex++;
                });

                // Remove social link
                $(document).on("click", ".tbp-remove-social", function() {
                    $(this).closest(".tbp-social-row").remove();
                });

                // Show/hide label field based on network selection
                $(document).on("change", ".tbp-social-row select", function() {
                    var $row = $(this).closest(".tbp-social-row");
                    var $label = $row.find(".tbp-social-label");
                    if ($(this).val() === "custom") {
                        $label.removeClass("hidden");
                    } else {
                        $label.addClass("hidden");
                    }
                });
            });
        ');
    }

    /**
     * Get social networks list
     */
    public static function get_social_networks() {
        $instance = self::instance();
        return $instance->social_networks;
    }

    /**
     * Get user social links
     */
    public static function get_user_social_links($user_id) {
        $links = get_user_meta($user_id, 'tbp_social_links', true);

        if (!is_array($links)) {
            return [];
        }

        $networks = self::get_social_networks();
        $result = [];

        foreach ($links as $link) {
            if (empty($link['network']) || empty($link['url'])) {
                continue;
            }

            $network_key = $link['network'];
            $network_data = isset($networks[$network_key]) ? $networks[$network_key] : $networks['custom'];

            $result[] = [
                'network' => $network_key,
                'url'     => $link['url'],
                'label'   => !empty($link['label']) ? $link['label'] : $network_data['label'],
                'icon'    => $network_data['icon'],
            ];
        }

        return $result;
    }

    /**
     * Get user custom avatar URL
     */
    public static function get_user_avatar_url($user_id, $size = 96) {
        $custom_avatar = get_user_meta($user_id, 'tbp_custom_avatar', true);

        if (!empty($custom_avatar)) {
            return $custom_avatar;
        }

        return get_avatar_url($user_id, ['size' => $size]);
    }

    /**
     * Get user long biography
     */
    public static function get_user_long_biography($user_id, $apply_filters = true) {
        $long_bio = get_user_meta($user_id, 'tbp_long_biography', true);

        if (empty($long_bio)) {
            return '';
        }

        if ($apply_filters) {
            $long_bio = apply_filters('the_content', $long_bio);
        }

        return $long_bio;
    }

    /**
     * Get current author ID (for archives and single posts)
     */
    public static function get_current_author_id() {
        // On author archive
        if (is_author()) {
            $author = get_queried_object();
            return $author ? $author->ID : 0;
        }

        // On single post
        if (is_singular()) {
            return get_post_field('post_author', get_the_ID());
        }

        // Fallback to global post
        global $post;
        if ($post) {
            return $post->post_author;
        }

        return 0;
    }
}

TBP_User_Profile_Fields::instance();
