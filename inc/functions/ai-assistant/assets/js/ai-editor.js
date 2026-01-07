(function(wp) {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, TextareaControl, Spinner, SelectControl } = wp.components;
    const { useState, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { createElement: el, Fragment } = wp.element;

    const AIIcon = el('svg', {
        width: 20,
        height: 20,
        viewBox: '0 0 24 24',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 2
    },
        el('path', { d: 'M12 2L2 7l10 5 10-5-10-5z' }),
        el('path', { d: 'M2 17l10 5 10-5' }),
        el('path', { d: 'M2 12l10 5 10-5' })
    );

    // Convert languages object to options array
    const getLanguageOptions = () => {
        const options = [];
        if (tbpAI.languages) {
            Object.entries(tbpAI.languages).forEach(([code, name]) => {
                options.push({ value: code, label: name });
            });
        }
        return options;
    };

    const AISidebarPanel = () => {
        const [isLoading, setIsLoading] = useState(false);
        const [customPrompt, setCustomPrompt] = useState('');
        const [result, setResult] = useState(null);
        const [error, setError] = useState(null);
        const [language, setLanguage] = useState(tbpAI.defaultLanguage || 'en');

        const { resetBlocks, insertBlocks } = useDispatch('core/block-editor');
        const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

        const postId = useSelect((select) => select('core/editor').getCurrentPostId());

        // Save language preference to post meta
        const { editPost } = useDispatch('core/editor');
        const savedLanguage = useSelect((select) =>
            select('core/editor').getEditedPostAttribute('meta')?._tbp_ai_language
        );

        useEffect(() => {
            if (savedLanguage) {
                setLanguage(savedLanguage);
            }
        }, [savedLanguage]);

        const handleLanguageChange = (newLang) => {
            setLanguage(newLang);
            // Save to post meta for persistence
            editPost({ meta: { _tbp_ai_language: newLang } });
        };

        const quickActions = [
            { key: 'enhance', label: 'Enhance' },
            { key: 'simplify', label: 'Simplify' },
            { key: 'expand', label: 'Expand' },
            { key: 'summarize', label: 'Summarize' },
            { key: 'seo', label: 'SEO' },
            { key: 'grammar', label: 'Grammar' },
        ];

        const toneActions = [
            { key: 'tone_formal', label: 'Formal' },
            { key: 'tone_casual', label: 'Casual' },
        ];

        const getContent = () => {
            return wp.data.select('core/editor').getEditedPostContent();
        };

        const handleAction = (actionType) => {
            const content = getContent();
            if (!content) {
                createErrorNotice('Please add some content first.', { type: 'snackbar' });
                return;
            }

            setIsLoading(true);
            setError(null);
            setResult(null);

            jQuery.ajax({
                url: tbpAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_ai_enhance',
                    nonce: tbpAI.nonce,
                    post_id: postId,
                    content: content,
                    action_type: actionType,
                    language: language
                },
                success: (response) => {
                    setIsLoading(false);
                    if (response.success) {
                        setResult(response.data.content);
                    } else {
                        setError(response.data.message || 'An error occurred.');
                    }
                },
                error: () => {
                    setIsLoading(false);
                    setError('Connection error. Please try again.');
                }
            });
        };

        const handleGenerate = () => {
            if (!customPrompt.trim()) {
                createErrorNotice('Please enter a prompt.', { type: 'snackbar' });
                return;
            }

            setIsLoading(true);
            setError(null);
            setResult(null);

            jQuery.ajax({
                url: tbpAI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_ai_generate',
                    nonce: tbpAI.nonce,
                    post_id: postId,
                    prompt: customPrompt,
                    language: language
                },
                success: (response) => {
                    setIsLoading(false);
                    if (response.success) {
                        setResult(response.data.content);
                    } else {
                        setError(response.data.message || 'An error occurred.');
                    }
                },
                error: () => {
                    setIsLoading(false);
                    setError('Connection error. Please try again.');
                }
            });
        };

        const applyResult = () => {
            if (!result) return;

            try {
                const blocks = wp.blocks.parse(result);
                if (blocks && blocks.length > 0) {
                    resetBlocks(blocks);
                    createSuccessNotice('Content applied successfully!', { type: 'snackbar' });
                    setResult(null);
                    setCustomPrompt('');
                }
            } catch (e) {
                createErrorNotice('Failed to apply content.', { type: 'snackbar' });
            }
        };

        const appendResult = () => {
            if (!result) return;

            try {
                const blocks = wp.blocks.parse(result);
                if (blocks && blocks.length > 0) {
                    insertBlocks(blocks);
                    createSuccessNotice('Content appended!', { type: 'snackbar' });
                    setResult(null);
                    setCustomPrompt('');
                }
            } catch (e) {
                createErrorNotice('Failed to append content.', { type: 'snackbar' });
            }
        };

        const copyResult = () => {
            if (!result) return;
            navigator.clipboard.writeText(result).then(() => {
                createSuccessNotice('Copied to clipboard!', { type: 'snackbar' });
            });
        };

        return el(Fragment, {},
            el(PluginSidebarMoreMenuItem, {
                target: 'tbp-ai-sidebar',
                icon: AIIcon
            }, 'AI Assistant'),
            el(PluginSidebar, {
                name: 'tbp-ai-sidebar',
                title: 'AI Assistant',
                icon: AIIcon
            },
                // Language Panel
                el(PanelBody, {
                    title: 'Language',
                    initialOpen: false
                },
                    el(SelectControl, {
                        value: language,
                        options: getLanguageOptions(),
                        onChange: handleLanguageChange,
                        help: 'Override the default language for this post'
                    })
                ),

                // Quick Actions Panel
                el(PanelBody, {
                    title: 'Quick Actions',
                    initialOpen: true
                },
                    el('p', { className: 'tbp-ai-description' }, 'Transform your existing content:'),
                    el('div', { className: 'tbp-ai-button-grid' },
                        quickActions.map((action) =>
                            el(Button, {
                                key: action.key,
                                variant: 'secondary',
                                onClick: () => handleAction(action.key),
                                disabled: isLoading,
                                className: 'tbp-ai-action-btn'
                            }, action.label)
                        )
                    )
                ),

                // Tone Panel
                el(PanelBody, {
                    title: 'Adjust Tone',
                    initialOpen: false
                },
                    el('div', { className: 'tbp-ai-button-grid' },
                        toneActions.map((action) =>
                            el(Button, {
                                key: action.key,
                                variant: 'secondary',
                                onClick: () => handleAction(action.key),
                                disabled: isLoading,
                                className: 'tbp-ai-action-btn'
                            }, action.label)
                        )
                    )
                ),

                // Custom Prompt Panel
                el(PanelBody, {
                    title: 'Custom Prompt',
                    initialOpen: true
                },
                    el(TextareaControl, {
                        value: customPrompt,
                        onChange: setCustomPrompt,
                        placeholder: 'E.g., Write an introduction about...',
                        rows: 3,
                        disabled: isLoading
                    }),
                    el(Button, {
                        variant: 'primary',
                        onClick: handleGenerate,
                        disabled: isLoading || !customPrompt.trim(),
                        className: 'tbp-ai-generate-btn'
                    }, 'Generate')
                ),

                // Loading State
                isLoading && el('div', { className: 'tbp-ai-loading' },
                    el(Spinner),
                    el('span', {}, 'Generating...')
                ),

                // Error State
                error && el('div', { className: 'tbp-ai-error' },
                    el('p', {}, error)
                ),

                // Result Panel
                result && el(PanelBody, {
                    title: 'AI Result',
                    initialOpen: true,
                    className: 'tbp-ai-result-panel'
                },
                    el('div', {
                        className: 'tbp-ai-result-preview',
                        dangerouslySetInnerHTML: { __html: getPreviewHtml(result) }
                    }),
                    el('div', { className: 'tbp-ai-result-actions' },
                        el(Button, {
                            variant: 'primary',
                            onClick: applyResult,
                            className: 'tbp-ai-result-btn'
                        }, 'Replace Content'),
                        el(Button, {
                            variant: 'secondary',
                            onClick: appendResult,
                            className: 'tbp-ai-result-btn'
                        }, 'Append'),
                        el(Button, {
                            variant: 'tertiary',
                            onClick: copyResult,
                            className: 'tbp-ai-result-btn'
                        }, 'Copy'),
                        el(Button, {
                            variant: 'tertiary',
                            onClick: () => setResult(null),
                            className: 'tbp-ai-result-btn'
                        }, 'Discard')
                    )
                )
            )
        );
    };

    function getPreviewHtml(content) {
        return content
            .replace(/<!-- wp:[^>]+-->/g, '')
            .replace(/<!-- \/wp:[^>]+-->/g, '')
            .replace(/class="[^"]*"/g, '')
            .trim();
    }

    if (typeof tbpAI !== 'undefined') {
        registerPlugin('tbp-ai-assistant', {
            render: AISidebarPanel
        });
    }

})(window.wp);
