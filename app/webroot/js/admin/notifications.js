/**
 * White Villas Real-time Notification Engine
 * Compatible with all modern browsers & cPanel / Apache environments
 */
(function(window, document, $) {
    'use strict';

    var pollIntervalActive = 25000;   // 25 seconds when user is active
    var pollIntervalIdle = 60000;     // 60 seconds when tab is hidden / idle
    var currentTimer = null;
    var lastSeenId = 0;
    var soundEnabled = true;
    var browserPushEnabled = true;
    var audioCtx = null;

    // Web Audio API Chime generator (Zero external MP3 dependency)
    function playChime() {
        if (!soundEnabled) return;
        try {
            var AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            if (!audioCtx) {
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            var now = audioCtx.currentTime;

            // First gentle tone (E5 ~ 659.25Hz)
            var osc1 = audioCtx.createOscillator();
            var gain1 = audioCtx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.08, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(audioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Second harmonious chime (A5 ~ 880Hz)
            var osc2 = audioCtx.createOscillator();
            var gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.1, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.6);
        } catch (e) {
            // Audio policy or unsupported device handled safely
        }
    }

    // Native Desktop / Mobile OS Push Notification
    function showDesktopPush(title, body, targetUrl) {
        if (!browserPushEnabled || !("Notification" in window)) return;
        if (Notification.permission === "granted") {
            try {
                var notif = new Notification(title, {
                    body: body,
                    icon: '/design2000/assets/images/logo.png'
                });
                notif.onclick = function() {
                    window.focus();
                    if (targetUrl && targetUrl !== 'javascript:void(0);') {
                        window.location.href = targetUrl;
                    }
                    notif.close();
                };
            } catch (e) {
                // Fallback safe
            }
        }
    }

    function updateBadge(count) {
        count = parseInt(count, 10) || 0;
        var badge = $('#notifUnreadBadge');
        var headerCount = $('#notifHeaderCount');
        var bellIcon = $('#notifBellIcon');

        if (count > 0) {
            badge.text(count > 99 ? '99+' : count).show();
            headerCount.text(count + ' new').show();
            bellIcon.addClass('notif-has-unread');
        } else {
            badge.hide().text('0');
            headerCount.hide().text('0');
            bellIcon.removeClass('notif-has-unread');
        }
    }

    function renderDropdownItems(items) {
        var container = $('#notifMenuList');
        if (!container.length) return;

        if (!items || !items.length) {
            if (!container.find('.wv-notif-item').length) {
                container.html(
                    '<div class="wv-notif-empty" style="padding: 24px 16px; text-align: center; color: #94a3b8; font-size: 12.5px;">' +
                    '<i class="fa fa-bell-slash-o" style="font-size: 28px; opacity: 0.5; margin-bottom: 6px; display: block;"></i>' +
                    'No unread notifications' +
                    '</div>'
                );
            }
            return;
        }

        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var iconClass = item.icon || 'fa-bell';
            var iconColor = '#3b82f6';
            if (item.priority === 'urgent') iconColor = '#ef4444';
            else if (item.priority === 'high') iconColor = '#f59e0b';
            else if (item.type === 'broadcast') iconColor = '#8b5cf6';
            else if (item.type && (item.type.indexOf('deal') !== -1 || item.type.indexOf('won') !== -1)) iconColor = '#10b981';

            html += '<a href="' + (item.url || 'javascript:void(0);') + '" class="wv-notif-item ' + (item.is_read == 1 ? 'read' : 'unread') + '" data-id="' + item.id + '">';
            html += '  <div class="wv-notif-icon" style="color:' + iconColor + '; background:' + iconColor + '18;">';
            html += '    <i class="fa ' + iconClass + '"></i>';
            html += '  </div>';
            html += '  <div class="wv-notif-content">';
            html += '    <div class="wv-notif-title">' + $('<div>').text(item.title).html() + '</div>';
            html += '    <div class="wv-notif-snippet">' + $('<div>').text(item.message).html() + '</div>';
            html += '    <div class="wv-notif-time"><i class="fa fa-clock-o"></i> ' + (item.time_ago || 'Just now') + '</div>';
            html += '  </div>';
            if (item.is_read == 0) {
                html += '  <span class="wv-notif-dot"></span>';
            }
            html += '</a>';
        }

        container.html(html);
    }

    function doPoll() {
        var pollUrl = '/admin/Notifications/poll';
        $.ajax({
            url: pollUrl,
            type: 'GET',
            data: { last_id: lastSeenId, limit: 8 },
            dataType: 'json',
            cache: false,
            success: function(resp) {
                if (!resp || resp.status !== 'success') return;

                soundEnabled = (resp.sound_enabled !== false);
                browserPushEnabled = (resp.browser_push_enabled !== false);

                var prevCount = parseInt($('#notifUnreadBadge').text(), 10) || 0;
                var newCount = parseInt(resp.unread_count, 10) || 0;

                updateBadge(newCount);

                if (resp.new_items && resp.new_items.length > 0) {
                    renderDropdownItems(resp.new_items);

                    // Track latest ID
                    for (var i = 0; i < resp.new_items.length; i++) {
                        var itemId = parseInt(resp.new_items[i].id, 10) || 0;
                        if (itemId > lastSeenId) {
                            lastSeenId = itemId;
                        }
                    }

                    // Play chime & desktop push on new arrivals
                    if (newCount > prevCount && lastSeenId > 0) {
                        playChime();
                        var topItem = resp.new_items[0];
                        if (topItem && topItem.is_read == 0 && document.hidden) {
                            showDesktopPush(topItem.title, topItem.message, topItem.url);
                        }
                    }
                } else if (newCount === 0) {
                    renderDropdownItems([]);
                }
            },
            complete: function() {
                scheduleNext();
            }
        });
    }

    function scheduleNext() {
        if (currentTimer) {
            clearTimeout(currentTimer);
        }
        var delay = document.hidden ? pollIntervalIdle : pollIntervalActive;
        currentTimer = setTimeout(doPoll, delay);
    }

    // Page visibility listener to throttle background requests
    $(document).on('visibilitychange', function() {
        if (!document.hidden) {
            doPoll(); // Catch up immediately on tab focus
        }
    });

    $(document).ready(function() {
        // Initial quick poll
        setTimeout(doPoll, 1000);

        // Mark individual item read on click in dropdown
        $(document).on('click', '.wv-notif-item', function(e) {
            var item = $(this);
            var id = item.data('id');
            if (id && item.hasClass('unread')) {
                $.post('/admin/Notifications/mark_read', { id: id }, function(res) {
                    if (res && res.status === 'success') {
                        item.removeClass('unread').addClass('read');
                        item.find('.wv-notif-dot').remove();
                        updateBadge(res.unread_count);
                    }
                }, 'json');
            }
        });

        // Mark all read button in dropdown
        $(document).on('click', '#notifMarkAllReadBtn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $.post('/admin/Notifications/mark_all_read', {}, function(res) {
                if (res && res.status === 'success') {
                    $('.wv-notif-item').removeClass('unread').addClass('read');
                    $('.wv-notif-dot').remove();
                    updateBadge(0);
                }
            }, 'json');
        });

        // Request desktop notification permission on first user click if enabled
        $(document).one('click', function() {
            if (browserPushEnabled && "Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }
        });
    });

    // Global Public API
    window.WhiteVillasNotifications = {
        playChime: playChime,
        updateBadge: updateBadge,
        pollNow: doPoll,
        requestPermissionAndTest: function() {
            if ("Notification" in window) {
                Notification.requestPermission().then(function(perm) {
                    if (perm === "granted") {
                        showDesktopPush("White Villas Notification System", "Real-time native push notifications are successfully configured!", null);
                    } else {
                        alert("Browser push permission: " + perm);
                    }
                });
            }
        }
    };

})(window, document, window.jQuery);
