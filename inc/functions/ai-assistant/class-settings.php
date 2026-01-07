<?php
/**
 * AI Assistant Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_AI_Settings {

    private static $instance = null;
    private $page_slug = 'tbp-ai-settings';

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page under Settings menu
     */
    public function add_settings_page() {
        add_options_page(
            __('AI Settings', 'tbp-core'),
            __('AI Settings', 'tbp-core'),
            'manage_options',
            $this->page_slug,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // API Settings Section
        add_settings_section(
            'tbp_ai_api_section',
            __('API Configuration', 'tbp-core'),
            [$this, 'render_api_section'],
            $this->page_slug
        );

        // API Key
        register_setting('tbp_ai_settings', 'tbp_ai_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        add_settings_field(
            'tbp_ai_api_key',
            __('Gemini API Key', 'tbp-core'),
            [$this, 'render_api_key_field'],
            $this->page_slug,
            'tbp_ai_api_section'
        );

        // Model Selection
        register_setting('tbp_ai_settings', 'tbp_ai_model', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'gemini-2.5-flash',
        ]);

        add_settings_field(
            'tbp_ai_model',
            __('AI Model', 'tbp-core'),
            [$this, 'render_model_field'],
            $this->page_slug,
            'tbp_ai_api_section'
        );

        // Default Language
        register_setting('tbp_ai_settings', 'tbp_ai_default_language', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'en',
        ]);

        add_settings_field(
            'tbp_ai_default_language',
            __('Default Language', 'tbp-core'),
            [$this, 'render_language_field'],
            $this->page_slug,
            'tbp_ai_api_section'
        );

        // CPT Settings Section
        add_settings_section(
            'tbp_ai_cpt_section',
            __('Post Type Settings', 'tbp-core'),
            [$this, 'render_cpt_section'],
            $this->page_slug
        );

        // Enabled CPTs
        register_setting('tbp_ai_settings', 'tbp_ai_enabled_cpts', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_cpts'],
            'default' => [],
        ]);

        add_settings_field(
            'tbp_ai_enabled_cpts',
            __('Enable AI for', 'tbp-core'),
            [$this, 'render_cpts_field'],
            $this->page_slug,
            'tbp_ai_cpt_section'
        );

        // Prompt Settings Section
        add_settings_section(
            'tbp_ai_prompt_section',
            __('Default Prompts', 'tbp-core'),
            [$this, 'render_prompt_section'],
            $this->page_slug
        );

        // System Prompt
        register_setting('tbp_ai_settings', 'tbp_ai_system_prompt', [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => $this->get_default_system_prompt(),
        ]);

        add_settings_field(
            'tbp_ai_system_prompt',
            __('System Prompt', 'tbp-core'),
            [$this, 'render_system_prompt_field'],
            $this->page_slug,
            'tbp_ai_prompt_section'
        );
    }

    /**
     * Get default system prompt
     */
    private function get_default_system_prompt() {
        return __('You are a helpful content assistant for a WordPress website. Your task is to help improve, enhance, or generate content for posts.

When enhancing content:
- Maintain the original tone and style
- Improve clarity and readability
- Fix grammar and spelling errors
- Optimize for SEO when appropriate
- Keep the content relevant to the post\'s taxonomies and categories

Always respond with only the improved content, no explanations or meta-commentary.', 'tbp-core');
    }

    /**
     * Sanitize CPTs array
     */
    public function sanitize_cpts($input) {
        if (!is_array($input)) {
            return [];
        }
        return array_map('sanitize_text_field', $input);
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if (!TBP_AI_Assistant::is_configured()) : ?>
                <div class="notice notice-warning">
                    <p>
                        <?php _e('Get your free Gemini API key from', 'tbp-core'); ?>
                        <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>
                    </p>
                </div>
            <?php else : ?>
                <div class="notice notice-success">
                    <p><?php _e('AI Assistant is configured and ready to use.', 'tbp-core'); ?></p>
                </div>
            <?php endif; ?>

            <form action="options.php" method="post">
                <?php
                settings_fields('tbp_ai_settings');
                do_settings_sections($this->page_slug);
                submit_button(__('Save Settings', 'tbp-core'));
                ?>
            </form>

            <?php $this->render_test_section(); ?>
        </div>
        <?php
    }

    /**
     * Render API section description
     */
    public function render_api_section() {
        echo '<p>' . __('Configure your Gemini API credentials. You can get a free API key from Google AI Studio.', 'tbp-core') . '</p>';
    }

    /**
     * Render CPT section description
     */
    public function render_cpt_section() {
        echo '<p>' . __('Select which post types should have AI features enabled.', 'tbp-core') . '</p>';
    }

    /**
     * Render prompt section description
     */
    public function render_prompt_section() {
        echo '<p>' . __('Customize the default prompts used by the AI assistant.', 'tbp-core') . '</p>';
    }

    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $value = get_option('tbp_ai_api_key', '');
        $masked = !empty($value) ? str_repeat('•', 20) . substr($value, -4) : '';
        ?>
        <input type="password"
               id="tbp_ai_api_key"
               name="tbp_ai_api_key"
               value="<?php echo esc_attr($value); ?>"
               class="regular-text"
               autocomplete="new-password">
        <button type="button" class="button" onclick="toggleApiKeyVisibility()">
            <?php _e('Show/Hide', 'tbp-core'); ?>
        </button>
        <p class="description">
            <?php _e('Enter your Gemini API key from', 'tbp-core'); ?>
            <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>
        </p>
        <script>
        function toggleApiKeyVisibility() {
            var input = document.getElementById('tbp_ai_api_key');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        </script>
        <?php
    }

    /**
     * Render model selection field
     */
    public function render_model_field() {
        $value = get_option('tbp_ai_model', 'gemini-2.5-flash');
        $models = [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash (Balanced speed & reasoning)',
            'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Fastest)',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro (Complex reasoning & creativity)',
        ];
        ?>
        <select id="tbp_ai_model" name="tbp_ai_model">
            <?php foreach ($models as $model_id => $model_name) : ?>
                <option value="<?php echo esc_attr($model_id); ?>" <?php selected($value, $model_id); ?>>
                    <?php echo esc_html($model_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Select the Gemini model to use. Flash models are faster and have higher rate limits.', 'tbp-core'); ?>
        </p>
        <?php
    }

    /**
     * Render language selection field
     */
    public function render_language_field() {
        $value = get_option('tbp_ai_default_language', 'en');
        $languages = $this->get_available_languages();
        ?>
        <select id="tbp_ai_default_language" name="tbp_ai_default_language">
            <?php foreach ($languages as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($value, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php _e('Default language for AI-generated content. Can be overridden per post.', 'tbp-core'); ?>
        </p>
        <?php
    }

    /**
     * Get available languages
     */
    public static function get_available_languages() {
        return [
            'en' => 'English',
            'es' => 'Spanish (Español)',
            'fr' => 'French (Français)',
            'de' => 'German (Deutsch)',
            'it' => 'Italian (Italiano)',
            'pt' => 'Portuguese (Português)',
            'nl' => 'Dutch (Nederlands)',
            'pl' => 'Polish (Polski)',
            'ru' => 'Russian (Русский)',
            'ja' => 'Japanese (日本語)',
            'zh' => 'Chinese (中文)',
            'ko' => 'Korean (한국어)',
            'ar' => 'Arabic (العربية)',
            'hi' => 'Hindi (हिन्दी)',
            'tr' => 'Turkish (Türkçe)',
            'vi' => 'Vietnamese (Tiếng Việt)',
            'th' => 'Thai (ไทย)',
            'id' => 'Indonesian (Bahasa Indonesia)',
            'cs' => 'Czech (Čeština)',
            'el' => 'Greek (Ελληνικά)',
            'he' => 'Hebrew (עברית)',
            'hu' => 'Hungarian (Magyar)',
            'ro' => 'Romanian (Română)',
            'sv' => 'Swedish (Svenska)',
            'da' => 'Danish (Dansk)',
            'fi' => 'Finnish (Suomi)',
            'no' => 'Norwegian (Norsk)',
            'uk' => 'Ukrainian (Українська)',
            'sq' => 'Albanian (Shqip)',
        ];
    }

    /**
     * Render CPTs field
     */
    public function render_cpts_field() {
        $enabled = get_option('tbp_ai_enabled_cpts', []);
        $post_types = get_post_types(['public' => true], 'objects');

        // Exclude attachments
        unset($post_types['attachment']);

        ?>
        <fieldset>
            <?php foreach ($post_types as $post_type) : ?>
                <label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox"
                           name="tbp_ai_enabled_cpts[]"
                           value="<?php echo esc_attr($post_type->name); ?>"
                           <?php checked(in_array($post_type->name, $enabled)); ?>>
                    <?php echo esc_html($post_type->label); ?>
                    <span class="description">(<?php echo esc_html($post_type->name); ?>)</span>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    /**
     * Render system prompt field
     */
    public function render_system_prompt_field() {
        $value = get_option('tbp_ai_system_prompt', $this->get_default_system_prompt());
        ?>
        <textarea id="tbp_ai_system_prompt"
                  name="tbp_ai_system_prompt"
                  rows="8"
                  class="large-text code"><?php echo esc_textarea($value); ?></textarea>
        <p class="description">
            <?php _e('This prompt provides context to the AI about how to behave. The post title, content, and taxonomies will be automatically added to each request.', 'tbp-core'); ?>
        </p>
        <button type="button" class="button" onclick="resetSystemPrompt()">
            <?php _e('Reset to Default', 'tbp-core'); ?>
        </button>
        <script>
        function resetSystemPrompt() {
            if (confirm('<?php _e('Reset system prompt to default?', 'tbp-core'); ?>')) {
                document.getElementById('tbp_ai_system_prompt').value = <?php echo json_encode($this->get_default_system_prompt()); ?>;
            }
        }
        </script>
        <?php
    }

    /**
     * Render test section
     */
    public function render_test_section() {
        if (!TBP_AI_Assistant::is_configured()) {
            return;
        }
        ?>
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Test API Connection', 'tbp-core'); ?></h2>
            <p><?php _e('Click the button below to test your API connection.', 'tbp-core'); ?></p>
            <button type="button" class="button button-secondary" id="tbp-test-api">
                <?php _e('Test Connection', 'tbp-core'); ?>
            </button>
            <div id="tbp-test-result" style="margin-top: 15px;"></div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#tbp-test-api').on('click', function() {
                var $btn = $(this);
                var $result = $('#tbp-test-result');

                $btn.prop('disabled', true).text('<?php _e('Testing...', 'tbp-core'); ?>');
                $result.html('');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tbp_ai_test_connection',
                        nonce: '<?php echo wp_create_nonce('tbp_ai_test'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error inline"><p><?php _e('Connection failed. Please check your settings.', 'tbp-core'); ?></p></div>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Test Connection', 'tbp-core'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
}
