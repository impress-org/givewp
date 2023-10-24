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

    return {
        originUrl,
        isEmbed,
        embedId: getEmbedId(),
    }
}