/**
 * Single script for frontend and block editor: applies custom CTA text to TGB popup
 * buttons. Containers must have .give-tgb-donation-form and data-tgb-button-text.
 * Works when the button is injected asynchronously and re-applies if the widget overwrites it.
 *
 * @unreleased
 */
(function () {
    var BUTTON_SELECTOR = '[id^="tgb-widget-button-"]';
    var MAX_WAIT_MS = 15000;

    function applyToButton(container, text) {
        var btn = container.querySelector(BUTTON_SELECTOR);
        if (btn) {
            btn.textContent = text;
            return btn;
        }
        return null;
    }

    function guardButtonText(button, text) {
        var guard = new MutationObserver(function () {
            if (button.textContent !== text) {
                button.textContent = text;
            }
        });
        guard.observe(button, { characterData: true, childList: true, subtree: true });
        return function () {
            guard.disconnect();
        };
    }

    function processContainer(container) {
        var text = container.getAttribute('data-tgb-button-text');
        if (!text) return;
        var applied = container.getAttribute('data-tgb-button-text-processed');
        if (applied === text) return;

        if (container._tgbGuardDisconnect) {
            container._tgbGuardDisconnect();
            container._tgbGuardDisconnect = null;
        }

        var btn = applyToButton(container, text);
        if (btn) {
            container._tgbGuardDisconnect = guardButtonText(btn, text);
            container.setAttribute('data-tgb-button-text-processed', text);
            return;
        }

        var observer = new MutationObserver(function () {
            var found = applyToButton(container, text);
            if (found) {
                observer.disconnect();
                container._tgbGuardDisconnect = guardButtonText(found, text);
                container.setAttribute('data-tgb-button-text-processed', text);
            }
        });
        observer.observe(container, { childList: true, subtree: true });
        setTimeout(function () {
            observer.disconnect();
        }, MAX_WAIT_MS);
    }

    function processAll() {
        var containers = document.querySelectorAll('.give-tgb-donation-form[data-tgb-button-text]');
        for (var i = 0; i < containers.length; i++) {
            processContainer(containers[i]);
        }
    }

    function run() {
        processAll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    var bodyObserver = new MutationObserver(function () {
        run();
    });
    if (document.body) {
        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    var delayedAttempts = [100, 400, 800, 1500, 2500, 4000];
    delayedAttempts.forEach(function (ms) {
        setTimeout(run, ms);
    });
})();
