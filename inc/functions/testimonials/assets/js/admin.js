/**
 * Testimonials Admin JS
 */
(function($) {
    'use strict';

    var TBPTestimonialsAdmin = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Settings modal
            $('#tbp-testimonials-settings-btn').on('click', function() {
                self.openSettingsModal();
            });

            $('#tbp-settings-cancel').on('click', function() {
                self.closeModal('#tbp-testimonials-settings-modal');
            });

            $('#tbp-settings-save').on('click', function() {
                self.saveSettings();
            });

            // Add testimonial modal
            $('#tbp-add-testimonial-btn').on('click', function() {
                self.openTestimonialModal();
            });

            $(document).on('click', '.tbp-edit-testimonial', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                self.openTestimonialModal(id);
            });

            $('#tbp-testimonial-cancel').on('click', function() {
                self.closeModal('#tbp-testimonial-modal');
            });

            $('#tbp-testimonial-save').on('click', function() {
                self.saveTestimonial();
            });

            // Reply modal
            $(document).on('click', '.tbp-reply-testimonial', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                self.openReplyModal(id);
            });

            $('#tbp-reply-cancel').on('click', function() {
                self.closeModal('#tbp-reply-modal');
            });

            $('#tbp-reply-save').on('click', function() {
                self.saveReply();
            });

            // Close modals on overlay/close button click
            $('.tbp-modal__close, .tbp-modal__overlay').on('click', function() {
                $(this).closest('.tbp-modal').fadeOut(200);
            });

            // Bulk actions
            $('#doaction').on('click', function() {
                self.handleBulkAction();
            });

            // Select all
            $('#cb-select-all').on('change', function() {
                $('input[name="testimonial_ids[]"]').prop('checked', $(this).prop('checked'));
            });

            // ESC key to close modal
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.tbp-modal:visible').fadeOut(200);
                }
            });
        },

        closeModal: function(selector) {
            $(selector).fadeOut(200);
        },

        openSettingsModal: function() {
            var self = this;
            var $modal = $('#tbp-testimonials-settings-modal');

            // Load current settings
            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tbp_testimonials_get_settings',
                    nonce: tbpTestimonials.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.populateSettings(response.data);
                        $modal.fadeIn(200);
                    }
                }
            });
        },

        closeSettingsModal: function() {
            $('#tbp-testimonials-settings-modal').fadeOut(200);
        },

        // Testimonial modal
        openTestimonialModal: function(id) {
            var self = this;
            var $modal = $('#tbp-testimonial-modal');
            var $form = $('#tbp-testimonial-form');
            var $title = $('#tbp-testimonial-modal-title');

            // Reset form
            $form[0].reset();
            $('#tbp-testimonial-id').val(0);
            $('#tbp-testimonial-message').hide();

            if (id) {
                // Edit mode - load testimonial data
                $title.text(tbpTestimonials.i18n.edit_testimonial || 'Edit Testimonial');
                $.ajax({
                    url: tbpTestimonials.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tbp_testimonials_get',
                        nonce: tbpTestimonials.nonce,
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            self.populateTestimonialForm(response.data);
                            $modal.fadeIn(200);
                        } else {
                            self.showNotice('error', response.data.message || tbpTestimonials.i18n.error);
                        }
                    }
                });
            } else {
                // Add mode
                $title.text(tbpTestimonials.i18n.add_testimonial || 'Add Testimonial');
                $modal.fadeIn(200);
            }
        },

        populateTestimonialForm: function(data) {
            $('#tbp-testimonial-id').val(data.id);
            $('#tbp-author-name').val(data.author_name);
            $('#tbp-author-email').val(data.author_email);
            $('#tbp-author-company').val(data.author_company);
            $('#tbp-author-position').val(data.author_position);
            $('#tbp-testimonial-rating').val(data.rating);
            $('#tbp-testimonial-status').val(data.status);
            $('#tbp-testimonial-content').val(data.content);
            $('#tbp-testimonial-featured').prop('checked', data.featured == 1);
            $('#tbp-testimonial-product').val(data.product_id || '');
        },

        saveTestimonial: function() {
            var self = this;
            var $form = $('#tbp-testimonial-form');
            var $saveBtn = $('#tbp-testimonial-save');
            var $message = $('#tbp-testimonial-message');
            var originalText = $saveBtn.text();

            // Validate required fields
            if (!$('#tbp-author-name').val() || !$('#tbp-author-email').val() || !$('#tbp-testimonial-content').val()) {
                $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                    .text(tbpTestimonials.i18n.required || 'Please fill in all required fields.').show();
                return;
            }

            $saveBtn.prop('disabled', true).text(tbpTestimonials.i18n.saving);
            $message.hide();

            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tbp_testimonials_save',
                    nonce: tbpTestimonials.nonce,
                    id: $('#tbp-testimonial-id').val(),
                    author_name: $('#tbp-author-name').val(),
                    author_email: $('#tbp-author-email').val(),
                    author_company: $('#tbp-author-company').val(),
                    author_position: $('#tbp-author-position').val(),
                    rating: $('#tbp-testimonial-rating').val(),
                    status: $('#tbp-testimonial-status').val(),
                    content: $('#tbp-testimonial-content').val(),
                    featured: $('#tbp-testimonial-featured').prop('checked') ? 1 : 0,
                    product_id: $('#tbp-testimonial-product').val()
                },
                success: function(response) {
                    if (response.success) {
                        self.closeModal('#tbp-testimonial-modal');
                        self.showNotice('success', response.data.message);
                        setTimeout(function() { location.reload(); }, 1000);
                    } else {
                        $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                            .text(response.data.message || tbpTestimonials.i18n.error).show();
                    }
                },
                error: function() {
                    $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                        .text(tbpTestimonials.i18n.error).show();
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        // Reply modal
        openReplyModal: function(id) {
            var self = this;
            var $modal = $('#tbp-reply-modal');

            $('#tbp-reply-testimonial-id').val(id);
            $('#tbp-reply-content').val('');
            $('#tbp-reply-message').hide();

            // Load testimonial and existing replies
            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tbp_testimonials_get_with_replies',
                    nonce: tbpTestimonials.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Display original testimonial
                        var t = response.data.testimonial;
                        var stars = '';
                        for (var i = 1; i <= 5; i++) {
                            stars += '<span class="tbp-star tbp-star--' + (i <= t.rating ? 'filled' : 'empty') + '">&#9733;</span>';
                        }
                        $('#tbp-reply-original').html(
                            '<div class="tbp-reply-original__content">' +
                            '<div class="tbp-reply-original__header">' +
                            '<strong>' + self.escapeHtml(t.author_name) + '</strong>' +
                            '<span class="tbp-stars">' + stars + '</span>' +
                            '</div>' +
                            '<p>' + self.escapeHtml(t.content) + '</p>' +
                            '</div>'
                        );

                        // Display existing replies
                        var repliesHtml = '';
                        if (response.data.replies && response.data.replies.length > 0) {
                            repliesHtml = '<h4>' + (tbpTestimonials.i18n.existing_replies || 'Existing Replies') + '</h4>';
                            response.data.replies.forEach(function(reply) {
                                repliesHtml += '<div class="tbp-existing-reply">' +
                                    '<div class="tbp-existing-reply__header">' +
                                    '<strong>' + self.escapeHtml(reply.author_name) + '</strong>' +
                                    '<span class="tbp-existing-reply__date">' + reply.date + '</span>' +
                                    '<a href="#" class="tbp-delete-reply" data-id="' + reply.id + '">&times;</a>' +
                                    '</div>' +
                                    '<p>' + self.escapeHtml(reply.content) + '</p>' +
                                    '</div>';
                            });
                        }
                        $('#tbp-existing-replies').html(repliesHtml);

                        $modal.fadeIn(200);
                    }
                }
            });
        },

        saveReply: function() {
            var self = this;
            var $saveBtn = $('#tbp-reply-save');
            var $message = $('#tbp-reply-message');
            var content = $('#tbp-reply-content').val();
            var testimonialId = $('#tbp-reply-testimonial-id').val();
            var originalText = $saveBtn.text();

            if (!content.trim()) {
                $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                    .text(tbpTestimonials.i18n.reply_required || 'Please enter a reply.').show();
                return;
            }

            $saveBtn.prop('disabled', true).text(tbpTestimonials.i18n.saving);
            $message.hide();

            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tbp_testimonials_add_reply',
                    nonce: tbpTestimonials.nonce,
                    testimonial_id: testimonialId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        $message.removeClass('tbp-form-message--error').addClass('tbp-form-message--success')
                            .text(response.data.message).show();
                        $('#tbp-reply-content').val('');
                        // Reload replies
                        self.openReplyModal(testimonialId);
                    } else {
                        $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                            .text(response.data.message || tbpTestimonials.i18n.error).show();
                    }
                },
                error: function() {
                    $message.removeClass('tbp-form-message--success').addClass('tbp-form-message--error')
                        .text(tbpTestimonials.i18n.error).show();
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        },

        populateSettings: function(settings) {
            var $form = $('#tbp-testimonials-settings-form');

            // Checkboxes
            $form.find('[name="allow_guests"]').prop('checked', settings.allow_guests);
            $form.find('[name="require_approval"]').prop('checked', settings.require_approval);
            $form.find('[name="allow_rating"]').prop('checked', settings.allow_rating);
            $form.find('[name="notify_admin"]').prop('checked', settings.notify_admin);
            $form.find('[name="require_email_verification"]').prop('checked', settings.require_email_verification);
            $form.find('[name="show_rating"]').prop('checked', settings.show_rating);
            $form.find('[name="show_date"]').prop('checked', settings.show_date);
            $form.find('[name="show_company"]').prop('checked', settings.show_company);

            // Text/number fields
            $form.find('[name="min_rating"]').val(settings.min_rating);
            $form.find('[name="min_length"]').val(settings.min_length);
            $form.find('[name="max_length"]').val(settings.max_length);
            $form.find('[name="notification_email"]').val(settings.notification_email);
            $form.find('[name="rate_limit"]').val(settings.rate_limit);
            $form.find('[name="rate_period"]').val(settings.rate_period);
        },

        saveSettings: function() {
            var self = this;
            var $form = $('#tbp-testimonials-settings-form');
            var $saveBtn = $('#tbp-settings-save');
            var originalText = $saveBtn.text();

            $saveBtn.prop('disabled', true).text(tbpTestimonials.i18n.saving);

            var data = {
                action: 'tbp_testimonials_save_settings',
                nonce: tbpTestimonials.nonce,
                allow_guests: $form.find('[name="allow_guests"]').prop('checked'),
                require_approval: $form.find('[name="require_approval"]').prop('checked'),
                allow_rating: $form.find('[name="allow_rating"]').prop('checked'),
                min_rating: $form.find('[name="min_rating"]').val(),
                min_length: $form.find('[name="min_length"]').val(),
                max_length: $form.find('[name="max_length"]').val(),
                notify_admin: $form.find('[name="notify_admin"]').prop('checked'),
                notification_email: $form.find('[name="notification_email"]').val(),
                rate_limit: $form.find('[name="rate_limit"]').val(),
                rate_period: $form.find('[name="rate_period"]').val(),
                require_email_verification: $form.find('[name="require_email_verification"]').prop('checked'),
                show_rating: $form.find('[name="show_rating"]').prop('checked'),
                show_date: $form.find('[name="show_date"]').prop('checked'),
                show_company: $form.find('[name="show_company"]').prop('checked')
            };

            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        self.closeSettingsModal();
                        self.showNotice('success', tbpTestimonials.i18n.saved);
                    } else {
                        self.showNotice('error', response.data.message || tbpTestimonials.i18n.error);
                    }
                },
                error: function() {
                    self.showNotice('error', tbpTestimonials.i18n.error);
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        handleBulkAction: function() {
            var self = this;
            var action = $('#bulk-action-selector-top').val();
            var ids = [];

            $('input[name="testimonial_ids[]"]:checked').each(function() {
                ids.push($(this).val());
            });

            if (!action) {
                return;
            }

            if (ids.length === 0) {
                alert(tbpTestimonials.i18n.select_items);
                return;
            }

            if (!confirm(tbpTestimonials.i18n.confirm_bulk)) {
                return;
            }

            $.ajax({
                url: tbpTestimonials.ajaxurl,
                type: 'POST',
                data: {
                    action: 'tbp_testimonials_bulk_action',
                    nonce: tbpTestimonials.nonce,
                    bulk_action: action,
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        self.showNotice('error', response.data.message || tbpTestimonials.i18n.error);
                    }
                },
                error: function() {
                    self.showNotice('error', tbpTestimonials.i18n.error);
                }
            });
        },

        showNotice: function(type, message) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    $(document).ready(function() {
        TBPTestimonialsAdmin.init();
    });

})(jQuery);
