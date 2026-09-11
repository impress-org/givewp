import {iframeResize} from 'iframe-resizer';

/**
 * External embed script for GiveWP donation forms.
 *
 * Loaded on non-WordPress sites, so it must stay self-contained: no
 * WordPress packages, no React. It registers the <givewp-donation-form>
 * custom element, which renders the form in an iframe pointed at the
 * donation-form-view route on the WordPress site.
 *
 * Display styles mirror the WordPress embeds: `onpage` (default) renders the
 * form inline, `modal` renders a button that opens the form in an overlay,
 * `newTab` renders a button that links to the standalone form page.
 *
 * Donor-facing text is either visually absent (the loading state is a
 * spinner) or overridable per attribute, so the WordPress-generated snippet
 * supplies translated strings.
 *
 * The WordPress site is located from the script's own URL, so the snippet
 * only has to name the form. A `wp-url` attribute overrides that for pages
 * that load the script from somewhere other than the WordPress site.
 *
 * @since TBD
 */

/**
 * The script URL shapes Route::scriptUrl() produces all sit directly under
 * the WordPress home URL: /give/embed/donation-form/script.js,
 * /index.php/give/embed/donation-form/script.js, or
 * /?givewp-route=embed/donation-form/script.js. Stripping that tail from the
 * script's src yields the home URL, trailing slash included.
 */
const SCRIPT_HOME_URL = ((): string | null => {
    const src = (document.currentScript as HTMLScriptElement | null)?.src;
    if (!src) {
        return null;
    }

    const url = new URL(src);
    url.search = '';
    url.hash = '';
    url.pathname = url.pathname.replace(/(index\.php\/)?give\/embed\/donation-form\/script\.js$/, '');

    return url.toString();
})();

const LOAD_TIMEOUT_MS = 10000;

/**
 * Embed ids must be stable across page loads: an offsite gateway (e.g.
 * PayPal) returns the donor to this page with the embed id in the URL, and
 * the matching element swaps itself to the receipt view. A DOM-order counter
 * is stable; a random or time-based id is not.
 */
let embedInstance = 0;

const STYLE_ID = 'givewp-embed-styles';

const EMBED_CSS = `
givewp-donation-form {
    display: block;
}
.givewp-embed__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 20rem;
}
.givewp-embed__spinner {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    border: 3px solid rgba(0, 0, 0, 0.12);
    border-top-color: rgba(0, 0, 0, 0.55);
    animation: givewp-embed-spin 0.8s linear infinite;
}
@keyframes givewp-embed-spin {
    to { transform: rotate(360deg); }
}
.givewp-embed__button {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border: 0;
    border-radius: 4px;
    background-color: var(--givewp-primary-color, #2d802f);
    color: #fff;
    font: inherit;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}
.givewp-embed__overlay {
    position: fixed;
    inset: 0;
    z-index: 2147483646;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    overflow-y: auto;
    padding: 2.5rem 1rem;
    background-color: rgba(0, 0, 0, 0.6);
}
.givewp-embed__dialog {
    position: relative;
    width: 100%;
    max-width: 34rem;
    border-radius: 8px;
    background-color: #fff;
    padding: 1.5rem 1rem 1rem;
}
.givewp-embed__close {
    position: absolute;
    top: 0.25rem;
    right: 0.5rem;
    border: 0;
    background: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
}
.givewp-embed__focus-guard {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
}
`;

function injectStyles() {
    if (document.getElementById(STYLE_ID)) {
        return;
    }

    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = EMBED_CSS;
    document.head.appendChild(style);
}

class GiveWPDonationForm extends HTMLElement {
    iframe: HTMLIFrameElement | null = null;
    wpOrigin: string = '';
    wpBase: URL | null = null;
    formId: string = '';
    embedId: string = '';
    overlay: HTMLElement | null = null;
    initialized: boolean = false;
    scrollOnInit: boolean = false;
    keydownHandler: ((event: KeyboardEvent) => void) | null = null;

    /**
     * The form app asks the parent page to navigate when it cannot navigate
     * window.top itself (see navigateTop.ts). Only messages from the
     * WordPress origin with a valid http(s) URL are honored.
     */
    messageHandler = (event: MessageEvent) => {
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
    };

    connectedCallback() {
        const formId = this.getAttribute('form-id');
        const wpUrl = this.getAttribute('wp-url') || SCRIPT_HOME_URL;

        if (!formId) {
            console.error('givewp-donation-form requires a form-id attribute.');
            return;
        }

        if (!wpUrl) {
            console.error('givewp-donation-form could not locate the WordPress site; add a wp-url attribute.');
            return;
        }

        // The full URL, not just the origin: WordPress in a subdirectory
        // (example.org/blog) serves its routes under that path. Only http(s)
        // may reach the iframe src.
        let wpBase: URL;
        try {
            wpBase = new URL(wpUrl);
        } catch (e) {
            console.error('givewp-donation-form: wp-url is not a valid URL.', wpUrl);
            return;
        }

        if (wpBase.protocol !== 'http:' && wpBase.protocol !== 'https:') {
            console.error('givewp-donation-form: wp-url must be an http(s) URL.', wpUrl);
            return;
        }

        // Route URLs are built by appending a query string, and WordPress
        // redirects /blog?x to /blog/?x; a trailing slash avoids the round trip.
        if (!wpBase.pathname.endsWith('/')) {
            wpBase.pathname += '/';
        }

        window.addEventListener('message', this.messageHandler);
        if (this.keydownHandler) {
            document.addEventListener('keydown', this.keydownHandler);
        }

        // SPA frameworks detach and reattach elements; the children and state
        // survive that, so only listeners need re-adding.
        if (this.initialized) {
            return;
        }
        this.initialized = true;

        injectStyles();

        // The launcher button is host-page chrome, so the host page styles it:
        // set --givewp-primary-color on givewp-donation-form in its CSS. The
        // attribute is a shortcut for the same property. Nothing about the
        // form's own colors is baked into the snippet; the form inside the
        // iframe resolves those itself on every load.
        const primaryColor = this.getAttribute('primary-color');
        if (primaryColor) {
            this.style.setProperty('--givewp-primary-color', primaryColor);
        }

        this.formId = formId;
        this.wpBase = wpBase;
        this.wpOrigin = wpBase.origin;
        this.embedId = `givewp-embed-external-${embedInstance++}`;

        const displayStyle = this.getAttribute('display-style') || 'onpage';
        const isReceiptReturn = this.isReceiptReturn();
        const src = isReceiptReturn ? this.getReceiptViewUrl() : this.getFormViewUrl(formId);

        if (displayStyle === 'newTab') {
            this.renderNewTabButton();
        } else if (displayStyle === 'modal') {
            this.renderModalButton(src);

            // A donor returning from a gateway redirect needs their receipt
            // without having to find the button again.
            if (isReceiptReturn) {
                this.openModal(src);
            }
        } else {
            this.scrollOnInit = isReceiptReturn;
            this.renderForm(src, this);
        }

        if (isReceiptReturn) {
            this.consumeReturnParams();
        }
    }

    /**
     * The return params are one-time input; leaving them in the address bar
     * makes the URL ugly to share and replays the receipt on every reload.
     */
    consumeReturnParams() {
        const url = new URL(window.location.href);
        ['givewp-event', 'givewp-listener', 'givewp-embed-id', 'givewp-receipt-id'].forEach((param) =>
            url.searchParams.delete(param)
        );
        window.history.replaceState(window.history.state, '', url.toString());
    }

    disconnectedCallback() {
        window.removeEventListener('message', this.messageHandler);
        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
        }
    }

    getButtonText(): string {
        return this.getAttribute('button-text') || 'Donate';
    }

    getFormViewUrl(formId: string): string {
        // Origin and pathname only: the page's query string and fragment may
        // carry data that should not be forwarded to the WordPress site, and
        // the offsite return flow appends its own parameters anyway.
        const originUrl = new URL(window.location.href);
        originUrl.search = '';
        originUrl.hash = '';

        const url = new URL(this.wpBase.toString());
        url.searchParams.set('givewp-route', 'donation-form-view');
        url.searchParams.set('form-id', formId);
        url.searchParams.set('origin-url', originUrl.toString());
        url.searchParams.set('embed-id', this.embedId);

        const locale = this.getAttribute('locale');
        if (locale) {
            url.searchParams.set('locale', locale);
        }

        return url.toString();
    }

    getStandaloneFormUrl(): string {
        const url = new URL(this.wpBase.toString());
        url.searchParams.set('givewp-route', 'donation-form-view');
        url.searchParams.set('form-id', this.formId);

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
        const url = new URL(this.wpBase.toString());
        url.searchParams.set('givewp-route', 'donation-confirmation-receipt-view');
        url.searchParams.set('receipt-id', params.get('givewp-receipt-id'));

        return url.toString();
    }

    renderForm(src: string, target: HTMLElement) {
        const loading = document.createElement('div');
        loading.className = 'givewp-embed__loading';
        loading.setAttribute('role', 'status');
        loading.setAttribute('aria-label', this.getAttribute('loading-text') || 'Loading');

        const spinner = document.createElement('span');
        spinner.className = 'givewp-embed__spinner';
        loading.appendChild(spinner);

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

        target.append(loading, iframe);
        this.iframe = iframe;

        iframeResize(
            {
                checkOrigin: [this.wpOrigin],
                heightCalculationMethod: 'taggedElement',
                onInit: () => {
                    window.clearTimeout(timeout);
                    loading.remove();
                    iframe.style.display = '';

                    // A gateway-redirect return lands at the top of the page;
                    // bring the receipt back into view.
                    if (this.scrollOnInit) {
                        this.scrollOnInit = false;
                        this.scrollIntoView({behavior: 'smooth', block: 'start'});
                    }
                },
            },
            iframe
        );
    }

    renderFallbackLink(loading: HTMLElement) {
        const link = document.createElement('a');
        link.href = this.getStandaloneFormUrl();
        link.target = '_blank';
        link.rel = 'noopener';
        link.className = 'givewp-embed__button';
        link.textContent = this.getAttribute('fallback-text') || 'Open donation form';

        loading.replaceWith(link);
        this.iframe?.remove();
        this.iframe = null;
    }

    renderNewTabButton() {
        const link = document.createElement('a');
        link.href = this.getStandaloneFormUrl();
        link.target = '_blank';
        link.rel = 'noopener';
        link.className = 'givewp-embed__button';
        link.textContent = this.getButtonText();

        this.appendChild(link);
    }

    renderModalButton(src: string) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'givewp-embed__button';
        button.textContent = this.getButtonText();
        button.addEventListener('click', () => this.openModal(src));

        this.appendChild(button);
    }

    openModal(src: string) {
        const launcher = document.activeElement as HTMLElement | null;

        if (this.overlay) {
            this.overlay.style.display = '';
            this.focusDialog();
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'givewp-embed__overlay';

        const dialog = document.createElement('div');
        dialog.className = 'givewp-embed__dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('aria-label', this.getAttribute('form-title') || 'Donation Form');
        dialog.tabIndex = -1;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'givewp-embed__close';
        close.setAttribute('aria-label', this.getAttribute('close-text') || 'Close');
        close.textContent = '×';

        const hide = () => {
            overlay.style.display = 'none';
            launcher?.focus();
        };

        close.addEventListener('click', hide);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                hide();
            }
        });
        this.keydownHandler = (event: KeyboardEvent) => {
            if (overlay.style.display !== 'none' && event.key === 'Escape') {
                hide();
            }
        };
        document.addEventListener('keydown', this.keydownHandler);

        /*
         * Focus guards bracket the dialog's contents. Tabbing past either end
         * lands on a guard, still inside the dialog, which hands focus to the
         * opposite end. A key listener cannot do this: the browser moves
         * focus after keydown, and while focus is inside the cross-origin
         * iframe the host document sees no key events at all.
         *
         * The iframe is hidden until the resizer handshake; a hidden element
         * cannot take focus, so only rendered elements count.
         */
        const focusables = () =>
            Array.from(dialog.querySelectorAll<HTMLElement>('a[href], button, iframe')).filter(
                (element) => element.getClientRects().length > 0
            );
        const startGuard = this.createFocusGuard(() => focusables().pop()?.focus());
        const endGuard = this.createFocusGuard(() => focusables().shift()?.focus());

        dialog.append(startGuard, close);
        overlay.appendChild(dialog);
        this.appendChild(overlay);
        this.overlay = overlay;

        // The iframe lives on across open/close so form state survives.
        this.renderForm(src, dialog);
        dialog.appendChild(endGuard);
        this.focusDialog();
    }

    createFocusGuard(onFocus: () => void): HTMLElement {
        const guard = document.createElement('span');
        guard.className = 'givewp-embed__focus-guard';
        guard.tabIndex = 0;
        guard.addEventListener('focus', onFocus);

        return guard;
    }

    /**
     * Initial focus goes to the close button, the first control, rather than
     * the dialog container: the container is tabindex -1, so a Shift+Tab from
     * it would walk backwards to the host page before reaching a guard.
     */
    focusDialog() {
        const dialog = this.overlay?.querySelector<HTMLElement>('.givewp-embed__dialog');

        (dialog?.querySelector<HTMLElement>('.givewp-embed__close') ?? dialog)?.focus();
    }
}

if (!customElements.get('givewp-donation-form')) {
    customElements.define('givewp-donation-form', GiveWPDonationForm);
}
