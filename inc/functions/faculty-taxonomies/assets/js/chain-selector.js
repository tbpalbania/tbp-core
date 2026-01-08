(function($) {
    'use strict';

    $(document).ready(function() {
        initChainSelectors();
    });

    function initChainSelectors() {
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
                        populateSelect($child, response.data);
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
        // Get the default option text from the first option
        var defaultText = $select.find('option:first').text();

        // Clear and reset to default
        $select.html('<option value="">' + defaultText + '</option>');

        // Find and clear the next child in chain
        var nextChildId = $select.data('child');
        if (nextChildId) {
            var $nextChild = $('#' + nextChildId);
            if ($nextChild.length) {
                clearChildSelects($nextChild);
            }
        }
    }

    function populateSelect($select, options) {
        var defaultText = $select.find('option:first').text();
        var html = '<option value="">' + defaultText + '</option>';

        $.each(options, function(index, option) {
            html += '<option value="' + option.id + '">' + option.name + '</option>';
        });

        $select.html(html);
    }

})(jQuery);
