/**
 * Slinky Menu Widget JavaScript
 * Accessible sliding navigation menu with tabs support
 */

(function() {
    'use strict';

    class TBPSlinkyMenu {
        constructor(element) {
            this.el = element;
            this.isTabbed = element.classList.contains('tbp-slinky--tabbed');

            // For tabbed menus, we manage multiple wrappers
            if (this.isTabbed) {
                this.panels = element.querySelectorAll('.tbp-slinky__panel');
                this.tabs = element.querySelectorAll('.tbp-slinky__tab');
                this.activePanel = element.querySelector('.tbp-slinky__panel--active');
                this.wrapper = this.activePanel ? this.activePanel.querySelector('.tbp-slinky__wrapper') : null;
                this.rootList = this.wrapper ? this.wrapper.querySelector('.tbp-slinky__list--root') : null;
            } else {
                this.wrapper = element.querySelector('.tbp-slinky__wrapper');
                this.rootList = element.querySelector('.tbp-slinky__list--root');
            }

            if (!this.wrapper || !this.rootList) {
                return;
            }

            // Settings from data attributes
            this.speed = parseInt(element.dataset.speed, 10) || 300;
            this.showParentLink = element.dataset.showParentLink === 'true';

            // State
            this.currentList = this.rootList;
            this.history = [];
            this.isAnimating = false;

            // Bind methods
            this.handleNextClick = this.handleNextClick.bind(this);
            this.handleBackClick = this.handleBackClick.bind(this);
            this.handleKeydown = this.handleKeydown.bind(this);
            this.handleTabClick = this.handleTabClick.bind(this);

            this.init();
        }

        init() {
            // Set initial height
            this.updateHeight();

            // Attach event listeners
            this.attachEvents();

            // Mark as initialized
            this.el.classList.add('tbp-slinky--initialized');
        }

        attachEvents() {
            // Next buttons (submenu triggers)
            this.el.querySelectorAll('.tbp-slinky__next').forEach(btn => {
                btn.addEventListener('click', this.handleNextClick);
            });

            // Back buttons
            this.el.querySelectorAll('.tbp-slinky__back').forEach(btn => {
                btn.addEventListener('click', this.handleBackClick);
            });

            // Tab buttons
            if (this.isTabbed && this.tabs) {
                this.tabs.forEach(tab => {
                    tab.addEventListener('click', this.handleTabClick);
                });
            }

            // Keyboard navigation
            this.el.addEventListener('keydown', this.handleKeydown);

            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => this.updateHeight(), 100);
            });
        }

        handleTabClick(e) {
            e.preventDefault();

            const clickedTab = e.currentTarget;
            if (clickedTab.classList.contains('tbp-slinky__tab--active')) {
                return;
            }

            const panelId = clickedTab.getAttribute('aria-controls');
            const newPanel = document.getElementById(panelId);

            if (!newPanel) {
                return;
            }

            // Update tabs
            this.tabs.forEach(tab => {
                tab.classList.remove('tbp-slinky__tab--active');
                tab.setAttribute('aria-selected', 'false');
            });
            clickedTab.classList.add('tbp-slinky__tab--active');
            clickedTab.setAttribute('aria-selected', 'true');

            // Update panels
            this.panels.forEach(panel => {
                panel.classList.remove('tbp-slinky__panel--active');
                panel.setAttribute('hidden', '');
            });
            newPanel.classList.add('tbp-slinky__panel--active');
            newPanel.removeAttribute('hidden');

            // Reset the previous panel's slinky state
            if (this.activePanel && this.activePanel !== newPanel) {
                this.resetPanelState(this.activePanel);
            }

            // Update references for the new panel
            this.activePanel = newPanel;
            this.wrapper = newPanel.querySelector('.tbp-slinky__wrapper');
            this.rootList = this.wrapper ? this.wrapper.querySelector('.tbp-slinky__list--root') : null;
            this.currentList = this.rootList;
            this.history = [];

            // Update height for new panel
            this.updateHeight();
        }

        resetPanelState(panel) {
            const wrapper = panel.querySelector('.tbp-slinky__wrapper');
            const rootList = wrapper ? wrapper.querySelector('.tbp-slinky__list--root') : null;

            if (!rootList) return;

            // Reset transform
            rootList.style.transform = '';

            // Remove active/slide classes from all sublists
            panel.querySelectorAll('.tbp-slinky__list--sub').forEach(list => {
                list.classList.remove('tbp-slinky__list--active');
            });

            panel.querySelectorAll('.tbp-slinky__list').forEach(list => {
                list.classList.remove('tbp-slinky__list--slide-left');
            });

            // Reset ARIA states
            panel.querySelectorAll('.tbp-slinky__next').forEach(btn => {
                btn.setAttribute('aria-expanded', 'false');
            });

            // Reset wrapper height
            if (wrapper) {
                wrapper.style.height = '';
            }
        }

        handleNextClick(e) {
            e.preventDefault();
            e.stopPropagation();

            if (this.isAnimating) return;

            const button = e.currentTarget;
            const item = button.closest('.tbp-slinky__item');
            const submenu = item.querySelector('.tbp-slinky__list--sub');

            if (!submenu) return;

            this.goToSubmenu(submenu, button);
        }

        handleBackClick(e) {
            e.preventDefault();
            e.stopPropagation();

            if (this.isAnimating) return;

            this.goBack();
        }

        handleKeydown(e) {
            const focusedElement = document.activeElement;

            // Only handle if focus is within the menu
            if (!this.el.contains(focusedElement)) return;

            // Tab navigation with arrow keys
            if (focusedElement.classList.contains('tbp-slinky__tab')) {
                this.handleTabKeydown(e, focusedElement);
                return;
            }

            switch (e.key) {
                case 'ArrowRight':
                    // If on a parent item, go to submenu
                    if (focusedElement.classList.contains('tbp-slinky__next')) {
                        e.preventDefault();
                        focusedElement.click();
                    } else {
                        const item = focusedElement.closest('.tbp-slinky__item');
                        if (item && item.classList.contains('tbp-slinky__item--has-children')) {
                            const nextBtn = item.querySelector('.tbp-slinky__next');
                            if (nextBtn) {
                                e.preventDefault();
                                nextBtn.click();
                            }
                        }
                    }
                    break;

                case 'ArrowLeft':
                    // Go back
                    if (this.history.length > 0) {
                        e.preventDefault();
                        this.goBack();
                    }
                    break;

                case 'ArrowDown':
                    e.preventDefault();
                    this.focusNextItem(focusedElement);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    this.focusPreviousItem(focusedElement);
                    break;

                case 'Home':
                    e.preventDefault();
                    this.focusFirstItem();
                    break;

                case 'End':
                    e.preventDefault();
                    this.focusLastItem();
                    break;

                case 'Escape':
                    if (this.history.length > 0) {
                        e.preventDefault();
                        this.goBack();
                    }
                    break;
            }
        }

        handleTabKeydown(e, focusedTab) {
            const tabList = Array.from(this.tabs).filter(tab =>
                getComputedStyle(tab).display !== 'none'
            );
            const currentIndex = tabList.indexOf(focusedTab);

            switch (e.key) {
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    const prevIndex = currentIndex - 1 < 0 ? tabList.length - 1 : currentIndex - 1;
                    tabList[prevIndex].focus();
                    break;

                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    const nextIndex = (currentIndex + 1) % tabList.length;
                    tabList[nextIndex].focus();
                    break;

                case 'Home':
                    e.preventDefault();
                    tabList[0].focus();
                    break;

                case 'End':
                    e.preventDefault();
                    tabList[tabList.length - 1].focus();
                    break;

                case 'Enter':
                case ' ':
                    e.preventDefault();
                    focusedTab.click();
                    break;
            }
        }

        goToSubmenu(submenu, triggerButton) {
            this.isAnimating = true;
            this.el.classList.add('tbp-slinky--animating');

            // Save current state
            this.history.push({
                list: this.currentList,
                button: triggerButton
            });

            // Update ARIA
            triggerButton.setAttribute('aria-expanded', 'true');

            // Activate the submenu
            submenu.classList.add('tbp-slinky__list--active');

            // Slide current list left
            this.currentList.classList.add('tbp-slinky__list--slide-left');

            // Position submenu and slide it in
            const currentTransform = this.getTranslateX(this.rootList);
            this.rootList.style.transform = `translateX(${currentTransform - 100}%)`;

            // Update current list reference
            this.currentList = submenu;

            // Update height after a small delay to allow DOM update
            requestAnimationFrame(() => {
                this.updateHeight(submenu);
            });

            // Focus first item in submenu after animation
            setTimeout(() => {
                this.isAnimating = false;
                this.el.classList.remove('tbp-slinky--animating');

                // Focus first focusable item in submenu
                const firstItem = submenu.querySelector('.tbp-slinky__back, .tbp-slinky__item-link, .tbp-slinky__item-text');
                if (firstItem) {
                    firstItem.focus();
                }
            }, this.speed);
        }

        goBack() {
            if (this.history.length === 0 || this.isAnimating) return;

            this.isAnimating = true;
            this.el.classList.add('tbp-slinky--animating');

            const previous = this.history.pop();
            const previousList = previous.list;
            const triggerButton = previous.button;

            // Update ARIA
            triggerButton.setAttribute('aria-expanded', 'false');

            // Slide root back
            const newTransform = this.history.length * -100;
            this.rootList.style.transform = newTransform === 0 ? '' : `translateX(${newTransform}%)`;

            // Remove slide class from previous list
            previousList.classList.remove('tbp-slinky__list--slide-left');

            // Update height
            this.updateHeight(previousList);

            // Deactivate current submenu after animation
            const currentSubmenu = this.currentList;

            setTimeout(() => {
                currentSubmenu.classList.remove('tbp-slinky__list--active');
                this.currentList = previousList;
                this.isAnimating = false;
                this.el.classList.remove('tbp-slinky--animating');

                // Focus the trigger button
                triggerButton.focus();
            }, this.speed);
        }

        goToRoot() {
            while (this.history.length > 0) {
                const previous = this.history.pop();
                previous.button.setAttribute('aria-expanded', 'false');
                previous.list.classList.remove('tbp-slinky__list--slide-left');
            }

            this.rootList.style.transform = '';

            // Deactivate all submenus
            this.el.querySelectorAll('.tbp-slinky__list--sub').forEach(list => {
                list.classList.remove('tbp-slinky__list--active');
            });

            this.currentList = this.rootList;
            this.updateHeight();
        }

        updateHeight(targetList = null) {
            const list = targetList || this.currentList;
            if (!list || !this.wrapper) return;

            // For submenus, we need to temporarily make them visible to measure
            const isSubmenu = list.classList.contains('tbp-slinky__list--sub');
            let wasHidden = false;

            if (isSubmenu && !list.classList.contains('tbp-slinky__list--active')) {
                list.style.visibility = 'visible';
                list.style.opacity = '1';
                list.style.position = 'relative';
                list.style.left = '0';
                wasHidden = true;
            }

            // Get the items in the list and calculate height
            let height = 0;
            const items = list.children;
            for (let i = 0; i < items.length; i++) {
                height += items[i].offsetHeight;
            }

            // Reset temporary styles
            if (wasHidden) {
                list.style.visibility = '';
                list.style.opacity = '';
                list.style.position = '';
                list.style.left = '';
            }

            // Use scrollHeight as fallback
            if (height === 0) {
                height = list.scrollHeight;
            }

            this.wrapper.style.height = height + 'px';
        }

        getTranslateX(element) {
            const transform = element.style.transform;
            if (!transform) return 0;

            const match = transform.match(/translateX\((-?\d+)%\)/);
            return match ? parseInt(match[1], 10) : 0;
        }

        focusNextItem(currentElement) {
            const items = this.getFocusableItems();
            const currentIndex = items.indexOf(currentElement);
            const nextIndex = (currentIndex + 1) % items.length;
            items[nextIndex]?.focus();
        }

        focusPreviousItem(currentElement) {
            const items = this.getFocusableItems();
            const currentIndex = items.indexOf(currentElement);
            const prevIndex = currentIndex - 1 < 0 ? items.length - 1 : currentIndex - 1;
            items[prevIndex]?.focus();
        }

        focusFirstItem() {
            const items = this.getFocusableItems();
            items[0]?.focus();
        }

        focusLastItem() {
            const items = this.getFocusableItems();
            items[items.length - 1]?.focus();
        }

        getFocusableItems() {
            // Get focusable items in the current visible list
            const visibleList = this.currentList;
            const focusable = visibleList.querySelectorAll(
                '.tbp-slinky__back, .tbp-slinky__item-link, .tbp-slinky__item-text[tabindex="0"], .tbp-slinky__next'
            );
            return Array.from(focusable);
        }

        destroy() {
            // Remove event listeners
            this.el.querySelectorAll('.tbp-slinky__next').forEach(btn => {
                btn.removeEventListener('click', this.handleNextClick);
            });

            this.el.querySelectorAll('.tbp-slinky__back').forEach(btn => {
                btn.removeEventListener('click', this.handleBackClick);
            });

            if (this.tabs) {
                this.tabs.forEach(tab => {
                    tab.removeEventListener('click', this.handleTabClick);
                });
            }

            this.el.removeEventListener('keydown', this.handleKeydown);

            // Reset state
            this.goToRoot();
            if (this.wrapper) {
                this.wrapper.style.height = '';
            }
            this.el.classList.remove('tbp-slinky--initialized');
        }
    }

    // Initialize all Slinky menus
    function initSlinkyMenus() {
        document.querySelectorAll('.tbp-slinky:not(.tbp-slinky--initialized)').forEach(menu => {
            new TBPSlinkyMenu(menu);
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSlinkyMenus);
    } else {
        initSlinkyMenus();
    }

    // Re-initialize when Elementor frontend is ready (for editor preview)
    if (typeof elementorFrontend !== 'undefined') {
        jQuery(window).on('elementor/frontend/init', function() {
            elementorFrontend.hooks.addAction('frontend/element_ready/tbp-slinky-menu.default', function($element) {
                const menu = $element.find('.tbp-slinky')[0];
                if (menu) {
                    // Destroy existing instance if any
                    if (menu.classList.contains('tbp-slinky--initialized')) {
                        menu.classList.remove('tbp-slinky--initialized');
                    }
                    new TBPSlinkyMenu(menu);
                }
            });
        });
    }

    // Expose for external use
    window.TBPSlinkyMenu = TBPSlinkyMenu;

})();
