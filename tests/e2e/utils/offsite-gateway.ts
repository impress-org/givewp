import {Page} from '@wordpress/e2e-test-utils-playwright';
import {wp} from './wp-cli';

/**
 * The offsite donation path, which no gateway that ships enabled can walk.
 *
 * An offsite gateway takes the donor's whole browser window away to the processor and gets it back on
 * a signed `give-listener=give-gateway` URL, and the site never sees the processor in between. Test
 * Gateway (Offsite) stands in for the real ones because its "offsite" page is that return route
 * itself, so the two halves of the trip are one URL.
 */

export const TEST_OFFSITE_GATEWAY = 'test-offsite-gateway';

/**
 * The return route, which is also where this gateway sends the donor instead of a processor.
 */
const OFFSITE_REDIRECT = /give-listener=give-gateway/;

/**
 * Enables Test Gateway (Offsite) for one form generation, leaving whatever else the site has enabled
 * alone.
 *
 * The two generations read separate options, so a gateway enabled for v2 forms is invisible to v3
 * forms and the other way round. Neither option has a REST route - GiveWP's settings are one option -
 * so this goes through WP-CLI.
 */
export function enableTestOffsiteGateway(formVersion: 2 | 3): void {
    const option = formVersion === 2 ? 'gateways' : 'gateways_v3';
    const gateways = JSON.parse(wp('option', 'pluck', 'give_settings', option, '--format=json') || '{}');

    gateways[TEST_OFFSITE_GATEWAY] = 1;

    wp('option', 'patch', 'update', 'give_settings', option, JSON.stringify(gateways), '--format=json');
}

/**
 * Submits the form and returns the URL the gateway redirected the donor to, without letting the site
 * load it.
 *
 * Aborting it is what makes this an offsite trip rather than a same-site hop. A real processor is
 * somewhere else, so the site cannot answer its own return route while the donor is away, and on a v2
 * form it matters twice: the form lives in an iframe, and a return route loaded inside that iframe
 * still looks like form processing to `Helpers\Form\Utils::isProcessingForm()`, which rewrites the
 * redirect to the plain success page. Coming back through `page.goto()` instead is the arrival a
 * returning donor makes.
 */
export async function captureOffsiteRedirect(page: Page, submit: () => Promise<void>): Promise<string> {
    await page.route(OFFSITE_REDIRECT, (route) => route.abort());

    try {
        const [request] = await Promise.all([page.waitForRequest(OFFSITE_REDIRECT, {timeout: 30_000}), submit()]);

        return request.url();
    } finally {
        await page.unroute(OFFSITE_REDIRECT);
    }
}

/**
 * The return URL the gateway signed, which is where the donor lands when they come back.
 *
 * It is a URL inside a URL, and the reason these specs exist: it carries a query string of its own, so
 * writing it onto the route URL unencoded would split it into separate parameters that the route reads
 * back as a truncated return URL and a signature it can never match.
 */
export function signedReturnUrl(offsiteUrl: string): string {
    const returnUrl = new URL(offsiteUrl).searchParams.get('givewp-return-url');

    if (!returnUrl) {
        throw new Error(`The gateway redirect carried no return URL to come back to: ${offsiteUrl}`);
    }

    return returnUrl;
}
