export default function getCurrentFormUrlData() {
    const originUrl = window.top.location.href;

    const isEmbed = window.frameElement !== null;

    const getEmbedId = () => {
        if (!isEmbed) {
            return null;
        }

        if (window.frameElement.hasAttribute('data-givewp-embed-id')) {
            return window.frameElement.getAttribute('data-givewp-embed-id');
        }

        return window.frameElement.id;
    };

    const getLocale = () => {
        if (!isEmbed) {
            return null;
        }

        if (window.frameElement.hasAttribute('data-form-locale')) {
            return window.frameElement.getAttribute('data-form-locale');
        }

        let locale = '';
        if (window.frameElement.src) {
            const url = new URL(window.frameElement.src);
            locale = url.searchParams.get('locale') || '';
        }

        return locale;
    };

    return {
        originUrl,
        isEmbed,
        embedId: getEmbedId(),
        locale: getLocale(),
    };
}
