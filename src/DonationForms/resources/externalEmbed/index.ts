import {iframeResize} from 'iframe-resizer';

/**
 * External embed script for GiveWP donation forms.
 *
 * Loaded on non-WordPress sites, so it must stay self-contained: no
 * WordPress packages, no React. It registers the <givewp-donation-form>
 * custom element, which renders the form in an iframe pointed at the
 * donation-form-view route on the WordPress site.
 *
 * @since TBD
 */

const LOAD_TIMEOUT_MS = 10000;

/**
 * Embed ids must be stable across page loads: an offsite gateway (e.g.
 * PayPal) returns the donor to this page with the embed id in the URL, and
 * the matching element swaps itself to the receipt view. A DOM-order counter
 * is stable; a random or time-based id is not.
 */
let embedInstance = 0;

class GiveWPDonationForm extends HTMLElement {
    iframe: HTMLIFrameElement | null = null;
    wpOrigin: string = '';
    embedId: string = '';

    connectedCallback() {
        const formId = this.getAttribute('form-id');
        const wpUrl = this.getAttribute('wp-url');

        if (!formId || !wpUrl) {
            console.error('givewp-donation-form requires form-id and wp-url attributes.');
            return;
        }

        let wpOrigin: string;
        try {
            wpOrigin = new URL(wpUrl).origin;
        } catch (e) {
            console.error('givewp-donation-form: wp-url is not a valid URL.', wpUrl);
            return;
        }

        this.wpOrigin = wpOrigin;
        this.embedId = `givewp-embed-external-${embedInstance++}`;

        const src = this.isReceiptReturn() ? this.getReceiptViewUrl() : this.getFormViewUrl(formId);

        this.renderIframe(src);
        this.listenForMessages();
    }

    getFormViewUrl(formId: string): string {
        const url = new URL('/', this.wpOrigin);
        url.searchParams.set('givewp-route', 'donation-form-view');
        url.searchParams.set('form-id', formId);
        url.searchParams.set('origin-url', window.location.href);
        url.searchParams.set('embed-id', this.embedId);

        const locale = this.getAttribute('locale');
        if (locale) {
            url.searchParams.set('locale', locale);
        }

        return url.toString();
    }

    /**
     * Mirrors the return-flow params the WordPress block handles server-side
     * (RouteListener('donation-completed', 'show-donation-confirmation-receipt')).
     */
    isReceiptReturn(): boolean {
        const params = new URLSearchParams(window.location.search);

        return (
            params.get('givewp-event') === 'donation-completed' &&
            params.get('givewp-listener') === 'show-donation-confirmation-receipt' &&
            params.get('givewp-embed-id') === this.embedId &&
            /^[a-z0-9]{32}$/i.test(params.get('givewp-receipt-id') || '')
        );
    }

    getReceiptViewUrl(): string {
        const params = new URLSearchParams(window.location.search);
        const url = new URL('/', this.wpOrigin);
        url.searchParams.set('givewp-route', 'donation-confirmation-receipt-view');
        url.searchParams.set('receipt-id', params.get('givewp-receipt-id'));

        return url.toString();
    }

    renderIframe(src: string) {
        const loading = document.createElement('p');
        loading.textContent = this.getAttribute('loading-text') || 'Loading donation form…';

        const iframe = document.createElement('iframe');
        iframe.src = src;
        // Matches the title the WordPress embeds use, so tooling and donors see one name.
        iframe.title = this.getAttribute('form-title') || 'Donation Form';
        iframe.style.cssText = 'width: 1px; min-width: 100%; border: 0; display: none;';
        iframe.setAttribute('data-givewp-embed', 'true');
        iframe.setAttribute('data-givewp-embed-id', this.embedId);

        // Browsers fire `load` even for error pages, so the signal that the
        // form is actually running is the iframe-resizer handshake (onInit).
        // Until it arrives - frame-blocking headers, ad blockers, network
        // failure - the timeout degrades to a plain link to the form.
        const timeout = window.setTimeout(() => this.renderFallbackLink(loading), LOAD_TIMEOUT_MS);

        this.append(loading, iframe);
        this.iframe = iframe;

        iframeResize(
            {
                checkOrigin: [this.wpOrigin],
                heightCalculationMethod: 'taggedElement',
                onInit: () => {
                    window.clearTimeout(timeout);
                    loading.remove();
                    iframe.style.display = '';
                },
            },
            iframe
        );
    }

    renderFallbackLink(loading: HTMLElement) {
        const link = document.createElement('a');
        link.href = new URL(`/?givewp-route=donation-form-view&form-id=${this.getAttribute('form-id')}`, this.wpOrigin).toString();
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent = this.getAttribute('fallback-text') || 'Open donation form';

        loading.replaceWith(link);
        this.iframe?.remove();
        this.iframe = null;
    }

    /**
     * The form app asks the parent page to navigate when it cannot navigate
     * window.top itself (see navigateTop.ts). Only messages from the
     * WordPress origin with a valid http(s) URL are honored.
     */
    listenForMessages() {
        window.addEventListener('message', (event: MessageEvent) => {
            if (event.origin !== this.wpOrigin) {
                return;
            }

            if (!event.data || typeof event.data !== 'object' || event.data.type !== 'givewp-navigate') {
                return;
            }

            if (event.source !== this.iframe?.contentWindow) {
                return;
            }

            let url: URL;
            try {
                url = new URL(event.data.url);
            } catch (e) {
                return;
            }

            if (url.protocol === 'http:' || url.protocol === 'https:') {
                window.location.assign(url.toString());
            }
        });
    }
}

if (!customElements.get('givewp-donation-form')) {
    customElements.define('givewp-donation-form', GiveWPDonationForm);
}
