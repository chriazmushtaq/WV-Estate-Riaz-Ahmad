(function($){
    'use strict';

    function setCookie(name, value, days) {
        var expires = '';
        if(days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    function applyTheme(themeValue) {
        var $body = $('body.wv-admin-theme');
        if($body.length === 0) {
            return;
        }
        $body.removeClass('theme-light theme-dark theme-gold theme-green').addClass(themeValue).attr('data-admin-theme', themeValue);
        $('[data-admin-theme-selector] .wv-admin-theme-chip').removeClass('active')
            .filter('[data-theme-value="' + themeValue + '"]').addClass('active');

        var cookieName = $body.attr('data-admin-theme-cookie');
        if(cookieName) {
            setCookie(cookieName, themeValue, 365);
        }
    }

    $(function(){
        var activeTheme = $('body.wv-admin-theme').attr('data-admin-theme');
        if(activeTheme) {
            applyTheme(activeTheme);
        }
        $(document).on('click', '[data-admin-theme-selector] .wv-admin-theme-chip', function(e){
            e.preventDefault();
            var themeValue = $(this).attr('data-theme-value');
            applyTheme(themeValue);
            $('#ThemeSettingAdminDefaultTheme').val(themeValue);
        });
    });
})(jQuery);
