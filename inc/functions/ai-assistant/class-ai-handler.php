<?php
/**
 * AI Handler - Gemini API Communication
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_AI_Handler {

    private static $instance = null;
    private $api_base = 'https://generativelanguage.googleapis.com/v1/models/';

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_tbp_ai_enhance', [$this, 'handle_enhance_request']);
        add_action('wp_ajax_tbp_ai_generate', [$this, 'handle_generate_request']);
        add_action('wp_ajax_tbp_ai_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_tbp_ai_acf_enhance', [$this, 'handle_acf_enhance']);
        add_action('wp_ajax_tbp_ai_acf_generate', [$this, 'handle_acf_generate']);
        add_action('wp_ajax_tbp_ai_acf_suggest', [$this, 'handle_acf_suggest']);
    }

    /**
     * Handle content enhancement request
     */
    public function handle_enhance_request() {
        check_ajax_referer('tbp_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'enhance';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : $this->get_default_language();

        if (empty($content)) {
            wp_send_json_error(['message' => __('No content to enhance.', 'tbp-core')]);
        }

        $context = $this->build_post_context($post_id);
        $prompt = $this->build_prompt($action_type, $content, $context, $language);

        $response = $this->call_gemini_api($prompt);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success([
            'content' => $response,
            'message' => __('Content enhanced successfully.', 'tbp-core'),
        ]);
    }

    /**
     * Handle content generation request
     */
    public function handle_generate_request() {
        check_ajax_referer('tbp_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $prompt_text = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : $this->get_default_language();

        if (empty($prompt_text)) {
            wp_send_json_error(['message' => __('Please provide a prompt.', 'tbp-core')]);
        }

        $context = $this->build_post_context($post_id);
        $system_prompt = get_option('tbp_ai_system_prompt', '');
        $language_name = $this->get_language_name($language);

        $full_prompt = $system_prompt . "\n\n";
        $full_prompt .= "IMPORTANT INSTRUCTIONS:\n";
        $full_prompt .= "1. Write ALL content in {$language_name}.\n";
        $full_prompt .= "2. Use proper Markdown formatting:\n";
        $full_prompt .= "   - Use ## for main headings, ### for subheadings\n";
        $full_prompt .= "   - Use **bold** for emphasis\n";
        $full_prompt .= "   - Use - or * for bullet lists\n";
        $full_prompt .= "   - Use 1. 2. 3. for numbered lists\n";
        $full_prompt .= "   - Separate paragraphs with blank lines\n";
        $full_prompt .= "3. Structure the content with appropriate headings and sections.\n\n";
        $full_prompt .= "Post Context:\n" . $context . "\n\n";
        $full_prompt .= "User Request: " . $prompt_text;

        $response = $this->call_gemini_api($full_prompt);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success([
            'content' => $response,
            'message' => __('Content generated successfully.', 'tbp-core'),
        ]);
    }

    /**
     * Handle test connection request
     */
    public function handle_test_connection() {
        check_ajax_referer('tbp_ai_test', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $response = $this->call_gemini_api('Say "Connection successful!" in exactly those words.');

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success([
            'message' => __('API connection successful! Response: ', 'tbp-core') . $response,
        ]);
    }

    /**
     * Handle ACF field enhancement
     */
    public function handle_acf_enhance() {
        check_ajax_referer('tbp_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';
        $field_label = isset($_POST['field_label']) ? sanitize_text_field($_POST['field_label']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $action_type = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'enhance';

        if (empty($content)) {
            wp_send_json_error(['message' => __('No content to enhance.', 'tbp-core')]);
        }

        $context = $this->build_post_context($post_id);
        $field_context = "This content is for the ACF field: \"{$field_label}\" (key: {$field_key}).\n";
        $field_context .= "Please ensure the response is appropriate for this specific field.\n\n";

        $prompt = $this->build_prompt($action_type, $content, $field_context . $context);

        $response = $this->call_gemini_api($prompt, false); // false = don't convert to blocks

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success([
            'content' => $response,
            'message' => __('Field content enhanced.', 'tbp-core'),
        ]);
    }

    /**
     * Handle ACF field generation
     */
    public function handle_acf_generate() {
        check_ajax_referer('tbp_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';
        $field_label = isset($_POST['field_label']) ? sanitize_text_field($_POST['field_label']) : '';
        $field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : 'text';
        $prompt_text = isset($_POST['prompt']) ? sanitize_textarea_field($_POST['prompt']) : '';

        $context = $this->build_post_context($post_id);
        $system_prompt = get_option('tbp_ai_system_prompt', '');

        // Build field-specific instructions
        $field_instructions = $this->get_field_type_instructions($field_type, $field_label);

        $full_prompt = $system_prompt . "\n\n";
        $full_prompt .= $field_instructions . "\n\n";
        $full_prompt .= "Post Context:\n" . $context . "\n\n";

        if (!empty($prompt_text)) {
            $full_prompt .= "User Request: " . $prompt_text;
        } else {
            $full_prompt .= "Generate appropriate content for the \"{$field_label}\" field based on the post context.";
        }

        $response = $this->call_gemini_api($full_prompt, false);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        wp_send_json_success([
            'content' => $response,
            'message' => __('Content generated.', 'tbp-core'),
        ]);
    }

    /**
     * Handle ACF field suggestions (select, checkbox, relationship, taxonomy)
     */
    public function handle_acf_suggest() {
        check_ajax_referer('tbp_ai_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'tbp-core')]);
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $field_key = isset($_POST['field_key']) ? sanitize_text_field($_POST['field_key']) : '';
        $field_label = isset($_POST['field_label']) ? sanitize_text_field($_POST['field_label']) : '';
        $field_type = isset($_POST['field_type']) ? sanitize_text_field($_POST['field_type']) : '';
        $options = isset($_POST['options']) ? $_POST['options'] : [];

        if (empty($options)) {
            wp_send_json_error(['message' => __('No options available.', 'tbp-core')]);
        }

        // Sanitize options
        $options = array_map(function($opt) {
            return [
                'value' => sanitize_text_field($opt['value'] ?? ''),
                'label' => sanitize_text_field($opt['label'] ?? ''),
            ];
        }, $options);

        $context = $this->build_post_context($post_id);

        // Build options list for AI
        $options_list = array_map(function($opt, $idx) {
            return ($idx + 1) . ". {$opt['label']} (value: {$opt['value']})";
        }, $options, array_keys($options));

        $is_multi = in_array($field_type, ['checkbox', 'multi_select', 'relationship', 'taxonomy']);

        $prompt = "Based on the following post context, suggest the most appropriate ";
        $prompt .= $is_multi ? "selections (can be multiple)" : "selection (only one)";
        $prompt .= " for the field \"{$field_label}\".\n\n";
        $prompt .= "Post Context:\n{$context}\n\n";
        $prompt .= "Available Options:\n" . implode("\n", $options_list) . "\n\n";
        $prompt .= "IMPORTANT: Respond with ONLY the values (not labels) of your suggestions, separated by commas if multiple.\n";
        $prompt .= "Example response format: value1, value2\n";
        $prompt .= "Do not include any explanation, just the values.";

        $response = $this->call_gemini_api($prompt, false);

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        // Parse suggested values
        $suggested_values = array_map('trim', explode(',', $response));
        $suggested_values = array_filter($suggested_values);

        // Validate against available options
        $valid_values = array_column($options, 'value');
        $suggested_values = array_filter($suggested_values, function($v) use ($valid_values) {
            return in_array($v, $valid_values);
        });

        if (empty($suggested_values)) {
            wp_send_json_error(['message' => __('AI could not determine appropriate selections.', 'tbp-core')]);
        }

        // Get labels for display
        $suggestions = [];
        foreach ($suggested_values as $value) {
            $key = array_search($value, array_column($options, 'value'));
            if ($key !== false) {
                $suggestions[] = [
                    'value' => $value,
                    'label' => $options[$key]['label'],
                ];
            }
        }

        wp_send_json_success([
            'suggestions' => $suggestions,
            'message' => __('Suggestions ready.', 'tbp-core'),
        ]);
    }

    /**
     * Get instructions based on ACF field type
     */
    private function get_field_type_instructions($field_type, $field_label) {
        $instructions = "You are generating content for an ACF field named \"{$field_label}\".\n";

        switch ($field_type) {
            case 'text':
                $instructions .= "This is a single-line text field. Keep the response concise, typically under 100 characters.\n";
                $instructions .= "Do not include any formatting or line breaks.";
                break;

            case 'textarea':
                $instructions .= "This is a plain text area field. You can include multiple lines but no HTML formatting.\n";
                $instructions .= "Keep paragraphs separated by blank lines.";
                break;

            case 'wysiwyg':
                $instructions .= "This is a rich text (WYSIWYG) field. You can include HTML formatting.\n";
                $instructions .= "Use proper HTML tags like <p>, <strong>, <em>, <ul>, <ol>, <li>, <h3>, <h4>.\n";
                $instructions .= "Do not include <h1> or <h2> tags as those are typically reserved for the main title.";
                break;

            case 'url':
                $instructions .= "This field expects a URL. Return only a valid URL, nothing else.";
                break;

            case 'email':
                $instructions .= "This field expects an email address. Return only a valid email format.";
                break;

            case 'number':
                $instructions .= "This field expects a number. Return only a numeric value.";
                break;

            default:
                $instructions .= "Generate appropriate content for this field type.";
        }

        $instructions .= "\n\nIMPORTANT: Return ONLY the content for the field, no explanations or meta-commentary.";

        return $instructions;
    }

    /**
     * Build post context for AI
     */
    private function build_post_context($post_id) {
        if (!$post_id) {
            return '';
        }

        $post = get_post($post_id);
        if (!$post) {
            return '';
        }

        $context = [];

        // Title
        $context[] = "Title: " . $post->post_title;

        // Post Type
        $post_type_obj = get_post_type_object($post->post_type);
        $context[] = "Post Type: " . ($post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type);

        // Excerpt if available
        if (!empty($post->post_excerpt)) {
            $context[] = "Excerpt: " . $post->post_excerpt;
        }

        // Current content
        if (!empty($post->post_content)) {
            $plain_content = wp_strip_all_tags($post->post_content);
            $plain_content = substr($plain_content, 0, 2000); // Limit to 2000 chars
            $context[] = "Current Content: " . $plain_content;
        }

        // Taxonomies
        $taxonomies = get_object_taxonomies($post->post_type, 'objects');
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($post_id, $taxonomy->name, ['fields' => 'names']);
            if (!empty($terms) && !is_wp_error($terms)) {
                $context[] = $taxonomy->label . ": " . implode(', ', $terms);
            }
        }

        // ACF Fields (if available)
        if (function_exists('get_fields')) {
            $fields = get_fields($post_id);
            if ($fields) {
                $acf_context = $this->build_acf_context($fields);
                if (!empty($acf_context)) {
                    $context[] = "Custom Fields:\n" . $acf_context;
                }
            }
        }

        return implode("\n", $context);
    }

    /**
     * Build ACF fields context
     */
    private function build_acf_context($fields, $prefix = '') {
        $context = [];

        foreach ($fields as $key => $value) {
            // Skip complex types like images, files
            if (is_array($value) && isset($value['type']) && in_array($value['type'], ['image', 'file', 'gallery'])) {
                continue;
            }

            // Handle nested arrays/groups
            if (is_array($value)) {
                if (isset($value['post_title'])) {
                    // Related post
                    $context[] = $prefix . $key . ": " . $value['post_title'];
                } elseif (isset($value['name']) && isset($value['term_id'])) {
                    // Term
                    $context[] = $prefix . $key . ": " . $value['name'];
                } elseif (!empty($value)) {
                    // Check if it's a list of related items
                    $first = reset($value);
                    if (is_object($first) || (is_array($first) && isset($first['post_title']))) {
                        $titles = [];
                        foreach ($value as $item) {
                            if (is_object($item) && isset($item->post_title)) {
                                $titles[] = $item->post_title;
                            } elseif (is_array($item) && isset($item['post_title'])) {
                                $titles[] = $item['post_title'];
                            }
                        }
                        if (!empty($titles)) {
                            $context[] = $prefix . $key . ": " . implode(', ', $titles);
                        }
                    }
                }
            } elseif (is_string($value) && !empty($value) && strlen($value) < 500) {
                $context[] = $prefix . $key . ": " . $value;
            }
        }

        return implode("\n", $context);
    }

    /**
     * Build prompt based on action type
     */
    private function build_prompt($action_type, $content, $context, $language = null) {
        $system_prompt = get_option('tbp_ai_system_prompt', '');

        if (!$language) {
            $language = $this->get_default_language();
        }
        $language_name = $this->get_language_name($language);

        $action_prompts = [
            'enhance' => 'Enhance and improve the following content while maintaining its meaning and tone:',
            'simplify' => 'Simplify the following content to make it easier to read:',
            'expand' => 'Expand the following content with more details and examples:',
            'summarize' => 'Summarize the following content concisely:',
            'seo' => 'Optimize the following content for SEO while keeping it natural and readable:',
            'grammar' => 'Fix any grammar and spelling errors in the following content:',
            'tone_formal' => 'Rewrite the following content in a more formal, professional tone:',
            'tone_casual' => 'Rewrite the following content in a more casual, friendly tone:',
        ];

        $action_prompt = $action_prompts[$action_type] ?? $action_prompts['enhance'];

        $full_prompt = $system_prompt . "\n\n";
        $full_prompt .= "IMPORTANT INSTRUCTIONS:\n";
        $full_prompt .= "1. Write ALL content in {$language_name}.\n";
        $full_prompt .= "2. Use proper Markdown formatting:\n";
        $full_prompt .= "   - Use ## for main headings, ### for subheadings\n";
        $full_prompt .= "   - Use **bold** for emphasis\n";
        $full_prompt .= "   - Use - or * for bullet lists\n";
        $full_prompt .= "   - Use 1. 2. 3. for numbered lists\n";
        $full_prompt .= "   - Separate paragraphs with blank lines\n";
        $full_prompt .= "3. Structure the content with appropriate headings and sections.\n\n";

        if (!empty($context)) {
            $full_prompt .= "Post Context:\n" . $context . "\n\n";
        }

        $full_prompt .= $action_prompt . "\n\n";
        $full_prompt .= $content;

        return $full_prompt;
    }

    /**
     * Get default language
     */
    private function get_default_language() {
        return get_option('tbp_ai_default_language', 'en');
    }

    /**
     * Get language name from code
     */
    private function get_language_name($code) {
        $languages = TBP_AI_Settings::get_available_languages();
        return $languages[$code] ?? 'English';
    }

    /**
     * Call Gemini API
     * @param string $prompt The prompt to send
     * @param bool $convert_to_blocks Whether to convert response to Gutenberg blocks
     */
    private function call_gemini_api($prompt, $convert_to_blocks = true) {
        $api_key = get_option('tbp_ai_api_key', '');
        $model = get_option('tbp_ai_model', 'gemini-2.5-flash');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('API key not configured.', 'tbp-core'));
        }

        $url = $this->api_base . $model . ':generateContent?key=' . $api_key;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
            ],
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code !== 200) {
            $error_message = isset($data['error']['message'])
                ? $data['error']['message']
                : __('API request failed.', 'tbp-core');
            return new WP_Error('api_error', $error_message);
        }

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $data['candidates'][0]['content']['parts'][0]['text'];
            return $convert_to_blocks ? $this->convert_to_html($text) : $this->clean_response($text);
        }

        return new WP_Error('invalid_response', __('Invalid API response.', 'tbp-core'));
    }

    /**
     * Clean response for ACF fields (convert markdown to HTML without Gutenberg blocks)
     */
    private function clean_response($text) {
        // Remove code block markers
        $text = preg_replace('/^```[\w]*\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        // Convert markdown formatting to HTML
        // Bold and italic combined
        $text = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $text);
        $text = preg_replace('/___(.+?)___/', '<strong><em>$1</em></strong>', $text);
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        // Italic
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
        // Links
        $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $text);

        // Headers to HTML
        $text = preg_replace('/^######\s*(.+)$/m', '<h6>$1</h6>', $text);
        $text = preg_replace('/^#####\s*(.+)$/m', '<h5>$1</h5>', $text);
        $text = preg_replace('/^####\s*(.+)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $text);

        // Lists
        $text = preg_replace_callback('/(?:^[-*+]\s+.+$\n?)+/m', function($matches) {
            $items = preg_split('/\n/', trim($matches[0]));
            $list = '<ul>';
            foreach ($items as $item) {
                $item = preg_replace('/^[-*+]\s+/', '', $item);
                if (!empty(trim($item))) {
                    $list .= '<li>' . trim($item) . '</li>';
                }
            }
            return $list . '</ul>';
        }, $text);

        $text = preg_replace_callback('/(?:^\d+\.\s+.+$\n?)+/m', function($matches) {
            $items = preg_split('/\n/', trim($matches[0]));
            $list = '<ol>';
            foreach ($items as $item) {
                $item = preg_replace('/^\d+\.\s+/', '', $item);
                if (!empty(trim($item))) {
                    $list .= '<li>' . trim($item) . '</li>';
                }
            }
            return $list . '</ol>';
        }, $text);

        return $text;
    }

    /**
     * Convert markdown to Gutenberg blocks
     */
    private function convert_to_html($text) {
        // Remove code block markers
        $text = preg_replace('/^```[\w]*\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        $lines = explode("\n", $text);
        $blocks = [];
        $current_list = [];
        $list_type = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines but close any open list
            if (empty($trimmed)) {
                if (!empty($current_list)) {
                    $blocks[] = $this->create_list_block($current_list, $list_type);
                    $current_list = [];
                    $list_type = null;
                }
                continue;
            }

            // Check for headers
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $m)) {
                // Close any open list first
                if (!empty($current_list)) {
                    $blocks[] = $this->create_list_block($current_list, $list_type);
                    $current_list = [];
                    $list_type = null;
                }
                $level = strlen($m[1]);
                $content = $this->convert_inline_markdown($m[2]);
                $blocks[] = $this->create_heading_block($content, $level);
                continue;
            }

            // Check for unordered list items
            if (preg_match('/^[-*+]\s+(.+)$/', $trimmed, $m)) {
                // If switching list type, close previous
                if ($list_type === 'ol' && !empty($current_list)) {
                    $blocks[] = $this->create_list_block($current_list, $list_type);
                    $current_list = [];
                }
                $list_type = 'ul';
                $current_list[] = $this->convert_inline_markdown($m[1]);
                continue;
            }

            // Check for ordered list items
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $m)) {
                // If switching list type, close previous
                if ($list_type === 'ul' && !empty($current_list)) {
                    $blocks[] = $this->create_list_block($current_list, $list_type);
                    $current_list = [];
                }
                $list_type = 'ol';
                $current_list[] = $this->convert_inline_markdown($m[1]);
                continue;
            }

            // Regular paragraph - close any open list first
            if (!empty($current_list)) {
                $blocks[] = $this->create_list_block($current_list, $list_type);
                $current_list = [];
                $list_type = null;
            }

            $content = $this->convert_inline_markdown($trimmed);
            $blocks[] = $this->create_paragraph_block($content);
        }

        // Close any remaining open list
        if (!empty($current_list)) {
            $blocks[] = $this->create_list_block($current_list, $list_type);
        }

        return implode("\n\n", $blocks);
    }

    /**
     * Convert inline markdown (bold, italic, links)
     */
    private function convert_inline_markdown($text) {
        // Bold and italic combined
        $text = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $text);
        $text = preg_replace('/___(.+?)___/', '<strong><em>$1</em></strong>', $text);
        // Bold
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.+?)__/', '<strong>$1</strong>', $text);
        // Italic
        $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
        // Links
        $text = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $text);

        return $text;
    }

    /**
     * Create a Gutenberg paragraph block
     */
    private function create_paragraph_block($content) {
        return "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->";
    }

    /**
     * Create a Gutenberg heading block
     */
    private function create_heading_block($content, $level) {
        $level = max(1, min(6, $level));
        if ($level === 2) {
            return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$content}</h2>\n<!-- /wp:heading -->";
        }
        return "<!-- wp:heading {\"level\":{$level}} -->\n<h{$level} class=\"wp-block-heading\">{$content}</h{$level}>\n<!-- /wp:heading -->";
    }

    /**
     * Create a Gutenberg list block
     */
    private function create_list_block($items, $type = 'ul') {
        $tag = $type === 'ol' ? 'ol' : 'ul';
        $attr = $type === 'ol' ? ' {"ordered":true}' : '';

        $list_items = '';
        foreach ($items as $item) {
            $list_items .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n";
        }

        return "<!-- wp:list{$attr} -->\n<{$tag} class=\"wp-block-list\">\n{$list_items}</{$tag}>\n<!-- /wp:list -->";
    }
}
