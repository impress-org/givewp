import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {editForm, section} from './utils/form';
import {createLegacyForm} from './utils/legacy-form';
import {createDonorAccount, deleteDonorAccount, DonorAccount} from './utils/donor';
import {logInAsDonor} from './utils/donor-dashboard';
import {DONOR, donationForm, expectReceipt, payWithTestGateway, waitForForm} from './utils/donation-form';
import {captureOffsiteRedirect, enableTestOffsiteGateway, TEST_OFFSITE_GATEWAY} from './utils/offsite-gateway';

/**
 * A signed-in donor donates with the email on their donor record, not one they type.
 *
 * The donor record and the WordPress account can carry different addresses, so the fixtures give
 * them separate emails. That is what makes these tests prove the form reads the donor record rather
 * than the WordPress user: a form that prefilled from the account would show the wrong address.
 */

test.use({storageState: {cookies: [], origins: []}});

test.describe('Logged-in donor email', () => {
    let donor: DonorAccount;
    let donorEmail: string;

    test.beforeAll(() => {
        const suffix = `${Date.now()}${Math.floor(Math.random() * 1000)}`;
        donorEmail = `logged-in-primary-${suffix}@example.test`;
        donor = createDonorAccount(donorEmail);
    });

    test.afterAll(() => {
        deleteDonorAccount(donor);
    });

    test('v3 form prefills, locks, and submits the donor primary email', async ({page, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await logInAsDonor(page, {login: donor.username, password: donor.password, donorId: donor.donorId});
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        const email = form.getByLabel('Email Address');
        await expect(email).toHaveValue(donorEmail);
        await expect(email).toBeDisabled();
        await expect(email).toHaveJSProperty('readOnly', true);

        await form.getByLabel('First name').fill(DONOR.firstName);
        await form.getByLabel('Last name').fill(DONOR.lastName);
        await form.getByRole('button', {name: 'Continue'}).click();

        await payWithTestGateway(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        await expectReceipt(form, '$10.00', donorEmail);
    });

    test('v3 form rejects a tampered email on the server', async ({page, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await logInAsDonor(page, {login: donor.username, password: donor.password, donorId: donor.donorId});
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        /*
         * The field is disabled in the browser, so changing the submitted body is what a client that
         * ignores the field would do. The signed route still has to refuse it.
         */
        const tamperedEmail = `tampered-${Date.now()}@example.test`;

        await page.route(/givewp-route=validate/, async (route) => {
            const body = route.request().postDataBuffer();

            if (!body || !body.includes(donorEmail)) {
                await route.continue();
                return;
            }

            await route.continue({postData: body.toString('utf8').replaceAll(donorEmail, tamperedEmail)});
        });

        await form.getByLabel('First name').fill(DONOR.firstName);
        await form.getByLabel('Last name').fill(DONOR.lastName);
        await form.getByRole('button', {name: 'Continue'}).click();

        await expect(form.locator('#givewp-field-error-email')).toContainText('primary email address');
        await expect(form.getByLabel('First name')).toBeVisible();
    });

    test('legacy form rejects a tampered email on the server', async ({page}) => {
        const {formId} = createLegacyForm();
        enableTestOffsiteGateway(2);

        await logInAsDonor(page, {login: donor.username, password: donor.password, donorId: donor.donorId});
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = page.frameLocator('iframe[name="give-embed-form"]');

        await form.locator('.give-section.introduction .advance-btn').click();
        await form.locator('.give-section.choose-amount .advance-btn').click();
        await form.locator('#give-first').fill(DONOR.firstName);
        await form.locator('#give-last').fill(DONOR.lastName);
        await form.getByText('Donate with Test Gateway (Offsite)').click();
        await form.locator('input[type="hidden"][name="give_email"]').evaluate((input: HTMLInputElement) => {
            input.value = 'tampered@example.test';
        });

        const [response] = await Promise.all([
            page.waitForResponse((response) => {
                const request = response.request();
                return (
                    request.url().includes('admin-ajax.php') &&
                    request.postData()?.includes('action=give_process_donation')
                );
            }),
            form.locator('#give-purchase-button').click(),
        ]);

        expect(await response.text()).toContain('must use your account primary email address');
    });

    test('legacy form prefills, locks, and submits the donor primary email', async ({page}) => {
        const {formId} = createLegacyForm();
        enableTestOffsiteGateway(2);

        await logInAsDonor(page, {login: donor.username, password: donor.password, donorId: donor.donorId});
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = page.frameLocator('iframe[name="give-embed-form"]');

        await form.locator('.give-section.introduction .advance-btn').click();
        await form.locator('.give-section.choose-amount .advance-btn').click();

        const email = form.locator('#give-email');
        await expect(email).toHaveValue(donorEmail);
        await expect(email).toBeDisabled();
        await expect(email).toHaveJSProperty('readOnly', true);

        // The visible input is disabled and does not post, so this is the value the form sends.
        await expect(form.locator('input[type="hidden"][name="give_email"]')).toHaveAttribute('value', donorEmail);

        await form.locator('#give-first').fill(DONOR.firstName);
        await form.locator('#give-last').fill(DONOR.lastName);

        await form.getByText('Donate with Test Gateway (Offsite)').click();
        await expect(form.locator('input[name="give-gateway"]')).toHaveValue(TEST_OFFSITE_GATEWAY);

        const offsiteUrl = await captureOffsiteRedirect(page, async () => {
            await form.locator('#give-purchase-button').click();
        });

        await page.goto(offsiteUrl);

        const receipt = page.frameLocator('iframe[name="give-embed-form"]').locator('.give-receipt-wrap');
        await expect(receipt).toBeVisible({timeout: 30_000});
        await expect(receipt).toContainText(donorEmail);
        await expect(receipt).toContainText('Complete');
    });
    test('locks the email field in place after an inline login, without a reload', async ({page, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await editForm(requestUtils, formId, (form) => {
            section(form, "Who's Giving Today?").innerBlocks.unshift({
                name: 'givewp/login',
                attributes: {required: true, loginRedirect: false},
                innerBlocks: [],
            });
        });

        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        const email = form.getByLabel('Email Address');
        await expect(email).toBeEditable();

        await form.locator('input[name="login"]').fill(donor.username);
        await form.locator('input[name="password"]').fill(donor.password);
        await form.getByRole('button', {name: 'Log In'}).click();

        await expect(form.getByText('Thank you for your continued support.')).toBeVisible();
        await expect(email).toHaveValue(donorEmail);
        await expect(email).toBeDisabled();
        await expect(email).toHaveJSProperty('readOnly', true);
    });
});
