<?php
/**
 * @module-title: Elementor Sprintf Fix
 * @module-version: 1.0.1
 * @module-description: Fixes "sprintf is not defined" error in Elementor Pro display conditions
 * @module-usage: Activate to fix Elementor display conditions. Deactivate once Elementor releases a fix.
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_Elementor_Sprintf_Fix {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Print script as early as possible - before wp_head even runs
        add_action('admin_print_scripts', [$this, 'print_sprintf_fix'], -9999);

        // Also add to wp_print_scripts for good measure
        add_action('wp_print_scripts', [$this, 'print_sprintf_fix'], -9999);
    }

    public function print_sprintf_fix() {
        // Only on Elementor editor pages
        if (!isset($_GET['action']) || $_GET['action'] !== 'elementor') {
            return;
        }

        // Only print once
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
        <script>
        // TBP Elementor sprintf fix - define sprintf immediately
        window.sprintf = function(format) {
            if (typeof format !== 'string') return '';
            var args = Array.prototype.slice.call(arguments, 1);
            var i = 0;
            return format.replace(/%([sd%])/g, function(match, type) {
                if (type === '%') return '%';
                var arg = args[i++];
                if (arg === undefined) return '';
                if (type === 'd') return parseInt(arg, 10);
                return String(arg);
            });
        };

        // Override with wp.i18n.sprintf when available (it's more complete)
        (function() {
            var checkWpI18n = function() {
                if (typeof wp !== 'undefined' && wp.i18n && typeof wp.i18n.sprintf === 'function') {
                    window.sprintf = wp.i18n.sprintf;
                    return true;
                }
                return false;
            };

            if (!checkWpI18n()) {
                var attempts = 0;
                var interval = setInterval(function() {
                    if (checkWpI18n() || attempts++ > 50) {
                        clearInterval(interval);
                    }
                }, 100);
            }
        })();
        </script>
        <?php
    }
}

TBP_Elementor_Sprintf_Fix::instance();
