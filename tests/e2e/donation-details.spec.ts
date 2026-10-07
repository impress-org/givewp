import {expect, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {chooseOption} from './utils/async-select';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {failedCalls, watchRestCalls} from './utils/rest';
import {createStandaloneForm} from './utils/standalone-form';

/**
 * A donation's details page: the Records tab that moves it to another form, and the Overview tab
 * that says which campaign it belongs to.
 *
 * What the update does to the donation and its revenue is covered by PHPUnit. What is not is whether
 * the two pickers offer the right choices, whether Save reaches the donation route with what was
 * picked, and whether the page shows the result after a reload.
 */
test.describe('Donation details', () => {
    test('moves a donation from a campaign form to a standalone form', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {title} = await createStandaloneForm(page, admin);
        const donation = createDonation(campaign.formId);

        const calls = watchRestCalls(page);
        await visitDonation(page, admin, donation, 'records');

        await expect(page.getByText(campaign.title, {exact: true}).first()).toBeVisible();

        // Choosing "No campaign" narrows the forms on offer to the ones with no campaign.
        await chooseOption(page, page.getByRole('combobox', {name: 'Select a campaign'}), 'No campaign');
        await chooseOption(page, page.getByRole('combobox', {name: 'Select a form'}), title);

        const saved = page.waitForResponse(
            (response) => /givewp\/v3\/donations\/\d+/.test(response.url()) && response.request().method() !== 'GET'
        );
        await page.getByRole('button', {name: 'Save changes'}).click();
        expect((await saved).ok(), 'Saving the donation should succeed').toBe(true);

        // After a reload the page is reading what was stored, not what the pickers still hold.
        await page.reload();
        await expect(page.getByRole('button', {name: 'Save changes'})).toBeVisible();
        await expect(page.getByText(title, {exact: true}).first()).toBeVisible();
        await expect(page.getByText(campaign.title, {exact: true})).toHaveCount(0);

        expect(failedCalls(calls)).toEqual([]);
    });

    test('says "No campaign" on the overview of a donation to a standalone form', async ({page, admin}) => {
        const {formId} = await createStandaloneForm(page, admin);
        const donation = createDonation(formId);

        await visitDonation(page, admin, donation, 'overview');

        await expect(page.getByText('Campaign name')).toBeVisible();
        await expect(page.getByText('No campaign', {exact: true})).toBeVisible();
    });
});

async function visitDonation(page: Page, admin, id: number, tab: 'overview' | 'records'): Promise<void> {
    await admin.visitAdminPage('edit.php', `post_type=give_forms&page=give-payment-history&id=${id}&tab=${tab}`);
    await expect(page.getByRole('button', {name: 'Save changes'})).toBeVisible({timeout: 15_000});
}
