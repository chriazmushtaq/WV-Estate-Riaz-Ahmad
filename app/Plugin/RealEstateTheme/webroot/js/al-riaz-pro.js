(function ($) {
    "use strict";

    $(function () {
        // Counter Controls with Event Delegation
        function updateVisual() {
            $('.rooms-visual').text(parseInt($('.js-rooms').val()) || 1);
            $('.adults-visual').text(parseInt($('.js-adults').val()) || 1);
            $('.children-visual').text(parseInt($('.js-children').val()) || 0);
        }

        $(document).on('click', '.rooms-b__control_plus, .rooms-b__control_minus, .adults-b__control_plus, .adults-b__control_minus, .children-b__control_plus, .children-b__control_minus', function () {
            var $btn = $(this);
            var isPlus = $btn.hasClass('rooms-b__control_plus') || $btn.hasClass('adults-b__control_plus') || $btn.hasClass('children-b__control_plus');
            var targetClass = $btn.hasClass('rooms-b__control_plus') || $btn.hasClass('rooms-b__control_minus') ? '.js-rooms' :
                             ($btn.hasClass('adults-b__control_plus') || $btn.hasClass('adults-b__control_minus') ? '.js-adults' : '.js-children');
            var minVal = targetClass === '.js-children' ? 0 : 1;
            var $input = $(targetClass);
            var currentVal = parseInt($input.val()) || minVal;

            if (isPlus) {
                $input.val(currentVal + 1);
            } else if (currentVal > minVal) {
                $input.val(currentVal - 1);
            }
            updateVisual();
        });

        // Initialize Fancybox
        if (typeof Fancybox !== "undefined") {
            Fancybox.bind("[data-fancybox]", { infinite: true });
        }

        // =========================================================================
        // Floating Vertical Side Navigation (#innerNav) - Optimized ScrollSpy
        // =========================================================================
        var $innerNav = $('#innerNav');
        var $navLinks = $innerNav.find('.nav-link');
        var sections = [];
        var ticking = false;

        function cacheSections() {
            sections = [];
            $navLinks.each(function () {
                var href = $(this).attr('href');
                if (href && href.startsWith('#') && href.length > 1) {
                    var $sec = $(href);
                    if ($sec.length) {
                        sections.push({ id: href, $el: $sec, $link: $(this) });
                    }
                }
            });
        }
        cacheSections();

        function updateScrollSpy() {
            var scrollPos = $(window).scrollTop() + 160;
            var winHeight = $(window).height();
            var docHeight = $(document).height();
            var activeSec = null;

            for (var i = 0; i < sections.length; i++) {
                var sec = sections[i];
                var top = sec.$el.offset().top;
                if (scrollPos >= top && scrollPos < top + sec.$el.outerHeight()) {
                    activeSec = sec;
                    break;
                }
            }

            if ($(window).scrollTop() + winHeight >= docHeight - 60 && sections.length) {
                activeSec = sections[sections.length - 1];
            }

            if (activeSec) {
                $navLinks.removeClass('active');
                activeSec.$link.addClass('active');
                $innerNav.toggleClass('is-dark', activeSec.$el.hasClass('bg-dark') || activeSec.$el.hasClass('r-changeNavColor'));
            } else if ($(window).scrollTop() < 300 && sections.length) {
                $navLinks.removeClass('active');
                sections[0].$link.addClass('active');
                $innerNav.removeClass('is-dark');
            }
            ticking = false;
        }

        function requestScrollSpy() {
            if (!ticking) {
                requestAnimationFrame(updateScrollSpy);
                ticking = true;
            }
        }

        $navLinks.on('click', function (e) {
            var targetId = $(this).attr('href');
            if (targetId && targetId.startsWith('#') && targetId.length > 1) {
                var $target = $(targetId);
                if ($target.length) {
                    e.preventDefault();
                    $navLinks.removeClass('active');
                    $(this).addClass('active');
                    $('html, body').stop().animate({ scrollTop: $target.offset().top - 40 }, 500, 'swing', requestScrollSpy);
                }
            }
        });

        $(window).on('scroll', requestScrollSpy);
        $(window).on('resize', function () {
            cacheSections();
            requestScrollSpy();
        });
        requestScrollSpy();

        // Forms Handling
        $('#bookForm').on('submit', function (e) {
            var action = $(this).attr('action');
            if (!action || action === '#' || action.startsWith('#')) {
                e.preventDefault();
                var $target = $('#rooms');
                if ($target.length) {
                    $('html, body').animate({ scrollTop: $target.offset().top - 80 }, 500);
                }
            }
        });

        $('#eventsBookForm form').on('submit', function (e) {
            e.preventDefault();
            var modalEl = document.getElementById('bookingFormTY');
            if (modalEl && typeof bootstrap !== 'undefined') {
                new bootstrap.Modal(modalEl).show();
            }
            this.reset();
        });

        $('.js-subscribe form, .js-subscribe_bottom form').on('submit', function (e) {
            e.preventDefault();
            $(this).find('.input-group').hide();
            $(this).prev('h2, h3').text('Thank you for subscribing!');
        });
    });
})(jQuery);
