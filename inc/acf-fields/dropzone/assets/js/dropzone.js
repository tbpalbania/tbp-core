/**
 * TBP ACF Dropzone Field
 */
(function($) {
    'use strict';

    if (typeof acf === 'undefined') {
        return;
    }

    var Field = acf.Field.extend({
        type: 'tbp_dropzone',

        events: {
            'click .tbp-dropzone-browse': 'onClickBrowse',
            'click .tbp-dropzone-file-remove': 'onClickRemove',
            'input .tbp-dropzone-file-title': 'onTitleChange',
        },

        $wrapper: function() {
            return this.$('.tbp-dropzone-wrapper');
        },

        $area: function() {
            return this.$('.tbp-dropzone-area');
        },

        $files: function() {
            return this.$('.tbp-dropzone-files');
        },

        $input: function() {
            return this.$('.tbp-dropzone-input');
        },

        initialize: function() {
            this.initDragDrop();
            this.initSortable();
            // Don't call updateInput() here - PHP already pre-populates the hidden input
        },

        initDragDrop: function() {
            var self = this;
            var $area = this.$area();

            $area.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('is-dragover');
            });

            $area.on('dragleave dragend drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('is-dragover');
            });

            $area.on('drop', function(e) {
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    self.uploadFiles(files);
                }
            });
        },

        initSortable: function() {
            var self = this;

            this.$files().sortable({
                items: '.tbp-dropzone-file',
                placeholder: 'tbp-dropzone-file ui-sortable-placeholder',
                cursor: 'move',
                opacity: 0.7,
                update: function() {
                    self.updateInput();
                }
            });
        },

        onClickBrowse: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.openMediaFrame();
        },

        onClickRemove: function(e) {
            e.preventDefault();
            e.stopPropagation();

            $(e.currentTarget).closest('.tbp-dropzone-file').remove();
            this.updateInput();
        },

        onTitleChange: function(e) {
            this.updateInput();
        },

        openMediaFrame: function() {
            var self = this;
            var $wrapper = this.$wrapper();
            var maxFiles = parseInt($wrapper.data('max-files')) || 0;
            var library = $wrapper.data('library') || 'all';
            var allowedTypes = $wrapper.data('allowed-types') || '';

            var frameOptions = {
                title: acf.__('Select Files'),
                button: {
                    text: acf.__('Select')
                },
                multiple: maxFiles !== 1,
                library: {}
            };

            if (library === 'uploadedTo') {
                frameOptions.library.uploadedTo = acf.get('post_id');
            }

            if (allowedTypes) {
                var types = allowedTypes.split(',').map(function(t) {
                    return t.trim();
                });
                // Note: WP media library doesn't filter by extension directly
                // This is handled via MIME types in the actual upload
            }

            var frame = wp.media(frameOptions);

            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                self.addFiles(attachments);
            });

            frame.open();
        },

        uploadFiles: function(files) {
            var self = this;
            var $wrapper = this.$wrapper();
            var $area = this.$area();
            var maxSize = parseInt($wrapper.data('max-size')) || 0;
            var allowedTypes = $wrapper.data('allowed-types') || '';
            var allowedArray = allowedTypes ? allowedTypes.split(',').map(function(t) {
                return t.trim().toLowerCase();
            }) : [];

            Array.from(files).forEach(function(file) {
                // Check file size
                if (maxSize > 0 && file.size > maxSize) {
                    self.showError(tbpDropzone.strings.maxSizeError + ' (' + file.name + ')');
                    return;
                }

                // Check file type
                if (allowedArray.length > 0) {
                    var ext = file.name.split('.').pop().toLowerCase();
                    if (allowedArray.indexOf(ext) === -1) {
                        self.showError(tbpDropzone.strings.typeError + ' (' + file.name + ')');
                        return;
                    }
                }

                // Show uploading state
                $area.addClass('is-uploading');

                // Create FormData for upload
                var formData = new FormData();
                formData.append('action', 'tbp_dropzone_upload');
                formData.append('file', file);
                formData.append('nonce', tbpDropzone.upload_nonce);

                // Upload via AJAX
                $.ajax({
                    url: tbpDropzone.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $area.removeClass('is-uploading');

                        if (response.success && response.data) {
                            // Format attachment data
                            var attachment = {
                                id: response.data.id,
                                filename: response.data.filename,
                                url: response.data.url,
                                type: response.data.type,
                                filesizeHumanReadable: response.data.filesizeHumanReadable,
                                sizes: response.data.sizes || {}
                            };
                            self.addFiles([attachment]);
                        } else {
                            var errorMsg = response.data ? response.data.message : tbpDropzone.strings.uploadError;
                            self.showError(errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        $area.removeClass('is-uploading');
                        self.showError(tbpDropzone.strings.uploadError);
                    }
                });
            });
        },

        addFiles: function(attachments) {
            var self = this;
            var $wrapper = this.$wrapper();
            var $files = this.$files();
            var maxFiles = parseInt($wrapper.data('max-files')) || 0;
            var previewSize = $wrapper.data('preview-size') || 'medium';

            // Check max files
            var currentCount = $files.find('.tbp-dropzone-file').length;

            attachments.forEach(function(attachment) {
                // Skip if max files reached
                if (maxFiles > 0 && currentCount >= maxFiles) {
                    self.showError(tbpDropzone.strings.maxFilesError);
                    return;
                }

                // Skip if already added
                if ($files.find('.tbp-dropzone-file[data-id="' + attachment.id + '"]').length) {
                    return;
                }

                // Get thumbnail
                var thumbUrl = '';
                if (attachment.type === 'image') {
                    if (attachment.sizes && attachment.sizes[previewSize]) {
                        thumbUrl = attachment.sizes[previewSize].url;
                    } else if (attachment.sizes && attachment.sizes.thumbnail) {
                        thumbUrl = attachment.sizes.thumbnail.url;
                    } else {
                        thumbUrl = attachment.url;
                    }
                }

                // Format file size
                var fileSize = '';
                if (attachment.filesizeHumanReadable) {
                    fileSize = attachment.filesizeHumanReadable;
                } else if (attachment.filesize) {
                    fileSize = self.formatFileSize(attachment.filesize);
                }

                // Get file extension
                var fileExt = attachment.filename.split('.').pop().toUpperCase();
                var fileMeta = fileExt + (fileSize ? ' • ' + fileSize : '');

                // Create file element with editable title
                var $file = $('<div class="tbp-dropzone-file" data-id="' + attachment.id + '">' +
                    '<div class="tbp-dropzone-file-preview">' +
                    (thumbUrl ? '<img src="' + thumbUrl + '" alt="">' : '<span class="dashicons dashicons-media-default"></span>') +
                    '</div>' +
                    '<div class="tbp-dropzone-file-info">' +
                    '<input type="text" class="tbp-dropzone-file-title" value="" placeholder="' + attachment.filename + '" title="Click to edit title">' +
                    '<span class="tbp-dropzone-file-meta">' + fileMeta + '</span>' +
                    '</div>' +
                    '<button type="button" class="tbp-dropzone-file-remove" title="Remove">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>' +
                    '</div>');

                $files.append($file);
                currentCount++;
            });

            this.updateInput();
        },

        updateInput: function() {
            var filesData = [];

            this.$files().find('.tbp-dropzone-file').each(function() {
                var $file = $(this);
                filesData.push({
                    id: $file.data('id'),
                    title: $file.find('.tbp-dropzone-file-title').val() || ''
                });
            });

            // Update via ACF's val method for Gutenberg support
            this.val(JSON.stringify(filesData));
        },

        // Override ACF's val method to get/set our value
        val: function(value) {
            if (typeof value !== 'undefined') {
                this.$input().val(value).trigger('change');
                return value;
            }
            return this.$input().val();
        },

        showError: function(message) {
            var self = this;
            var $wrapper = this.$wrapper();

            // Remove existing error
            $wrapper.find('.tbp-dropzone-error').remove();

            // Add error message
            var $error = $('<div class="tbp-dropzone-error">' + message + '</div>');
            $wrapper.append($error);

            // Auto remove after 5 seconds
            setTimeout(function() {
                $error.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            var k = 1024;
            var sizes = ['B', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }
    });

    acf.registerFieldType(Field);

})(jQuery);
