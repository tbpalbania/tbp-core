/**
 * TBP ACF Tabs Widget
 */
(function($) {
    'use strict';

    const TBP_ACF_Tabs = {
        isMobile: false,
        scrollTimeout: null,

        init: function() {
            this.checkMobile();
            this.bindEvents();
            this.initExistingTabs();

            // Re-check on resize
            $(window).on('resize', this.debounce(this.checkMobile.bind(this), 250));
        },

        checkMobile: function() {
            this.isMobile = window.innerWidth <= 767;
        },

        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },

        bindEvents: function() {
            // Tab click
            $(document).on('click', '.tbp-acf-tab-title', this.handleTabClick.bind(this));

            // Dots click
            $(document).on('click', '.tbp-acf-tabs-dot', this.handleDotClick.bind(this));

            // Tooltip events
            $(document).on('click', '.tbp-acf-field-tooltip-icon', this.handleTooltipClick.bind(this));
            $(document).on('click', '.tbp-acf-field-tooltip-overlay, .tbp-acf-field-tooltip-close', this.closeTooltip.bind(this));

            // Elementor editor support
            if (typeof elementorFrontend !== 'undefined') {
                elementorFrontend.hooks.addAction('frontend/element_ready/tbp-acf-tabs.default', this.initWidget.bind(this));
            }
        },

        handleTooltipClick: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $tooltip = $(e.currentTarget).closest('.tbp-acf-field-tooltip');
            const isActive = $tooltip.hasClass('is-active');

            // Close all other tooltips
            $('.tbp-acf-field-tooltip.is-active').removeClass('is-active');

            // Toggle current tooltip
            if (!isActive) {
                $tooltip.addClass('is-active');
            }
        },

        closeTooltip: function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).closest('.tbp-acf-field-tooltip').removeClass('is-active');
        },

        initExistingTabs: function() {
            const self = this;
            $('.tbp-acf-tabs-wrapper').each(function() {
                const $widget = $(this).closest('.elementor-widget-tbp-acf-tabs');
                if ($widget.length) {
                    self.initWidget($widget);
                } else {
                    self.initWidget($(this).parent());
                }
            });
        },

        initWidget: function($scope) {
            const self = this;
            const $wrapper = $scope.find('.tbp-acf-tabs-wrapper');
            if (!$wrapper.length) return;

            // Ensure first tab is active
            const $firstTab = $wrapper.find('.tbp-acf-tab-title').first();
            const $firstContent = $wrapper.find('.tbp-acf-tab-content').first();
            const $firstDot = $wrapper.find('.tbp-acf-tabs-dot').first();

            if (!$wrapper.find('.tbp-acf-tab-title.active').length) {
                $firstTab.addClass('active').attr('aria-selected', 'true');
                $firstContent.addClass('active');
                $firstDot.addClass('active');
            }

            // Handle keyboard navigation
            $wrapper.find('.tbp-acf-tab-title').on('keydown', function(e) {
                self.handleKeyboard(e, $(this));
            });

            // Mobile: Initialize scroll detection
            if (this.isMobile) {
                this.initMobileScrollSync($wrapper);
            }
        },

        initMobileScrollSync: function($wrapper) {
            const self = this;
            const $content = $wrapper.find('.tbp-acf-tabs-content');
            let scrollTimeout;
            let isProgrammaticScroll = false;

            // Store flag on wrapper
            $wrapper.data('isProgrammaticScroll', false);
            $wrapper.data('setProgrammaticScroll', function(value) {
                isProgrammaticScroll = value;
                $wrapper.data('isProgrammaticScroll', value);
            });

            // Content scroll detection
            $content.on('scroll', function() {
                if (isProgrammaticScroll) return;

                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    self.detectActiveFromContentScroll($wrapper);
                }, 100);
            });
        },

        detectActiveFromContentScroll: function($wrapper) {
            const $content = $wrapper.find('.tbp-acf-tabs-content');
            const $panels = $wrapper.find('.tbp-acf-tab-content');
            const scrollLeft = $content[0].scrollLeft;
            const contentWidth = $content[0].offsetWidth;

            if (contentWidth === 0) return;

            // Calculate which panel is visible
            const panelIndex = Math.round(scrollLeft / contentWidth);
            const clampedIndex = Math.max(0, Math.min(panelIndex, $panels.length - 1));
            const currentActive = $wrapper.find('.tbp-acf-tab-title.active').data('tab');

            if (clampedIndex !== currentActive) {
                this.updateActiveStateOnly($wrapper, clampedIndex);
            }
        },

        updateActiveStateOnly: function($wrapper, tabIndex) {
            const $tabs = $wrapper.find('.tbp-acf-tab-title');
            const $contents = $wrapper.find('.tbp-acf-tab-content');
            const $dots = $wrapper.find('.tbp-acf-tabs-dot');

            // Update active classes only (no scrolling)
            $tabs.removeClass('active').attr('aria-selected', 'false');
            $contents.removeClass('active');
            $dots.removeClass('active');

            $tabs.filter('[data-tab="' + tabIndex + '"]').addClass('active').attr('aria-selected', 'true');
            $contents.filter('[data-tab="' + tabIndex + '"]').addClass('active');
            $dots.filter('[data-tab="' + tabIndex + '"]').addClass('active');

            // Scroll tab nav to show active tab
            if (this.isMobile) {
                this.scrollTabIntoView($wrapper, tabIndex);
            }

            $wrapper.trigger('tbp-acf-tabs-switched', [tabIndex]);
        },

        handleTabClick: function(e) {
            e.preventDefault();

            const $tab = $(e.currentTarget);
            const $wrapper = $tab.closest('.tbp-acf-tabs-wrapper');
            const tabIndex = $tab.data('tab');

            this.switchTab($wrapper, tabIndex);
        },

        handleDotClick: function(e) {
            e.preventDefault();

            const $dot = $(e.currentTarget);
            const $wrapper = $dot.closest('.tbp-acf-tabs-wrapper');
            const tabIndex = $dot.data('tab');

            this.switchTab($wrapper, tabIndex);
        },

        switchTab: function($wrapper, tabIndex) {
            this.updateActiveState($wrapper, tabIndex);
        },

        updateActiveState: function($wrapper, tabIndex) {
            const self = this;
            const $tabs = $wrapper.find('.tbp-acf-tab-title');
            const $contents = $wrapper.find('.tbp-acf-tab-content');
            const $dots = $wrapper.find('.tbp-acf-tabs-dot');

            // Deactivate all
            $tabs.removeClass('active').attr('aria-selected', 'false');
            $contents.removeClass('active');
            $dots.removeClass('active');

            // Activate selected
            $tabs.filter('[data-tab="' + tabIndex + '"]').addClass('active').attr('aria-selected', 'true');
            $contents.filter('[data-tab="' + tabIndex + '"]').addClass('active');
            $dots.filter('[data-tab="' + tabIndex + '"]').addClass('active');

            // Mobile: Scroll content to correct panel
            if (this.isMobile) {
                this.scrollTabIntoView($wrapper, tabIndex);
                this.scrollContentToPanel($wrapper, tabIndex);
            }

            // Trigger custom event
            $wrapper.trigger('tbp-acf-tabs-switched', [tabIndex]);
        },

        scrollTabIntoView: function($wrapper, tabIndex) {
            const $nav = $wrapper.find('.tbp-acf-tabs-nav');
            const $activeTab = $wrapper.find('.tbp-acf-tab-title[data-tab="' + tabIndex + '"]');

            if (!$activeTab.length) return;

            const tabOffsetLeft = $activeTab[0].offsetLeft;

            $nav[0].scrollTo({
                left: tabOffsetLeft,
                behavior: 'smooth'
            });
        },

        scrollContentToPanel: function($wrapper, tabIndex) {
            const $content = $wrapper.find('.tbp-acf-tabs-content');
            const contentWidth = $content[0].offsetWidth;
            const targetScroll = tabIndex * contentWidth;

            // Set flag to prevent scroll detection loop
            const setFlag = $wrapper.data('setProgrammaticScroll');
            if (setFlag) setFlag(true);

            $content[0].scrollTo({
                left: targetScroll,
                behavior: 'smooth'
            });

            // Reset flag after scroll completes
            setTimeout(function() {
                if (setFlag) setFlag(false);
            }, 500);
        },

        handleKeyboard: function(e, $currentTab) {
            const $wrapper = $currentTab.closest('.tbp-acf-tabs-wrapper');
            const $tabs = $wrapper.find('.tbp-acf-tab-title');
            const currentIndex = $tabs.index($currentTab);
            let newIndex;

            switch (e.keyCode) {
                case 37: // Left arrow
                case 38: // Up arrow
                    e.preventDefault();
                    newIndex = currentIndex > 0 ? currentIndex - 1 : $tabs.length - 1;
                    break;

                case 39: // Right arrow
                case 40: // Down arrow
                    e.preventDefault();
                    newIndex = currentIndex < $tabs.length - 1 ? currentIndex + 1 : 0;
                    break;

                case 36: // Home
                    e.preventDefault();
                    newIndex = 0;
                    break;

                case 35: // End
                    e.preventDefault();
                    newIndex = $tabs.length - 1;
                    break;

                case 13: // Enter
                case 32: // Space
                    e.preventDefault();
                    this.switchTab($wrapper, $currentTab.data('tab'));
                    return;

                default:
                    return;
            }

            if (newIndex !== undefined) {
                const $newTab = $tabs.eq(newIndex);
                $newTab.focus();
                this.switchTab($wrapper, $newTab.data('tab'));
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        TBP_ACF_Tabs.init();
    });

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/tbp-acf-tabs.default', function($scope) {
                TBP_ACF_Tabs.initWidget($scope);
            });
        }
    });

})(jQuery);
