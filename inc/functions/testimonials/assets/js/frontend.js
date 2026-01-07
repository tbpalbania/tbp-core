/**
 * Testimonials Frontend JS
 */
(function($) {
    'use strict';

    var TBPTestimonials = {

        init: function() {
            this.form = $('.tbp-testimonial-form__form');
            if (!this.form.length) return;

            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            this.form.on('submit', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });

            // Character counter
            this.form.find('.tbp-testimonial-form__textarea').on('input', function() {
                self.updateCharCount($(this));
            });
        },

        updateCharCount: function($textarea) {
            var $counter = $textarea.siblings('.tbp-testimonial-form__char-count');
            if ($counter.length) {
                var current = $textarea.val().length;
                var max = tbpTestimonialsForm.settings.max_length;
                $counter.text(current + ' / ' + max);

                if (current > max) {
                    $counter.addClass('tbp-testimonial-form__char-count--over');
                } else {
                    $counter.removeClass('tbp-testimonial-form__char-count--over');
                }
            }
        },

        validateForm: function($form) {
            var self = this;
            var errors = [];

            // Check required fields
            var name = $form.find('[name="author_name"]').val().trim();
            var email = $form.find('[name="author_email"]').val().trim();
            var content = $form.find('[name="content"]').val().trim();

            if (!name) {
                errors.push(tbpTestimonialsForm.i18n.required);
            }

            if (!email || !self.isValidEmail(email)) {
                errors.push(tbpTestimonialsForm.i18n.invalid_email);
            }

            if (!content) {
                errors.push(tbpTestimonialsForm.i18n.required);
            } else {
                if (content.length < tbpTestimonialsForm.settings.min_length) {
                    errors.push(tbpTestimonialsForm.i18n.too_short);
                }
                if (content.length > tbpTestimonialsForm.settings.max_length) {
                    errors.push(tbpTestimonialsForm.i18n.too_long);
                }
            }

            // Check if guests allowed
            if (!tbpTestimonialsForm.settings.allow_guests && !$form.data('user-logged-in')) {
                errors.push(tbpTestimonialsForm.i18n.guests_disabled);
            }

            return errors;
        },

        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        submitForm: function($form) {
            var self = this;

            // Validate
            var errors = this.validateForm($form);
            if (errors.length > 0) {
                this.showMessage($form, 'error', errors[0]);
                return;
            }

            // Check honeypot
            if ($form.find('[name="website_url"]').val()) {
                return;
            }

            var $submitBtn = $form.find('.tbp-testimonial-form__submit');
            var originalText = $submitBtn.text();

            $submitBtn.prop('disabled', true).text(tbpTestimonialsForm.i18n.submitting);

            $.ajax({
                url: tbpTestimonialsForm.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=tbp_submit_testimonial&nonce=' + tbpTestimonialsForm.nonce,
                success: function(response) {
                    if (response.success) {
                        self.showMessage($form, 'success', response.data.message);
                        $form[0].reset();

                        // Reset star rating to 5
                        $form.find('[name="rating"][value="5"]').prop('checked', true);
                    } else {
                        self.showMessage($form, 'error', response.data.message || tbpTestimonialsForm.i18n.error);
                    }
                },
                error: function() {
                    self.showMessage($form, 'error', tbpTestimonialsForm.i18n.error);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        showMessage: function($form, type, message) {
            $form.find('.tbp-testimonial-form__message').remove();

            var $message = $('<div class="tbp-testimonial-form__message tbp-testimonial-form__message--' + type + '">' + message + '</div>');
            $form.prepend($message);

            $('html, body').animate({
                scrollTop: $message.offset().top - 100
            }, 300);

            if (type === 'error') {
                setTimeout(function() {
                    $message.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        }
    };

    $(document).ready(function() {
        TBPTestimonials.init();
    });

})(jQuery);
