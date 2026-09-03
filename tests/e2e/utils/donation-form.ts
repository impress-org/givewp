import {expect, FrameLocator, Page, test} from '@wordpress/e2e-test-utils-playwright';

/**
 * Helpers shared by the specs that donate through a v3 form.
 */

export const DONOR = {
    firstName: 'Ada',
    lastName: 'Lovelace',
    email: 'ada@example.test',
};

/**
 * The embed iframe a v3 form renders into.
 *
 * Every format that shows the form on the page - the block, the shortcode, the modal - puts it in
 * this iframe, so one locator covers them all.
 */
export function donationForm(page: Page): FrameLocator {
    return page.frameLocator('iframe[title="Donation Form"]');
}

/**
 * Waits for the form app to mount.
 */
export async function waitForForm(form: FrameLocator): Promise<void> {
    await expect(form.locator('#root-givewp-donation-form')).toBeVisible({timeout: 30_000});
}

/**
 * Fills the donor fields the default form asks for.
 */
export async function fillDonorDetails(form: FrameLocator): Promise<void> {
    await form.getByLabel('First name').fill(DONOR.firstName);
    await form.getByLabel('Last name').fill(DONOR.lastName);
    await form.getByLabel('Email Address').fill(DONOR.email);
}

/**
 * Selects the Test Donation gateway, skipping the test if the site does not offer it.
 *
 * Test Donation is enabled on a fresh install and is the only gateway that can complete a donation
 * without an account somewhere else, so it is what these specs pay with. A site that has turned it
 * off cannot run them and says so rather than failing.
 */
export async function payWithTestGateway(form: FrameLocator): Promise<void> {
    await expect(form.locator('.givewp-fields-gateways__list')).toBeVisible();

    const testGateway = form.getByRole('radio', {name: 'Donate with Test Donation'});

    if ((await testGateway.count()) === 0) {
        test.skip(true, 'The Test Donation gateway is not enabled on this site.');
    }

    await testGateway.check();
}

/**
 * Asserts the receipt that replaces the form once a donation completes.
 *
 * The rows are read back off the donation that was just written, so reaching them is what makes a
 * passing donation test proof the donation was recorded rather than only that the request returned.
 */
export async function expectReceipt(form: FrameLocator, total: string): Promise<void> {
    await expect(form.locator('.details-row--email-address')).toContainText(DONOR.email, {timeout: 30_000});
    await expect(form.locator('.details-row--payment-status')).toContainText('Completed');
    await expect(form.locator('.details-row--donation-total')).toContainText(total);
}
