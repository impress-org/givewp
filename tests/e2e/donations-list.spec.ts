import {expect, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {failedCalls, watchRestCalls} from './utils/rest';
import {createStandaloneForm} from './utils/standalone-form';
import {wp} from './utils/wp-cli';
import {applyFilter, listRows, listTable, rowById, selectionBar} from './utils/list-table';

/**
 * The Donations list: which form a donation went to, and filtering the list by it.
 *
 * Now that a form can live without a campaign, "No campaign" says nothing about where a donation
 * came from, so the list names the form and filters by it. The column's rules and the endpoint's
 * `formId` filter are covered by PHPUnit; what is not is whether the list reaches the route with
 * what the admin picked, whether the form filter lists every form and fits the page, and whether
 * the list narrows to what was picked. The list renders through `@wordpress/dataviews`.
 *
 * Donations are made with `createDonation` and forms inside each test, so a run means the same thing
 * on a fresh install and on a developer site full of other donations.
 */
const DONATIONS_LIST = 'post_type=give_forms&page=give-payment-history';

test.describe('Donations list', () => {
    test('shows the form on each donation, and "No campaign" beside a standalone form', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        await visitDonations(page, admin);

        const standaloneRow = rowById(page, standalone);
        await expect(standaloneRow).toContainText('No campaign');
        await expect(standaloneRow.getByRole('link', {name: title})).toHaveAttribute(
            'href',
            new RegExp(`post\\.php\\?post=${formId}&action=edit`)
        );

        // A donation in a campaign names the campaign as well as the form, and each links to its own screen.
        const campaignRow = rowById(page, inCampaign);
        await expect(campaignRow).not.toContainText('No campaign');
        await expect(campaignRow.getByRole('link', {name: 'Visit campaign page'})).toHaveAttribute(
            'href',
            new RegExp(`page=give-campaigns&id=${campaign.campaignId}`)
        );
        await expect(campaignRow.getByRole('link', {name: campaign.title})).toHaveAttribute(
            'href',
            new RegExp(`post\\.php\\?post=${campaign.formId}&action=edit`)
        );
    });

    test('filters by form and combines with the campaign filter', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        const calls = watchRestCalls(page);
        await visitDonations(page, admin);

        await expect(rowById(page, standalone)).toBeVisible();
        await expect(rowById(page, inCampaign)).toBeVisible();

        await applyFilter(page, 'Form', title);
        await expect(rowById(page, standalone)).toBeVisible();
        await expect(rowById(page, inCampaign)).toHaveCount(0);

        // The standalone form's donations are the ones with no campaign, so the two filters agree. The
        // request has to carry both filters: dropping the form one would still leave this row showing.
        const reloaded = page.waitForResponse(
            (response) => response.url().includes('campaignId=none') && response.url().includes(`formId=${formId}`)
        );
        await applyFilter(page, 'Campaign', 'No campaign');
        await reloaded;
        await expect(rowById(page, standalone)).toBeVisible();

        // A campaign's form and "No campaign" share no donations, so the list empties and says why.
        await applyFilter(page, 'Form', campaign.title);
        await expect(rowById(page, standalone)).toHaveCount(0);
        await expect(rowById(page, inCampaign)).toHaveCount(0);
        await expect(listTable(page)).toContainText('No donations match your search or filters.');

        expect(
            calls.some((call) => call.url.includes(`formId=${formId}`)),
            'The filter should send the form it was given'
        ).toBe(true);
        expect(failedCalls(calls)).toEqual([]);
    });

    test('lists a draft form so its donations can still be filtered', async ({page, admin, requestUtils}) => {
        const {formId, title} = await createStandaloneForm(page, admin);
        const donation = createDonation(formId);

        // A form taken offline keeps its donations, and archiving a campaign takes its forms offline.
        await requestUtils.rest({method: 'POST', path: `/wp/v2/give_forms/${formId}`, data: {status: 'draft'}});

        await visitDonations(page, admin);
        await applyFilter(page, 'Form', title, `${title} (Draft)`);

        await expect(rowById(page, donation)).toBeVisible();
    });

    test('lists forms past the first hundred', async ({page, admin, requestUtils}) => {
        const prefix = `Paging form ${Date.now()}`;

        // The list waits for a donation row before the filters are used, so a fresh install needs one.
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        // The REST API pages at 100, so more than that proves the filter reads every page.
        wp(
            'eval',
            `for ($i = 1; $i <= 101; $i++) { wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => sprintf('${prefix} %03d', $i)]); }`
        );

        await visitDonations(page, admin);

        // Picking the 101st form is the proof: the option is only there if the second page loaded.
        const filtered = page.waitForResponse((response) => response.url().includes('formId='));
        await applyFilter(page, 'Form', `${prefix} 101`);
        await filtered;
    });

    test('opens the form filter without adding a horizontal scrollbar at a narrow width', async ({
        page,
        admin,
        requestUtils,
    }) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);
        await page.setViewportSize({width: 1000, height: 800});

        await visitDonations(page, admin);
        await listTable(page).getByRole('button', {name: 'Filter', exact: true}).click();

        const addFilter = listTable(page).getByRole('button', {name: 'Add filter', exact: true});
        if (await addFilter.isVisible()) {
            await addFilter.click();
        }
        await page.getByRole('menuitem', {name: 'Form', exact: true}).click();
        await expect(page.locator('.dataviews-filters__summary-popover').getByRole('option').first()).toBeVisible();

        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);

        expect(overflow, 'The open filter should not make the page scroll sideways').toBeLessThanOrEqual(0);
    });

    test('selects only the filtered donations for a bulk action', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        await visitDonations(page, admin);
        await applyFilter(page, 'Form', title);
        await expect(rowById(page, inCampaign)).toHaveCount(0);

        await rowById(page, standalone).getByRole('checkbox').check();
        await expect(selectionBar(page)).toContainText('1 Item selected');

        // The action is read, not run: the confirmation lists what it would apply to, then it is cancelled.
        await selectionBar(page).getByRole('button', {name: 'Resend Email Receipts'}).click();

        const dialog = page.getByRole('dialog', {name: 'Resend Email Receipts'});
        await expect(dialog).toContainText(String(standalone));
        await expect(dialog).not.toContainText(String(inCampaign));

        await dialog.getByRole('button', {name: 'Cancel'}).click();
        await expect(selectionBar(page)).toContainText('1 Item selected');
    });
});

/**
 * Opens the list and waits for its rows, so the filters are mounted by the time a test uses them.
 */
async function visitDonations(page: Page, admin): Promise<void> {
    await admin.visitAdminPage('edit.php', DONATIONS_LIST);
    await expect(page.getByRole('heading', {level: 1, name: 'Donations'})).toBeVisible({timeout: 15_000});
    await expect(listRows(page).first()).toBeVisible({timeout: 15_000});
}
