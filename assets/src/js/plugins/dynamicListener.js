(function(globalSope) {
    'use strict';

    /**
     * Including this file adds the `addDynamicListener` to the ELement prototype.
     * 
     * The dynamic listener gets an extra `selector` parameter that only calls the callback
     * if the target element matches the selector.
     * 
     * The listener has to be added to the container/root element and the selector should match
     * the elements that should trigger the event.
     * 
     * Browser support: IE9+
     */

    // Polyfil Element.matches
    // https://developer.mozilla.org/en/docs/Web/API/Element/matches#Polyfill
    if (!Element.prototype.matches) {
        Element.prototype.matches = 
            Element.prototype.matchesSelector || 
            Element.prototype.mozMatchesSelector ||
            Element.prototype.msMatchesSelector || 
            Element.prototype.oMatchesSelector || 
            Element.prototype.webkitMatchesSelector ||
            function(s) {
                var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                    i = matches.length;
                while (--i >= 0 && matches.item(i) !== this) {}
                return i > -1;            
            };
    }

    /**
     * Returns a modified callback function that calls the
     * initial callback function only if the target element matches the given selector
     * 
     * @param {string} selector 
     * @param {function} callback 
     */
    function getConditionalCallback(selector, callback) {
        return function(e) {
            if(!e.target) return;
            if(!e.target.matches(selector)) return;
            callback.apply(this, arguments);
        };
    }

    /**
     * 
     * 
     * @param {Element} rootElement The root element to add the linster too.
     * @param {string} eventType The event type to listen for.
     * @param {string} selector The selector that should match the dynamic elements.
     * @param {function} callback The function to call when an event occurs on the given selector.
     * @param {boolean|object} options Passed as the regular `options` parameter to the addEventListener function
     *                                 Set to `true` to use capture.
     *                                 Usually used as an object to add the listener as `passive`
     */
    globalSope.addDynamicEventListener = function (rootElement, eventType, selector, callback, options) {
        rootElement.addEventListener(eventType, getConditionalCallback(selector, callback), options);
    };
})(window);
