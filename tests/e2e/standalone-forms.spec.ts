import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {donationForm, expectReceipt, fillDonorDetails, payWithTestGateway, waitForForm} from './utils/donation-form';
import {createStandaloneForm} from './utils/standalone-form';

/**
 * A donation form with no campaign, made from the forms list's Add form button.
 *
 * Every other v3 spec gets its form from a campaign, so this is the one that proves a form can be
 * made, listed and paid through without one. `CreateFormRoute` and the list endpoint are covered by
 * PHPUnit; what is not is whether the button reaches that route, whether the builder can publish
 * what it made, and whether a donor can then give to it. The E2E workflow runs this with the add-ons
 * in `ADDONS` active, which is what smoke tests them against a form outside a campaign.
 */
test.describe('Standalone donation forms', () => {
    test('Add form makes a form with no campaign that takes a donation', async ({page, admin}) => {
        const {formId, title} = await createStandaloneForm(page, admin);

        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-forms');

        const row = page.getByRole('row', {name: new RegExp(title)});

        await expect(row).toContainText('No campaign', {timeout: 15_000});
        await expect(row).toContainText('Published');

        // Donors are not logged in, and a logged-in donor gets a prefilled email and a linked account.
        await page.context().clearCookies();
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);

        await form.getByRole('button', {name: 'Donate now'}).click();
        await form.getByRole('button', {name: 'Continue'}).click();

        await fillDonorDetails(form);
        await form.getByRole('button', {name: 'Continue'}).click();

        await payWithTestGateway(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        await expectReceipt(form, '$10.00');
    });
});
