import {iframeResize} from 'iframe-resizer';
import EMBED_CSS from './styles.scss?inline';

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
 * Donor-facing text comes from the WordPress site: the route that serves
 * this script prints window.givewpDonationFormEmbed ahead of it with the
 * strings translated in the site's locale. Attributes override per element.
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

type EmbedI18n = {
    donate: string;
    loading: string;
    formTitle: string;
    openForm: string;
    close: string;
};

declare const window: {
    givewpDonationFormEmbed?: {i18n?: Partial<EmbedI18n>};
} & Window;

/*
 * The English values are only reached when the file is loaded from
 * somewhere other than the route (a direct build path); the served script
 * always carries the site's translations.
 */
const I18N: EmbedI18n = {
    donate: 'Donate',
    loading: 'Loading',
    formTitle: 'Donation Form',
    openForm: 'Open donation form',
    close: 'Close',
    ...window.givewpDonationFormEmbed?.i18n,
};

/**
 * Embed ids must be stable across page loads: an offsite gateway (e.g.
 * PayPal) returns the donor to this page with the embed id in the URL, and
 * the matching element swaps itself to the receipt view. A DOM-order counter
 * is stable; a random or time-based id is not.
 */
let embedInstance = 0;

const STYLE_ID = 'givewp-embed-styles';

/**
 * The same icon the WordPress block's modal close button draws
 * (Campaigns/Blocks/shared/components/ModalForm/ModalClose.tsx).
 */
const CLOSE_ICON_SVG =
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">' +
    '<path stroke="black" stroke-width="2" d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path>' +
    '</svg>';

const EXIT_ANIMATION_MS = 150;

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
    modalButton: HTMLButtonElement | null = null;
    initialized: boolean = false;
    scrollOnInit: boolean = false;
    keydownHandler: ((event: KeyboardEvent) => void) | null = null;

    /**
     * Where focus returns when the modal closes: whatever had focus when it
     * was last opened. Tracked on the element because the overlay is built
     * once and reopened, and a receipt return opens it with nothing focused.
     */
    launcher: HTMLElement | null = null;

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

    /**
     * Reads the attributes, resolves the WordPress base URL, and renders the
     * chosen display style. Runs again when an SPA reattaches the element, so
     * everything after the initialized guard happens once.
     */
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

    /**
     * Drops the document-level listeners; the DOM subtree is left intact so a
     * reattach does not rebuild the iframe.
     */
    disconnectedCallback() {
        window.removeEventListener('message', this.messageHandler);
        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
        }
    }

    /**
     * Label for the modal and new-tab launchers.
     */
    getButtonText(): string {
        return this.getAttribute('button-text') || I18N.donate;
    }

    /**
     * The iframe src for the form, pointed at the donation-form-view route
     * with the host page as origin-url so gateway redirects can come back.
     */
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

    /**
     * The form on its own page, for the new-tab launcher and the fallback link.
     */
    getStandaloneFormUrl(): string {
        const url = new URL(this.wpBase.toString());
        url.searchParams.set('givewp-route', 'donation-form-view');
        url.searchParams.set('form-id', this.formId);

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

    /**
     * The iframe src for the receipt of a donation that just returned from an
     * offsite gateway; the receipt id comes from the return params.
     */
    getReceiptViewUrl(): string {
        const params = new URLSearchParams(window.location.search);
        const url = new URL(this.wpBase.toString());
        url.searchParams.set('givewp-route', 'donation-confirmation-receipt-view');
        url.searchParams.set('receipt-id', params.get('givewp-receipt-id'));

        return url.toString();
    }

    /**
     * Renders the loading state and the hidden iframe into target, reveals the
     * iframe on the resizer handshake, and falls back to a link on timeout.
     * onInit runs after the reveal.
     */
    renderForm(src: string, target: HTMLElement, onInit?: () => void) {
        const loading = document.createElement('div');
        loading.className = 'givewp-embed__loading';
        loading.setAttribute('role', 'status');
        loading.setAttribute('aria-label', this.getAttribute('loading-text') || I18N.loading);

        const spinner = document.createElement('span');
        spinner.className = 'givewp-embed__spinner';
        loading.appendChild(spinner);

        const iframe = document.createElement('iframe');
        iframe.src = src;
        // Matches the title the WordPress embeds use, so tooling and donors see one name.
        iframe.title = this.getAttribute('form-title') || I18N.formTitle;
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

                    onInit?.();
                },
            },
            iframe
        );
    }

    /**
     * Replaces the loading state with a link to the standalone form when the
     * iframe never completed the handshake.
     */
    renderFallbackLink(loading: HTMLElement) {
        const link = document.createElement('a');
        link.href = this.getStandaloneFormUrl();
        link.target = '_blank';
        link.rel = 'noopener';
        link.className = 'givewp-donation-form-link';
        link.textContent = this.getAttribute('fallback-text') || I18N.openForm;

        loading.replaceWith(link);
        this.iframe?.remove();
        this.iframe = null;

        // In the modal the overlay waits for the handshake; show it now so
        // the donor can reach the link.
        this.setLauncherLoading(false);
        this.showOverlay();
    }

    /**
     * A styled link that opens the standalone form in a new tab.
     */
    renderNewTabButton() {
        const link = document.createElement('a');
        link.href = this.getStandaloneFormUrl();
        link.target = '_blank';
        link.rel = 'noopener';
        link.className = 'givewp-donation-form-link';
        link.textContent = this.getButtonText();

        this.appendChild(link);
    }

    /**
     * A button that opens the form in the modal overlay. While the form loads
     * it shows a spinner in place of its label, as the WordPress block does.
     */
    renderModalButton(src: string) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'givewp-donation-form-modal__open';

        const label = document.createElement('span');
        label.className = 'givewp-donation-form-modal__open__label';
        label.textContent = this.getButtonText();
        button.appendChild(label);

        button.addEventListener('click', () => this.openModal(src));

        this.appendChild(button);
        this.modalButton = button;
    }

    /**
     * Swaps the launcher label for a spinner while the form loads: the same
     * markup and attribute as the block's launcher, so its styles apply.
     */
    setLauncherLoading(isLoading: boolean) {
        const button = this.modalButton;
        if (!button) {
            return;
        }

        // The visible label is the button's accessible name; only while loading is it replaced.
        // data-pending is the attribute react-aria's Button sets in the block, so the shared CSS applies.
        button.toggleAttribute('data-pending', isLoading);
        button.querySelector('.givewp-donation-form-modal__open__spinner')?.remove();

        if (!isLoading) {
            button.removeAttribute('aria-label');
            return;
        }

        button.setAttribute('aria-label', this.getAttribute('loading-text') || I18N.loading);
        const spinner = document.createElement('span');
        spinner.className = 'givewp-donation-form-modal__open__spinner';
        spinner.setAttribute('aria-hidden', 'true');
        button.appendChild(spinner);
    }

    /**
     * Opens the modal, building it on first open. Like the WordPress block,
     * the overlay stays hidden and the launcher shows a spinner until the
     * form inside has completed the resizer handshake. Focus returns to the
     * launcher on close and stays inside the dialog while open.
     */
    openModal(src: string) {
        this.launcher = document.activeElement as HTMLElement | null;

        if (this.overlay) {
            this.showOverlay();
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'givewp-donation-form-modal__overlay';
        overlay.style.display = 'none';

        const dialog = document.createElement('div');
        dialog.className = 'givewp-donation-form-modal';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('aria-label', this.getAttribute('form-title') || I18N.formTitle);
        dialog.tabIndex = -1;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'givewp-donation-form-modal__close';
        close.setAttribute('aria-label', this.getAttribute('close-text') || I18N.close);
        close.innerHTML = CLOSE_ICON_SVG;

        close.addEventListener('click', () => this.hideOverlay());
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                this.hideOverlay();
            }
        });
        this.keydownHandler = (event: KeyboardEvent) => {
            if (this.isOverlayOpen() && event.key === 'Escape') {
                this.hideOverlay();
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
        this.setLauncherLoading(true);
        this.renderForm(src, dialog, () => {
            this.setLauncherLoading(false);
            this.showOverlay();
        });
        dialog.appendChild(endGuard);
    }

    isOverlayOpen(): boolean {
        return !!this.overlay && this.overlay.style.display !== 'none';
    }

    /**
     * Reveals the overlay with the block's enter animation and moves focus
     * into the dialog.
     */
    showOverlay() {
        const overlay = this.overlay;
        const dialog = overlay?.querySelector<HTMLElement>('.givewp-donation-form-modal');
        if (!overlay || !dialog) {
            return;
        }

        overlay.style.display = '';
        // The iframe was laid out while hidden; ask for a fresh height, as the block does on init.
        (this.iframe as any)?.iFrameResizer?.resize();
        overlay.removeAttribute('data-exiting');
        overlay.setAttribute('data-entering', 'true');
        dialog.setAttribute('data-entering', 'true');
        dialog.addEventListener(
            'animationend',
            () => {
                overlay.removeAttribute('data-entering');
                dialog.removeAttribute('data-entering');
            },
            {once: true}
        );

        this.focusDialog();
    }

    /**
     * Plays the block's exit animation, then hides the overlay and returns
     * focus to the launcher. A timer rather than animationend, so a host
     * page that disables animations still closes the modal.
     */
    hideOverlay() {
        const overlay = this.overlay;
        if (!overlay || !this.isOverlayOpen() || overlay.hasAttribute('data-exiting')) {
            return;
        }

        overlay.removeAttribute('data-entering');
        overlay.setAttribute('data-exiting', 'true');
        window.setTimeout(() => {
            overlay.style.display = 'none';
            overlay.removeAttribute('data-exiting');
            this.launcher?.focus();
        }, EXIT_ANIMATION_MS);
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
        const dialog = this.overlay?.querySelector<HTMLElement>('.givewp-donation-form-modal');

        (dialog?.querySelector<HTMLElement>('.givewp-donation-form-modal__close') ?? dialog)?.focus();
    }
}

if (!customElements.get('givewp-donation-form')) {
    customElements.define('givewp-donation-form', GiveWPDonationForm);
}
