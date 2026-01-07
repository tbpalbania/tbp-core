/**
 * TBP Advanced Search Widget
 */

(function ($, elementor) {
    'use strict';

    var searchTimer;

    // Modal functions
    var openModal = function ($modal) {
        $modal.css('display', 'block');
        setTimeout(function () {
            $modal.addClass('tbp-modal-open');
            $('body').addClass('tbp-modal-active');
        }, 10);
        // Auto-focus input after modal animation completes
        setTimeout(function () {
            $modal.find('.tbp-search-input').trigger('focus');
        }, 350);
    };

    var closeModal = function ($modal) {
        $modal.removeClass('tbp-modal-open');
        $('body').removeClass('tbp-modal-active');
        setTimeout(function () {
            $modal.css('display', 'none');
            // Clear search input and results
            $modal.find('.tbp-search-input').val('');
            $modal.find('.tbp-search-result').hide();
            $modal.find('.tbp-ajax-search').removeClass('tbp-has-results');
        }, 300);
    };

    // Helper function to render a single item
    var renderItem = function (item, showThumbnail, target) {
        var output = '<li class="tbp-search-item">';
        output += '<a href="' + item.url + '" target="' + target + '">';

        // Thumbnail (only render if exists and enabled)
        if (showThumbnail && item.thumbnail && item.thumbnail.url) {
            output += '<div class="tbp-search-item-thumb">';
            output += '<img src="' + item.thumbnail.url + '" alt="' + (item.thumbnail.alt || item.title) + '" loading="lazy">';
            output += '</div>';
        }

        output += '<div class="tbp-search-item-content">';
        output += '<div class="tbp-search-title">' + item.title + '</div>';
        if (item.text) {
            output += '<div class="tbp-search-text">' + item.text + '</div>';
        }
        if (item.count !== undefined) {
            output += '<div class="tbp-search-count">' + item.count + ' items</div>';
        }
        output += '</div>';
        output += '</a>';
        output += '</li>';

        return output;
    };

    // Helper function to group items by a key
    var groupItemsBy = function (items, keyField, labelField) {
        var groups = {};

        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var groupKey = item[keyField] || 'other';
            var groupLabel = item[labelField] || groupKey;

            if (!groups[groupKey]) {
                groups[groupKey] = {
                    label: groupLabel,
                    items: []
                };
            }
            groups[groupKey].items.push(item);
        }

        return groups;
    };

    // Render grouped items
    var renderGroupedItems = function (groups, showThumbnail, target) {
        var output = '';
        for (var groupKey in groups) {
            if (groups.hasOwnProperty(groupKey)) {
                var group = groups[groupKey];
                output += '<div class="tbp-search-group">';
                output += '<div class="tbp-search-group-header">' + group.label + '</div>';
                output += '<ul class="tbp-list tbp-list-divider">';
                for (var i = 0; i < group.items.length; i++) {
                    output += renderItem(group.items[i], showThumbnail, target);
                }
                output += '</ul>';
                output += '</div>';
            }
        }
        return output;
    };

    // Render ungrouped items
    var renderUngroupedItems = function (items, showThumbnail, target) {
        var output = '<ul class="tbp-list tbp-list-divider">';
        for (var i = 0; i < items.length; i++) {
            output += renderItem(items[i], showThumbnail, target);
        }
        output += '</ul>';
        return output;
    };

    var widgetAdvancedSearch = function ($scope, $) {
        var $searchContainer = $scope.find('.tbp-search-container'),
            $searchWidget = $scope.find('.tbp-ajax-search');

        // Modal Search (Layout 2)
        var $modalTrigger = $scope.find('.tbp-search-modal-trigger'),
            $modal = $scope.find('.tbp-search-modal');

        if ($modalTrigger.length && $modal.length) {
            var enableShortcut = $modal.attr('data-keyboard-shortcut') === 'yes';

            // Open modal on trigger click
            $modalTrigger.on('click', function (e) {
                e.preventDefault();
                openModal($modal);
            });

            // Close modal on close button click
            $modal.find('.tbp-search-modal-close').on('click', function (e) {
                e.preventDefault();
                closeModal($modal);
            });

            // Close modal on overlay click
            $modal.find('.tbp-search-modal-overlay').on('click', function (e) {
                e.preventDefault();
                closeModal($modal);
            });

            // Keyboard shortcuts
            $(document).on('keydown', function (e) {
                // Close modal on ESC key
                if (e.keyCode === 27 && $modal.hasClass('tbp-modal-open')) {
                    closeModal($modal);
                }

                // CMD/CTRL + K to open modal
                if (enableShortcut && (e.metaKey || e.ctrlKey) && e.keyCode === 75) {
                    e.preventDefault();
                    if ($modal.hasClass('tbp-modal-open')) {
                        closeModal($modal);
                    } else {
                        openModal($modal);
                    }
                }
            });
        }

        if (!$searchWidget.length) {
            return;
        }

        var $resultHolder = $searchWidget.find('.tbp-search-result'),
            $settings = $searchWidget.data('settings'),
            $target = $searchWidget.attr('data-anchor-target');

        if ('yes' === $target) {
            $target = '_blank';
        } else {
            $target = '_self';
        }

        // Click outside to close
        if ($settings.close_on_click_outside) {
            $(document).on('click', function (e) {
                if (!$(e.target).closest($searchWidget).length && $resultHolder.is(':visible')) {
                    $resultHolder.hide();
                    $searchWidget.removeClass('tbp-has-results');
                }
            });
        }

        // Prevent form submission on Enter
        $searchWidget.on('keyup keypress', function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        // Handle search input
        $searchWidget.find('.tbp-search-input').on('keyup', function () {
            var $search = $(this).val();

            clearTimeout(searchTimer);

            if ($search.length < 3) {
                $resultHolder.hide();
                $searchWidget.removeClass('tbp-search-loading tbp-has-results');
                return;
            }

            searchTimer = setTimeout(function () {
                $searchWidget.addClass('tbp-search-loading');

                $.ajax({
                    url: tbpSearchConfig.ajaxurl,
                    type: 'post',
                    data: {
                        action: 'tbp_advanced_search',
                        s: $search,
                        settings: $settings,
                    },
                    success: function (response) {
                        try {
                            var data = typeof response === 'string' ? JSON.parse(response) : response;
                            var showThumbnail = $settings.show_thumbnail || false;
                            var excerptLines = $settings.excerpt_lines || 2;
                            var groupPostTypes = $settings.group_post_types || false;
                            var groupTaxonomies = $settings.group_taxonomies || false;

                            var posts = data.results || [];
                            var terms = data.terms || [];
                            var hasResults = posts.length > 0 || terms.length > 0;

                            if (hasResults) {
                                var output = '<div class="tbp-search-result-inner" style="--tbp-excerpt-lines: ' + excerptLines + ';">';
                                output += '<div class="tbp-search-result-header">';
                                output += tbpSearchConfig.search.search_result;
                                output += '<button type="button" class="tbp-search-result-close-btn">&times;</button>';
                                output += '</div>';

                                // Render posts
                                if (posts.length > 0) {
                                    if (groupPostTypes) {
                                        var postGroups = groupItemsBy(posts, 'post_type', 'post_type_label');
                                        output += renderGroupedItems(postGroups, showThumbnail, $target);
                                    } else {
                                        output += renderUngroupedItems(posts, showThumbnail, $target);
                                    }
                                }

                                // Render terms
                                if (terms.length > 0) {
                                    if (groupTaxonomies) {
                                        var termGroups = groupItemsBy(terms, 'taxonomy', 'taxonomy_label');
                                        output += renderGroupedItems(termGroups, showThumbnail, $target);
                                    } else {
                                        output += renderUngroupedItems(terms, showThumbnail, $target);
                                    }
                                }

                                output += '<a href="#" class="tbp-search-more">' + tbpSearchConfig.search.more_result + '</a>';
                                output += '</div>';

                                $resultHolder.html(output);
                                $resultHolder.show();
                                $searchWidget.addClass('tbp-has-results');

                                // Close button handler
                                $resultHolder.find('.tbp-search-result-close-btn').on('click', function (e) {
                                    e.preventDefault();
                                    $resultHolder.hide();
                                    $searchWidget.removeClass('tbp-has-results');
                                    $searchWidget.find('.tbp-search-input').val('');
                                });

                                // View all results handler
                                $resultHolder.find('.tbp-search-more').on('click', function (e) {
                                    e.preventDefault();
                                    $searchWidget.submit();
                                });

                            } else {
                                var notFound = '<div class="tbp-search-result-inner" style="--tbp-excerpt-lines: ' + excerptLines + ';">';
                                notFound += '<div class="tbp-search-result-header">';
                                notFound += tbpSearchConfig.search.search_result;
                                notFound += '<button type="button" class="tbp-search-result-close-btn">&times;</button>';
                                notFound += '</div>';
                                notFound += '<div class="tbp-search-text not-found">';
                                notFound += '"' + $search + '" ' + tbpSearchConfig.search.not_found;
                                notFound += '</div>';
                                notFound += '</div>';

                                $resultHolder.html(notFound);
                                $resultHolder.show();
                                $searchWidget.addClass('tbp-has-results');

                                // Close button handler
                                $resultHolder.find('.tbp-search-result-close-btn').on('click', function (e) {
                                    e.preventDefault();
                                    $resultHolder.hide();
                                    $searchWidget.removeClass('tbp-has-results');
                                    $searchWidget.find('.tbp-search-input').val('');
                                });
                            }

                            $searchWidget.removeClass('tbp-search-loading');

                        } catch (error) {
                            console.error('TBP Advanced Search Error:', error);
                            $searchWidget.removeClass('tbp-search-loading');
                            $resultHolder.hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('TBP Advanced Search AJAX Error:', error);
                        $searchWidget.removeClass('tbp-search-loading');
                        $resultHolder.hide();
                    }
                });
            }, 450);
        });
    };

    // Initialize widget
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/tbp-advanced-search.default',
            widgetAdvancedSearch
        );
    });

})(jQuery, window.elementorFrontend);
