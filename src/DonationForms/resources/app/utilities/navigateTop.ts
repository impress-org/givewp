import getCurrentFormUrlData from '@givewp/forms/app/utilities/getCurrentFormUrlData';

/**
 * Navigate the top-level window. A cross-origin embed cannot always navigate
 * window.top directly, so fall back to asking the parent page to navigate via
 * postMessage. The payload is only a URL; the receiving embed script is
 * responsible for validating it before navigating.
 *
 * The message is addressed to the host page's origin, taken from the
 * origin-url the embed script passed. The URL can carry a gateway approval
 * token or a receipt key, so it is not broadcast to whatever page happens to
 * frame the form. Without a usable origin the message is not sent.
 *
 * @since 4.17.0
 */
export default function navigateTop(url: string | URL): void {
    try {
        window.top.location.assign(url.toString());
    } catch (e) {
        const targetOrigin = getHostOrigin();

        if (targetOrigin) {
            window.parent.postMessage({type: 'givewp-navigate', url: url.toString()}, targetOrigin);
        }
    }
}

function getHostOrigin(): string | null {
    try {
        const origin = new URL(getCurrentFormUrlData().originUrl).origin;

        return origin === 'null' ? null : origin;
    } catch (e) {
        return null;
    }
}
