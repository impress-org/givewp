/**
 * Navigate the top-level window. A cross-origin embed cannot always navigate
 * window.top directly, so fall back to asking the parent page to navigate via
 * postMessage. The payload is only a URL; the receiving embed script is
 * responsible for validating it before navigating.
 *
 * @since TBD
 */
export default function navigateTop(url: string | URL): void {
    try {
        window.top.location.assign(url.toString());
    } catch (e) {
        window.parent.postMessage({type: 'givewp-navigate', url: url.toString()}, '*');
    }
}
