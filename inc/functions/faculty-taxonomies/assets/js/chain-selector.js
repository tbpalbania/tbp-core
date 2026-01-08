(function($) {
    'use strict';

    $(document).ready(function() {
        initChainSelectors();
    });

    // Custom template for Select2 options with icons
    function formatOption(option) {
        if (!option.id) {
            return option.text;
        }

        var $option = $(option.element);
        var icon = $option.data('icon') || option.icon;

        if (icon) {
            return $('<span class="tbp-select2-option"><img src="' + icon + '" class="tbp-select2-icon" /><span>' + option.text + '</span></span>');
        }

        return option.text;
    }

    // Custom template for selected option
    function formatSelection(option) {
        if (!option.id) {
            return option.text;
        }

        var $option = $(option.element);
        var icon = $option.data('icon') || option.icon;

        if (icon) {
            return $('<span class="tbp-select2-selection"><img src="' + icon + '" class="tbp-select2-icon" /><span>' + option.text + '</span></span>');
        }

        return option.text;
    }

    function initChainSelectors() {
        // Initialize all Select2 fields
        $('.tbp-select2').each(function() {
            var $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return; // Already initialized
            }

            $select.select2({
                placeholder: $select.data('placeholder') || '',
                allowClear: true,
                width: '100%',
                templateResult: formatOption,
                templateSelection: formatSelection,
                language: {
                    noResults: function() {
                        return tbpChainSelector.i18n.no_results;
                    },
                    searching: function() {
                        return tbpChainSelector.i18n.searching;
                    }
                }
            });
        });

        // Handle chain selection change
        $(document).on('change', '.tbp-chain-select', function() {
            var $select = $(this);
            var parentId = $select.val();
            var childId = $select.data('child');
            var childTaxonomy = $select.data('child-taxonomy');
            var parentMetaKey = $select.data('parent-meta-key');

            if (!childId) {
                return;
            }

            var $child = $('#' + childId);

            if (!$child.length) {
                return;
            }

            // Clear child and all subsequent selects
            clearChildSelects($child);

            if (!parentId) {
                return;
            }

            // Show loading state
            $child.prop('disabled', true);

            // Get parent icon for departments
            var parentIcon = '';
            if (childTaxonomy === 'tbp_department') {
                var $selectedOption = $select.find('option:selected');
                parentIcon = $selectedOption.data('icon') || '';
            }

            $.ajax({
                url: tbpChainSelector.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_get_chain_terms',
                    nonce: tbpChainSelector.nonce,
                    parent_id: parentId,
                    child_taxonomy: childTaxonomy,
                    parent_meta_key: parentMetaKey
                },
                success: function(response) {
                    if (response.success && response.data) {
                        populateSelect($child, response.data, parentIcon);
                    }
                },
                error: function() {
                    console.error('TBP Chain Selector: Failed to load terms');
                },
                complete: function() {
                    $child.prop('disabled', false);
                }
            });
        });
    }

    function clearChildSelects($select) {
        // Destroy Select2 first
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        // Clear options except placeholder
        $select.find('option:not(:first)').remove();
        $select.val('');

        // Reinitialize Select2
        $select.select2({
            placeholder: $select.data('placeholder') || '',
            allowClear: true,
            width: '100%',
            templateResult: formatOption,
            templateSelection: formatSelection,
            language: {
                noResults: function() {
                    return tbpChainSelector.i18n.no_results;
                },
                searching: function() {
                    return tbpChainSelector.i18n.searching;
                }
            }
        });

        // Find and clear the next child in chain
        var nextChildId = $select.data('child');
        if (nextChildId) {
            var $nextChild = $('#' + nextChildId);
            if ($nextChild.length) {
                clearChildSelects($nextChild);
            }
        }
    }

    function populateSelect($select, options, parentIcon) {
        // Destroy Select2 first
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        // Clear existing options except first
        $select.find('option:not(:first)').remove();

        // Add new options
        $.each(options, function(index, option) {
            var $option = $('<option></option>')
                .attr('value', option.id)
                .text(option.text);

            // Add icon data attribute
            if (option.icon) {
                $option.attr('data-icon', option.icon);
            } else if (parentIcon) {
                $option.attr('data-icon', parentIcon);
            }

            $select.append($option);
        });

        // Reinitialize Select2
        $select.select2({
            placeholder: $select.data('placeholder') || '',
            allowClear: true,
            width: '100%',
            templateResult: formatOption,
            templateSelection: formatSelection,
            language: {
                noResults: function() {
                    return tbpChainSelector.i18n.no_results;
                },
                searching: function() {
                    return tbpChainSelector.i18n.searching;
                }
            }
        });
    }

})(jQuery);
