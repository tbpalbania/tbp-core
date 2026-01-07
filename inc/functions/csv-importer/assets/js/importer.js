/**
 * TBP CSV/XLSX Importer Wizard
 */
(function($) {
    'use strict';

    const Importer = {
        currentStep: 1,
        totalSteps: 5,
        uploadId: null,
        fileName: null,
        fileHeaders: [],
        sampleData: [],
        totalRows: 0,
        mapping: {},
        importResults: {
            created: 0,
            updated: 0,
            skipped: 0,
            errors: 0
        },

        init: function() {
            this.bindEvents();
            this.setupDragDrop();
        },

        bindEvents: function() {
            const self = this;

            // File input change
            $('#tbp-import-file').on('change', function() {
                self.handleFileSelect(this.files[0]);
            });

            // Import type change
            $('input[name="import_type"]').on('change', function() {
                self.toggleDestination();
            });

            // Navigation buttons
            $('.tbp-next-btn').on('click', function() {
                self.nextStep();
            });

            $('.tbp-prev-btn').on('click', function() {
                self.prevStep();
            });

            $('.tbp-import-btn').on('click', function() {
                self.startImport();
            });

            $('.tbp-reset-btn').on('click', function() {
                self.reset();
            });

            // Preview button
            $('#tbp-preview-btn').on('click', function() {
                self.preview();
            });
        },

        setupDragDrop: function() {
            const self = this;
            const $dropArea = $('.tbp-upload-area');

            $dropArea.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            $dropArea.on('dragleave dragend drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            $dropArea.on('drop', function(e) {
                const files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    self.handleFileSelect(files[0]);
                }
            });
        },

        handleFileSelect: function(file) {
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            if (!['csv', 'xlsx'].includes(ext)) {
                alert(tbpImporter.strings.uploadError + ': Invalid file type');
                return;
            }

            // Save filename
            this.fileName = file.name;

            // Show file info
            $('.tbp-file-info').html(
                '<strong>' + file.name + '</strong> (' + this.formatBytes(file.size) + ')'
            ).show();

            // Upload and parse
            this.uploadFile(file);
        },

        uploadFile: function(file) {
            const self = this;
            const formData = new FormData();
            formData.append('action', 'tbp_importer_upload');
            formData.append('nonce', tbpImporter.nonce);
            formData.append('file', file);

            $('.tbp-file-info').append(' <span class="spinner is-active"></span>');

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.tbp-file-info .spinner').remove();

                    if (response.success) {
                        self.uploadId = response.data.upload_id;
                        self.fileHeaders = response.data.headers;
                        self.sampleData = response.data.sample;
                        self.totalRows = response.data.total_rows;

                        $('.tbp-file-info').append(
                            ' <span class="text-success">(' + response.data.total_rows + ' rows)</span>'
                        );
                    } else {
                        $('.tbp-file-info').html(
                            '<span class="text-error">' + response.data.message + '</span>'
                        );
                    }
                },
                error: function() {
                    $('.tbp-file-info .spinner').remove();
                    $('.tbp-file-info').html(
                        '<span class="text-error">' + tbpImporter.strings.uploadError + '</span>'
                    );
                }
            });
        },

        toggleDestination: function() {
            const type = $('input[name="import_type"]:checked').val();
            if (type === 'posts') {
                $('.tbp-destination-posts').show();
                $('.tbp-destination-taxonomy').hide();
            } else {
                $('.tbp-destination-posts').hide();
                $('.tbp-destination-taxonomy').show();
            }
        },

        nextStep: function() {
            // Validate current step
            if (!this.validateStep(this.currentStep)) {
                return;
            }

            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updateUI();
                this.updateSidebar();

                // Load step data if needed
                if (this.currentStep === 3) {
                    this.loadMappingFields();
                }
            }
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateUI();
            }
        },

        validateStep: function(step) {
            switch (step) {
                case 1:
                    if (!this.uploadId) {
                        alert(tbpImporter.strings.selectFile);
                        return false;
                    }
                    break;
                case 3:
                    // Check if title/name is mapped
                    const type = $('input[name="import_type"]:checked').val();
                    const requiredField = type === 'posts' ? 'post_title' : 'name';
                    let hasTitleMapping = false;

                    $('#tbp-mapping-body select').each(function() {
                        if ($(this).val() === requiredField) {
                            hasTitleMapping = true;
                            return false;
                        }
                    });

                    if (!hasTitleMapping) {
                        alert(tbpImporter.strings.mapRequired);
                        return false;
                    }

                    // Store mapping
                    this.storeMapping();
                    break;
            }
            return true;
        },

        updateUI: function() {
            // Update step indicators
            $('.tbp-step').removeClass('active completed');
            for (let i = 1; i <= this.totalSteps; i++) {
                if (i < this.currentStep) {
                    $(`.tbp-step[data-step="${i}"]`).addClass('completed');
                } else if (i === this.currentStep) {
                    $(`.tbp-step[data-step="${i}"]`).addClass('active');
                }
            }

            // Show/hide content
            $('.tbp-wizard-content').hide();
            $(`.tbp-wizard-content[data-step="${this.currentStep}"]`).show();

            // Update buttons
            if (this.currentStep === 1) {
                $('.tbp-prev-btn').hide();
            } else {
                $('.tbp-prev-btn').show();
            }

            if (this.currentStep === this.totalSteps - 1) {
                $('.tbp-next-btn').hide();
                $('.tbp-import-btn').show();
            } else if (this.currentStep === this.totalSteps) {
                $('.tbp-next-btn').hide();
                $('.tbp-prev-btn').hide();
                $('.tbp-import-btn').hide();
            } else {
                $('.tbp-next-btn').show();
                $('.tbp-import-btn').hide();
            }
        },

        loadMappingFields: function() {
            const self = this;
            const type = $('input[name="import_type"]:checked').val();
            const destination = type === 'posts' ? $('#tbp-post-type').val() : $('#tbp-taxonomy').val();

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_importer_get_fields',
                    nonce: tbpImporter.nonce,
                    type: type,
                    destination: destination
                },
                success: function(response) {
                    if (response.success) {
                        self.renderMappingTable(response.data.fields);
                    }
                }
            });
        },

        renderMappingTable: function(fields) {
            const self = this;
            const $tbody = $('#tbp-mapping-body');
            $tbody.empty();

            // Group fields
            const grouped = {};
            fields.forEach(function(field) {
                const group = field.group || 'Other';
                if (!grouped[group]) {
                    grouped[group] = [];
                }
                grouped[group].push(field);
            });

            // Build options HTML
            let optionsHtml = '';
            Object.keys(grouped).forEach(function(group) {
                if (group !== 'Other') {
                    optionsHtml += `<optgroup label="${group}">`;
                }
                grouped[group].forEach(function(field) {
                    optionsHtml += `<option value="${field.value}">${field.label}</option>`;
                });
                if (group !== 'Other') {
                    optionsHtml += '</optgroup>';
                }
            });

            // Create rows for each CSV column
            self.fileHeaders.forEach(function(header, index) {
                const sample = self.sampleData[0] ? (self.sampleData[0][header] || '') : '';
                const truncatedSample = sample.length > 50 ? sample.substring(0, 50) + '...' : sample;

                const $row = $('<tr>');
                $row.append(`<td><strong>${header}</strong></td>`);
                $row.append(`<td><code title="${sample}">${truncatedSample}</code></td>`);
                $row.append(`
                    <td>
                        <select data-column="${header}" class="tbp-field-select">
                            ${optionsHtml}
                        </select>
                    </td>
                `);

                $tbody.append($row);
            });

            // Auto-map common fields
            this.autoMap();
        },

        autoMap: function() {
            const commonMappings = {
                'title': 'post_title',
                'post_title': 'post_title',
                'name': 'name',
                'content': 'post_content',
                'post_content': 'post_content',
                'description': 'description',
                'excerpt': 'post_excerpt',
                'post_excerpt': 'post_excerpt',
                'slug': 'post_name',
                'post_name': 'post_name',
                'date': 'post_date',
                'post_date': 'post_date',
                'author': 'post_author',
                'image': 'featured_image',
                'featured_image': 'featured_image',
                'thumbnail': 'featured_image',
                'parent': 'parent',
            };

            $('#tbp-mapping-body select').each(function() {
                const column = $(this).data('column').toLowerCase();
                if (commonMappings[column]) {
                    $(this).val(commonMappings[column]);
                }
            });
        },

        storeMapping: function() {
            this.mapping = {};
            const self = this;

            $('#tbp-mapping-body select').each(function() {
                const column = $(this).data('column');
                const value = $(this).val();
                if (value) {
                    self.mapping[column] = value;
                }
            });
        },

        preview: function() {
            const self = this;
            const $btn = $('#tbp-preview-btn');
            const $results = $('#tbp-preview-results');

            this.storeMapping();

            $btn.prop('disabled', true).text(tbpImporter.strings.processing);
            $results.html('<span class="spinner is-active"></span>');

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_importer_preview',
                    nonce: tbpImporter.nonce,
                    upload_id: this.uploadId,
                    mapping: this.mapping,
                    type: $('input[name="import_type"]:checked').val()
                },
                success: function(response) {
                    $btn.prop('disabled', false).text('Preview First 5 Items');

                    if (response.success) {
                        let html = '<table class="widefat striped"><thead><tr>';
                        const fields = Object.keys(self.mapping);

                        // Headers
                        fields.forEach(function(field) {
                            html += `<th>${self.mapping[field]}</th>`;
                        });
                        html += '</tr></thead><tbody>';

                        // Rows
                        response.data.preview.forEach(function(item) {
                            html += '<tr>';
                            Object.keys(self.mapping).forEach(function(csvCol) {
                                const wpField = self.mapping[csvCol];
                                const value = item[wpField] || '';
                                const truncated = value.length > 30 ? value.substring(0, 30) + '...' : value;
                                html += `<td title="${value}">${truncated}</td>`;
                            });
                            html += '</tr>';
                        });

                        html += '</tbody></table>';
                        $results.html(html);
                    } else {
                        $results.html(`<p class="text-error">${response.data.message}</p>`);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Preview First 5 Items');
                    $results.html('<p class="text-error">Error loading preview</p>');
                }
            });
        },

        startImport: function() {
            this.currentStep = 5;
            this.updateUI();
            this.importResults = { created: 0, updated: 0, skipped: 0, errors: 0 };

            // Reset UI
            $('.tbp-progress-fill').css('width', '0%');
            $('.tbp-progress-text .current').text('0');
            $('.tbp-progress-text .total').text(this.totalRows);
            $('#tbp-import-log-content').empty();
            $('.tbp-import-summary').hide();

            // Start batch processing
            this.processBatch(0);
        },

        processBatch: function(start) {
            const self = this;

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_importer_process',
                    nonce: tbpImporter.nonce,
                    upload_id: this.uploadId,
                    mapping: this.mapping,
                    type: $('input[name="import_type"]:checked').val(),
                    destination: $('input[name="import_type"]:checked').val() === 'posts'
                        ? $('#tbp-post-type').val()
                        : $('#tbp-taxonomy').val(),
                    status: $('#tbp-post-status').val(),
                    duplicate_action: $('#tbp-duplicate-action').val(),
                    batch_start: start
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;

                        // Update totals
                        self.importResults.created += data.results.created;
                        self.importResults.updated += data.results.updated;
                        self.importResults.skipped += data.results.skipped;
                        self.importResults.errors += data.results.errors;

                        // Update progress
                        const percent = (data.processed / data.total) * 100;
                        $('.tbp-progress-fill').css('width', percent + '%');
                        $('.tbp-progress-text .current').text(data.processed);

                        // Add to log
                        data.results.log.forEach(function(msg) {
                            const isError = msg.includes('Error');
                            const cls = isError ? 'log-error' : 'log-success';
                            $('#tbp-import-log-content').append(`<div class="${cls}">${msg}</div>`);
                        });

                        // Scroll log to bottom
                        const $log = $('#tbp-import-log-content');
                        $log.scrollTop($log[0].scrollHeight);

                        if (!data.done) {
                            // Continue with next batch
                            self.processBatch(data.processed);
                        } else {
                            // Complete
                            self.importComplete();
                        }
                    } else {
                        $('#tbp-import-log-content').append(
                            `<div class="log-error">${response.data.message}</div>`
                        );
                        self.importComplete();
                    }
                },
                error: function() {
                    $('#tbp-import-log-content').append(
                        '<div class="log-error">AJAX error occurred</div>'
                    );
                    self.importComplete();
                }
            });
        },

        importComplete: function() {
            // Show summary
            $('.tbp-import-summary .created').text(this.importResults.created);
            $('.tbp-import-summary .updated').text(this.importResults.updated);
            $('.tbp-import-summary .skipped').text(this.importResults.skipped);
            $('.tbp-import-summary .errors').text(this.importResults.errors);
            $('.tbp-import-summary').show();

            // Show reset button
            $('.tbp-reset-btn').show();
        },

        reset: function() {
            this.currentStep = 1;
            this.uploadId = null;
            this.fileHeaders = [];
            this.sampleData = [];
            this.totalRows = 0;
            this.mapping = {};
            this.importResults = { created: 0, updated: 0, skipped: 0, errors: 0 };

            // Reset UI
            $('#tbp-import-file').val('');
            $('.tbp-file-info').hide().empty();
            $('#tbp-mapping-body').empty();
            $('#tbp-preview-results').empty();
            $('#tbp-import-log-content').empty();
            $('.tbp-import-summary').hide();
            $('.tbp-reset-btn').hide();

            this.updateUI();
        },

        formatBytes: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        // Update sidebar values
        updateSidebar: function() {
            const type = $('input[name="import_type"]:checked').val();
            const destination = type === 'posts'
                ? $('#tbp-post-type option:selected').text()
                : $('#tbp-taxonomy option:selected').text();

            // File info
            if (this.uploadId) {
                $('#sidebar-file-info .value').text(this.fileName || 'Uploaded');
                $('#sidebar-rows .value').text(this.totalRows);
            }

            // Destination
            $('#sidebar-destination .value').text(destination);

            // Mapped fields
            const mappedCount = Object.keys(this.mapping).length;
            $('#sidebar-mapped .value').text(mappedCount);

            // Post status
            const status = $('#tbp-post-status option:selected').text();
            $('#sidebar-status .value').text(status);

            // Duplicates handling
            const dupAction = $('#tbp-duplicate-action option:selected').text();
            $('#sidebar-duplicates .value').text(dupAction);
        }
    };

    /**
     * Exporter Module
     */
    const Exporter = {
        selectedFields: [],

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            const self = this;

            // Export type change
            $('input[name="export_type"]').on('change', function() {
                self.toggleExportSource();
            });

            // Load fields button
            $('#tbp-load-export-fields').on('click', function() {
                self.loadFields();
            });

            // Select all / Deselect all
            $('#tbp-select-all-fields').on('click', function() {
                $('#tbp-export-fields-list input[type="checkbox"]').prop('checked', true);
            });

            $('#tbp-deselect-all-fields').on('click', function() {
                $('#tbp-export-fields-list input[type="checkbox"]').prop('checked', false);
            });

            // Preview export
            $('#tbp-preview-export').on('click', function() {
                self.previewExport();
            });

            // Download export
            $('#tbp-download-export').on('click', function() {
                self.downloadExport();
            });

            // Update sidebar on changes
            $('#tbp-export-post-type, #tbp-export-taxonomy').on('change', function() {
                self.updateSidebar();
            });

            $('#tbp-export-format').on('change', function() {
                $('#export-sidebar-format .value').text($(this).val().toUpperCase());
            });

            // Update field count on checkbox change
            $(document).on('change', '#tbp-export-fields-list input[type="checkbox"]', function() {
                const count = $('#tbp-export-fields-list input:checked').length;
                $('#export-sidebar-fields .value').text(count);
            });
        },

        toggleExportSource: function() {
            const type = $('input[name="export_type"]:checked').val();
            if (type === 'posts') {
                $('.tbp-export-source-posts').show();
                $('.tbp-export-source-taxonomy').hide();
            } else {
                $('.tbp-export-source-posts').hide();
                $('.tbp-export-source-taxonomy').show();
            }
            // Hide fields section when source changes
            $('.tbp-export-fields-section').hide();
            $('#tbp-export-fields-list').empty();
            $('#tbp-export-preview').empty();
        },

        loadFields: function() {
            const self = this;
            const type = $('input[name="export_type"]:checked').val();
            const source = type === 'posts' ? $('#tbp-export-post-type').val() : $('#tbp-export-taxonomy').val();

            $('#tbp-load-export-fields').prop('disabled', true).text('Loading...');

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_exporter_get_fields',
                    nonce: tbpImporter.nonce,
                    type: type,
                    source: source
                },
                success: function(response) {
                    $('#tbp-load-export-fields').prop('disabled', false).text('Load Available Fields');

                    if (response.success) {
                        self.renderFieldsList(response.data.fields);
                        $('.tbp-export-fields-section').show();

                        // Update sidebar with total count
                        if (response.data.total_count !== undefined) {
                            $('#export-sidebar-total .value').text(response.data.total_count);
                        }

                        // Update field count
                        const count = $('#tbp-export-fields-list input:checked').length;
                        $('#export-sidebar-fields .value').text(count);

                        self.updateSidebar();
                    }
                },
                error: function() {
                    $('#tbp-load-export-fields').prop('disabled', false).text('Load Available Fields');
                    alert('Error loading fields');
                }
            });
        },

        renderFieldsList: function(fields) {
            const $container = $('#tbp-export-fields-list');
            $container.empty();

            // Group fields
            const grouped = {};
            fields.forEach(function(field) {
                const group = field.group || 'Other';
                if (!grouped[group]) {
                    grouped[group] = [];
                }
                grouped[group].push(field);
            });

            // Render groups
            Object.keys(grouped).forEach(function(group) {
                const $group = $('<div class="tbp-field-group">');
                $group.append(`<h4>${group}</h4>`);

                const $fields = $('<div class="tbp-field-group-items">');

                grouped[group].forEach(function(field) {
                    const checked = field.checked ? 'checked' : '';
                    $fields.append(`
                        <label class="tbp-field-checkbox">
                            <input type="checkbox" value="${field.value}" ${checked} />
                            ${field.label}
                        </label>
                    `);
                });

                $group.append($fields);
                $container.append($group);
            });
        },

        getSelectedFields: function() {
            const fields = [];
            $('#tbp-export-fields-list input:checked').each(function() {
                fields.push($(this).val());
            });
            return fields;
        },

        previewExport: function() {
            const self = this;
            const fields = this.getSelectedFields();

            if (fields.length === 0) {
                alert('Please select at least one field');
                return;
            }

            const type = $('input[name="export_type"]:checked').val();
            const source = type === 'posts' ? $('#tbp-export-post-type').val() : $('#tbp-export-taxonomy').val();
            const status = $('#tbp-export-post-status').val();
            const useSlugs = $('#tbp-export-use-slugs').is(':checked');

            $('#tbp-preview-export').prop('disabled', true).text('Loading...');
            $('#tbp-export-preview').html('<span class="spinner is-active"></span>');

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_exporter_preview',
                    nonce: tbpImporter.nonce,
                    type: type,
                    source: source,
                    fields: fields,
                    use_slugs: useSlugs ? 'true' : 'false',
                    status: status
                },
                success: function(response) {
                    $('#tbp-preview-export').prop('disabled', false).text('Preview (First 5 rows)');

                    if (response.success) {
                        self.renderPreviewTable(response.data.headers, response.data.data);
                    } else {
                        $('#tbp-export-preview').html(`<p class="text-error">${response.data.message}</p>`);
                    }
                },
                error: function() {
                    $('#tbp-preview-export').prop('disabled', false).text('Preview (First 5 rows)');
                    $('#tbp-export-preview').html('<p class="text-error">Error loading preview</p>');
                }
            });
        },

        renderPreviewTable: function(headers, data) {
            let html = '<table class="widefat striped"><thead><tr>';

            // Headers
            headers.forEach(function(header) {
                html += `<th>${header}</th>`;
            });
            html += '</tr></thead><tbody>';

            // Rows
            data.forEach(function(row) {
                html += '<tr>';
                headers.forEach(function(header) {
                    let value = row[header] || '';
                    if (typeof value === 'string' && value.length > 50) {
                        value = value.substring(0, 50) + '...';
                    }
                    html += `<td>${value}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('#tbp-export-preview').html(html);
        },

        downloadExport: function() {
            const fields = this.getSelectedFields();

            if (fields.length === 0) {
                alert('Please select at least one field');
                return;
            }

            const type = $('input[name="export_type"]:checked').val();
            const source = type === 'posts' ? $('#tbp-export-post-type').val() : $('#tbp-export-taxonomy').val();
            const format = $('#tbp-export-format').val();
            const status = $('#tbp-export-post-status').val();
            const useSlugs = $('#tbp-export-use-slugs').is(':checked');

            $('#tbp-download-export').prop('disabled', true).text('Generating...');

            $.ajax({
                url: tbpImporter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_exporter_download',
                    nonce: tbpImporter.nonce,
                    type: type,
                    source: source,
                    fields: fields,
                    format: format,
                    use_slugs: useSlugs ? 'true' : 'false',
                    status: status
                },
                success: function(response) {
                    $('#tbp-download-export').prop('disabled', false).text('Download Export');

                    if (response.success) {
                        // Trigger download
                        const link = document.createElement('a');
                        link.href = response.data.file_url;
                        link.download = '';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        $('#tbp-export-preview').prepend(
                            `<p class="text-success">Exported ${response.data.count} items. <a href="${response.data.file_url}" target="_blank">Download again</a></p>`
                        );
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    $('#tbp-download-export').prop('disabled', false).text('Download Export');
                    alert('Error generating export');
                }
            });
        },

        updateSidebar: function() {
            const type = $('input[name="export_type"]:checked').val();
            const source = type === 'posts'
                ? $('#tbp-export-post-type option:selected').text()
                : $('#tbp-export-taxonomy option:selected').text();

            $('#export-sidebar-source .value').text(source);
        }
    };

    $(document).ready(function() {
        Importer.init();
        Exporter.init();
    });

})(jQuery);
