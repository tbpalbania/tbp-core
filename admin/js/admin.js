jQuery(document).ready(function($) {
    'use strict';

    // Info modal
    $(document).on('click', '.tbp-module-info', function(e) {
        e.preventDefault();

        var moduleData = $(this).data('info');
        var $modal = $('#tbp-info-modal');

        $modal.find('.tbp-info-modal-title').text(moduleData.title);
        $modal.find('.tbp-info-version').text(moduleData.version);
        $modal.find('.tbp-info-description').text(moduleData.description);

        if (moduleData.usage) {
            $modal.find('.tbp-info-usage-section').show();
            $modal.find('.tbp-info-usage').html(moduleData.usage);
        } else {
            $modal.find('.tbp-info-usage-section').hide();
        }

        $modal.show();
    });

    // Close modal
    $(document).on('click', '.tbp-info-modal-close, .tbp-info-modal', function(e) {
        if (e.target === this) {
            $('#tbp-info-modal').hide();
        }
    });
});
