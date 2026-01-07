/**
 * TBP AI Assistant - ACF Integration
 */
(function($) {
    'use strict';

    const TBP_AI_ACF = {
        // Text-based fields (content generation)
        textTypes: ['text', 'textarea', 'wysiwyg'],
        // Selection fields (AI suggestions)
        selectTypes: ['select', 'checkbox', 'radio', 'button_group'],
        // Relationship fields
        relationTypes: ['relationship', 'post_object', 'taxonomy'],

        init: function() {
            if (typeof acf !== 'undefined') {
                acf.addAction('ready', this.addAIButtons.bind(this));
                acf.addAction('append', this.addAIButtons.bind(this));
            } else {
                $(document).ready(this.addAIButtons.bind(this));
            }
        },

        addAIButtons: function() {
            const self = this;
            const allTypes = [...this.textTypes, ...this.selectTypes, ...this.relationTypes];

            $('.acf-field').each(function() {
                const $field = $(this);
                const fieldType = $field.data('type');
                const fieldKey = $field.data('key');
                const $label = $field.find('> .acf-label label').first();
                const fieldLabel = $label.text().replace('*', '').trim();

                // Skip if not supported or already has AI button
                if (!allTypes.includes(fieldType) || $field.find('.tbp-ai-acf-btn').length) {
                    return;
                }

                // Skip tab fields (they're layout only)
                if (fieldType === 'tab' || fieldType === 'group' || fieldType === 'repeater') {
                    return;
                }

                const $aiBtn = $('<button>', {
                    type: 'button',
                    class: 'tbp-ai-acf-btn',
                    title: 'AI Assistant',
                    'data-field-key': fieldKey,
                    'data-field-type': fieldType,
                    'data-field-label': fieldLabel
                }).html('<span class="dashicons dashicons-superhero-alt"></span>');

                $label.append($aiBtn);

                $aiBtn.on('click', function(e) {
                    e.preventDefault();
                    if (self.textTypes.includes(fieldType)) {
                        self.openTextModal($field, fieldKey, fieldType, fieldLabel);
                    } else if (self.selectTypes.includes(fieldType)) {
                        self.openSelectModal($field, fieldKey, fieldType, fieldLabel);
                    } else if (self.relationTypes.includes(fieldType)) {
                        self.openRelationModal($field, fieldKey, fieldType, fieldLabel);
                    }
                });
            });
        },

        // ============ TEXT FIELDS MODAL ============
        openTextModal: function($field, fieldKey, fieldType, fieldLabel) {
            const self = this;
            const currentContent = this.getTextContent($field, fieldType);

            const $modal = this.createModal('AI Assistant - ' + fieldLabel, `
                <div class="tbp-ai-acf-section">
                    <label>Quick Actions</label>
                    <div class="tbp-ai-acf-actions">
                        <button type="button" class="button tbp-ai-acf-action" data-action="enhance" ${!currentContent ? 'disabled' : ''}>Enhance</button>
                        <button type="button" class="button tbp-ai-acf-action" data-action="simplify" ${!currentContent ? 'disabled' : ''}>Simplify</button>
                        <button type="button" class="button tbp-ai-acf-action" data-action="expand" ${!currentContent ? 'disabled' : ''}>Expand</button>
                        <button type="button" class="button tbp-ai-acf-action" data-action="grammar" ${!currentContent ? 'disabled' : ''}>Fix Grammar</button>
                    </div>
                </div>
                <div class="tbp-ai-acf-section">
                    <label>Custom Prompt</label>
                    <textarea class="tbp-ai-acf-prompt" rows="3" placeholder="E.g., Write a short description about..."></textarea>
                    <button type="button" class="button button-primary tbp-ai-acf-generate">Generate</button>
                </div>
                <div class="tbp-ai-acf-loading" style="display: none;">
                    <span class="spinner is-active"></span>
                    <span>Generating...</span>
                </div>
                <div class="tbp-ai-acf-result" style="display: none;">
                    <label>Result</label>
                    <div class="tbp-ai-acf-result-content"></div>
                    <div class="tbp-ai-acf-result-actions">
                        <button type="button" class="button button-primary tbp-ai-acf-apply">Apply</button>
                        <button type="button" class="button tbp-ai-acf-copy">Copy</button>
                    </div>
                </div>
                <div class="tbp-ai-acf-error" style="display: none;"></div>
            `);

            let generatedContent = '';
            const ui = this.getUIElements($modal);

            // Quick actions
            $modal.find('.tbp-ai-acf-action').on('click', function() {
                const action = $(this).data('action');
                self.enhanceText(fieldKey, fieldType, fieldLabel, currentContent, action, ui, (content) => {
                    generatedContent = content;
                });
            });

            // Generate
            $modal.find('.tbp-ai-acf-generate').on('click', function() {
                const prompt = $modal.find('.tbp-ai-acf-prompt').val().trim();
                if (!prompt) {
                    alert('Please enter a prompt.');
                    return;
                }
                self.generateText(fieldKey, fieldType, fieldLabel, prompt, ui, (content) => {
                    generatedContent = content;
                });
            });

            // Apply
            $modal.find('.tbp-ai-acf-apply').on('click', function() {
                if (generatedContent) {
                    self.setTextContent($field, fieldType, generatedContent);
                    self.closeModal();
                }
            });

            // Copy
            $modal.find('.tbp-ai-acf-copy').on('click', function() {
                self.copyToClipboard(generatedContent, $(this));
            });
        },

        // ============ SELECT FIELDS MODAL ============
        openSelectModal: function($field, fieldKey, fieldType, fieldLabel) {
            const self = this;
            const options = this.getSelectOptions($field, fieldType);

            if (!options.length) {
                alert('No options available for this field.');
                return;
            }

            const $modal = this.createModal('AI Suggestions - ' + fieldLabel, `
                <div class="tbp-ai-acf-section">
                    <p class="tbp-ai-acf-info">AI will analyze the post content and suggest the best options for this field.</p>
                    <button type="button" class="button button-primary tbp-ai-acf-suggest">Get AI Suggestions</button>
                </div>
                <div class="tbp-ai-acf-loading" style="display: none;">
                    <span class="spinner is-active"></span>
                    <span>Analyzing...</span>
                </div>
                <div class="tbp-ai-acf-suggestions" style="display: none;">
                    <label>Suggested Selections</label>
                    <div class="tbp-ai-acf-suggestions-list"></div>
                    <div class="tbp-ai-acf-result-actions">
                        <button type="button" class="button button-primary tbp-ai-acf-apply-suggestions">Apply Suggestions</button>
                    </div>
                </div>
                <div class="tbp-ai-acf-error" style="display: none;"></div>
            `);

            let suggestions = [];
            const ui = this.getUIElements($modal);

            $modal.find('.tbp-ai-acf-suggest').on('click', function() {
                self.getSuggestions(fieldKey, fieldType, fieldLabel, options, ui, (result) => {
                    suggestions = result;
                    const html = suggestions.map(s => `<div class="tbp-ai-suggestion-item"><span class="dashicons dashicons-yes"></span> ${s.label}</div>`).join('');
                    $modal.find('.tbp-ai-acf-suggestions-list').html(html);
                    $modal.find('.tbp-ai-acf-suggestions').show();
                });
            });

            $modal.find('.tbp-ai-acf-apply-suggestions').on('click', function() {
                if (suggestions.length) {
                    self.setSelectValues($field, fieldType, suggestions.map(s => s.value));
                    self.closeModal();
                }
            });
        },

        // ============ RELATIONSHIP FIELDS MODAL ============
        openRelationModal: function($field, fieldKey, fieldType, fieldLabel) {
            const self = this;

            // Get available choices from the field
            const options = this.getRelationOptions($field, fieldType);

            if (!options.length) {
                alert('No options available. Please ensure there are posts/terms to choose from.');
                return;
            }

            const $modal = this.createModal('AI Suggestions - ' + fieldLabel, `
                <div class="tbp-ai-acf-section">
                    <p class="tbp-ai-acf-info">AI will suggest related ${fieldType === 'taxonomy' ? 'terms' : 'posts'} based on the current content.</p>
                    <button type="button" class="button button-primary tbp-ai-acf-suggest">Get AI Suggestions</button>
                </div>
                <div class="tbp-ai-acf-loading" style="display: none;">
                    <span class="spinner is-active"></span>
                    <span>Analyzing...</span>
                </div>
                <div class="tbp-ai-acf-suggestions" style="display: none;">
                    <label>Suggested ${fieldType === 'taxonomy' ? 'Terms' : 'Posts'}</label>
                    <div class="tbp-ai-acf-suggestions-list"></div>
                    <div class="tbp-ai-acf-result-actions">
                        <button type="button" class="button button-primary tbp-ai-acf-apply-suggestions">Apply Suggestions</button>
                    </div>
                </div>
                <div class="tbp-ai-acf-error" style="display: none;"></div>
            `);

            let suggestions = [];
            const ui = this.getUIElements($modal);

            $modal.find('.tbp-ai-acf-suggest').on('click', function() {
                self.getSuggestions(fieldKey, fieldType, fieldLabel, options, ui, (result) => {
                    suggestions = result;
                    const html = suggestions.map(s => `<div class="tbp-ai-suggestion-item"><span class="dashicons dashicons-yes"></span> ${s.label}</div>`).join('');
                    $modal.find('.tbp-ai-acf-suggestions-list').html(html);
                    $modal.find('.tbp-ai-acf-suggestions').show();
                });
            });

            $modal.find('.tbp-ai-acf-apply-suggestions').on('click', function() {
                if (suggestions.length) {
                    self.setRelationValues($field, fieldType, suggestions);
                    self.closeModal();
                }
            });
        },

        // ============ HELPER METHODS ============
        createModal: function(title, content) {
            $('.tbp-ai-acf-modal, .tbp-ai-acf-overlay').remove();

            const $overlay = $('<div>', { class: 'tbp-ai-acf-overlay' });
            const $modal = $('<div>', { class: 'tbp-ai-acf-modal' });

            $modal.html(`
                <div class="tbp-ai-acf-modal-content">
                    <div class="tbp-ai-acf-modal-header">
                        <h3>${title}</h3>
                        <button type="button" class="tbp-ai-acf-close">&times;</button>
                    </div>
                    <div class="tbp-ai-acf-modal-body">${content}</div>
                </div>
            `);

            $('body').append($overlay).append($modal);

            const self = this;
            $overlay.on('click', () => self.closeModal());
            $modal.find('.tbp-ai-acf-close').on('click', () => self.closeModal());

            return $modal;
        },

        closeModal: function() {
            $('.tbp-ai-acf-modal, .tbp-ai-acf-overlay').remove();
        },

        getUIElements: function($modal) {
            return {
                $loading: $modal.find('.tbp-ai-acf-loading'),
                $result: $modal.find('.tbp-ai-acf-result'),
                $resultContent: $modal.find('.tbp-ai-acf-result-content'),
                $error: $modal.find('.tbp-ai-acf-error'),
                $suggestions: $modal.find('.tbp-ai-acf-suggestions')
            };
        },

        copyToClipboard: function(text, $btn) {
            navigator.clipboard.writeText(text).then(() => {
                const original = $btn.text();
                $btn.text('Copied!');
                setTimeout(() => $btn.text(original), 2000);
            });
        },

        // ============ TEXT FIELD METHODS ============
        getTextContent: function($field, fieldType) {
            switch (fieldType) {
                case 'text':
                    return $field.find('input[type="text"]').val();
                case 'textarea':
                    return $field.find('textarea').val();
                case 'wysiwyg':
                    const editorId = $field.find('.wp-editor-area').attr('id');
                    if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                        return tinymce.get(editorId).getContent();
                    }
                    return $field.find('.wp-editor-area').val();
                default:
                    return '';
            }
        },

        setTextContent: function($field, fieldType, content) {
            switch (fieldType) {
                case 'text':
                    $field.find('input[type="text"]').val(content).trigger('change');
                    break;
                case 'textarea':
                    $field.find('textarea').val(content).trigger('change');
                    break;
                case 'wysiwyg':
                    const editorId = $field.find('.wp-editor-area').attr('id');
                    if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                        tinymce.get(editorId).setContent(content);
                    }
                    $field.find('.wp-editor-area').val(content).trigger('change');
                    break;
            }
        },

        enhanceText: function(fieldKey, fieldType, fieldLabel, content, action, ui, callback) {
            ui.$loading.show();
            ui.$result.hide();
            ui.$error.hide();

            $.ajax({
                url: tbpAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_ai_acf_enhance',
                    nonce: tbpAI.nonce,
                    post_id: tbpAI.postId,
                    field_key: fieldKey,
                    field_type: fieldType,
                    field_label: fieldLabel,
                    content: content,
                    action_type: action
                },
                success: function(response) {
                    ui.$loading.hide();
                    if (response.success) {
                        ui.$resultContent.html(response.data.content);
                        ui.$result.show();
                        callback(response.data.content);
                    } else {
                        ui.$error.text(response.data.message).show();
                    }
                },
                error: function() {
                    ui.$loading.hide();
                    ui.$error.text('Connection error.').show();
                }
            });
        },

        generateText: function(fieldKey, fieldType, fieldLabel, prompt, ui, callback) {
            ui.$loading.show();
            ui.$result.hide();
            ui.$error.hide();

            $.ajax({
                url: tbpAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_ai_acf_generate',
                    nonce: tbpAI.nonce,
                    post_id: tbpAI.postId,
                    field_key: fieldKey,
                    field_type: fieldType,
                    field_label: fieldLabel,
                    prompt: prompt
                },
                success: function(response) {
                    ui.$loading.hide();
                    if (response.success) {
                        ui.$resultContent.html(response.data.content);
                        ui.$result.show();
                        callback(response.data.content);
                    } else {
                        ui.$error.text(response.data.message).show();
                    }
                },
                error: function() {
                    ui.$loading.hide();
                    ui.$error.text('Connection error.').show();
                }
            });
        },

        // ============ SELECT FIELD METHODS ============
        getSelectOptions: function($field, fieldType) {
            const options = [];

            switch (fieldType) {
                case 'select':
                    $field.find('select option').each(function() {
                        const val = $(this).val();
                        if (val) {
                            options.push({ value: val, label: $(this).text() });
                        }
                    });
                    break;

                case 'checkbox':
                case 'radio':
                case 'button_group':
                    $field.find('input[type="checkbox"], input[type="radio"]').each(function() {
                        options.push({
                            value: $(this).val(),
                            label: $(this).closest('label').text().trim() || $(this).val()
                        });
                    });
                    break;
            }

            return options;
        },

        setSelectValues: function($field, fieldType, values) {
            switch (fieldType) {
                case 'select':
                    const $select = $field.find('select');
                    if ($select.prop('multiple')) {
                        $select.val(values).trigger('change');
                    } else {
                        $select.val(values[0]).trigger('change');
                    }
                    break;

                case 'checkbox':
                    $field.find('input[type="checkbox"]').each(function() {
                        $(this).prop('checked', values.includes($(this).val())).trigger('change');
                    });
                    break;

                case 'radio':
                case 'button_group':
                    $field.find('input[type="radio"]').each(function() {
                        if (values.includes($(this).val())) {
                            $(this).prop('checked', true).trigger('change');
                        }
                    });
                    break;
            }
        },

        // ============ RELATIONSHIP FIELD METHODS ============
        getRelationOptions: function($field, fieldType) {
            const options = [];

            if (fieldType === 'taxonomy') {
                // Taxonomy field - get from checkboxes or select
                $field.find('input[type="checkbox"], select option').each(function() {
                    const val = $(this).val();
                    if (val) {
                        const label = $(this).is('option') ? $(this).text() : $(this).closest('label').text().trim();
                        options.push({ value: val, label: label });
                    }
                });
            } else {
                // Relationship/Post Object - get from available choices
                $field.find('.choices .acf-rel-item, .choices-list li').each(function() {
                    options.push({
                        value: $(this).data('id') || $(this).find('input').val(),
                        label: $(this).find('.acf-rel-item-title, span').first().text().trim()
                    });
                });

                // Also check select2 if used
                if (!options.length) {
                    $field.find('select option').each(function() {
                        const val = $(this).val();
                        if (val) {
                            options.push({ value: val, label: $(this).text() });
                        }
                    });
                }
            }

            return options;
        },

        setRelationValues: function($field, fieldType, suggestions) {
            const values = suggestions.map(s => s.value);

            if (fieldType === 'taxonomy') {
                $field.find('input[type="checkbox"]').each(function() {
                    $(this).prop('checked', values.includes($(this).val())).trigger('change');
                });

                const $select = $field.find('select');
                if ($select.length) {
                    $select.val(values).trigger('change');
                }
            } else {
                // For relationship fields, we need to trigger the ACF selection
                suggestions.forEach(s => {
                    const $item = $field.find(`.choices .acf-rel-item[data-id="${s.value}"]`);
                    if ($item.length) {
                        $item.trigger('click');
                    }
                });
            }
        },

        getSuggestions: function(fieldKey, fieldType, fieldLabel, options, ui, callback) {
            ui.$loading.show();
            if (ui.$suggestions) ui.$suggestions.hide();
            ui.$error.hide();

            $.ajax({
                url: tbpAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_ai_acf_suggest',
                    nonce: tbpAI.nonce,
                    post_id: tbpAI.postId,
                    field_key: fieldKey,
                    field_type: fieldType,
                    field_label: fieldLabel,
                    options: options
                },
                success: function(response) {
                    ui.$loading.hide();
                    if (response.success) {
                        callback(response.data.suggestions);
                    } else {
                        ui.$error.text(response.data.message).show();
                    }
                },
                error: function() {
                    ui.$loading.hide();
                    ui.$error.text('Connection error.').show();
                }
            });
        }
    };

    TBP_AI_ACF.init();

})(jQuery);
