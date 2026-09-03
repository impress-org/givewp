import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createLegacyForm} from './utils/legacy-form';
import {
    captureOffsiteRedirect,
    enableTestOffsiteGateway,
    signedReturnUrl,
    TEST_OFFSITE_GATEWAY,
} from './utils/offsite-gateway';

/**
 * A legacy (v2) donation form on the front end, paid through an offsite gateway.
 *
 * The form posts to the legacy processor, the gateway redirects the donor away with a signed return
 * URL, and coming back lands on GatewayRoute, which checks the signature before completing the
 * donation. Every gateway that leaves the site rides this path on v2 forms, and none of it is
 * reachable from PHPUnit: the return URL is assembled by the legacy adapter from the page the form
 * was embedded on, so only a real embed produces the shape that broke.
 *
 * The legacy adapter hands the gateway that return URL raw, and it carries a query string of its own
 * because an embedded form's receipt is the form's page plus `giveDonationAction=showReceipt`. That is
 * the ampersand the signature has to survive.
 */

// Donors are not logged in, and a logged-in admin's session would prefill and attach the donation.
test.use({storageState: {cookies: [], origins: []}});

test.describe('Legacy donation forms', () => {
    let formId: number;

    test.beforeAll(() => {
        enableTestOffsiteGateway(2);
        ({formId} = createLegacyForm());
    });

    test('takes an offsite donation out to the gateway and back to the receipt', async ({page}) => {
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = page.frameLocator('iframe[name="give-embed-form"]');

        await form.locator('.give-section.introduction .advance-btn').click();
        await form.locator('.give-section.choose-amount .advance-btn').click();

        await form.locator('#give-first').fill('Ada');
        await form.locator('#give-last').fill('Lovelace');
        await form.locator('#give-email').fill('ada@example.test');

        // The radio itself is hidden behind its styled label, and picking a gateway reloads its fields.
        await form.getByText('Donate with Test Gateway (Offsite)').click();
        await expect(form.locator('input[name="give-gateway"]')).toHaveValue(TEST_OFFSITE_GATEWAY);

        const offsiteUrl = await captureOffsiteRedirect(page, async () => {
            await form.locator('#give-purchase-button').click();
        });

        /*
         * Read whole off the route URL rather than in the pieces an unencoded ampersand would have left
         * behind. Without it the route sees a return URL cut off at the first argument and refuses the
         * signature, which is the failure this covers.
         */
        const returnUrl = signedReturnUrl(offsiteUrl);

        expect(returnUrl).toContain('giveDonationAction=showReceipt');
        expect(returnUrl).toContain(`payment-confirmation=${TEST_OFFSITE_GATEWAY}`);

        // Back from the processor, in the donor's own window rather than in the form's iframe.
        await page.goto(offsiteUrl);

        // The receipt for an embedded form is the page the form was on, showing the form's iframe again.
        await expect(page).toHaveURL(new RegExp(`payment-confirmation=${TEST_OFFSITE_GATEWAY}`));

        const receipt = page.frameLocator('iframe[name="give-embed-form"]').locator('.give-receipt-wrap');

        await expect(receipt).toBeVisible({timeout: 30_000});
        await expect(receipt).toContainText('ada@example.test');
        await expect(receipt).toContainText('Complete');
    });
});
