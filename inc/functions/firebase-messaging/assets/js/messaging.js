/**
 * TBP Firebase Messaging
 *
 * Real-time chat using Firebase Firestore
 */

(function($) {
    'use strict';

    window.TBPMessaging = {
        db: null,
        currentUser: null,
        openChats: {},
        unsubscribers: {},
        onlineUsers: {},
        maxChats: 3,

        /**
         * Initialize messaging
         */
        init: function() {
            var self = this;
            this.currentUser = tbpMessaging.currentUser;

            // Wait for Firebase to be ready
            if (typeof TBPFirebase !== 'undefined') {
                TBPFirebase.onReady(function(fb) {
                    self.db = fb.db;
                    self.setupPresence();
                    self.bindEvents();
                    self.loadUsers();
                    self.listenToConversations();
                });
            } else {
                console.warn('TBPMessaging: Firebase not available');
                this.bindEvents();
            }
        },

        /**
         * Bind UI events
         */
        bindEvents: function() {
            var self = this;

            // FAB click
            $('#tbp-messaging-fab').on('click', function() {
                self.toggleOffcanvas();
            });

            // Close offcanvas
            $('#tbp-messaging-offcanvas-close').on('click', function() {
                self.closeOffcanvas();
            });

            // Tab switching
            $('.tbp-messaging-tab').on('click', function() {
                var tab = $(this).data('tab');
                self.switchTab(tab);
            });

            // Search users
            var searchTimeout;
            $('#tbp-messaging-search').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    self.searchUsers(query);
                }, 300);
            });

            // Click on user
            $(document).on('click', '.tbp-messaging-user', function() {
                var userId = $(this).data('user-id');
                var userData = $(this).data('user');
                self.openChat(userId, userData);
            });

            // Chat window events (delegated)
            $(document).on('click', '.tbp-chat-header', function(e) {
                if (!$(e.target).closest('.tbp-chat-header-actions').length) {
                    var $window = $(this).closest('.tbp-chat-window');
                    self.toggleMinimize($window);
                }
            });

            $(document).on('click', '.tbp-chat-minimize', function(e) {
                e.stopPropagation();
                var $window = $(this).closest('.tbp-chat-window');
                self.toggleMinimize($window);
            });

            $(document).on('click', '.tbp-chat-close', function(e) {
                e.stopPropagation();
                var $window = $(this).closest('.tbp-chat-window');
                var conversationId = $window.data('conversation-id');
                self.closeChat(conversationId);
            });

            // Send message
            $(document).on('keypress', '.tbp-chat-input', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    var $window = $(this).closest('.tbp-chat-window');
                    self.sendMessage($window);
                }
            });

            $(document).on('click', '.tbp-chat-send', function() {
                var $window = $(this).closest('.tbp-chat-window');
                self.sendMessage($window);
            });

            // Typing indicator
            var typingTimeout;
            $(document).on('input', '.tbp-chat-input', function() {
                var $window = $(this).closest('.tbp-chat-window');
                var conversationId = $window.data('conversation-id');

                self.setTyping(conversationId, true);

                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(function() {
                    self.setTyping(conversationId, false);
                }, 2000);
            });

            // Close offcanvas on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#tbp-messaging-offcanvas, #tbp-messaging-fab').length) {
                    self.closeOffcanvas();
                }
            });
        },

        /**
         * Toggle offcanvas
         */
        toggleOffcanvas: function() {
            $('#tbp-messaging-offcanvas').toggleClass('is-open');
        },

        /**
         * Close offcanvas
         */
        closeOffcanvas: function() {
            $('#tbp-messaging-offcanvas').removeClass('is-open');
        },

        /**
         * Switch tab
         */
        switchTab: function(tab) {
            $('.tbp-messaging-tab').removeClass('active');
            $('.tbp-messaging-tab[data-tab="' + tab + '"]').addClass('active');

            if (tab === 'online') {
                this.loadUsers();
            } else {
                this.loadRecentChats();
            }
        },

        /**
         * Load users list
         */
        loadUsers: function() {
            var self = this;
            var $list = $('#tbp-messaging-users');

            $.ajax({
                url: tbpMessaging.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_messaging_get_users',
                    nonce: tbpMessaging.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        self.renderUsers(response.data.users);
                    }
                }
            });
        },

        /**
         * Search users
         */
        searchUsers: function(query) {
            var self = this;

            $.ajax({
                url: tbpMessaging.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_messaging_get_users',
                    nonce: tbpMessaging.nonce,
                    search: query,
                },
                success: function(response) {
                    if (response.success) {
                        self.renderUsers(response.data.users);
                    }
                }
            });
        },

        /**
         * Render users list
         */
        renderUsers: function(users) {
            var self = this;
            var $list = $('#tbp-messaging-users');
            var onlineCount = 0;

            $list.empty();

            if (!users.length) {
                $list.html(
                    '<div class="tbp-messaging-empty">' +
                    '<i class="ti ti-users-minus"></i>' +
                    '<p>' + tbpMessaging.i18n.noUsers + '</p>' +
                    '</div>'
                );
                return;
            }

            users.forEach(function(user) {
                var isOnline = self.onlineUsers[user.uid] || false;
                if (isOnline) onlineCount++;

                var html =
                    '<div class="tbp-messaging-user" data-user-id="' + user.id + '" data-user=\'' + JSON.stringify(user) + '\'>' +
                    '<div class="tbp-messaging-user-avatar">' +
                    '<img src="' + user.avatar + '" alt="">' +
                    '<span class="status-dot ' + (isOnline ? 'online' : '') + '"></span>' +
                    '</div>' +
                    '<div class="tbp-messaging-user-info">' +
                    '<span class="tbp-messaging-user-name">' + self.escapeHtml(user.name) + '</span>' +
                    '<span class="tbp-messaging-user-role">' + self.escapeHtml(user.roleName || user.role) + '</span>' +
                    '</div>' +
                    '</div>';

                $list.append(html);
            });

            $('#tbp-online-count').text(onlineCount);
        },

        /**
         * Load recent chats
         */
        loadRecentChats: function() {
            // TODO: Load from Firestore conversations
            var $list = $('#tbp-messaging-users');
            $list.html(
                '<div class="tbp-messaging-empty">' +
                '<i class="ti ti-message-off"></i>' +
                '<p>' + tbpMessaging.i18n.noMessages + '</p>' +
                '</div>'
            );
        },

        /**
         * Open chat with user
         */
        openChat: function(userId, userData) {
            var self = this;

            // Get or create conversation
            $.ajax({
                url: tbpMessaging.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbp_messaging_get_conversation',
                    nonce: tbpMessaging.nonce,
                    user_id: userId,
                },
                success: function(response) {
                    if (response.success) {
                        self.createChatWindow(
                            response.data.conversationId,
                            response.data.otherUser
                        );
                        self.closeOffcanvas();
                    }
                }
            });
        },

        /**
         * Create chat window
         */
        createChatWindow: function(conversationId, user) {
            var self = this;

            // Check if already open
            if (this.openChats[conversationId]) {
                var $existing = $('[data-conversation-id="' + conversationId + '"]');
                $existing.removeClass('is-minimized');
                return;
            }

            // Limit open chats
            var openKeys = Object.keys(this.openChats);
            if (openKeys.length >= this.maxChats) {
                this.closeChat(openKeys[0]);
            }

            // Clone template
            var template = document.getElementById('tbp-chat-window-template');
            var $window = $(template.content.cloneNode(true)).find('.tbp-chat-window');

            // Set data
            $window.attr('data-conversation-id', conversationId);
            $window.find('.tbp-chat-avatar img').attr('src', user.avatar);
            $window.find('.tbp-chat-user-name').text(user.name);
            $window.find('.tbp-chat-user-status').text(tbpMessaging.i18n.online);

            // Add to container
            $('#tbp-messaging-chats').append($window);

            // Animate in
            setTimeout(function() {
                $window.addClass('is-open');
            }, 10);

            // Track
            this.openChats[conversationId] = {
                user: user,
                $window: $window,
            };

            // Listen to messages
            this.listenToMessages(conversationId);

            // Listen to typing
            this.listenToTyping(conversationId, user.uid);

            // Focus input
            $window.find('.tbp-chat-input').focus();
        },

        /**
         * Close chat window
         */
        closeChat: function(conversationId) {
            var chat = this.openChats[conversationId];
            if (!chat) return;

            // Unsubscribe from listeners
            if (this.unsubscribers[conversationId]) {
                this.unsubscribers[conversationId].forEach(function(unsub) {
                    unsub();
                });
                delete this.unsubscribers[conversationId];
            }

            // Remove window
            chat.$window.removeClass('is-open');
            setTimeout(function() {
                chat.$window.remove();
            }, 250);

            delete this.openChats[conversationId];
        },

        /**
         * Toggle minimize
         */
        toggleMinimize: function($window) {
            $window.toggleClass('is-minimized');

            if (!$window.hasClass('is-minimized')) {
                $window.find('.tbp-chat-input').focus();
                this.scrollToBottom($window);
            }
        },

        /**
         * Send message
         */
        sendMessage: function($window) {
            var self = this;
            var conversationId = $window.data('conversation-id');
            var $input = $window.find('.tbp-chat-input');
            var text = $input.val().trim();

            if (!text || !this.db) return;

            // Clear input
            $input.val('');
            this.setTyping(conversationId, false);

            // Add message to Firestore
            var message = {
                senderId: this.currentUser.uid,
                text: text,
                timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                readBy: [this.currentUser.uid],
            };

            this.db.collection('conversations')
                .doc(conversationId)
                .collection('messages')
                .add(message)
                .then(function() {
                    // Update conversation lastMessage
                    return self.db.collection('conversations').doc(conversationId).set({
                        participants: self.getParticipants(conversationId),
                        lastMessage: {
                            text: text,
                            senderId: self.currentUser.uid,
                            timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                        },
                        updatedAt: firebase.firestore.FieldValue.serverTimestamp(),
                    }, { merge: true });
                })
                .catch(function(error) {
                    console.error('Error sending message:', error);
                });
        },

        /**
         * Get participants from conversation ID
         */
        getParticipants: function(conversationId) {
            return conversationId.split('_');
        },

        /**
         * Listen to messages
         */
        listenToMessages: function(conversationId) {
            var self = this;
            if (!this.db) return;

            var unsub = this.db.collection('conversations')
                .doc(conversationId)
                .collection('messages')
                .orderBy('timestamp', 'asc')
                .onSnapshot(function(snapshot) {
                    snapshot.docChanges().forEach(function(change) {
                        if (change.type === 'added') {
                            self.addMessageToChat(conversationId, change.doc.id, change.doc.data());
                        }
                    });
                });

            if (!this.unsubscribers[conversationId]) {
                this.unsubscribers[conversationId] = [];
            }
            this.unsubscribers[conversationId].push(unsub);
        },

        /**
         * Add message to chat
         */
        addMessageToChat: function(conversationId, messageId, message) {
            var chat = this.openChats[conversationId];
            if (!chat) return;

            var $messages = chat.$window.find('.tbp-chat-messages');
            var isSent = message.senderId === this.currentUser.uid;
            var time = message.timestamp ? this.formatTime(message.timestamp.toDate()) : tbpMessaging.i18n.justNow;

            var html =
                '<div class="tbp-chat-message ' + (isSent ? 'sent' : 'received') + '" data-message-id="' + messageId + '">' +
                '<div class="tbp-chat-message-bubble">' + this.escapeHtml(message.text) + '</div>' +
                '<span class="tbp-chat-message-time">' + time + '</span>' +
                '</div>';

            // Avoid duplicates
            if ($messages.find('[data-message-id="' + messageId + '"]').length) {
                return;
            }

            $messages.append(html);
            this.scrollToBottom(chat.$window);
        },

        /**
         * Listen to typing indicator
         */
        listenToTyping: function(conversationId, otherUid) {
            var self = this;
            if (!this.db) return;

            var unsub = this.db.collection('conversations')
                .doc(conversationId)
                .collection('typing')
                .doc(otherUid)
                .onSnapshot(function(doc) {
                    var data = doc.data();
                    var isTyping = data && data.isTyping;
                    self.showTyping(conversationId, isTyping);
                });

            if (!this.unsubscribers[conversationId]) {
                this.unsubscribers[conversationId] = [];
            }
            this.unsubscribers[conversationId].push(unsub);
        },

        /**
         * Set typing status
         */
        setTyping: function(conversationId, isTyping) {
            if (!this.db) return;

            this.db.collection('conversations')
                .doc(conversationId)
                .collection('typing')
                .doc(this.currentUser.uid)
                .set({
                    isTyping: isTyping,
                    timestamp: firebase.firestore.FieldValue.serverTimestamp(),
                });
        },

        /**
         * Show typing indicator
         */
        showTyping: function(conversationId, isTyping) {
            var chat = this.openChats[conversationId];
            if (!chat) return;

            var $typing = chat.$window.find('.tbp-chat-typing');
            $typing.toggle(isTyping);

            if (isTyping) {
                this.scrollToBottom(chat.$window);
            }
        },

        /**
         * Setup presence system
         */
        setupPresence: function() {
            var self = this;
            if (!this.db) return;

            // Set current user online
            var userRef = this.db.collection('users').doc(this.currentUser.uid);

            userRef.set({
                displayName: this.currentUser.name,
                email: this.currentUser.email,
                avatar: this.currentUser.avatar,
                role: this.currentUser.role,
                lastSeen: firebase.firestore.FieldValue.serverTimestamp(),
                status: 'online',
            }, { merge: true });

            // Set offline on disconnect
            window.addEventListener('beforeunload', function() {
                userRef.update({
                    status: 'offline',
                    lastSeen: firebase.firestore.FieldValue.serverTimestamp(),
                });
            });

            // Listen to online users
            this.db.collection('users')
                .where('status', '==', 'online')
                .onSnapshot(function(snapshot) {
                    self.onlineUsers = {};
                    snapshot.forEach(function(doc) {
                        self.onlineUsers[doc.id] = true;
                    });
                    self.updateOnlineStatus();
                });
        },

        /**
         * Update online status in UI
         */
        updateOnlineStatus: function() {
            var self = this;

            // Update users list
            $('.tbp-messaging-user').each(function() {
                var userData = $(this).data('user');
                if (userData) {
                    var isOnline = self.onlineUsers[userData.uid] || false;
                    $(this).find('.status-dot').toggleClass('online', isOnline);
                }
            });

            // Update chat windows
            Object.keys(this.openChats).forEach(function(convId) {
                var chat = self.openChats[convId];
                var isOnline = self.onlineUsers[chat.user.uid] || false;
                chat.$window.find('.tbp-chat-status').toggleClass('online', isOnline);
                chat.$window.find('.tbp-chat-user-status').text(
                    isOnline ? tbpMessaging.i18n.online : tbpMessaging.i18n.offline
                );
            });

            // Update online count
            var count = Object.keys(this.onlineUsers).length;
            $('#tbp-online-count').text(count);
        },

        /**
         * Listen to user's conversations for unread badges
         */
        listenToConversations: function() {
            var self = this;
            if (!this.db) return;

            this.db.collection('conversations')
                .where('participants', 'array-contains', this.currentUser.uid)
                .onSnapshot(function(snapshot) {
                    var unreadTotal = 0;
                    // TODO: Calculate unread messages
                    self.updateUnreadBadge(unreadTotal);
                });
        },

        /**
         * Update unread badge on FAB
         */
        updateUnreadBadge: function(count) {
            var $badge = $('.tbp-messaging-fab-badge');
            if (count > 0) {
                $badge.text(count > 99 ? '99+' : count).show();
            } else {
                $badge.hide();
            }
        },

        /**
         * Scroll to bottom of messages
         */
        scrollToBottom: function($window) {
            var $messages = $window.find('.tbp-chat-messages');
            $messages.scrollTop($messages[0].scrollHeight);
        },

        /**
         * Format timestamp
         */
        formatTime: function(date) {
            var now = new Date();
            var diff = now - date;
            var minutes = Math.floor(diff / 60000);
            var hours = Math.floor(diff / 3600000);

            if (minutes < 1) return tbpMessaging.i18n.justNow;
            if (minutes < 60) return minutes + 'm';
            if (hours < 24) return hours + 'h';

            return date.toLocaleDateString();
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        TBPMessaging.init();
    });

})(jQuery);
