/**
 * @since TBD Support cross-origin embeds: guard window.top and window.frameElement access with URL parameter fallbacks.
 * @since 3.22.0 Add locale support
 */
export default function getCurrentFormUrlData() {
    const urlParams = new URLSearchParams(window.location.search);

    let originUrl;
    let isCrossOriginEmbed = false;

    try {
        originUrl = window.top.location.href;
    } catch (e) {
        // Cross-origin embed: the parent page URL is unreadable, so use the
        // origin-url param passed by the embed script, then the referrer.
        isCrossOriginEmbed = true;
        originUrl = urlParams.get('origin-url') || document.referrer || window.location.href;
    }

    const isEmbed = window.self !== window.top;

    const getEmbedId = () => {
        if (!isEmbed) {
            return null;
        }

        if (window.frameElement) {
            if (window.frameElement.hasAttribute('data-givewp-embed-id')) {
                return window.frameElement.getAttribute('data-givewp-embed-id');
            }

            return window.frameElement.id;
        }

        return urlParams.get('embed-id');
    };

    const getLocale = () => {
        if (!isEmbed) {
            return null;
        }

        if (window.frameElement) {
            if (window.frameElement.hasAttribute('data-form-locale')) {
                return window.frameElement.getAttribute('data-form-locale');
            }

            let locale = '';
            if (window.frameElement.src) {
                const url = new URL(window.frameElement.src);
                locale = url.searchParams.get('locale') || '';
            }

            return locale;
        }

        return urlParams.get('locale') || '';
    };

    return {
        originUrl,
        isEmbed,
        isCrossOriginEmbed,
        embedId: getEmbedId(),
        locale: getLocale(),
    };
}
