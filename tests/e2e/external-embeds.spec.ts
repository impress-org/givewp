import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {
    donationForm,
    expectReceipt,
    fillDonorDetails,
    payWithTestGateway,
    waitForForm,
} from './utils/donation-form';
import {WP_BASE_URL} from './environment';
import path from 'path';

/**
 * A v3 donation form embedded on a non-WordPress site via externalFormEmbed.js.
 *
 * There is no second server: navigation to a fictitious external origin is fulfilled with fixture
 * HTML that loads the real embed script and points at the real wp-env site. The iframe request
 * then crosses origins for real, which is the whole point - the SecurityError guards, the URL
 * parameter fallbacks, and the embed script itself only show their behavior cross-origin.
 */

// Match the WordPress protocol: an https fixture page loading an http embed
// script (CI runs wp-env on plain http) would be blocked as mixed content.
const EXTERNAL_ORIGIN = `${new URL(WP_BASE_URL).protocol}//external-site.test`;
const EXTERNAL_PAGE = `${EXTERNAL_ORIGIN}/donate`;

/*
 * The script is served from the fixture origin and fulfilled with the local build artifact rather
 * than fetched from wp-env: the plugin's mount path under wp-content/plugins depends on the
 * checkout directory name (give locally, givewp in CI), and the script's behavior doesn't - it
 * only cares about the wp-url attribute.
 */
const EXTERNAL_SCRIPT_URL = `${EXTERNAL_ORIGIN}/externalFormEmbed.js`;
const EXTERNAL_SCRIPT_PATH = path.join(process.cwd(), 'build/externalFormEmbed.js');

function externalPageHtml(formId: number, wpUrl: string = WP_BASE_URL, attributes: string = ''): string {
    return `<!DOCTYPE html>
<html>
<head><title>External donation page</title></head>
<body>
    <h1>Support our cause</h1>
    <givewp-donation-form form-id="${formId}" wp-url="${wpUrl}" ${attributes}></givewp-donation-form>
    <script src="${EXTERNAL_SCRIPT_URL}" defer></script>
</body>
</html>`;
}

/*
 * Donors on an external site are never logged into WordPress.
 *
 * Private Network Access checks are a test-environment artifact: the fixture page's public origin
 * requests wp-env, which resolves to 127.0.0.1, and Chrome blocks public-to-local requests. A real
 * embed talks to a public WordPress site, where PNA never applies.
 */
test.use({
    storageState: {cookies: [], origins: []},
    launchOptions: {
        args: [
            '--disable-features=LocalNetworkAccessChecks,PrivateNetworkAccessChecks,BlockInsecurePrivateNetworkRequests',
        ],
    },
});

test.describe('External donation form embeds', () => {
    let formId: number;

    test.beforeAll(async ({requestUtils}) => {
        ({formId} = await createCampaignWithForm(requestUtils));
    });

    test.beforeEach(async ({page}) => {
        await page.route(EXTERNAL_SCRIPT_URL, (route) =>
            route.fulfill({contentType: 'application/javascript', path: EXTERNAL_SCRIPT_PATH})
        );
        await page.route(`${EXTERNAL_PAGE}*`, (route) =>
            route.fulfill({contentType: 'text/html', body: externalPageHtml(formId)})
        );
    });

    test('renders the form cross-origin', async ({page}) => {
        await page.goto(EXTERNAL_PAGE);

        /*
         * The form app mounting is the regression gate for every cross-origin guard: before them,
         * reading window.top at module scope threw a SecurityError that killed the whole bundle.
         */
        await waitForForm(donationForm(page));

        const iframe = page.locator('givewp-donation-form iframe');
        await expect(iframe).toHaveAttribute('title', 'Donation Form');
        await expect(iframe).toHaveAttribute('src', /origin-url=/);
        await expect(iframe).toHaveAttribute('src', /embed-id=/);
    });

    test('takes a guest donation through to the receipt', async ({page}) => {
        await page.goto(EXTERNAL_PAGE);

        const form = donationForm(page);
        await waitForForm(form);

        await form.getByRole('button', {name: 'Donate now'}).click();

        await fillDonorDetails(form);
        await form.getByRole('button', {name: 'Continue'}).click();

        await payWithTestGateway(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        await expectReceipt(form, '$10.00');
    });

    test('swaps to the receipt view when an offsite gateway returns', async ({page}) => {
        /*
         * Simulates the return leg: WordPress redirects the donor back to the external page with
         * the RouteListener args. The embed swaps its iframe to the receipt view route; whether a
         * given receipt id renders content is the server's business, covered elsewhere.
         */
        const receiptId = 'a'.repeat(32);
        const returnParams = new URLSearchParams({
            'givewp-event': 'donation-completed',
            'givewp-listener': 'show-donation-confirmation-receipt',
            'givewp-embed-id': 'givewp-embed-external-0',
            'givewp-receipt-id': receiptId,
        });

        await page.goto(`${EXTERNAL_PAGE}?${returnParams}`);

        const iframe = page.locator('givewp-donation-form iframe');
        await expect(iframe).toHaveAttribute('src', /donation-confirmation-receipt-view/);
        await expect(iframe).toHaveAttribute('src', new RegExp(`receipt-id=${receiptId}`));

        // The one-time return params are consumed and removed from the URL.
        await expect(page).toHaveURL(EXTERNAL_PAGE);
    });

    test('opens the form in an overlay with the modal display style', async ({page}) => {
        await page.route(`${EXTERNAL_PAGE}*`, (route) =>
            route.fulfill({
                contentType: 'text/html',
                body: externalPageHtml(formId, WP_BASE_URL, 'display-style="modal" button-text="Give now"'),
            })
        );

        await page.goto(EXTERNAL_PAGE);

        await page.getByRole('button', {name: 'Give now'}).click();

        const form = donationForm(page);
        await waitForForm(form);
        await expect(page.locator('.givewp-embed__overlay')).toBeVisible();

        await page.locator('.givewp-embed__close').click();
        await expect(page.locator('.givewp-embed__overlay')).toBeHidden();
    });

    test('degrades to a link when the form cannot load', async ({page}) => {
        await page.route(`${EXTERNAL_PAGE}*`, (route) =>
            route.fulfill({
                contentType: 'text/html',
                // .invalid never resolves, so the iframe never fires load and the timeout runs.
                body: externalPageHtml(formId, 'https://blackhole.invalid'),
            })
        );

        await page.goto(EXTERNAL_PAGE);

        const fallback = page.locator('givewp-donation-form a');
        await expect(fallback).toHaveText('Open donation form', {timeout: 15_000});
        await expect(fallback).toHaveAttribute('href', /donation-form-view/);
        await expect(page.locator('givewp-donation-form iframe')).toHaveCount(0);
    });
});
