(function (window, document) {
    'use strict';

    var pending = {};

    document.addEventListener('click', function (event) {
        var button = closest(event.target, '.js-wv-whatsapp-send');
        if (!button) {
            return;
        }

        event.preventDefault();
        sendFromButton(button);
    });

    window.addEventListener('message', function (event) {
        if (event.source !== window || !event.data || event.data.source !== 'WHITE_VILLAS_EXTENSION') {
            return;
        }

        var requestId = event.data.requestId;
        if (!requestId || !pending[requestId]) {
            return;
        }

        pending[requestId](event.data);
        delete pending[requestId];
    });

    function sendFromButton(button) {
        var payload = {
            phone: button.getAttribute('data-wv-wa-phone') || '',
            message: button.getAttribute('data-wv-wa-message') || '',
            attachmentUrl: button.getAttribute('data-wv-wa-attachment') || '',
            autoSend: button.getAttribute('data-wv-wa-auto') === '1'
        };

        payload.phone = normalizePhone(payload.phone);

        if (!payload.phone) {
            notify('Client phone number is missing.');
            return;
        }

        if (!payload.message && !payload.attachmentUrl) {
            notify('Message or attachment is missing.');
            return;
        }

        button.disabled = true;
        var originalHtml = button.innerHTML;
        button.innerHTML = '<span class="fa fa-spinner fa-spin"></span>&nbsp;Opening...';

        callExtension(payload, 1200).then(function (response) {
            if (response && response.ok) {
                notify('Opening WhatsApp Web...');
                return;
            }

            fallbackOpen(payload, response && response.error ? response.error : '');
        }).catch(function () {
            fallbackOpen(payload, '');
        }).then(function () {
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }

    function callExtension(payload, timeoutMs) {
        return new Promise(function (resolve, reject) {
            var requestId = 'wv_' + Date.now() + '_' + Math.random().toString(16).slice(2);
            var timer = window.setTimeout(function () {
                delete pending[requestId];
                reject(new Error('Extension not available'));
            }, timeoutMs || 1200);

            pending[requestId] = function (response) {
                window.clearTimeout(timer);
                resolve(response);
            };

            window.postMessage({
                source: 'WHITE_VILLAS_PORTAL',
                type: 'WV_PORTAL_SEND',
                requestId: requestId,
                payload: payload
            }, '*');
        });
    }

    function fallbackOpen(payload, error) {
        var url = 'https://web.whatsapp.com/send?phone=' + encodeURIComponent(payload.phone);
        if (payload.message) {
            url += '&text=' + encodeURIComponent(payload.message);
        }

        window.open(url, '_blank', 'noopener');

        if (payload.attachmentUrl) {
            notify('WhatsApp opened with message text. Install/enable the White Villas Chrome extension to attach the file automatically.');
        } else if (error) {
            notify(error);
        } else {
            notify('WhatsApp opened.');
        }
    }

    function normalizePhone(input) {
        var phone = String(input || '').replace(/[^0-9+]/g, '');
        if (phone.charAt(0) === '+') {
            phone = phone.slice(1);
        }
        if (phone.indexOf('0092') === 0) {
            phone = '92' + phone.slice(4);
        }
        if (phone.charAt(0) === '0' && phone.length === 11) {
            phone = '92' + phone.slice(1);
        }
        return phone.replace(/\D/g, '');
    }

    function closest(element, selector) {
        while (element && element.nodeType === 1) {
            if (matches(element, selector)) {
                return element;
            }
            element = element.parentNode;
        }
        return null;
    }

    function matches(element, selector) {
        var fn = element.matches || element.msMatchesSelector || element.webkitMatchesSelector;
        return fn && fn.call(element, selector);
    }

    function notify(message) {
        var old = document.querySelector('.wv-whatsapp-status');
        if (old) {
            old.parentNode.removeChild(old);
        }

        var box = document.createElement('div');
        box.className = 'wv-whatsapp-status';
        box.textContent = message;
        document.documentElement.appendChild(box);

        window.setTimeout(function () {
            if (box.parentNode) {
                box.parentNode.removeChild(box);
            }
        }, 4500);
    }
})(window, document);
