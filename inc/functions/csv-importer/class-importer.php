<?php
/**
 * TBP CSV/XLSX Importer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_CSV_Importer {

    private static $instance = null;

    public static function init() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // Import AJAX handlers
        add_action('wp_ajax_tbp_importer_upload', [$this, 'ajax_upload_file']);
        add_action('wp_ajax_tbp_importer_get_fields', [$this, 'ajax_get_fields']);
        add_action('wp_ajax_tbp_importer_preview', [$this, 'ajax_preview_import']);
        add_action('wp_ajax_tbp_importer_process', [$this, 'ajax_process_import']);

        // Export AJAX handlers
        add_action('wp_ajax_tbp_exporter_get_fields', [$this, 'ajax_export_get_fields']);
        add_action('wp_ajax_tbp_exporter_preview', [$this, 'ajax_export_preview']);
        add_action('wp_ajax_tbp_exporter_download', [$this, 'ajax_export_download']);
    }

    public function add_admin_menu() {
        add_management_page(
            __('TBP Importer', 'tbp-core'),
            __('TBP Importer', 'tbp-core'),
            'manage_options',
            'tbp-importer',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'tools_page_tbp-importer') {
            return;
        }

        wp_enqueue_style(
            'tbp-importer',
            TBP_IMPORTER_URL . 'assets/css/importer.css',
            [],
            TBP_CORE_VERSION
        );

        wp_enqueue_script(
            'tbp-importer',
            TBP_IMPORTER_URL . 'assets/js/importer.js',
            ['jquery'],
            TBP_CORE_VERSION,
            true
        );

        wp_localize_script('tbp-importer', 'tbpImporter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tbp_importer_nonce'),
            'strings' => [
                'uploadError' => __('Error uploading file', 'tbp-core'),
                'parseError' => __('Error parsing file', 'tbp-core'),
                'importSuccess' => __('Import completed successfully', 'tbp-core'),
                'importError' => __('Error during import', 'tbp-core'),
                'processing' => __('Processing...', 'tbp-core'),
                'selectFile' => __('Please select a file', 'tbp-core'),
                'selectDestination' => __('Please select a destination', 'tbp-core'),
                'mapRequired' => __('Please map at least the title field', 'tbp-core'),
            ]
        ]);
    }

    public function render_admin_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'import';
        ?>
        <div class="wrap tbp-importer-wrap">
            <h1><?php _e('TBP Import / Export', 'tbp-core'); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=tbp-importer&tab=import" class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Import', 'tbp-core'); ?>
                </a>
                <a href="?page=tbp-importer&tab=export" class="nav-tab <?php echo $active_tab === 'export' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Export', 'tbp-core'); ?>
                </a>
            </nav>

            <?php if ($active_tab === 'export'): ?>
                <?php $this->render_export_page(); ?>
            <?php else: ?>
            <div class="tbp-importer-layout">
            <div class="tbp-importer-wizard">
                <!-- Progress Steps -->
                <div class="tbp-wizard-steps">
                    <div class="tbp-step active" data-step="1">
                        <span class="step-number">1</span>
                        <span class="step-label"><?php _e('Upload', 'tbp-core'); ?></span>
                    </div>
                    <div class="tbp-step" data-step="2">
                        <span class="step-number">2</span>
                        <span class="step-label"><?php _e('Destination', 'tbp-core'); ?></span>
                    </div>
                    <div class="tbp-step" data-step="3">
                        <span class="step-number">3</span>
                        <span class="step-label"><?php _e('Mapping', 'tbp-core'); ?></span>
                    </div>
                    <div class="tbp-step" data-step="4">
                        <span class="step-number">4</span>
                        <span class="step-label"><?php _e('Options', 'tbp-core'); ?></span>
                    </div>
                    <div class="tbp-step" data-step="5">
                        <span class="step-number">5</span>
                        <span class="step-label"><?php _e('Import', 'tbp-core'); ?></span>
                    </div>
                </div>

                <!-- Step 1: Upload -->
                <div class="tbp-wizard-content" data-step="1">
                    <h2><?php _e('Upload File', 'tbp-core'); ?></h2>
                    <p><?php _e('Upload a CSV or XLSX file to import.', 'tbp-core'); ?></p>

                    <div class="tbp-upload-area">
                        <input type="file" id="tbp-import-file" accept=".csv,.xlsx" />
                        <label for="tbp-import-file" class="tbp-upload-label">
                            <span class="dashicons dashicons-upload"></span>
                            <span><?php _e('Choose file or drag here', 'tbp-core'); ?></span>
                        </label>
                        <div class="tbp-file-info" style="display:none;"></div>
                    </div>

                    <div class="tbp-import-type">
                        <h3><?php _e('Import Type', 'tbp-core'); ?></h3>
                        <label>
                            <input type="radio" name="import_type" value="posts" checked />
                            <?php _e('Posts / Custom Post Types', 'tbp-core'); ?>
                        </label>
                        <label>
                            <input type="radio" name="import_type" value="taxonomy" />
                            <?php _e('Taxonomy Terms', 'tbp-core'); ?>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Destination -->
                <div class="tbp-wizard-content" data-step="2" style="display:none;">
                    <h2><?php _e('Select Destination', 'tbp-core'); ?></h2>

                    <div class="tbp-destination-posts">
                        <h3><?php _e('Post Type', 'tbp-core'); ?></h3>
                        <select id="tbp-post-type">
                            <?php
                            $post_types = get_post_types(['public' => true], 'objects');
                            foreach ($post_types as $pt) {
                                if ($pt->name === 'attachment') continue;
                                echo '<option value="' . esc_attr($pt->name) . '">' . esc_html($pt->label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="tbp-destination-taxonomy" style="display:none;">
                        <h3><?php _e('Taxonomy', 'tbp-core'); ?></h3>
                        <select id="tbp-taxonomy">
                            <?php
                            $taxonomies = get_taxonomies(['public' => true], 'objects');
                            foreach ($taxonomies as $tax) {
                                echo '<option value="' . esc_attr($tax->name) . '">' . esc_html($tax->label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Step 3: Field Mapping -->
                <div class="tbp-wizard-content" data-step="3" style="display:none;">
                    <h2><?php _e('Map Fields', 'tbp-core'); ?></h2>
                    <p><?php _e('Map your CSV/XLSX columns to WordPress fields.', 'tbp-core'); ?></p>

                    <div class="tbp-mapping-table-wrap">
                        <table class="tbp-mapping-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('CSV Column', 'tbp-core'); ?></th>
                                    <th><?php _e('Sample Data', 'tbp-core'); ?></th>
                                    <th><?php _e('Map To', 'tbp-core'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="tbp-mapping-body">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 4: Options -->
                <div class="tbp-wizard-content" data-step="4" style="display:none;">
                    <h2><?php _e('Import Options', 'tbp-core'); ?></h2>

                    <div class="tbp-options-group">
                        <h3><?php _e('Post Status', 'tbp-core'); ?></h3>
                        <select id="tbp-post-status">
                            <option value="publish"><?php _e('Published', 'tbp-core'); ?></option>
                            <option value="draft"><?php _e('Draft', 'tbp-core'); ?></option>
                            <option value="pending"><?php _e('Pending', 'tbp-core'); ?></option>
                            <option value="private"><?php _e('Private', 'tbp-core'); ?></option>
                        </select>
                    </div>

                    <div class="tbp-options-group">
                        <h3><?php _e('Duplicate Handling', 'tbp-core'); ?></h3>
                        <select id="tbp-duplicate-action">
                            <option value="skip"><?php _e('Skip duplicates', 'tbp-core'); ?></option>
                            <option value="update"><?php _e('Update existing', 'tbp-core'); ?></option>
                            <option value="create"><?php _e('Create new anyway', 'tbp-core'); ?></option>
                        </select>
                        <p class="description"><?php _e('Duplicates are detected by title/name match.', 'tbp-core'); ?></p>
                    </div>

                    <div class="tbp-options-group">
                        <h3><?php _e('Preview', 'tbp-core'); ?></h3>
                        <button type="button" class="button" id="tbp-preview-btn">
                            <?php _e('Preview First 5 Items', 'tbp-core'); ?>
                        </button>
                        <div id="tbp-preview-results"></div>
                    </div>
                </div>

                <!-- Step 5: Import -->
                <div class="tbp-wizard-content" data-step="5" style="display:none;">
                    <h2><?php _e('Import Progress', 'tbp-core'); ?></h2>

                    <div class="tbp-import-progress">
                        <div class="tbp-progress-bar">
                            <div class="tbp-progress-fill" style="width: 0%;"></div>
                        </div>
                        <div class="tbp-progress-text">
                            <span class="current">0</span> / <span class="total">0</span>
                        </div>
                    </div>

                    <div class="tbp-import-log">
                        <h3><?php _e('Import Log', 'tbp-core'); ?></h3>
                        <div id="tbp-import-log-content"></div>
                    </div>

                    <div class="tbp-import-summary" style="display:none;">
                        <h3><?php _e('Summary', 'tbp-core'); ?></h3>
                        <ul>
                            <li><?php _e('Created:', 'tbp-core'); ?> <span class="created">0</span></li>
                            <li><?php _e('Updated:', 'tbp-core'); ?> <span class="updated">0</span></li>
                            <li><?php _e('Skipped:', 'tbp-core'); ?> <span class="skipped">0</span></li>
                            <li><?php _e('Errors:', 'tbp-core'); ?> <span class="errors">0</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="tbp-wizard-nav">
                    <button type="button" class="button tbp-prev-btn" style="display:none;">
                        <?php _e('Previous', 'tbp-core'); ?>
                    </button>
                    <button type="button" class="button button-primary tbp-next-btn">
                        <?php _e('Next', 'tbp-core'); ?>
                    </button>
                    <button type="button" class="button button-primary tbp-import-btn" style="display:none;">
                        <?php _e('Start Import', 'tbp-core'); ?>
                    </button>
                    <button type="button" class="button tbp-reset-btn" style="display:none;">
                        <?php _e('Start New Import', 'tbp-core'); ?>
                    </button>
                </div>
            </div>

            <!-- Import Sidebar -->
            <div class="tbp-importer-sidebar">
                <div class="tbp-sidebar-section">
                    <h3><span class="dashicons dashicons-info-outline"></span> <?php _e('Import Summary', 'tbp-core'); ?></h3>
                    <div class="tbp-sidebar-content">
                        <div class="tbp-sidebar-item" id="sidebar-file-info">
                            <span class="label"><?php _e('File:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                        <div class="tbp-sidebar-item" id="sidebar-rows">
                            <span class="label"><?php _e('Rows:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                        <div class="tbp-sidebar-item" id="sidebar-destination">
                            <span class="label"><?php _e('Destination:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                        <div class="tbp-sidebar-item" id="sidebar-mapped">
                            <span class="label"><?php _e('Fields Mapped:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                        <div class="tbp-sidebar-item" id="sidebar-status">
                            <span class="label"><?php _e('Post Status:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                        <div class="tbp-sidebar-item" id="sidebar-duplicates">
                            <span class="label"><?php _e('Duplicates:', 'tbp-core'); ?></span>
                            <span class="value">—</span>
                        </div>
                    </div>
                </div>

                <div class="tbp-sidebar-section">
                    <h3><span class="dashicons dashicons-lightbulb"></span> <?php _e('Tips', 'tbp-core'); ?></h3>
                    <div class="tbp-sidebar-tips">
                        <p><?php _e('• First row should contain column headers', 'tbp-core'); ?></p>
                        <p><?php _e('• HTML supported in content/excerpt', 'tbp-core'); ?></p>
                        <p><?php _e('• Featured image: URL or attachment ID', 'tbp-core'); ?></p>
                        <p><?php _e('• ACF relationships: use slugs or IDs', 'tbp-core'); ?></p>
                        <p><?php _e('• Parent: use slug, title, or ID', 'tbp-core'); ?></p>
                    </div>
                </div>

                <div class="tbp-sidebar-section">
                    <h3><span class="dashicons dashicons-chart-bar"></span> <?php _e('Supported Formats', 'tbp-core'); ?></h3>
                    <div class="tbp-sidebar-formats">
                        <span class="format-badge csv">CSV</span>
                        <span class="format-badge xlsx">XLSX</span>
                    </div>
                </div>
            </div>
            </div><!-- .tbp-importer-layout -->
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render Export Page
     */
    private function render_export_page() {
        ?>
        <div class="tbp-exporter-layout">
        <div class="tbp-exporter-panel">
            <div class="tbp-export-section">
                <h2><?php _e('Export Data', 'tbp-core'); ?></h2>
                <p><?php _e('Export posts or taxonomy terms to CSV or XLSX format.', 'tbp-core'); ?></p>

                <!-- Export Type -->
                <div class="tbp-export-row">
                    <h3><?php _e('What to Export', 'tbp-core'); ?></h3>
                    <label>
                        <input type="radio" name="export_type" value="posts" checked />
                        <?php _e('Posts / Custom Post Types', 'tbp-core'); ?>
                    </label>
                    <label>
                        <input type="radio" name="export_type" value="taxonomy" />
                        <?php _e('Taxonomy Terms', 'tbp-core'); ?>
                    </label>
                </div>

                <!-- Source Selection -->
                <div class="tbp-export-row">
                    <div class="tbp-export-source-posts">
                        <h3><?php _e('Post Type', 'tbp-core'); ?></h3>
                        <select id="tbp-export-post-type">
                            <?php
                            $post_types = get_post_types(['public' => true], 'objects');
                            foreach ($post_types as $pt) {
                                if ($pt->name === 'attachment') continue;
                                echo '<option value="' . esc_attr($pt->name) . '">' . esc_html($pt->label) . '</option>';
                            }
                            ?>
                        </select>

                        <h3><?php _e('Post Status', 'tbp-core'); ?></h3>
                        <select id="tbp-export-post-status">
                            <option value="any"><?php _e('All Statuses', 'tbp-core'); ?></option>
                            <option value="publish"><?php _e('Published', 'tbp-core'); ?></option>
                            <option value="draft"><?php _e('Draft', 'tbp-core'); ?></option>
                            <option value="pending"><?php _e('Pending', 'tbp-core'); ?></option>
                            <option value="private"><?php _e('Private', 'tbp-core'); ?></option>
                        </select>
                    </div>

                    <div class="tbp-export-source-taxonomy" style="display:none;">
                        <h3><?php _e('Taxonomy', 'tbp-core'); ?></h3>
                        <select id="tbp-export-taxonomy">
                            <?php
                            $taxonomies = get_taxonomies(['public' => true], 'objects');
                            foreach ($taxonomies as $tax) {
                                echo '<option value="' . esc_attr($tax->name) . '">' . esc_html($tax->label) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Load Fields Button -->
                <div class="tbp-export-row">
                    <button type="button" class="button" id="tbp-load-export-fields">
                        <?php _e('Load Available Fields', 'tbp-core'); ?>
                    </button>
                </div>

                <!-- Field Selection -->
                <div class="tbp-export-row tbp-export-fields-section" style="display:none;">
                    <h3><?php _e('Select Fields to Export', 'tbp-core'); ?></h3>
                    <p class="description"><?php _e('Check the fields you want to include in the export. Taxonomies and relations will use slugs instead of IDs.', 'tbp-core'); ?></p>

                    <div class="tbp-field-select-actions">
                        <button type="button" class="button button-small" id="tbp-select-all-fields"><?php _e('Select All', 'tbp-core'); ?></button>
                        <button type="button" class="button button-small" id="tbp-deselect-all-fields"><?php _e('Deselect All', 'tbp-core'); ?></button>
                    </div>

                    <div id="tbp-export-fields-list" class="tbp-export-fields-list">
                        <!-- Fields will be loaded here -->
                    </div>
                </div>

                <!-- Export Options -->
                <div class="tbp-export-row tbp-export-fields-section" style="display:none;">
                    <h3><?php _e('Export Options', 'tbp-core'); ?></h3>

                    <label>
                        <strong><?php _e('Format:', 'tbp-core'); ?></strong>
                    </label>
                    <select id="tbp-export-format">
                        <option value="csv"><?php _e('CSV', 'tbp-core'); ?></option>
                        <option value="xlsx"><?php _e('XLSX (Excel)', 'tbp-core'); ?></option>
                    </select>

                    <label style="margin-left: 20px;">
                        <input type="checkbox" id="tbp-export-use-slugs" checked />
                        <?php _e('Use slugs instead of IDs for taxonomies and relations', 'tbp-core'); ?>
                    </label>
                </div>

                <!-- Preview & Download -->
                <div class="tbp-export-row tbp-export-fields-section" style="display:none;">
                    <button type="button" class="button" id="tbp-preview-export">
                        <?php _e('Preview (First 5 rows)', 'tbp-core'); ?>
                    </button>
                    <button type="button" class="button button-primary" id="tbp-download-export">
                        <?php _e('Download Export', 'tbp-core'); ?>
                    </button>
                </div>

                <!-- Preview Results -->
                <div id="tbp-export-preview" class="tbp-export-preview"></div>
            </div>
        </div>

        <!-- Export Sidebar -->
        <div class="tbp-exporter-sidebar">
            <div class="tbp-sidebar-section">
                <h3><span class="dashicons dashicons-info-outline"></span> <?php _e('Export Summary', 'tbp-core'); ?></h3>
                <div class="tbp-sidebar-content">
                    <div class="tbp-sidebar-item" id="export-sidebar-source">
                        <span class="label"><?php _e('Source:', 'tbp-core'); ?></span>
                        <span class="value">—</span>
                    </div>
                    <div class="tbp-sidebar-item" id="export-sidebar-total">
                        <span class="label"><?php _e('Total Items:', 'tbp-core'); ?></span>
                        <span class="value">—</span>
                    </div>
                    <div class="tbp-sidebar-item" id="export-sidebar-fields">
                        <span class="label"><?php _e('Fields Selected:', 'tbp-core'); ?></span>
                        <span class="value">0</span>
                    </div>
                    <div class="tbp-sidebar-item" id="export-sidebar-format">
                        <span class="label"><?php _e('Format:', 'tbp-core'); ?></span>
                        <span class="value">CSV</span>
                    </div>
                </div>
            </div>

            <div class="tbp-sidebar-section">
                <h3><span class="dashicons dashicons-lightbulb"></span> <?php _e('Tips', 'tbp-core'); ?></h3>
                <div class="tbp-sidebar-tips">
                    <p><?php _e('• Enable "Use slugs" for re-importable data', 'tbp-core'); ?></p>
                    <p><?php _e('• XLSX format preserves special characters better', 'tbp-core'); ?></p>
                    <p><?php _e('• Select only needed fields for smaller files', 'tbp-core'); ?></p>
                    <p><?php _e('• Preview before downloading to verify data', 'tbp-core'); ?></p>
                </div>
            </div>

            <div class="tbp-sidebar-section">
                <h3><span class="dashicons dashicons-chart-bar"></span> <?php _e('Export Formats', 'tbp-core'); ?></h3>
                <div class="tbp-sidebar-formats">
                    <span class="format-badge csv">CSV</span>
                    <span class="format-badge xlsx">XLSX</span>
                </div>
            </div>
        </div>
        </div><!-- .tbp-exporter-layout -->
        <?php
    }

    /**
     * AJAX: Upload and parse file
     */
    public function ajax_upload_file() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => __('No file uploaded', 'tbp-core')]);
        }

        $file = $_FILES['file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'xlsx'])) {
            wp_send_json_error(['message' => __('Invalid file type. Please upload CSV or XLSX.', 'tbp-core')]);
        }

        // Parse the file
        $data = $this->parse_file($file['tmp_name'], $extension);

        if (is_wp_error($data)) {
            wp_send_json_error(['message' => $data->get_error_message()]);
        }

        // Store in transient for later use
        $upload_id = wp_generate_uuid4();
        set_transient('tbp_import_' . $upload_id, $data, HOUR_IN_SECONDS);

        wp_send_json_success([
            'upload_id' => $upload_id,
            'headers' => $data['headers'],
            'sample' => array_slice($data['rows'], 0, 3),
            'total_rows' => count($data['rows']),
        ]);
    }

    /**
     * Parse CSV or XLSX file
     */
    private function parse_file($file_path, $extension) {
        if ($extension === 'csv') {
            return $this->parse_csv($file_path);
        } else {
            return $this->parse_xlsx($file_path);
        }
    }

    /**
     * Parse CSV file
     */
    private function parse_csv($file_path) {
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return new WP_Error('parse_error', __('Could not read file', 'tbp-core'));
        }

        // Detect delimiter
        $first_line = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if (substr_count($first_line, ';') > substr_count($first_line, ',')) {
            $delimiter = ';';
        } elseif (substr_count($first_line, "\t") > substr_count($first_line, ',')) {
            $delimiter = "\t";
        }

        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            return new WP_Error('parse_error', __('Could not read headers', 'tbp-core'));
        }

        // Clean headers
        $headers = array_map('trim', $headers);
        $headers = array_map(function($h) {
            return preg_replace('/^\xEF\xBB\xBF/', '', $h); // Remove BOM
        }, $headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * Parse XLSX file
     */
    private function parse_xlsx($file_path) {
        require_once TBP_IMPORTER_PATH . 'lib/SimpleXLSX.php';

        $xlsx = \Shuchkin\SimpleXLSX::parse($file_path);

        if (!$xlsx) {
            return new WP_Error('parse_error', \Shuchkin\SimpleXLSX::parseError());
        }

        $sheet = $xlsx->rows();
        if (empty($sheet)) {
            return new WP_Error('parse_error', __('Empty file', 'tbp-core'));
        }

        $headers = array_shift($sheet);
        $headers = array_map('trim', $headers);

        $rows = [];
        foreach ($sheet as $row) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * AJAX: Get available fields for mapping
     */
    public function ajax_get_fields() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $type = sanitize_text_field($_POST['type'] ?? 'posts');
        $destination = sanitize_text_field($_POST['destination'] ?? 'post');

        $fields = [];

        if ($type === 'posts') {
            $fields = $this->get_post_fields($destination);
        } else {
            $fields = $this->get_taxonomy_fields($destination);
        }

        wp_send_json_success(['fields' => $fields]);
    }

    /**
     * Get available post fields
     */
    private function get_post_fields($post_type) {
        $fields = [
            ['value' => '', 'label' => __('-- Do not import --', 'tbp-core')],
            ['value' => 'post_title', 'label' => __('Title', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_content', 'label' => __('Content', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_excerpt', 'label' => __('Excerpt', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_name', 'label' => __('Slug', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_date', 'label' => __('Date', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_author', 'label' => __('Author (ID or email)', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'post_parent', 'label' => __('Parent (slug, title or ID)', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'menu_order', 'label' => __('Menu Order', 'tbp-core'), 'group' => 'Core'],
        ];

        // Add taxonomies
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        foreach ($taxonomies as $tax) {
            $fields[] = [
                'value' => 'tax_' . $tax->name,
                'label' => $tax->label . ' (' . __('comma-separated', 'tbp-core') . ')',
                'group' => 'Taxonomies',
            ];
        }

        // Add featured image
        if (post_type_supports($post_type, 'thumbnail')) {
            $fields[] = [
                'value' => 'featured_image',
                'label' => __('Featured Image (URL or ID)', 'tbp-core'),
                'group' => 'Media',
            ];
        }

        // Add ACF fields if available
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups(['post_type' => $post_type]);

            foreach ($field_groups as $group) {
                $acf_fields = acf_get_fields($group['key']);
                if ($acf_fields) {
                    foreach ($acf_fields as $field) {
                        $fields[] = [
                            'value' => 'acf_' . $field['name'],
                            'label' => $field['label'],
                            'group' => 'ACF: ' . $group['title'],
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Get available taxonomy fields
     */
    private function get_taxonomy_fields($taxonomy) {
        $fields = [
            ['value' => '', 'label' => __('-- Do not import --', 'tbp-core')],
            ['value' => 'name', 'label' => __('Name', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'slug', 'label' => __('Slug', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'description', 'label' => __('Description', 'tbp-core'), 'group' => 'Core'],
            ['value' => 'parent', 'label' => __('Parent (name or ID)', 'tbp-core'), 'group' => 'Core'],
        ];

        // Add ACF fields if available
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups(['taxonomy' => $taxonomy]);

            foreach ($field_groups as $group) {
                $acf_fields = acf_get_fields($group['key']);
                if ($acf_fields) {
                    foreach ($acf_fields as $field) {
                        $fields[] = [
                            'value' => 'acf_' . $field['name'],
                            'label' => $field['label'],
                            'group' => 'ACF: ' . $group['title'],
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * AJAX: Preview import
     */
    public function ajax_preview_import() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $upload_id = sanitize_text_field($_POST['upload_id'] ?? '');
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : [];
        $type = sanitize_text_field($_POST['type'] ?? 'posts');

        $data = get_transient('tbp_import_' . $upload_id);
        if (!$data) {
            wp_send_json_error(['message' => __('Upload expired. Please re-upload.', 'tbp-core')]);
        }

        $preview = [];
        $rows = array_slice($data['rows'], 0, 5);

        foreach ($rows as $row) {
            $item = [];
            foreach ($mapping as $csv_col => $wp_field) {
                if (!empty($wp_field) && isset($row[$csv_col])) {
                    $item[$wp_field] = $row[$csv_col];
                }
            }
            $preview[] = $item;
        }

        wp_send_json_success(['preview' => $preview]);
    }

    /**
     * AJAX: Process import
     */
    public function ajax_process_import() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $upload_id = sanitize_text_field($_POST['upload_id'] ?? '');
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : [];
        $type = sanitize_text_field($_POST['type'] ?? 'posts');
        $destination = sanitize_text_field($_POST['destination'] ?? 'post');
        $status = sanitize_text_field($_POST['status'] ?? 'draft');
        $duplicate_action = sanitize_text_field($_POST['duplicate_action'] ?? 'skip');
        $batch_start = intval($_POST['batch_start'] ?? 0);
        $batch_size = 10;

        $data = get_transient('tbp_import_' . $upload_id);
        if (!$data) {
            wp_send_json_error(['message' => __('Upload expired. Please re-upload.', 'tbp-core')]);
        }

        $rows = array_slice($data['rows'], $batch_start, $batch_size);
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'log' => [],
        ];

        foreach ($rows as $index => $row) {
            $row_num = $batch_start + $index + 2; // +2 for header and 0-index

            if ($type === 'posts') {
                $result = $this->import_post($row, $mapping, $destination, $status, $duplicate_action);
            } else {
                $result = $this->import_term($row, $mapping, $destination, $duplicate_action);
            }

            if ($result['status'] === 'created') {
                $results['created']++;
                $results['log'][] = sprintf(__('Row %d: Created "%s"', 'tbp-core'), $row_num, $result['title']);
            } elseif ($result['status'] === 'updated') {
                $results['updated']++;
                $results['log'][] = sprintf(__('Row %d: Updated "%s"', 'tbp-core'), $row_num, $result['title']);
            } elseif ($result['status'] === 'skipped') {
                $results['skipped']++;
                $results['log'][] = sprintf(__('Row %d: Skipped "%s" (duplicate)', 'tbp-core'), $row_num, $result['title']);
            } else {
                $results['errors']++;
                $results['log'][] = sprintf(__('Row %d: Error - %s', 'tbp-core'), $row_num, $result['message'] ?? 'Unknown error');
            }
        }

        $total = count($data['rows']);
        $processed = min($batch_start + $batch_size, $total);
        $done = $processed >= $total;

        wp_send_json_success([
            'results' => $results,
            'processed' => $processed,
            'total' => $total,
            'done' => $done,
        ]);
    }

    /**
     * Import a single post
     */
    private function import_post($row, $mapping, $post_type, $status, $duplicate_action) {
        $post_data = [
            'post_type' => $post_type,
            'post_status' => $status,
        ];
        $meta_data = [];
        $tax_data = [];
        $acf_data = [];
        $featured_image = '';

        // Map fields
        foreach ($mapping as $csv_col => $wp_field) {
            if (empty($wp_field) || !isset($row[$csv_col])) {
                continue;
            }

            $value = $row[$csv_col];

            if (strpos($wp_field, 'tax_') === 0) {
                $taxonomy = substr($wp_field, 4);
                $tax_data[$taxonomy] = array_map('trim', explode(',', $value));
            } elseif (strpos($wp_field, 'acf_') === 0) {
                $field_name = substr($wp_field, 4);
                $acf_data[$field_name] = $value;
            } elseif ($wp_field === 'featured_image') {
                $featured_image = $value;
            } elseif ($wp_field === 'post_author') {
                // Handle author by email or ID
                if (is_email($value)) {
                    $user = get_user_by('email', $value);
                    $post_data['post_author'] = $user ? $user->ID : get_current_user_id();
                } else {
                    $post_data['post_author'] = intval($value);
                }
            } elseif ($wp_field === 'post_parent') {
                // Handle parent by slug, title or ID
                if (!empty($value)) {
                    $value = trim($value);
                    if (is_numeric($value)) {
                        $post_data['post_parent'] = intval($value);
                    } else {
                        // Try to find by slug first using get_posts
                        $parent_posts = get_posts([
                            'post_type' => $post_type,
                            'name' => $value, // slug
                            'post_status' => 'any',
                            'posts_per_page' => 1,
                            'suppress_filters' => true,
                        ]);
                        if (!empty($parent_posts)) {
                            $post_data['post_parent'] = $parent_posts[0]->ID;
                        } else {
                            // Try to find by title
                            $parent_posts = get_posts([
                                'post_type' => $post_type,
                                'title' => $value,
                                'post_status' => 'any',
                                'posts_per_page' => 1,
                                'suppress_filters' => true,
                            ]);
                            if (!empty($parent_posts)) {
                                $post_data['post_parent'] = $parent_posts[0]->ID;
                            }
                        }
                    }
                }
            } elseif (in_array($wp_field, ['post_content', 'post_excerpt'])) {
                // Preserve HTML content - don't sanitize
                $post_data[$wp_field] = $value;
            } else {
                $post_data[$wp_field] = sanitize_text_field($value);
            }
        }

        // Check for required title
        if (empty($post_data['post_title'])) {
            return [
                'status' => 'error',
                'message' => __('No title provided', 'tbp-core'),
            ];
        }

        $title = $post_data['post_title'];

        // Check for duplicates
        $existing = get_posts([
            'post_type' => $post_type,
            'title' => $title,
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);

        if (!empty($existing)) {
            if ($duplicate_action === 'skip') {
                return [
                    'status' => 'skipped',
                    'title' => $title,
                ];
            } elseif ($duplicate_action === 'update') {
                $post_data['ID'] = $existing[0]->ID;
            }
        }

        // Insert or update post
        if (isset($post_data['ID'])) {
            $post_id = wp_update_post($post_data, true);
            $action = 'updated';
        } else {
            $post_id = wp_insert_post($post_data, true);
            $action = 'created';
        }

        if (is_wp_error($post_id)) {
            return [
                'status' => 'error',
                'message' => $post_id->get_error_message(),
                'title' => $title,
            ];
        }

        // Set taxonomies
        foreach ($tax_data as $taxonomy => $terms) {
            wp_set_object_terms($post_id, $terms, $taxonomy);
        }

        // Set ACF fields
        if (!empty($acf_data) && function_exists('update_field')) {
            foreach ($acf_data as $field_name => $value) {
                $value = $this->process_acf_value($field_name, $value, $post_id);
                update_field($field_name, $value, $post_id);
            }
        }

        // Set featured image (URL or ID)
        if (!empty($featured_image)) {
            if (is_numeric($featured_image)) {
                // It's an attachment ID
                set_post_thumbnail($post_id, intval($featured_image));
            } elseif (filter_var($featured_image, FILTER_VALIDATE_URL)) {
                // It's a URL - sideload it
                $this->set_featured_image($post_id, $featured_image);
            }
        }

        return [
            'status' => $action,
            'title' => $title,
            'id' => $post_id,
        ];
    }

    /**
     * Import a single term
     */
    private function import_term($row, $mapping, $taxonomy, $duplicate_action) {
        $term_data = [];
        $acf_data = [];

        // Map fields
        foreach ($mapping as $csv_col => $wp_field) {
            if (empty($wp_field) || !isset($row[$csv_col])) {
                continue;
            }

            $value = $row[$csv_col];

            if (strpos($wp_field, 'acf_') === 0) {
                $field_name = substr($wp_field, 4);
                $acf_data[$field_name] = $value;
            } elseif ($wp_field === 'parent') {
                // Handle parent by name or ID
                if (is_numeric($value)) {
                    $term_data['parent'] = intval($value);
                } else {
                    $parent = get_term_by('name', $value, $taxonomy);
                    $term_data['parent'] = $parent ? $parent->term_id : 0;
                }
            } else {
                $term_data[$wp_field] = $value;
            }
        }

        // Check for required name
        if (empty($term_data['name'])) {
            return [
                'status' => 'error',
                'message' => __('No name provided', 'tbp-core'),
            ];
        }

        $name = $term_data['name'];

        // Check for duplicates
        $existing = get_term_by('name', $name, $taxonomy);

        if ($existing) {
            if ($duplicate_action === 'skip') {
                return [
                    'status' => 'skipped',
                    'title' => $name,
                ];
            } elseif ($duplicate_action === 'update') {
                $result = wp_update_term($existing->term_id, $taxonomy, $term_data);
                $action = 'updated';
                $term_id = $existing->term_id;
            } else {
                // Create new anyway - need unique slug
                $term_data['slug'] = wp_unique_term_slug($name, (object) ['taxonomy' => $taxonomy]);
                $result = wp_insert_term($name, $taxonomy, $term_data);
                $action = 'created';
                $term_id = is_array($result) ? $result['term_id'] : 0;
            }
        } else {
            $result = wp_insert_term($name, $taxonomy, $term_data);
            $action = 'created';
            $term_id = is_array($result) ? $result['term_id'] : 0;
        }

        if (is_wp_error($result)) {
            return [
                'status' => 'error',
                'message' => $result->get_error_message(),
                'title' => $name,
            ];
        }

        // Set ACF fields
        if (!empty($acf_data) && function_exists('update_field') && $term_id) {
            foreach ($acf_data as $field_name => $value) {
                update_field($field_name, $value, $taxonomy . '_' . $term_id);
            }
        }

        return [
            'status' => $action,
            'title' => $name,
            'id' => $term_id,
        ];
    }

    /**
     * Set featured image from URL
     */
    private function set_featured_image($post_id, $image_url) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_sideload_image($image_url, $post_id, '', 'id');

        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    /**
     * Process ACF value - converts slugs to IDs for relationship/post_object fields
     */
    private function process_acf_value($field_name, $value, $post_id) {
        if (!function_exists('acf_get_field')) {
            return $value;
        }

        $field = acf_get_field($field_name);
        if (!$field) {
            return $value;
        }

        $field_type = $field['type'] ?? '';

        // Handle relationship and post_object fields
        if (in_array($field_type, ['relationship', 'post_object'])) {
            $post_types = $field['post_type'] ?? [];

            // Convert empty or 'all' to actual searchable post types
            if (empty($post_types) || in_array('all', $post_types)) {
                $post_types = get_post_types(['public' => true]);
            }

            // Check if it's comma-separated (multiple values)
            $values = array_map('trim', explode(',', $value));
            $post_ids = [];

            foreach ($values as $val) {
                if (empty($val)) {
                    continue;
                }

                if (is_numeric($val)) {
                    // Already an ID
                    $post_ids[] = intval($val);
                } else {
                    // Try to find by slug first
                    $found_posts = get_posts([
                        'post_type' => $post_types,
                        'name' => $val, // slug
                        'post_status' => 'any',
                        'posts_per_page' => 1,
                        'suppress_filters' => true,
                    ]);

                    if (!empty($found_posts)) {
                        $post_ids[] = $found_posts[0]->ID;
                    } else {
                        // Try to find by title
                        $found_posts = get_posts([
                            'post_type' => $post_types,
                            'title' => $val,
                            'post_status' => 'any',
                            'posts_per_page' => 1,
                            'suppress_filters' => true,
                        ]);
                        if (!empty($found_posts)) {
                            $post_ids[] = $found_posts[0]->ID;
                        }
                    }
                }
            }

            // Return single ID or array based on field type
            if ($field_type === 'post_object' && !($field['multiple'] ?? false)) {
                return !empty($post_ids) ? $post_ids[0] : '';
            }

            return $post_ids;
        }

        // Handle page_link field
        if ($field_type === 'page_link') {
            if (is_numeric($value)) {
                return intval($value);
            }
            // Search by slug
            $found_posts = get_posts([
                'post_type' => get_post_types(['public' => true]),
                'name' => $value,
                'post_status' => 'any',
                'posts_per_page' => 1,
            ]);
            return !empty($found_posts) ? $found_posts[0]->ID : $value;
        }

        // Handle taxonomy fields
        if ($field_type === 'taxonomy') {
            $taxonomy = $field['taxonomy'] ?? 'category';
            $values = array_map('trim', explode(',', $value));
            $term_ids = [];

            foreach ($values as $val) {
                if (empty($val)) {
                    continue;
                }

                if (is_numeric($val)) {
                    $term_ids[] = intval($val);
                } else {
                    // Try slug first, then name
                    $term = get_term_by('slug', $val, $taxonomy);
                    if (!$term) {
                        $term = get_term_by('name', $val, $taxonomy);
                    }
                    if ($term) {
                        $term_ids[] = $term->term_id;
                    }
                }
            }

            // Return format based on field settings
            $field_format = $field['field_type'] ?? 'checkbox';
            if (in_array($field_format, ['select', 'radio'])) {
                return !empty($term_ids) ? $term_ids[0] : '';
            }

            return $term_ids;
        }

        return $value;
    }

    /**
     * AJAX: Get export fields
     */
    public function ajax_export_get_fields() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $type = sanitize_text_field($_POST['type'] ?? 'posts');
        $source = sanitize_text_field($_POST['source'] ?? 'post');
        $status = sanitize_text_field($_POST['status'] ?? 'any');

        if ($type === 'posts') {
            $fields = $this->get_export_post_fields($source);
            // Get total count
            $count_args = [
                'post_type' => $source,
                'posts_per_page' => -1,
                'post_status' => $status === 'any' ? ['publish', 'draft', 'pending', 'private'] : $status,
                'fields' => 'ids',
            ];
            $total_count = count(get_posts($count_args));
        } else {
            $fields = $this->get_export_taxonomy_fields($source);
            // Get total count
            $total_count = wp_count_terms(['taxonomy' => $source, 'hide_empty' => false]);
            if (is_wp_error($total_count)) {
                $total_count = 0;
            }
        }

        wp_send_json_success([
            'fields' => $fields,
            'total_count' => $total_count,
        ]);
    }

    /**
     * Get exportable post fields
     */
    private function get_export_post_fields($post_type) {
        $fields = [
            ['value' => 'ID', 'label' => __('ID', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_title', 'label' => __('Title', 'tbp-core'), 'group' => 'Core', 'checked' => true],
            ['value' => 'post_name', 'label' => __('Slug', 'tbp-core'), 'group' => 'Core', 'checked' => true],
            ['value' => 'post_content', 'label' => __('Content', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_excerpt', 'label' => __('Excerpt', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_status', 'label' => __('Status', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_date', 'label' => __('Date', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_author', 'label' => __('Author', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'menu_order', 'label' => __('Menu Order', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'post_parent', 'label' => __('Parent', 'tbp-core'), 'group' => 'Core', 'checked' => false],
        ];

        // Add taxonomies
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        foreach ($taxonomies as $tax) {
            $fields[] = [
                'value' => 'tax_' . $tax->name,
                'label' => $tax->label,
                'group' => 'Taxonomies',
                'checked' => false,
            ];
        }

        // Add featured image
        if (post_type_supports($post_type, 'thumbnail')) {
            $fields[] = [
                'value' => 'featured_image',
                'label' => __('Featured Image URL', 'tbp-core'),
                'group' => 'Media',
                'checked' => false,
            ];
        }

        // Add ACF fields
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups(['post_type' => $post_type]);

            foreach ($field_groups as $group) {
                $acf_fields = acf_get_fields($group['key']);
                if ($acf_fields) {
                    foreach ($acf_fields as $field) {
                        $fields[] = [
                            'value' => 'acf_' . $field['name'],
                            'label' => $field['label'],
                            'group' => 'ACF: ' . $group['title'],
                            'checked' => false,
                            'acf_type' => $field['type'],
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Get exportable taxonomy fields
     */
    private function get_export_taxonomy_fields($taxonomy) {
        $fields = [
            ['value' => 'term_id', 'label' => __('Term ID', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'name', 'label' => __('Name', 'tbp-core'), 'group' => 'Core', 'checked' => true],
            ['value' => 'slug', 'label' => __('Slug', 'tbp-core'), 'group' => 'Core', 'checked' => true],
            ['value' => 'description', 'label' => __('Description', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'parent', 'label' => __('Parent', 'tbp-core'), 'group' => 'Core', 'checked' => false],
            ['value' => 'count', 'label' => __('Post Count', 'tbp-core'), 'group' => 'Core', 'checked' => false],
        ];

        // Add ACF fields
        if (function_exists('acf_get_field_groups')) {
            $field_groups = acf_get_field_groups(['taxonomy' => $taxonomy]);

            foreach ($field_groups as $group) {
                $acf_fields = acf_get_fields($group['key']);
                if ($acf_fields) {
                    foreach ($acf_fields as $field) {
                        $fields[] = [
                            'value' => 'acf_' . $field['name'],
                            'label' => $field['label'],
                            'group' => 'ACF: ' . $group['title'],
                            'checked' => false,
                            'acf_type' => $field['type'],
                        ];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * AJAX: Preview export
     */
    public function ajax_export_preview() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $type = sanitize_text_field($_POST['type'] ?? 'posts');
        $source = sanitize_text_field($_POST['source'] ?? 'post');
        $fields = isset($_POST['fields']) ? array_map('sanitize_text_field', $_POST['fields']) : [];
        $use_slugs = isset($_POST['use_slugs']) && $_POST['use_slugs'] === 'true';
        $status = sanitize_text_field($_POST['status'] ?? 'any');

        if (empty($fields)) {
            wp_send_json_error(['message' => __('Please select at least one field', 'tbp-core')]);
        }

        if ($type === 'posts') {
            $data = $this->get_export_posts_data($source, $fields, $use_slugs, $status, 5);
        } else {
            $data = $this->get_export_terms_data($source, $fields, $use_slugs, 5);
        }

        wp_send_json_success(['data' => $data, 'headers' => $fields]);
    }

    /**
     * AJAX: Download export
     */
    public function ajax_export_download() {
        check_ajax_referer('tbp_importer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'tbp-core')]);
        }

        $type = sanitize_text_field($_POST['type'] ?? 'posts');
        $source = sanitize_text_field($_POST['source'] ?? 'post');
        $fields = isset($_POST['fields']) ? array_map('sanitize_text_field', $_POST['fields']) : [];
        $use_slugs = isset($_POST['use_slugs']) && $_POST['use_slugs'] === 'true';
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $status = sanitize_text_field($_POST['status'] ?? 'any');

        if (empty($fields)) {
            wp_send_json_error(['message' => __('Please select at least one field', 'tbp-core')]);
        }

        if ($type === 'posts') {
            $data = $this->get_export_posts_data($source, $fields, $use_slugs, $status, -1);
        } else {
            $data = $this->get_export_terms_data($source, $fields, $use_slugs, -1);
        }

        // Generate file
        $filename = $source . '-export-' . date('Y-m-d-His');

        if ($format === 'xlsx') {
            $file_url = $this->generate_xlsx($data, $fields, $filename);
        } else {
            $file_url = $this->generate_csv($data, $fields, $filename);
        }

        wp_send_json_success(['file_url' => $file_url, 'count' => count($data)]);
    }

    /**
     * Get posts data for export
     */
    private function get_export_posts_data($post_type, $fields, $use_slugs, $status, $limit) {
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => $limit,
            'post_status' => $status === 'any' ? ['publish', 'draft', 'pending', 'private'] : $status,
            'orderby' => 'ID',
            'order' => 'ASC',
        ];

        $posts = get_posts($args);
        $data = [];

        foreach ($posts as $post) {
            $row = [];

            foreach ($fields as $field) {
                if (strpos($field, 'tax_') === 0) {
                    $taxonomy = substr($field, 4);
                    $terms = wp_get_post_terms($post->ID, $taxonomy);
                    if (!is_wp_error($terms)) {
                        $term_values = [];
                        foreach ($terms as $term) {
                            $term_values[] = $use_slugs ? $term->slug : $term->name;
                        }
                        $row[$field] = implode(', ', $term_values);
                    } else {
                        $row[$field] = '';
                    }
                } elseif (strpos($field, 'acf_') === 0) {
                    $field_name = substr($field, 4);
                    $value = function_exists('get_field') ? get_field($field_name, $post->ID, false) : '';
                    $row[$field] = $this->format_acf_value($value, $use_slugs);
                } elseif ($field === 'featured_image') {
                    $thumbnail_id = get_post_thumbnail_id($post->ID);
                    $row[$field] = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
                } elseif ($field === 'post_author') {
                    $author = get_userdata($post->post_author);
                    $row[$field] = $use_slugs ? ($author ? $author->user_login : '') : $post->post_author;
                } elseif ($field === 'post_parent') {
                    if ($use_slugs && $post->post_parent) {
                        $parent = get_post($post->post_parent);
                        $row[$field] = $parent ? $parent->post_name : '';
                    } else {
                        $row[$field] = $post->post_parent;
                    }
                } else {
                    $row[$field] = isset($post->$field) ? $post->$field : '';
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Get terms data for export
     */
    private function get_export_terms_data($taxonomy, $fields, $use_slugs, $limit) {
        $args = [
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'number' => $limit > 0 ? $limit : 0,
            'orderby' => 'term_id',
            'order' => 'ASC',
        ];

        $terms = get_terms($args);
        $data = [];

        if (is_wp_error($terms)) {
            return $data;
        }

        foreach ($terms as $term) {
            $row = [];

            foreach ($fields as $field) {
                if (strpos($field, 'acf_') === 0) {
                    $field_name = substr($field, 4);
                    $value = function_exists('get_field') ? get_field($field_name, $taxonomy . '_' . $term->term_id, false) : '';
                    $row[$field] = $this->format_acf_value($value, $use_slugs);
                } elseif ($field === 'parent') {
                    if ($use_slugs && $term->parent) {
                        $parent = get_term($term->parent, $taxonomy);
                        $row[$field] = !is_wp_error($parent) ? $parent->slug : '';
                    } else {
                        $row[$field] = $term->parent;
                    }
                } else {
                    $row[$field] = isset($term->$field) ? $term->$field : '';
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Format ACF field value for export
     */
    private function format_acf_value($value, $use_slugs) {
        if (is_array($value)) {
            // Handle arrays (repeaters, relationships, etc.)
            $formatted = [];
            foreach ($value as $item) {
                if (is_object($item) && isset($item->ID)) {
                    // Post object
                    $formatted[] = $use_slugs ? $item->post_name : $item->ID;
                } elseif (is_object($item) && isset($item->term_id)) {
                    // Term object
                    $formatted[] = $use_slugs ? $item->slug : $item->term_id;
                } elseif (is_array($item)) {
                    // Nested array (repeater row)
                    $formatted[] = json_encode($item);
                } else {
                    $formatted[] = $item;
                }
            }
            return implode(', ', $formatted);
        } elseif (is_object($value)) {
            if (isset($value->ID)) {
                return $use_slugs ? $value->post_name : $value->ID;
            } elseif (isset($value->term_id)) {
                return $use_slugs ? $value->slug : $value->term_id;
            }
            return '';
        }

        return $value;
    }

    /**
     * Generate CSV file
     */
    private function generate_csv($data, $headers, $filename) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/tbp-exports';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $file_path = $export_dir . '/' . $filename . '.csv';
        $handle = fopen($file_path, 'w');

        // Add BOM for Excel UTF-8 compatibility
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($handle, $headers);

        // Write data
        foreach ($data as $row) {
            $row_data = [];
            foreach ($headers as $header) {
                $row_data[] = isset($row[$header]) ? $row[$header] : '';
            }
            fputcsv($handle, $row_data);
        }

        fclose($handle);

        return $upload_dir['baseurl'] . '/tbp-exports/' . $filename . '.csv';
    }

    /**
     * Generate XLSX file
     */
    private function generate_xlsx($data, $headers, $filename) {
        require_once TBP_IMPORTER_PATH . 'lib/SimpleXLSXGen.php';

        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/tbp-exports';

        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }

        $file_path = $export_dir . '/' . $filename . '.xlsx';

        // Prepare data with headers
        $xlsx_data = [$headers];

        foreach ($data as $row) {
            $row_data = [];
            foreach ($headers as $header) {
                $row_data[] = isset($row[$header]) ? $row[$header] : '';
            }
            $xlsx_data[] = $row_data;
        }

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($xlsx_data);
        $xlsx->saveAs($file_path);

        return $upload_dir['baseurl'] . '/tbp-exports/' . $filename . '.xlsx';
    }
}
