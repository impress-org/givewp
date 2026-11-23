import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {createDonorWithRenewal} from './utils/subscription';

/**
 * A donor's details page: the Recent Transactions panel on the Overview tab.
 *
 * The panel asks the donations route for the donor's five latest donations and labels each one
 * from a map in the React component. core-data drops a page that holds fewer rows than `per_page`
 * when the route's total says there are more, so a total that counts other donors' donations
 * leaves the panel empty with every request succeeding. The total is covered by PHPUnit. What is
 * not is whether the panel shows the rows, and what it calls a renewal.
 */
test.describe('Donor details', () => {
    test('lists a renewal as Completed in Recent Transactions', async ({page, admin, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);
        const donorId = createDonorWithRenewal(formId);

        // Another donor's donation, so the site holds more donations than this donor has.
        createDonation(formId);

        await admin.visitAdminPage('edit.php', `post_type=give_forms&page=give-donors&view=overview&id=${donorId}`);

        const heading = page.getByRole('heading', {name: 'Recent Transactions'});
        await expect(heading).toBeVisible({timeout: 15_000});

        // The build hashes the panel's class names, so its rows are found from the heading.
        const transactions = heading.locator('xpath=ancestor::header/following-sibling::div[1]');

        // The first donation and the renewal.
        await expect(transactions.getByText('Completed', {exact: true})).toHaveCount(2);
        await expect(transactions.getByText('Pending', {exact: true})).toHaveCount(0);
    });
});
