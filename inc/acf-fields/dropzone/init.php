<?php
/**
 * @module-title: Dropzone
 * @module-description: Drag and drop file upload field with preview support for images, documents, and other files.
 * @module-version: 1.0.0
 * @module-usage: Add "Dropzone" field type in ACF field groups. Supports multiple file uploads with drag & drop.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define field constants
define('TBP_ACF_DROPZONE_PATH', plugin_dir_path(__FILE__));
define('TBP_ACF_DROPZONE_URL', plugin_dir_url(__FILE__));
define('TBP_ACF_DROPZONE_VERSION', '1.0.0');

/**
 * ACF Dropzone Field Type
 */
class TBP_ACF_Field_Dropzone extends acf_field {

    public function __construct() {
        $this->name = 'tbp_dropzone';
        $this->label = __('Dropzone', 'tbp-core');
        $this->category = 'content';
        $this->defaults = [
            'max_files' => 0,
            'max_size' => 10,
            'allowed_types' => '',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'library' => 'all',
            'display_mode' => 'grid',
        ];

        parent::__construct();
    }

    /**
     * Render field settings
     */
    public function render_field_settings($field) {
        // Max files
        acf_render_field_setting($field, [
            'label' => __('Maximum Files', 'tbp-core'),
            'instructions' => __('Maximum number of files allowed. Set to 0 for unlimited.', 'tbp-core'),
            'type' => 'number',
            'name' => 'max_files',
            'min' => 0,
        ]);

        // Max size
        acf_render_field_setting($field, [
            'label' => __('Maximum File Size', 'tbp-core'),
            'instructions' => __('Maximum file size in megabytes.', 'tbp-core'),
            'type' => 'number',
            'name' => 'max_size',
            'min' => 0,
            'append' => 'MB',
        ]);

        // Allowed file types
        acf_render_field_setting($field, [
            'label' => __('Allowed File Types', 'tbp-core'),
            'instructions' => __('Comma separated list of file types. Leave empty for all types.', 'tbp-core'),
            'type' => 'text',
            'name' => 'allowed_types',
            'placeholder' => 'jpg, png, pdf, docx',
        ]);

        // Return format
        acf_render_field_setting($field, [
            'label' => __('Return Format', 'tbp-core'),
            'instructions' => __('Specify the return value format.', 'tbp-core'),
            'type' => 'radio',
            'name' => 'return_format',
            'choices' => [
                'array' => __('File Array', 'tbp-core'),
                'url' => __('File URL', 'tbp-core'),
                'id' => __('File ID', 'tbp-core'),
            ],
            'layout' => 'horizontal',
        ]);

        // Preview size
        acf_render_field_setting($field, [
            'label' => __('Preview Size', 'tbp-core'),
            'instructions' => __('Image size for preview thumbnails.', 'tbp-core'),
            'type' => 'select',
            'name' => 'preview_size',
            'choices' => acf_get_image_sizes(),
        ]);

        // Library
        acf_render_field_setting($field, [
            'label' => __('Library', 'tbp-core'),
            'instructions' => __('Limit the media library choice.', 'tbp-core'),
            'type' => 'radio',
            'name' => 'library',
            'choices' => [
                'all' => __('All', 'tbp-core'),
                'uploadedTo' => __('Uploaded to post', 'tbp-core'),
            ],
            'layout' => 'horizontal',
        ]);

        // Display mode
        acf_render_field_setting($field, [
            'label' => __('Display Mode', 'tbp-core'),
            'instructions' => __('How uploaded files are displayed.', 'tbp-core'),
            'type' => 'radio',
            'name' => 'display_mode',
            'choices' => [
                'grid' => __('Grid', 'tbp-core'),
                'list' => __('List', 'tbp-core'),
            ],
            'layout' => 'horizontal',
        ]);
    }

    /**
     * Render field input
     */
    public function render_field($field) {
        $value = $field['value'];
        $file_ids = [];

        if (!empty($value)) {
            if (is_array($value)) {
                foreach ($value as $file) {
                    if (is_array($file) && isset($file['ID'])) {
                        $file_ids[] = $file['ID'];
                    } elseif (is_numeric($file)) {
                        $file_ids[] = intval($file);
                    }
                }
            } elseif (is_numeric($value)) {
                $file_ids[] = intval($value);
            }
        }

        $max_files = intval($field['max_files']);
        $max_size = intval($field['max_size']) * 1024 * 1024; // Convert to bytes
        $allowed_types = $field['allowed_types'];
        ?>
        <div class="tbp-dropzone-wrapper tbp-dropzone-<?php echo esc_attr($field['display_mode']); ?>"
             data-max-files="<?php echo esc_attr($max_files); ?>"
             data-max-size="<?php echo esc_attr($max_size); ?>"
             data-allowed-types="<?php echo esc_attr($allowed_types); ?>"
             data-preview-size="<?php echo esc_attr($field['preview_size']); ?>"
             data-library="<?php echo esc_attr($field['library']); ?>"
             data-display-mode="<?php echo esc_attr($field['display_mode']); ?>">

            <div class="tbp-dropzone-area">
                <div class="tbp-dropzone-message">
                    <span class="tbp-dropzone-icon dashicons dashicons-upload"></span>
                    <span class="tbp-dropzone-text"><?php _e('Drop files here', 'tbp-core'); ?></span>
                    <span class="tbp-dropzone-or"><?php _e('or', 'tbp-core'); ?></span>
                    <button type="button" class="tbp-dropzone-browse button button-secondary"><?php _e('Select from Media Library', 'tbp-core'); ?></button>
                    <span class="tbp-dropzone-hint">
                        <?php
                        $hints = [];
                        if ($max_files > 0) {
                            $hints[] = sprintf(__('Max %d files', 'tbp-core'), $max_files);
                        }
                        if ($max_size > 0) {
                            $hints[] = sprintf(__('Max %dMB each', 'tbp-core'), $field['max_size']);
                        }
                        if (!empty($allowed_types)) {
                            $hints[] = strtoupper($allowed_types);
                        }
                        echo esc_html(implode(' • ', $hints));
                        ?>
                    </span>
                </div>
            </div>

            <div class="tbp-dropzone-files">
                <?php foreach ($file_ids as $file_id) :
                    $file_path = get_attached_file($file_id);
                    $file_name = basename($file_path);
                    $file_type = wp_check_filetype($file_name);
                    $file_size = file_exists($file_path) ? size_format(filesize($file_path)) : '';
                    $file_ext = strtoupper($file_type['ext']);
                    $is_image = wp_attachment_is_image($file_id);
                    $thumb_url = $is_image ? wp_get_attachment_image_url($file_id, $field['preview_size']) : '';
                ?>
                <div class="tbp-dropzone-file" data-id="<?php echo esc_attr($file_id); ?>">
                    <div class="tbp-dropzone-file-preview">
                        <?php if ($is_image && $thumb_url) : ?>
                            <img src="<?php echo esc_url($thumb_url); ?>" alt="">
                        <?php else : ?>
                            <span class="dashicons dashicons-media-default"></span>
                        <?php endif; ?>
                    </div>
                    <div class="tbp-dropzone-file-info">
                        <span class="tbp-dropzone-file-name"><?php echo esc_html($file_name); ?></span>
                        <span class="tbp-dropzone-file-meta"><?php echo esc_html($file_ext . ($file_size ? ' • ' . $file_size : '')); ?></span>
                    </div>
                    <button type="button" class="tbp-dropzone-file-remove" title="<?php esc_attr_e('Remove', 'tbp-core'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="<?php echo esc_attr($field['name']); ?>" value="<?php echo esc_attr(implode(',', $file_ids)); ?>" class="tbp-dropzone-input">
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     */
    public function input_admin_enqueue_scripts() {
        wp_enqueue_media();

        wp_enqueue_style(
            'tbp-acf-dropzone',
            TBP_ACF_DROPZONE_URL . 'assets/css/dropzone.css',
            [],
            TBP_ACF_DROPZONE_VERSION
        );

        wp_enqueue_script(
            'tbp-acf-dropzone',
            TBP_ACF_DROPZONE_URL . 'assets/js/dropzone.js',
            ['jquery', 'acf-input'],
            TBP_ACF_DROPZONE_VERSION,
            true
        );

        wp_localize_script('tbp-acf-dropzone', 'tbpDropzone', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'upload_nonce' => wp_create_nonce('tbp_dropzone_upload'),
            'strings' => [
                'dropzone' => __('Drop files here or click to upload', 'tbp-core'),
                'maxFilesError' => __('Maximum number of files exceeded.', 'tbp-core'),
                'maxSizeError' => __('File is too large.', 'tbp-core'),
                'typeError' => __('File type not allowed.', 'tbp-core'),
                'uploadError' => __('Upload failed. Please try again.', 'tbp-core'),
            ],
        ]);
    }

    /**
     * Format value for API
     */
    public function format_value($value, $post_id, $field) {
        if (empty($value)) {
            return $field['max_files'] === 1 ? false : [];
        }

        $file_ids = is_array($value) ? $value : explode(',', $value);
        $file_ids = array_filter(array_map('intval', $file_ids));

        if (empty($file_ids)) {
            return $field['max_files'] === 1 ? false : [];
        }

        $files = [];

        foreach ($file_ids as $file_id) {
            $file = acf_get_attachment($file_id);

            if (!$file) {
                continue;
            }

            switch ($field['return_format']) {
                case 'url':
                    $files[] = $file['url'];
                    break;
                case 'id':
                    $files[] = $file['ID'];
                    break;
                default:
                    $files[] = $file;
                    break;
            }
        }

        // Return single value if max_files is 1
        if ($field['max_files'] === 1) {
            return !empty($files) ? $files[0] : false;
        }

        return $files;
    }

    /**
     * Update value before save
     */
    public function update_value($value, $post_id, $field) {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return array_filter(array_map('intval', $value));
    }

    /**
     * Validate value
     */
    public function validate_value($valid, $value, $field, $input) {
        if (!$valid) {
            return $valid;
        }

        if (empty($value)) {
            return $valid;
        }

        $file_ids = is_array($value) ? $value : explode(',', $value);
        $file_ids = array_filter(array_map('intval', $file_ids));

        // Check max files
        if ($field['max_files'] > 0 && count($file_ids) > $field['max_files']) {
            return sprintf(__('Maximum %d files allowed.', 'tbp-core'), $field['max_files']);
        }

        return $valid;
    }
}

// Initialize field
function tbp_acf_register_dropzone_field() {
    if (function_exists('acf_register_field_type')) {
        acf_register_field_type(new TBP_ACF_Field_Dropzone());
    }
}
add_action('acf/include_field_types', 'tbp_acf_register_dropzone_field');

// AJAX handler for file uploads
function tbp_acf_dropzone_upload() {
    // Check nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tbp_dropzone_upload')) {
        wp_send_json_error(['message' => __('Security check failed.', 'tbp-core')]);
    }

    // Check permissions
    if (!current_user_can('upload_files')) {
        wp_send_json_error(['message' => __('You do not have permission to upload files.', 'tbp-core')]);
    }

    // Check if file was uploaded
    if (empty($_FILES['file'])) {
        wp_send_json_error(['message' => __('No file was uploaded.', 'tbp-core')]);
    }

    // Handle the upload
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload('file', 0);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(['message' => $attachment_id->get_error_message()]);
    }

    // Get attachment data
    $attachment = get_post($attachment_id);
    $file_path = get_attached_file($attachment_id);
    $file_url = wp_get_attachment_url($attachment_id);
    $file_type = wp_check_filetype(basename($file_path));
    $is_image = wp_attachment_is_image($attachment_id);

    $response = [
        'id' => $attachment_id,
        'filename' => basename($file_path),
        'url' => $file_url,
        'type' => $is_image ? 'image' : 'file',
        'mime' => $file_type['type'],
        'filesizeHumanReadable' => size_format(filesize($file_path)),
    ];

    // Add image sizes if it's an image
    if ($is_image) {
        $sizes = [];
        foreach (get_intermediate_image_sizes() as $size) {
            $image = wp_get_attachment_image_src($attachment_id, $size);
            if ($image) {
                $sizes[$size] = [
                    'url' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                ];
            }
        }
        $response['sizes'] = $sizes;
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_tbp_dropzone_upload', 'tbp_acf_dropzone_upload');
