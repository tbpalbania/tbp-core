(function($) {
    'use strict';

    $(document).ready(function() {
        initAlphabetFilter();
    });

    function initAlphabetFilter() {
        $('.tbp-alphabet-filter').each(function() {
            var $filter = $(this);
            var method = $filter.data('method') || 'url';

            if (method === 'ajax') {
                initAjaxFilter($filter);
            } else {
                initUrlFilter($filter);
            }
        });
    }

    function initUrlFilter($filter) {
        var urlParams = new URLSearchParams(window.location.search);
        var currentLetter = urlParams.get('letter');
        var currentSearch = urlParams.get('search');

        // Update active state for letter
        if (currentLetter) {
            $filter.find('.tbp-alphabet-letter, .tbp-alphabet-all').removeClass('is-active');
            $filter.find('.tbp-alphabet-letter[data-letter="' + currentLetter.toUpperCase() + '"]').addClass('is-active');
        }

        // Handle search input - submit on enter only
        $filter.find('.tbp-alphabet-search input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitUrlFilter($filter);
            }
        });

        // Handle clear button
        $filter.find('.tbp-alphabet-clear').on('click', function(e) {
            e.preventDefault();
            $filter.find('.tbp-alphabet-search input').val('');
            window.location.href = $(this).attr('href');
        });
    }

    function submitUrlFilter($filter) {
        var searchVal = $filter.find('.tbp-alphabet-search input').val().trim();
        var url = new URL(window.location);

        // Get current letter from active state
        var $activeLetter = $filter.find('.tbp-alphabet-letter.is-active');
        var currentLetter = $activeLetter.length ? $activeLetter.data('letter') : '';

        if (searchVal) {
            url.searchParams.set('search', searchVal);
        } else {
            url.searchParams.delete('search');
        }

        if (currentLetter) {
            url.searchParams.set('letter', currentLetter.toLowerCase());
        }

        window.location.href = url.toString();
    }

    function initAjaxFilter($filter) {
        var $target = $($filter.data('target'));
        var postType = $filter.data('post-type') || 'post';
        var taxonomy = $filter.data('taxonomy') || '';
        var term = $filter.data('term') || '';
        var currentLetter = '';
        var currentSearch = '';

        if (!$target.length) {
            console.warn('Alphabet Filter: Target container not found:', $filter.data('target'));
            return;
        }

        // Handle letter clicks
        $filter.on('click', '.tbp-alphabet-letter:not(.is-inactive)', function(e) {
            e.preventDefault();

            var $this = $(this);
            currentLetter = $this.data('letter') || '';

            // Update active state
            $filter.find('.tbp-alphabet-letter').removeClass('is-active');
            $filter.find('.tbp-alphabet-all').removeClass('is-active');
            $this.addClass('is-active');

            // Update URL without reload
            updateURL(currentLetter, currentSearch);

            // Fetch filtered posts
            fetchPosts($filter, $target, currentLetter, currentSearch, postType, taxonomy, term);
        });

        // Handle All button
        $filter.on('click', '.tbp-alphabet-all', function(e) {
            e.preventDefault();

            currentLetter = '';

            // Update active state
            $filter.find('.tbp-alphabet-letter').removeClass('is-active');
            $filter.find('.tbp-alphabet-all').addClass('is-active');

            // Update URL without reload
            updateURL(currentLetter, currentSearch);

            // Fetch filtered posts
            fetchPosts($filter, $target, currentLetter, currentSearch, postType, taxonomy, term);
        });

        // Handle Clear button
        $filter.on('click', '.tbp-alphabet-clear', function(e) {
            e.preventDefault();

            currentLetter = '';
            currentSearch = '';

            // Clear search input
            $filter.find('.tbp-alphabet-search input').val('');

            // Update active state
            $filter.find('.tbp-alphabet-letter').removeClass('is-active');
            $filter.find('.tbp-alphabet-all').addClass('is-active');

            // Update URL without reload
            updateURL(currentLetter, currentSearch);

            // Fetch filtered posts
            fetchPosts($filter, $target, currentLetter, currentSearch, postType, taxonomy, term);
        });

        // Handle search input - submit on enter only
        $filter.find('.tbp-alphabet-search input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                currentSearch = $(this).val().trim();

                // Update URL without reload
                updateURL(currentLetter, currentSearch);

                // Fetch filtered posts
                fetchPosts($filter, $target, currentLetter, currentSearch, postType, taxonomy, term);
            }
        });
    }

    function updateURL(letter, search) {
        var url = new URL(window.location);

        if (letter) {
            url.searchParams.set('letter', letter.toLowerCase());
        } else {
            url.searchParams.delete('letter');
        }

        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        window.history.pushState({}, '', url);
    }

    function fetchPosts($filter, $target, letter, search, postType, taxonomy, term) {
        $filter.addClass('is-loading');
        $target.addClass('is-loading');

        $.ajax({
            url: tbpAlphabetFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tbp_alphabet_filter',
                nonce: tbpAlphabetFilter.nonce,
                letter: letter,
                search: search,
                post_type: postType,
                taxonomy: taxonomy,
                term: term
            },
            success: function(response) {
                if (response.success) {
                    renderPosts($target, response.data.posts);
                }
            },
            error: function(xhr, status, error) {
                console.error('Alphabet Filter AJAX error:', error);
            },
            complete: function() {
                $filter.removeClass('is-loading');
                $target.removeClass('is-loading');
            }
        });
    }

    function renderPosts($target, posts) {
        // Try to detect the template structure from existing content
        var $existingItems = $target.find('article, .elementor-post, .e-loop-item');
        var template = 'default';

        if ($existingItems.length && $existingItems.first().hasClass('elementor-post')) {
            template = 'elementor-posts';
        } else if ($existingItems.length && $existingItems.first().hasClass('e-loop-item')) {
            template = 'elementor-loop';
        }

        // Clear container
        $target.empty();

        if (posts.length === 0) {
            $target.html('<p class="tbp-no-results">No results found.</p>');
            return;
        }

        // Render posts based on detected template
        posts.forEach(function(post) {
            var html = '';

            switch (template) {
                case 'elementor-posts':
                    html = renderElementorPost(post);
                    break;
                case 'elementor-loop':
                    html = renderElementorLoopItem(post);
                    break;
                default:
                    html = renderDefaultPost(post);
            }

            $target.append(html);
        });

        // Trigger event for other scripts
        $(document).trigger('tbp_alphabet_filter_updated', [posts, $target]);
    }

    function renderDefaultPost(post) {
        return '<article class="tbp-filtered-post">' +
            '<h3><a href="' + post.url + '">' + post.title + '</a></h3>' +
            (post.excerpt ? '<p>' + post.excerpt + '</p>' : '') +
            '</article>';
    }

    function renderElementorPost(post) {
        var html = '<article class="elementor-post elementor-grid-item">';

        if (post.thumbnail) {
            html += '<div class="elementor-post__thumbnail">';
            html += '<a href="' + post.url + '"><img src="' + post.thumbnail + '" alt="' + post.title + '"></a>';
            html += '</div>';
        }

        html += '<div class="elementor-post__text">';
        html += '<h3 class="elementor-post__title"><a href="' + post.url + '">' + post.title + '</a></h3>';

        if (post.excerpt) {
            html += '<div class="elementor-post__excerpt"><p>' + post.excerpt + '</p></div>';
        }

        html += '</div>';
        html += '</article>';

        return html;
    }

    function renderElementorLoopItem(post) {
        return '<div class="e-loop-item">' +
            '<div class="e-loop-item__content">' +
            '<h3><a href="' + post.url + '">' + post.title + '</a></h3>' +
            (post.excerpt ? '<p>' + post.excerpt + '</p>' : '') +
            '</div>' +
            '</div>';
    }

})(jQuery);
