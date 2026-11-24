(function($){
    'use strict';

    $(function(){
        var $topbarSearch = $('#adminTopbarSearch');
        var $topbarSearchBtn = $('#adminTopbarSearchBtn');
        var $topbarSearchWrap = $topbarSearch.closest('.wv-admin-quick-search');
        var $sidebarSearch = $('#adminSidebarSearch');

        function setPageSearchState(stateName) {
            $topbarSearchWrap.removeClass('page-search-empty page-search-miss');
            if(stateName) {
                $topbarSearchWrap.addClass(stateName);
            }
        }

        function performPageSearch(backward) {
            var term = $.trim($topbarSearch.val());
            if(term === '') {
                setPageSearchState('page-search-empty');
                $topbarSearch.trigger('focus');
                return false;
            }
            setPageSearchState('');

            if(typeof window.find === 'function') {
                var found = window.find(term, false, !!backward, true, false, false, false);
                if(!found) {
                    setPageSearchState('page-search-miss');
                }
                return found;
            }

            var haystack = '';
            try {
                haystack = (($('.content').text() || '') + ' ' + ($('.page-title').text() || '')).toLowerCase();
            } catch (ex) {
                haystack = '';
            }
            var foundFallback = haystack.indexOf(term.toLowerCase()) !== -1;
            if(!foundFallback) {
                setPageSearchState('page-search-miss');
            }
            return foundFallback;
        }

        function filterSidebarMenu(inputEl) {
            var $input = inputEl ? $(inputEl) : $('#adminSidebarSearch');
            if (!$input.length) {
                $input = $('.wv-admin-sidebar-search');
            }
            if (!$input.length) {
                return;
            }
            var term = $.trim($input.val()).toLowerCase();
            var $clearBtn = $('#sidebarSearchClear');
            if ($clearBtn.length) {
                $clearBtn.toggle(term.length > 0);
            }

            var $nav = $('#sidebar-main .navigation');
            if (!$nav.length) {
                return;
            }

            // Remove previous no-results message
            $nav.find('.wv-sidebar-no-results').remove();

            if (term === '') {
                $nav.find('li').removeClass('menu-item-hidden').show();
                $nav.find('li').children('ul').each(function(){
                    var $ul = $(this);
                    var $parentLi = $ul.closest('li');
                    var wasPreOpen = $parentLi.data('pre-search-open');
                    var isActive = $parentLi.hasClass('active') || $ul.find('li.active').length > 0;

                    if (wasPreOpen === true || (wasPreOpen === undefined && isActive && !$parentLi.data('search-opened'))) {
                        $ul.removeClass('hidden-ul').show();
                        $parentLi.addClass('open');
                    } else {
                        $ul.addClass('hidden-ul').hide();
                        $parentLi.removeClass('open');
                    }
                    $parentLi.removeData('pre-search-open');
                    $parentLi.removeData('search-opened');
                });
                return;
            }

            var totalMatched = 0;

            $nav.children('li').each(function(){
                var $li = $(this);
                var $a = $li.children('a');
                if (!$a.length) {
                    return;
                }

                var parentText = $.trim($a.find('span').text() || $a.text()).toLowerCase();
                var $subUl = $li.children('ul');
                var hasChildren = $subUl.length > 0;
                var parentMatches = parentText.indexOf(term) !== -1;
                var childMatchesCount = 0;

                if (hasChildren) {
                    if ($li.data('pre-search-open') === undefined) {
                        var isCurrentlyOpen = !$subUl.hasClass('hidden-ul') && $subUl.is(':visible');
                        $li.data('pre-search-open', isCurrentlyOpen);
                    }

                    var $children = $subUl.children('li');
                    $children.each(function(){
                        var $childLi = $(this);
                        var childText = $.trim($childLi.find('span').text() || $childLi.text()).toLowerCase();
                        if (parentMatches || childText.indexOf(term) !== -1) {
                            $childLi.removeClass('menu-item-hidden').show();
                            childMatchesCount++;
                        } else {
                            $childLi.addClass('menu-item-hidden').hide();
                        }
                    });
                }

                var isMatched = parentMatches || (childMatchesCount > 0);

                if (isMatched) {
                    totalMatched++;
                    $li.removeClass('menu-item-hidden').show();
                    if (hasChildren) {
                        if (!$li.hasClass('open')) {
                            $li.data('search-opened', true);
                        }
                        $li.addClass('open');
                        $subUl.removeClass('hidden-ul').show();
                    }
                } else {
                    $li.addClass('menu-item-hidden').hide();
                    if (hasChildren && $li.data('search-opened')) {
                        $subUl.addClass('hidden-ul').hide();
                        $li.removeClass('open');
                    }
                }
            });

            if (totalMatched === 0) {
                $('<li class="wv-sidebar-no-results" style="padding:12px 14px;color:#94a3b8;font-size:12px;text-align:center;list-style:none;">' +
                  '<i class="fa fa-info-circle" style="margin-right:6px;color:#60a5fa;"></i> ' +
                  'No matching menu' +
                  '</li>').appendTo($nav);
            }
        }

        $('[data-admin-fullscreen]').on('click', function(){
            var doc = document;
            var el = document.documentElement;
            if(!doc.fullscreenElement && el.requestFullscreen){
                el.requestFullscreen();
            } else if(doc.exitFullscreen) {
                doc.exitFullscreen();
            }
        });

        $topbarSearch.on('input', function(){
            setPageSearchState('');
        });

        $topbarSearch.on('keydown', function(e){
            if(e.keyCode === 13) {
                e.preventDefault();
                performPageSearch(e.shiftKey);
            }
        });

        $topbarSearchBtn.on('click', function(e){
            e.preventDefault();
            performPageSearch(false);
        });

        $(document).off('input.adminSidebarSearch keyup.adminSidebarSearch change.adminSidebarSearch paste.adminSidebarSearch search.adminSidebarSearch', '#adminSidebarSearch, .wv-admin-sidebar-search')
            .on('input.adminSidebarSearch keyup.adminSidebarSearch change.adminSidebarSearch paste.adminSidebarSearch search.adminSidebarSearch', '#adminSidebarSearch, .wv-admin-sidebar-search', function(){
                filterSidebarMenu(this);
            });

        $(document).off('click.adminSidebarClear', '#sidebarSearchClear')
            .on('click.adminSidebarClear', '#sidebarSearchClear', function(e){
                e.preventDefault();
                var $input = $('#adminSidebarSearch');
                if(!$input.length){ $input = $('.wv-admin-sidebar-search'); }
                $input.val('').trigger('input').focus();
            });

        $(document).off('keydown.adminSidebarSearchEsc', '#adminSidebarSearch, .wv-admin-sidebar-search')
            .on('keydown.adminSidebarSearchEsc', '#adminSidebarSearch, .wv-admin-sidebar-search', function(e){
                if(e.keyCode === 27){
                    e.preventDefault();
                    $(this).val('').trigger('input');
                }
            });

        $('#toggle-sidebar, #toggle-sidebar-desktop, #sidebar-inline-toggle').on('click.adminSidebarSearchFocus', function(){
            setTimeout(function(){
                filterSidebarMenu($('#adminSidebarSearch').get(0));
            }, 80);
        });

        if($('#adminSidebarSearch').val()) {
            filterSidebarMenu($('#adminSidebarSearch').get(0));
        }
    });
})(jQuery);
