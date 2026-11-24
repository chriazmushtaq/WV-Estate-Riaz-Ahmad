(function($){
    'use strict';

    $(function(){
        var $body = $('body');
        var $sidebar = $('#sidebar-main');
        var $input = $('#adminSidebarSearch');
        var desktopBreakpoint = window.matchMedia('(min-width: 768px)');

        function syncCollapsedState(forceExpanded) {
            if(forceExpanded === true) {
                $sidebar.removeClass('sidebar-collapsed');
                $body.removeClass('wv-admin-sidebar-collapsed');
                return;
            }
            if($sidebar.hasClass('sidebar-collapsed')) {
                $body.addClass('wv-admin-sidebar-collapsed');
            } else {
                $body.removeClass('wv-admin-sidebar-collapsed');
            }
        }

        $('#toggle-sidebar, #toggle-sidebar-desktop').on('click', function(e){
            e.preventDefault();
            if(desktopBreakpoint.matches) {
                $sidebar.toggleClass('sidebar-collapsed');
                syncCollapsedState();
            } else {
                $body.toggleClass('wv-admin-sidebar-open');
            }
        });

        $input.on('input', function(){
            var term = $.trim($(this).val()).toLowerCase();
            $('#sidebar-main .navigation > li').each(function(){
                var $li = $(this);
                var haystack = $.trim($li.text()).toLowerCase();
                var matched = term === '' || haystack.indexOf(term) !== -1;
                $li.toggle(matched);
            });
        });

        $(document).on('click', function(e){
            if(!desktopBreakpoint.matches && $body.hasClass('wv-admin-sidebar-open')) {
                if($(e.target).closest('#sidebar-main, #toggle-sidebar').length === 0) {
                    $body.removeClass('wv-admin-sidebar-open');
                }
            }
        });

        syncCollapsedState(false);
    });
})(jQuery);
