import {expect, Locator, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {chooseOption, openOptions, scrollMenuToEnd} from './utils/async-select';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {failedCalls, watchRestCalls} from './utils/rest';
import {createStandaloneForm} from './utils/standalone-form';
import {wp} from './utils/wp-cli';

/**
 * The Donations list: which form a donation went to, and filtering the list by it.
 *
 * Now that a form can live without a campaign, "No campaign" says nothing about where a donation
 * came from, so the list names the form and filters by it. The column's rules and the endpoint's
 * `formId` filter are covered by PHPUnit; what is not is whether the React list reaches the route
 * with what the admin picked, whether the form menu loads, pages and fits the page, and whether
 * the list narrows to what was picked.
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

        const standaloneRow = donationRow(page, standalone);
        await expect(standaloneRow).toContainText('No campaign');
        await expect(standaloneRow.getByRole('link', {name: title})).toHaveAttribute(
            'href',
            new RegExp(`post\\.php\\?post=${formId}&action=edit`)
        );

        // A donation in a campaign names the campaign as well as the form, and each links to its own screen.
        const campaignRow = donationRow(page, inCampaign);
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

        await expect(donationRow(page, standalone)).toBeVisible();
        await expect(donationRow(page, inCampaign)).toBeVisible();

        await chooseOption(page, formFilter(page), title);
        await expect(donationRow(page, standalone)).toBeVisible();
        await expect(donationRow(page, inCampaign)).toHaveCount(0);

        // The standalone form's donations are the ones with no campaign, so the two filters agree. The
        // list reloads when a filter changes and a reload drops what is typed into the next one, so wait it out.
        const reloaded = page.waitForResponse((response) => response.url().includes('campaignId=none'));
        await chooseOption(page, campaignFilter(page), 'No campaign');
        await reloaded;
        await expect(donationRow(page, standalone)).toBeVisible();

        // A campaign's form and "No campaign" share no donations, so the list empties.
        await chooseOption(page, formFilter(page), campaign.title);
        await expect(donationRow(page, standalone)).toHaveCount(0);
        await expect(donationRow(page, inCampaign)).toHaveCount(0);

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
        await chooseOption(page, formFilter(page), title, `${title} (Draft)`);

        await expect(donationRow(page, donation)).toBeVisible();
    });

    test('searches past the first page of forms', async ({page, admin}) => {
        const prefix = `Paging form ${Date.now()}`;

        // Forms in the menu are posts, so plain ones are enough to fill it. More than a page of them.
        wp(
            'eval',
            `for ($i = 1; $i <= 32; $i++) { wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => sprintf('${prefix} %02d', $i)]); }`
        );

        await visitDonations(page, admin);
        await formFilter(page).fill(prefix);

        await expect(openOptions(page)).toHaveCount(30);

        const last = openOptions(page).filter({hasText: `${prefix} 32`});

        await expect
            .poll(
                async () => {
                    await scrollMenuToEnd(page);

                    return last.count();
                },
                {message: 'Scrolling the menu to its end should load the next page of forms'}
            )
            .toBe(1);
    });

    test('opens the form menu without adding a horizontal scrollbar at a narrow width', async ({
        page,
        admin,
        requestUtils,
    }) => {
        await createCampaignWithForm(requestUtils);
        await page.setViewportSize({width: 1000, height: 800});

        await visitDonations(page, admin);
        await formFilter(page).click();

        await expect(openOptions(page).first()).toBeVisible();

        // The filter sits near the right edge, so a menu anchored to its left edge would run off the page.
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);

        expect(overflow, 'The open menu should not make the page scroll sideways').toBeLessThanOrEqual(0);
    });

    test('selects only the filtered donations for a bulk action', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        await visitDonations(page, admin);
        await chooseOption(page, formFilter(page), title);
        await expect(donationRow(page, inCampaign)).toHaveCount(0);

        await page.getByRole('checkbox', {name: 'Select all donations'}).check();

        // The action is read, not run: the confirmation lists what it would apply to, then it is cancelled.
        await page.getByText('Bulk Actions', {exact: true}).click();
        await openOptions(page).filter({hasText: 'Resend Email Receipts'}).click();
        await page.getByRole('button', {name: 'Apply'}).click();

        const dialog = page.getByRole('dialog').filter({hasText: 'Resend Email Receipts for following donations'});
        await expect(dialog).toContainText(`${standalone} from`);
        await expect(dialog).not.toContainText(`${inCampaign} from`);

        await dialog.getByRole('button', {name: 'Cancel'}).click();
    });
});

/**
 * Opens the list and waits for its rows, so the filters are mounted by the time a test types into them.
 */
async function visitDonations(page: Page, admin): Promise<void> {
    await admin.visitAdminPage('edit.php', DONATIONS_LIST);
    await expect(page.getByRole('heading', {level: 1, name: 'Donations'})).toBeVisible({timeout: 15_000});
    await expect(page.getByRole('row', {name: /^Select donation \d+ /}).first()).toBeVisible({timeout: 15_000});
}

/**
 * The list row for donation `id`.
 */
function donationRow(page: Page, id: number): Locator {
    return page.getByRole('row', {name: new RegExp(`^Select donation ${id} `)});
}

function formFilter(page: Page): Locator {
    return page.getByRole('combobox', {name: 'filter donations by form'});
}

function campaignFilter(page: Page): Locator {
    return page.getByRole('combobox', {name: 'filter donations by campaign'});
}
