<?php
/**
 * @module-title: CSV/XLSX Importer
 * @module-version: 1.0.0
 * @module-description: Multi-step wizard for importing posts and taxonomies from CSV/XLSX files with field mapping
 * @module-usage: Access via Tools > TBP Importer in WordPress admin
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TBP_IMPORTER_PATH', plugin_dir_path(__FILE__));
define('TBP_IMPORTER_URL', plugin_dir_url(__FILE__));

require_once TBP_IMPORTER_PATH . 'class-importer.php';

TBP_CSV_Importer::init();
