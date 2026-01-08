/**
 * TBP Google Integration
 *
 * Firebase initialization and authentication
 *
 * @package TBP_Core
 */

(function() {
    'use strict';

    // Global TBP Firebase instance
    window.TBPFirebase = {
        app: null,
        auth: null,
        db: null,
        currentUser: null,
        initialized: false,
        onReadyCallbacks: [],

        /**
         * Initialize Firebase
         */
        init: function() {
            if (this.initialized || !tbpGoogle.configured.firebase) {
                return Promise.resolve(false);
            }

            if (typeof firebase === 'undefined') {
                console.error('TBPFirebase: Firebase SDK not loaded');
                return Promise.reject('Firebase SDK not loaded');
            }

            try {
                // Initialize Firebase app
                this.app = firebase.initializeApp(tbpGoogle.firebase);
                this.auth = firebase.auth();
                this.db = firebase.firestore();
                this.initialized = true;

                console.log('TBPFirebase: Initialized');

                // Auto-authenticate with custom token
                return this.authenticateWithWordPress();
            } catch (error) {
                console.error('TBPFirebase: Init error', error);
                return Promise.reject(error);
            }
        },

        /**
         * Authenticate with WordPress custom token
         */
        authenticateWithWordPress: function() {
            var self = this;

            return new Promise(function(resolve, reject) {
                // Get custom token from WordPress
                fetch(tbpGoogle.restUrl + 'firebase-token', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-WP-Nonce': tbpGoogle.restNonce,
                    },
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.token) {
                        return self.auth.signInWithCustomToken(data.token);
                    }
                    throw new Error('No token received');
                })
                .then(function(userCredential) {
                    self.currentUser = userCredential.user;
                    console.log('TBPFirebase: Authenticated as', self.currentUser.uid);

                    // Run ready callbacks
                    self.onReadyCallbacks.forEach(function(cb) {
                        cb(self);
                    });

                    resolve(self.currentUser);
                })
                .catch(function(error) {
                    console.error('TBPFirebase: Auth error', error);
                    reject(error);
                });
            });
        },

        /**
         * Register callback for when Firebase is ready
         */
        onReady: function(callback) {
            if (this.initialized && this.currentUser) {
                callback(this);
            } else {
                this.onReadyCallbacks.push(callback);
            }
        },

        /**
         * Get Firestore collection reference
         */
        collection: function(path) {
            if (!this.db) {
                console.error('TBPFirebase: Firestore not initialized');
                return null;
            }
            return this.db.collection(path);
        },

        /**
         * Get Firestore document reference
         */
        doc: function(path) {
            if (!this.db) {
                console.error('TBPFirebase: Firestore not initialized');
                return null;
            }
            return this.db.doc(path);
        },

        /**
         * Get current user ID
         */
        getUserId: function() {
            return this.currentUser ? this.currentUser.uid : null;
        },

        /**
         * Get WordPress user ID from Firebase UID
         */
        getWPUserId: function() {
            return tbpGoogle.userId;
        },

        /**
         * Sign out
         */
        signOut: function() {
            if (this.auth) {
                return this.auth.signOut();
            }
            return Promise.resolve();
        }
    };

    // Google OAuth helper
    window.TBPGoogleOAuth = {
        /**
         * Start OAuth flow
         */
        connect: function(scopes, redirectTo) {
            scopes = scopes || ['openid', 'email', 'profile'];
            redirectTo = redirectTo || window.location.href;

            return new Promise(function(resolve, reject) {
                jQuery.ajax({
                    url: tbpGoogle.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tbp_google_oauth_init',
                        nonce: tbpGoogle.nonce,
                        scopes: scopes,
                        redirect_to: redirectTo,
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.auth_url;
                            resolve();
                        } else {
                            reject(response.data.message);
                        }
                    },
                    error: function() {
                        reject('Request failed');
                    }
                });
            });
        },

        /**
         * Connect with Drive scope
         */
        connectDrive: function(redirectTo) {
            return this.connect([
                'openid',
                'email',
                'profile',
                'https://www.googleapis.com/auth/drive.file',
                'https://www.googleapis.com/auth/drive.readonly',
            ], redirectTo);
        },

        /**
         * Connect with Calendar scope
         */
        connectCalendar: function(redirectTo) {
            return this.connect([
                'openid',
                'email',
                'profile',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events',
            ], redirectTo);
        },

        /**
         * Get fresh access token
         */
        getToken: function() {
            return new Promise(function(resolve, reject) {
                jQuery.ajax({
                    url: tbpGoogle.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'tbp_google_refresh_token',
                        nonce: tbpGoogle.nonce,
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data.access_token);
                        } else {
                            reject(response.data.message);
                        }
                    },
                    error: function() {
                        reject('Request failed');
                    }
                });
            });
        }
    };

    // Auto-initialize Firebase when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        if (tbpGoogle.configured.firebase) {
            TBPFirebase.init().catch(function(error) {
                console.warn('TBPFirebase: Auto-init failed', error);
            });
        }
    });

})();
