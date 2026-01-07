<?php
/**
 * @module-title: SVG Upload
 * @module-version: 1.0.0
 * @module-description: Enable safe SVG uploads with sanitization to prevent XSS/XML vulnerabilities
 * @module-usage: Activate module to allow SVG uploads in Media Library
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * TBP SVG Upload Handler
 */
class TBP_SVG_Upload {

    private static $instance = null;

    /**
     * Allowed SVG tags
     */
    private $allowed_tags = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan', 'textpath', 'defs', 'use', 'symbol', 'clippath', 'mask',
        'lineargradient', 'radialgradient', 'stop', 'pattern', 'image', 'switch',
        'title', 'desc', 'metadata', 'style', 'font', 'glyph', 'marker', 'view',
        'a', 'altglyph', 'altglyphdef', 'altglyphitem', 'animatecolor', 'animatemotion',
        'animatetransform', 'filter', 'feblend', 'fecolormatrix', 'fecomponenttransfer',
        'fecomposite', 'feconvolvematrix', 'fediffuselighting', 'fedisplacementmap',
        'fedistantlight', 'feflood', 'fefunca', 'fefuncb', 'fefuncg', 'fefuncr',
        'fegaussianblur', 'feimage', 'femerge', 'femergenode', 'femorphology', 'feoffset',
        'fepointlight', 'fespecularlighting', 'fespotlight', 'fetile', 'feturbulence',
        'hkern', 'vkern', 'glyphref', 'mpath', 'tref', 'animate', 'set',
        'foreignobject', 'cursor'
    ];

    /**
     * Allowed SVG attributes
     */
    private $allowed_attrs = [
        'class', 'id', 'style', 'fill', 'stroke', 'stroke-width', 'stroke-linecap',
        'stroke-linejoin', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-opacity',
        'fill-opacity', 'opacity', 'transform', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry', 'width', 'height', 'd', 'points', 'viewbox',
        'preserveaspectratio', 'xmlns', 'xmlns:xlink', 'version', 'href', 'xlink:href',
        'clip-path', 'clip-rule', 'fill-rule', 'mask', 'filter', 'marker-start',
        'marker-mid', 'marker-end', 'gradientunits', 'gradienttransform', 'spreadmethod',
        'offset', 'stop-color', 'stop-opacity', 'patternunits', 'patterntransform',
        'font-family', 'font-size', 'font-style', 'font-weight', 'text-anchor',
        'dominant-baseline', 'alignment-baseline', 'baseline-shift', 'letter-spacing',
        'word-spacing', 'text-decoration', 'writing-mode', 'dx', 'dy', 'rotate',
        'textlength', 'lengthadjust', 'startoffset', 'method', 'spacing', 'role',
        'aria-label', 'aria-labelledby', 'aria-describedby', 'aria-hidden', 'focusable',
        'tabindex', 'lang', 'xml:lang', 'xml:space', 'enable-background', 'color',
        'color-interpolation', 'color-interpolation-filters', 'color-profile',
        'color-rendering', 'cursor', 'direction', 'display', 'flood-color', 'flood-opacity',
        'font-size-adjust', 'font-stretch', 'font-variant', 'glyph-orientation-horizontal',
        'glyph-orientation-vertical', 'image-rendering', 'kerning', 'lighting-color',
        'overflow', 'pointer-events', 'shape-rendering', 'text-rendering', 'unicode-bidi',
        'vector-effect', 'visibility', 'in', 'in2', 'result', 'mode', 'type', 'values',
        'stddeviation', 'edgemode', 'kernelmatrix', 'order', 'divisor', 'bias',
        'targetx', 'targety', 'preservealpha', 'surfacescale', 'diffuseconstant',
        'specularconstant', 'specularexponent', 'limitingconeangle', 'azimuth',
        'elevation', 'pointsatx', 'pointsaty', 'pointsatz', 'k1', 'k2', 'k3', 'k4',
        'operator', 'radius', 'basefrequency', 'numoctaves', 'seed', 'stitchtiles',
        'scale', 'xchannelselector', 'ychannelselector'
    ];

    /**
     * Disallowed tags (security risk)
     */
    private $disallowed_tags = [
        'script', 'handler', 'foreignobject'
    ];

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Allow SVG uploads
        add_filter('upload_mimes', [$this, 'allow_svg_mime']);
        add_filter('wp_check_filetype_and_ext', [$this, 'fix_mime_type'], 10, 4);

        // Sanitize on upload
        add_filter('wp_handle_upload_prefilter', [$this, 'sanitize_svg']);
        add_filter('wp_handle_sideload_prefilter', [$this, 'sanitize_svg']);

        // Fix admin display
        add_filter('wp_prepare_attachment_for_js', [$this, 'fix_admin_preview'], 10, 3);
        add_filter('wp_get_attachment_image_src', [$this, 'fix_image_src'], 10, 4);
        add_filter('admin_post_thumbnail_html', [$this, 'fix_featured_image'], 10, 3);
        add_filter('wp_generate_attachment_metadata', [$this, 'generate_svg_metadata'], 10, 2);
        add_filter('wp_calculate_image_srcset_meta', [$this, 'disable_srcset'], 10, 4);

        // Admin styles
        add_action('admin_enqueue_scripts', [$this, 'admin_styles']);
    }

    /**
     * Allow SVG mime type
     */
    public function allow_svg_mime($mimes) {
        if (current_user_can('upload_files')) {
            $mimes['svg'] = 'image/svg+xml';
            $mimes['svgz'] = 'image/svg+xml';
        }
        return $mimes;
    }

    /**
     * Fix mime type detection for SVG
     */
    public function fix_mime_type($data, $file, $filename, $mimes) {
        $ext = isset($data['ext']) ? $data['ext'] : '';

        if (empty($ext)) {
            $parts = explode('.', $filename);
            $ext = strtolower(end($parts));
        }

        if ($ext === 'svg' || $ext === 'svgz') {
            $data['type'] = 'image/svg+xml';
            $data['ext'] = $ext;
        }

        return $data;
    }

    /**
     * Sanitize SVG on upload
     */
    public function sanitize_svg($file) {
        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            return $file;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'svg' && $ext !== 'svgz') {
            return $file;
        }

        if (!current_user_can('upload_files')) {
            $file['error'] = __('Sorry, you are not allowed to upload SVG files.', 'tbp-core');
            return $file;
        }

        $content = file_get_contents($file['tmp_name']);

        // Handle gzipped SVGs
        $is_gzipped = $this->is_gzipped($content);
        if ($is_gzipped) {
            $content = gzdecode($content);
            if ($content === false) {
                $file['error'] = __('Could not decode gzipped SVG file.', 'tbp-core');
                return $file;
            }
        }

        // Sanitize
        $clean = $this->sanitize($content);

        if ($clean === false) {
            $file['error'] = __('SVG file could not be sanitized for security reasons.', 'tbp-core');
            return $file;
        }

        // Re-gzip if needed
        if ($is_gzipped) {
            $clean = gzencode($clean);
        }

        file_put_contents($file['tmp_name'], $clean);

        return $file;
    }

    /**
     * Check if content is gzipped
     */
    private function is_gzipped($content) {
        return substr($content, 0, 3) === "\x1f\x8b\x08";
    }

    /**
     * Sanitize SVG content
     */
    private function sanitize($content) {
        // Remove XML declaration
        $content = preg_replace('/<\?xml[^>]*\?>/i', '', $content);

        // Remove DOCTYPE
        $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);

        // Remove CDATA sections containing scripts
        $content = preg_replace('/<!\[CDATA\[.*?\]\]>/s', '', $content);

        // Remove event handlers
        $content = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);

        // Remove javascript: URLs
        $content = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $content);

        // Remove script tags
        $content = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $content);

        // Load into DOMDocument
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        // Try to load the SVG
        $loaded = @$dom->loadXML($content, LIBXML_NONET | LIBXML_NOBLANKS);

        if (!$loaded) {
            // Try loading as HTML fragment
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        }

        libxml_clear_errors();

        if (!$loaded) {
            return false;
        }

        // Get SVG element
        $svg = $dom->getElementsByTagName('svg')->item(0);
        if (!$svg) {
            return false;
        }

        // Clean the DOM
        $this->clean_node($svg);

        // Export
        $clean = $dom->saveXML($svg);

        if (empty($clean)) {
            return false;
        }

        return $clean;
    }

    /**
     * Recursively clean DOM nodes
     */
    private function clean_node($node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tag_name = strtolower($node->nodeName);

        // Remove disallowed tags
        if (in_array($tag_name, $this->disallowed_tags)) {
            $node->parentNode->removeChild($node);
            return;
        }

        // Remove non-whitelisted tags (except svg namespace)
        if (!in_array($tag_name, $this->allowed_tags) && strpos($tag_name, ':') === false) {
            // Keep children, remove tag
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }
            $node->parentNode->removeChild($node);
            return;
        }

        // Clean attributes
        $attrs_to_remove = [];
        foreach ($node->attributes as $attr) {
            $attr_name = strtolower($attr->nodeName);
            $attr_value = strtolower($attr->nodeValue);

            // Remove event handlers
            if (strpos($attr_name, 'on') === 0) {
                $attrs_to_remove[] = $attr->nodeName;
                continue;
            }

            // Remove javascript: URLs
            if (strpos($attr_value, 'javascript:') !== false) {
                $attrs_to_remove[] = $attr->nodeName;
                continue;
            }

            // Remove data: URLs (except images)
            if (strpos($attr_value, 'data:') !== false && strpos($attr_value, 'data:image/') === false) {
                $attrs_to_remove[] = $attr->nodeName;
                continue;
            }

            // Check whitelist (allow namespaced attributes)
            if (!in_array($attr_name, $this->allowed_attrs) && strpos($attr_name, ':') === false && strpos($attr_name, 'data-') !== 0 && strpos($attr_name, 'aria-') !== 0) {
                $attrs_to_remove[] = $attr->nodeName;
            }
        }

        foreach ($attrs_to_remove as $attr_name) {
            $node->removeAttribute($attr_name);
        }

        // Process children
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            $this->clean_node($child);
        }
    }

    /**
     * Fix admin preview for SVGs
     */
    public function fix_admin_preview($response, $attachment, $meta) {
        if ($response['mime'] !== 'image/svg+xml') {
            return $response;
        }

        $dimensions = $this->get_svg_dimensions($attachment->ID);

        if ($dimensions) {
            $response['width'] = $dimensions['width'];
            $response['height'] = $dimensions['height'];
        }

        $response['sizes'] = [
            'full' => [
                'url' => $response['url'],
                'width' => $dimensions ? $dimensions['width'] : 100,
                'height' => $dimensions ? $dimensions['height'] : 100,
                'orientation' => 'portrait',
            ],
            'thumbnail' => [
                'url' => $response['url'],
                'width' => 150,
                'height' => 150,
                'orientation' => 'portrait',
            ],
            'medium' => [
                'url' => $response['url'],
                'width' => 300,
                'height' => 300,
                'orientation' => 'portrait',
            ],
            'large' => [
                'url' => $response['url'],
                'width' => 1024,
                'height' => 1024,
                'orientation' => 'portrait',
            ],
        ];

        $response['icon'] = $response['url'];

        return $response;
    }

    /**
     * Fix image src for SVGs
     */
    public function fix_image_src($image, $attachment_id, $size, $icon) {
        if (get_post_mime_type($attachment_id) !== 'image/svg+xml') {
            return $image;
        }

        if ($image) {
            $dimensions = $this->get_svg_dimensions($attachment_id);
            $image[1] = $dimensions ? $dimensions['width'] : 100;
            $image[2] = $dimensions ? $dimensions['height'] : 100;
        }

        return $image;
    }

    /**
     * Fix featured image display
     */
    public function fix_featured_image($content, $post_id, $thumbnail_id) {
        if (get_post_mime_type($thumbnail_id) === 'image/svg+xml') {
            $content = '<span class="svg">' . $content . '</span>';
        }
        return $content;
    }

    /**
     * Generate metadata for SVG
     */
    public function generate_svg_metadata($metadata, $attachment_id) {
        if (get_post_mime_type($attachment_id) !== 'image/svg+xml') {
            return $metadata;
        }

        $svg_path = get_attached_file($attachment_id);
        $dimensions = $this->get_svg_dimensions($attachment_id);

        if (!$dimensions) {
            return $metadata;
        }

        $upload_dir = wp_upload_dir();
        $relative_path = str_replace(trailingslashit($upload_dir['basedir']), '', $svg_path);
        $filename = basename($svg_path);

        $metadata = [
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'file' => $relative_path,
            'sizes' => [],
        ];

        foreach (get_intermediate_image_sizes() as $size) {
            $metadata['sizes'][$size] = [
                'file' => $filename,
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'mime-type' => 'image/svg+xml',
            ];
        }

        return $metadata;
    }

    /**
     * Disable srcset for SVGs
     */
    public function disable_srcset($image_meta, $size_array, $image_src, $attachment_id) {
        if ($attachment_id && get_post_mime_type($attachment_id) === 'image/svg+xml') {
            $image_meta['sizes'] = [];
        }
        return $image_meta;
    }

    /**
     * Get SVG dimensions
     */
    private function get_svg_dimensions($attachment_id) {
        $file = get_attached_file($attachment_id);

        if (!$file || !file_exists($file)) {
            return false;
        }

        $content = file_get_contents($file);

        // Handle gzipped
        if ($this->is_gzipped($content)) {
            $content = gzdecode($content);
        }

        if (empty($content)) {
            return false;
        }

        libxml_use_internal_errors(true);
        $svg = @simplexml_load_string($content);
        libxml_clear_errors();

        if (!$svg) {
            return false;
        }

        $attrs = $svg->attributes();
        $width = 0;
        $height = 0;

        // Try viewBox first
        if (isset($attrs->viewBox)) {
            $viewbox = explode(' ', (string)$attrs->viewBox);
            if (count($viewbox) === 4) {
                $width = floatval($viewbox[2]);
                $height = floatval($viewbox[3]);
            }
        }

        // Override with width/height if available
        if (isset($attrs->width) && isset($attrs->height)) {
            $w = (string)$attrs->width;
            $h = (string)$attrs->height;

            // Skip percentage values
            if (strpos($w, '%') === false && strpos($h, '%') === false) {
                $width = floatval($w);
                $height = floatval($h);
            }
        }

        if ($width <= 0 || $height <= 0) {
            return false;
        }

        return [
            'width' => (int)$width,
            'height' => (int)$height,
        ];
    }

    /**
     * Admin styles for SVG display
     */
    public function admin_styles() {
        wp_add_inline_style('wp-admin', '
            .media-icon img[src$=".svg"],
            .media-icon img[src$=".svgz"],
            .attachment-preview img[src$=".svg"],
            .attachment-preview img[src$=".svgz"] {
                width: 100% !important;
                height: auto !important;
            }
            span.svg img {
                width: 100%;
                height: auto;
            }
            .attachment-266x266,
            .attachment-info .thumbnail .thumbnail-image,
            .edit-attachment-frame .attachment-media-view .details-image {
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGklEQVQ4T2NkYGD4z0AEYBxVQBQNDLwBtHQBAD9WECV/4LmDAAAAAElFTkSuQmCC) repeat;
            }
        ');
    }
}

TBP_SVG_Upload::instance();
