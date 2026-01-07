/**
 * Staff Admin JavaScript
 */

(function($) {
    'use strict';

    const TBPStaffAdmin = {
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.updateTitlePreview();
        },

        bindEvents: function() {
            // Name field changes - update title preview
            $(document).on('input', '.tbp-staff-name-field', this.updateTitlePreview.bind(this));

            // Add repeater item
            $(document).on('click', '.tbp-staff-repeater__add', this.addRepeaterItem.bind(this));

            // Remove repeater item
            $(document).on('click', '.tbp-staff-repeater__remove', this.removeRepeaterItem.bind(this));

            // Toggle repeater item
            $(document).on('click', '.tbp-staff-repeater__toggle', this.toggleRepeaterItem.bind(this));

            // Update repeater title on input
            $(document).on('input', '.tbp-staff-repeater__title-field', this.updateRepeaterTitle.bind(this));

            // Current position checkbox - disable end date
            $(document).on('change', '.tbp-staff-current-checkbox', this.toggleEndDate.bind(this));
        },

        updateTitlePreview: function() {
            const firstName = $('#tbp_staff_first_name').val() || '';
            const lastName = $('#tbp_staff_last_name').val() || '';
            const fullName = (firstName + ' ' + lastName).trim() || 'Staff Member';

            $('#tbp_staff_title_preview').val(fullName);
        },

        initSortable: function() {
            $('.tbp-staff-repeater__items').sortable({
                handle: '.tbp-staff-repeater__handle',
                placeholder: 'tbp-staff-repeater__item ui-sortable-placeholder',
                forcePlaceholderSize: true,
                update: function() {
                    TBPStaffAdmin.reindexRepeater($(this));
                }
            });
        },

        addRepeaterItem: function(e) {
            e.preventDefault();

            const $button = $(e.currentTarget);
            const type = $button.data('type');
            const $container = $button.closest('.tbp-staff-repeater').find('.tbp-staff-repeater__items');
            const newIndex = $container.find('.tbp-staff-repeater__item').length;

            $button.prop('disabled', true);

            $.ajax({
                url: tbpStaffAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_staff_add_' + type,
                    nonce: tbpStaffAdmin.nonce,
                    index: newIndex
                },
                success: function(response) {
                    if (response.success) {
                        const $newItem = $(response.data.html);
                        $container.append($newItem);

                        // Scroll to new item
                        $('html, body').animate({
                            scrollTop: $newItem.offset().top - 100
                        }, 300);

                        // Focus first input
                        $newItem.find('input:first').focus();
                    }
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },

        removeRepeaterItem: function(e) {
            e.preventDefault();

            if (!confirm(tbpStaffAdmin.i18n.confirmDelete)) {
                return;
            }

            const $item = $(e.currentTarget).closest('.tbp-staff-repeater__item');
            const $container = $item.closest('.tbp-staff-repeater__items');

            $item.slideUp(200, function() {
                $item.remove();
                TBPStaffAdmin.reindexRepeater($container);
            });
        },

        toggleRepeaterItem: function(e) {
            e.preventDefault();

            const $item = $(e.currentTarget).closest('.tbp-staff-repeater__item');
            const $toggle = $(e.currentTarget);
            const isCollapsed = $item.hasClass('tbp-staff-repeater__item--collapsed');

            $item.toggleClass('tbp-staff-repeater__item--collapsed');
            $toggle.attr('aria-expanded', isCollapsed ? 'true' : 'false');
        },

        updateRepeaterTitle: function(e) {
            const $input = $(e.currentTarget);
            const $item = $input.closest('.tbp-staff-repeater__item');
            const $title = $item.find('.tbp-staff-repeater__title');
            const type = $input.closest('.tbp-staff-repeater').data('type');

            const defaultTitle = type === 'education'
                ? tbpStaffAdmin.i18n.newEducation
                : tbpStaffAdmin.i18n.newExperience;

            $title.text($input.val() || defaultTitle);
        },

        toggleEndDate: function(e) {
            const $checkbox = $(e.currentTarget);
            const $endDate = $checkbox.closest('.tbp-staff-repeater__item').find('.tbp-staff-end-date');

            if ($checkbox.is(':checked')) {
                $endDate.prop('disabled', true).val('');
            } else {
                $endDate.prop('disabled', false);
            }
        },

        reindexRepeater: function($container) {
            const type = $container.closest('.tbp-staff-repeater').data('type');

            $container.find('.tbp-staff-repeater__item').each(function(index) {
                const $item = $(this);
                $item.attr('data-index', index);

                // Update all input names
                $item.find('input, textarea, select').each(function() {
                    const $input = $(this);
                    const name = $input.attr('name');

                    if (name) {
                        const newName = name.replace(
                            /tbp_staff_(education|experience)\[\d+\]/,
                            'tbp_staff_' + type + '[' + index + ']'
                        );
                        $input.attr('name', newName);
                    }
                });
            });
        }
    };

    $(document).ready(function() {
        TBPStaffAdmin.init();
    });

})(jQuery);
