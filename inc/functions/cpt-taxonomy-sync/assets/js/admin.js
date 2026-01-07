/**
 * TBP CPT Taxonomy Sync Admin JS
 */
(function($) {
    'use strict';

    const TBP_CTS_Admin = {

        init: function() {
            this.bindEvents();
            this.initSelects();
        },

        bindEvents: function() {
            // Type dropdown change
            $(document).on('change', '.tbp-cts-type-dropdown', this.handleTypeChange.bind(this));

            // Form submit (new rule)
            $('#tbp-cts-new-rule-form').on('submit', this.handleFormSubmit.bind(this));

            // Edit form submit
            $('#tbp-cts-edit-rule-form').on('submit', this.handleEditSubmit.bind(this));

            // Delete rule
            $(document).on('click', '.tbp-cts-delete-rule', this.handleDeleteRule.bind(this));

            // Toggle status
            $(document).on('change', '.tbp-cts-toggle-status', this.handleToggleStatus.bind(this));

            // Edit rule
            $(document).on('click', '.tbp-cts-edit-rule', this.handleEditRule.bind(this));

            // Sync now
            $(document).on('click', '.tbp-cts-sync-now', this.handleSyncNow.bind(this));

            // Modal close
            $(document).on('click', '.tbp-cts-modal-close, .tbp-cts-modal-cancel', this.closeModal.bind(this));
            $(document).on('click', '.tbp-cts-modal', function(e) {
                if ($(e.target).hasClass('tbp-cts-modal')) {
                    TBP_CTS_Admin.closeModal();
                }
            });

            // ESC key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    TBP_CTS_Admin.closeModal();
                }
            });
        },

        initSelects: function() {
            // Initialize any pre-selected values
            $('.tbp-cts-type-dropdown').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });
        },

        handleTypeChange: function(e) {
            const $typeSelect = $(e.currentTarget);
            const $nameSelect = $typeSelect.siblings('.tbp-cts-name-dropdown');
            const selectedType = $typeSelect.val();

            if (!selectedType) {
                $nameSelect.prop('disabled', true).html('<option value="">Select...</option>');
                return;
            }

            // Get items based on type
            const items = selectedType === 'post_type' ? tbpCTSData.post_types : tbpCTSData.taxonomies;

            let options = '<option value="">Select...</option>';
            for (const [key, label] of Object.entries(items)) {
                options += '<option value="' + key + '">' + label + ' (' + key + ')</option>';
            }

            $nameSelect.html(options).prop('disabled', false);
        },

        populateNameDropdown: function($typeSelect, selectedValue) {
            const $nameSelect = $typeSelect.siblings('.tbp-cts-name-dropdown');
            const selectedType = $typeSelect.val();

            if (!selectedType) return;

            const items = selectedType === 'post_type' ? tbpCTSData.post_types : tbpCTSData.taxonomies;

            let options = '<option value="">Select...</option>';
            for (const [key, label] of Object.entries(items)) {
                const selected = key === selectedValue ? ' selected' : '';
                options += '<option value="' + key + '"' + selected + '>' + label + ' (' + key + ')</option>';
            }

            $nameSelect.html(options).prop('disabled', false);
        },

        handleFormSubmit: function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Validate
            const sourceType = $('#source_type').val();
            const sourceName = $('#source_name').val();
            const destType = $('#dest_type').val();
            const destName = $('#dest_name').val();

            if (!sourceType || !sourceName || !destType || !destName) {
                this.showMessage('error', 'Please select both source and destination.');
                return;
            }

            // Prevent same source and destination
            if (sourceType === destType && sourceName === destName) {
                this.showMessage('error', 'Source and destination cannot be the same.');
                return;
            }

            $submitBtn.text(tbpCTS.strings.saving).prop('disabled', true);
            $form.addClass('tbp-cts-loading');

            $.ajax({
                url: tbpCTS.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_cts_save_rule',
                    nonce: tbpCTS.nonce,
                    source_type: sourceType,
                    source_name: sourceName,
                    dest_type: destType,
                    dest_name: destName,
                    sync_on_create: $form.find('[name="sync_on_create"]').is(':checked') ? 1 : 0,
                    sync_on_update: $form.find('[name="sync_on_update"]').is(':checked') ? 1 : 0,
                    sync_on_delete: $form.find('[name="sync_on_delete"]').is(':checked') ? 1 : 0,
                    bidirectional: $form.find('[name="bidirectional"]').is(':checked') ? 1 : 0,
                },
                success: function(response) {
                    $form.removeClass('tbp-cts-loading');
                    $submitBtn.text(originalText).prop('disabled', false);

                    if (response.success) {
                        TBP_CTS_Admin.showMessage('success', response.data.message);

                        // Add new row to table or reload
                        const $tbody = $('#tbp-cts-rules-tbody');
                        if ($tbody.length) {
                            $tbody.append(response.data.row_html);
                        } else {
                            location.reload();
                        }

                        // Reset form
                        $form[0].reset();
                        $('.tbp-cts-name-dropdown').prop('disabled', true).html('<option value="">Select...</option>');
                    } else {
                        TBP_CTS_Admin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    $form.removeClass('tbp-cts-loading');
                    $submitBtn.text(originalText).prop('disabled', false);
                    TBP_CTS_Admin.showMessage('error', tbpCTS.strings.error);
                }
            });
        },

        handleEditRule: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            const ruleData = $row.data('rule');

            if (!ruleData) return;

            // Populate edit form
            $('#edit_rule_id').val(ruleData.id);
            $('#edit_source_type').val(ruleData.source_type);
            this.populateNameDropdown($('#edit_source_type'), ruleData.source_name);

            $('#edit_dest_type').val(ruleData.dest_type);
            this.populateNameDropdown($('#edit_dest_type'), ruleData.dest_name);

            $('#edit_sync_on_create').prop('checked', ruleData.sync_on_create);
            $('#edit_sync_on_update').prop('checked', ruleData.sync_on_update);
            $('#edit_sync_on_delete').prop('checked', ruleData.sync_on_delete);
            $('#edit_bidirectional').prop('checked', ruleData.bidirectional);

            // Open modal
            $('#tbp-cts-edit-modal').addClass('is-open');
        },

        handleEditSubmit: function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            const ruleId = $('#edit_rule_id').val();

            $submitBtn.text(tbpCTS.strings.saving).prop('disabled', true);
            $form.addClass('tbp-cts-loading');

            $.ajax({
                url: tbpCTS.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_cts_update_rule',
                    nonce: tbpCTS.nonce,
                    rule_id: ruleId,
                    source_type: $('#edit_source_type').val(),
                    source_name: $('#edit_source_name').val(),
                    dest_type: $('#edit_dest_type').val(),
                    dest_name: $('#edit_dest_name').val(),
                    sync_on_create: $('#edit_sync_on_create').is(':checked') ? 1 : 0,
                    sync_on_update: $('#edit_sync_on_update').is(':checked') ? 1 : 0,
                    sync_on_delete: $('#edit_sync_on_delete').is(':checked') ? 1 : 0,
                    bidirectional: $('#edit_bidirectional').is(':checked') ? 1 : 0,
                },
                success: function(response) {
                    $form.removeClass('tbp-cts-loading');
                    $submitBtn.text(originalText).prop('disabled', false);

                    if (response.success) {
                        TBP_CTS_Admin.showMessage('success', response.data.message);
                        TBP_CTS_Admin.closeModal();

                        // Replace row
                        const $oldRow = $('tr[data-rule-id="' + ruleId + '"]');
                        $oldRow.replaceWith(response.data.row_html);
                    } else {
                        TBP_CTS_Admin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    $form.removeClass('tbp-cts-loading');
                    $submitBtn.text(originalText).prop('disabled', false);
                    TBP_CTS_Admin.showMessage('error', tbpCTS.strings.error);
                }
            });
        },

        handleSyncNow: function(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            const ruleId = $row.data('rule-id');

            if ($btn.hasClass('is-syncing')) return;

            $btn.addClass('is-syncing');
            $row.addClass('tbp-cts-loading');

            $.ajax({
                url: tbpCTS.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_cts_manual_sync',
                    nonce: tbpCTS.nonce,
                    rule_id: ruleId,
                },
                success: function(response) {
                    $btn.removeClass('is-syncing');
                    $row.removeClass('tbp-cts-loading');

                    if (response.success) {
                        TBP_CTS_Admin.showMessage('success', response.data.message);

                        // Replace row with updated HTML
                        $row.replaceWith(response.data.row_html);
                    } else {
                        TBP_CTS_Admin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    $btn.removeClass('is-syncing');
                    $row.removeClass('tbp-cts-loading');
                    TBP_CTS_Admin.showMessage('error', tbpCTS.strings.error);
                }
            });
        },

        handleDeleteRule: function(e) {
            e.preventDefault();

            if (!confirm(tbpCTS.strings.confirmDelete)) {
                return;
            }

            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            const ruleId = $row.data('rule-id');

            $row.addClass('tbp-cts-loading');

            $.ajax({
                url: tbpCTS.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_cts_delete_rule',
                    nonce: tbpCTS.nonce,
                    rule_id: ruleId,
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();

                            if ($('#tbp-cts-rules-tbody tr').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        $row.removeClass('tbp-cts-loading');
                        TBP_CTS_Admin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    $row.removeClass('tbp-cts-loading');
                    TBP_CTS_Admin.showMessage('error', tbpCTS.strings.error);
                }
            });
        },

        handleToggleStatus: function(e) {
            const $toggle = $(e.currentTarget);
            const $row = $toggle.closest('tr');
            const ruleId = $row.data('rule-id');
            const isActive = $toggle.is(':checked');

            $row.addClass('tbp-cts-loading');

            $.ajax({
                url: tbpCTS.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbp_cts_toggle_rule',
                    nonce: tbpCTS.nonce,
                    rule_id: ruleId,
                    active: isActive ? 1 : 0,
                },
                success: function(response) {
                    $row.removeClass('tbp-cts-loading');

                    if (!response.success) {
                        $toggle.prop('checked', !isActive);
                        TBP_CTS_Admin.showMessage('error', response.data.message);
                    }
                },
                error: function() {
                    $row.removeClass('tbp-cts-loading');
                    $toggle.prop('checked', !isActive);
                    TBP_CTS_Admin.showMessage('error', tbpCTS.strings.error);
                }
            });
        },

        closeModal: function() {
            $('.tbp-cts-modal').removeClass('is-open');
        },

        showMessage: function(type, message) {
            const $wrap = $('.tbp-cts-wrap');
            const $existingMsg = $wrap.find('.tbp-cts-message');

            if ($existingMsg.length) {
                $existingMsg.remove();
            }

            const $msg = $('<div class="tbp-cts-message tbp-cts-message-' + type + '">' + message + '</div>');
            $wrap.find('h1').after($msg);

            setTimeout(function() {
                $msg.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    $(document).ready(function() {
        TBP_CTS_Admin.init();
    });

})(jQuery);
