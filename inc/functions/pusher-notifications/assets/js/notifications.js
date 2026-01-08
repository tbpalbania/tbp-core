/**
 * TBP Pusher Notifications
 *
 * Handles real-time notifications via Pusher and dropdown UI
 */
(function($) {
    'use strict';

    var TBPNotifications = {
        pusher: null,
        channel: null,
        $toggle: null,
        $badge: null,
        $dropdown: null,
        isOpen: false,
        notifications: [],
        unreadCount: 0,

        init: function() {
            this.$toggle = $('.tbp-portal-notifications-toggle');
            this.$badge = this.$toggle.find('.tbp-portal-notification-badge');

            if (!this.$toggle.length) {
                return;
            }

            // Create dropdown
            this.createDropdown();

            // Initialize Pusher if configured
            if (typeof tbpPusher !== 'undefined' && tbpPusher.isConfigured && tbpPusher.appKey) {
                this.initPusher();
            }

            // Bind events
            this.bindEvents();

            // Load initial notifications
            this.loadNotifications();
        },

        createDropdown: function() {
            var dropdownHtml = '<div class="tbp-notifications-dropdown">' +
                '<div class="tbp-notifications-header">' +
                    '<h3>' + this.translate('Notifications') + '</h3>' +
                    '<button type="button" class="tbp-notifications-mark-all">' + this.translate('Mark all as read') + '</button>' +
                '</div>' +
                '<div class="tbp-notifications-list">' +
                    '<div class="tbp-notifications-loading"><div class="spinner"></div></div>' +
                '</div>' +
                '<div class="tbp-notifications-footer">' +
                    '<a href="' + tbpPusher.allNotificationsUrl + '">' + this.translate('View all notifications') + '</a>' +
                '</div>' +
            '</div>';

            this.$dropdown = $(dropdownHtml);
            this.$toggle.parent().css('position', 'relative').append(this.$dropdown);
        },

        translate: function(text) {
            // Simple translation placeholder - can be extended with wp_localize_script
            var translations = {
                'Notifications': 'Notifications',
                'Mark all as read': 'Mark all as read',
                'View all notifications': 'View all notifications',
                'No notifications': 'No notifications',
                'You\'re all caught up!': 'You\'re all caught up!'
            };
            return translations[text] || text;
        },

        initPusher: function() {
            var self = this;

            // Note: Private/presence channels require server-side auth at /pusher/auth
            // For now, we use public channels or skip Pusher entirely
            // Real-time will be added when auth endpoint is configured

            try {
                this.pusher = new Pusher(tbpPusher.appKey, {
                    cluster: tbpPusher.cluster,
                    encrypted: true
                });

                // Use public channel for broadcasts (no auth required)
                this.broadcastChannel = this.pusher.subscribe('tbp-notifications');

                // Listen for new notifications on public channel
                this.broadcastChannel.bind('notification', function(data) {
                    // Only process if notification is for this user or broadcast
                    if (data.user_id === 0 || data.user_id == tbpPusher.userId) {
                        self.onNotification(data);
                    }
                });

            } catch (e) {
                console.warn('Pusher initialization failed:', e);
            }
        },

        bindEvents: function() {
            var self = this;

            // Toggle dropdown
            this.$toggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggleDropdown();
            });

            // Close on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.tbp-notifications-dropdown, .tbp-portal-notifications-toggle').length) {
                    self.closeDropdown();
                }
            });

            // Mark all as read
            this.$dropdown.on('click', '.tbp-notifications-mark-all', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });

            // Click notification
            this.$dropdown.on('click', '.tbp-notification-item', function(e) {
                var $item = $(this);
                var notificationId = $item.data('id');
                var actionUrl = $item.data('url');

                // Mark as read
                if ($item.hasClass('is-unread')) {
                    self.markAsRead(notificationId);
                }

                // Navigate if has action URL
                if (actionUrl) {
                    window.location.href = actionUrl;
                }
            });

            // Close on escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.isOpen) {
                    self.closeDropdown();
                }
            });
        },

        toggleDropdown: function() {
            if (this.isOpen) {
                this.closeDropdown();
            } else {
                this.openDropdown();
            }
        },

        openDropdown: function() {
            this.$dropdown.addClass('is-open');
            this.isOpen = true;
            this.loadNotifications();
        },

        closeDropdown: function() {
            this.$dropdown.removeClass('is-open');
            this.isOpen = false;
        },

        loadNotifications: function() {
            var self = this;
            var $list = this.$dropdown.find('.tbp-notifications-list');

            $list.html('<div class="tbp-notifications-loading"><div class="spinner"></div></div>');

            $.ajax({
                url: tbpPusher.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_get_notifications',
                    nonce: tbpPusher.nonce,
                    limit: 5
                },
                success: function(response) {
                    if (response.success) {
                        self.notifications = response.data.notifications;
                        self.unreadCount = response.data.unread_count;
                        self.updateBadge(self.unreadCount);
                        self.renderNotifications();
                    }
                },
                error: function() {
                    $list.html('<div class="tbp-notifications-empty"><p>Failed to load notifications</p></div>');
                }
            });
        },

        renderNotifications: function() {
            var self = this;
            var $list = this.$dropdown.find('.tbp-notifications-list');

            if (!this.notifications.length) {
                $list.html(
                    '<div class="tbp-notifications-empty">' +
                        '<i class="ti ti-bell-off"></i>' +
                        '<p>' + this.translate('No notifications') + '</p>' +
                    '</div>'
                );
                return;
            }

            var html = '';

            $.each(this.notifications, function(i, notification) {
                var unreadClass = notification.is_read == 0 ? 'is-unread' : '';
                var icon = notification.icon || 'ti-bell';

                html += '<div class="tbp-notification-item ' + unreadClass + '" ' +
                    'data-id="' + notification.id + '" ' +
                    'data-type="' + notification.type + '" ' +
                    'data-url="' + (notification.action_url || '') + '">' +
                    '<div class="tbp-notification-icon">' +
                        '<i class="ti ' + icon + '"></i>' +
                    '</div>' +
                    '<div class="tbp-notification-content">' +
                        '<h4 class="tbp-notification-title">' + self.escapeHtml(notification.title) + '</h4>' +
                        '<p class="tbp-notification-message">' + self.escapeHtml(notification.message) + '</p>' +
                        '<span class="tbp-notification-time">' + notification.time_ago + '</span>' +
                    '</div>' +
                '</div>';
            });

            $list.html(html);
        },

        onNotification: function(data) {
            // Add to beginning of list
            this.notifications.unshift(data);
            this.unreadCount++;

            // Update badge
            this.updateBadge(this.unreadCount);

            // Show toast notification
            this.showToast(data);

            // Re-render if dropdown is open
            if (this.isOpen) {
                this.renderNotifications();
            }

            // Trigger custom event
            $(document).trigger('tbp_notification', [data]);
        },

        updateBadge: function(count) {
            if (count > 0) {
                this.$badge.text(count > 99 ? '99+' : count).show();
            } else {
                this.$badge.hide();
            }
        },

        markAsRead: function(notificationId) {
            var self = this;

            $.ajax({
                url: tbpPusher.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_mark_notification_read',
                    notification_id: notificationId,
                    nonce: tbpPusher.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(response.data.unread_count);
                        self.$dropdown.find('[data-id="' + notificationId + '"]').removeClass('is-unread');
                    }
                }
            });
        },

        markAllAsRead: function() {
            var self = this;
            var $btn = this.$dropdown.find('.tbp-notifications-mark-all');

            $btn.prop('disabled', true);

            $.ajax({
                url: tbpPusher.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_mark_all_notifications_read',
                    nonce: tbpPusher.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(0);
                        self.$dropdown.find('.tbp-notification-item').removeClass('is-unread');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        },

        showToast: function(notification) {
            // Create toast container if doesn't exist
            if (!$('#tbp-toast-container').length) {
                $('body').append('<div id="tbp-toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 10000;"></div>');
            }

            var icon = notification.icon || 'ti-bell';
            var $toast = $(
                '<div class="tbp-toast" style="' +
                    'background: #fff; padding: 16px 20px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); ' +
                    'margin-top: 12px; display: flex; gap: 12px; align-items: flex-start; max-width: 360px; ' +
                    'animation: slideIn 0.3s ease; cursor: pointer;">' +
                    '<i class="ti ' + icon + '" style="font-size: 20px; color: var(--tbp-accent-2, #5CD4D6);"></i>' +
                    '<div>' +
                        '<strong style="display: block; margin-bottom: 4px;">' + this.escapeHtml(notification.title) + '</strong>' +
                        '<span style="font-size: 13px; color: #666;">' + this.escapeHtml(notification.message) + '</span>' +
                    '</div>' +
                '</div>'
            );

            $('#tbp-toast-container').append($toast);

            // Click to dismiss and navigate
            $toast.on('click', function() {
                $(this).remove();
                if (notification.action_url) {
                    window.location.href = notification.action_url;
                }
            });

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    $(document).ready(function() {
        if (typeof tbpPusher !== 'undefined') {
            TBPNotifications.init();
        }
    });

    // Expose to global scope
    window.TBPNotifications = TBPNotifications;

})(jQuery);
