/**
 * TBP Repeater List Widget
 */

(function ($, elementor) {
    'use strict';

    var widgetRepeaterList = function ($scope, $) {
        var $widget = $scope.find('.tbp-repeater-list-items');

        if (!$widget.length) {
            return;
        }

        // Any additional frontend functionality can be added here
    };

    // Initialize widget
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/tbp-repeater-list.default',
            widgetRepeaterList
        );
    });

})(jQuery, window.elementorFrontend);
