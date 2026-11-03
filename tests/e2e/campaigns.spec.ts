import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {donationForm, expectReceipt, fillDonorDetails, payWithTestGateway, waitForForm} from './utils/donation-form';
import {createCampaignWithForm} from './utils/campaign';
import {
    allRecords,
    chip,
    expectListMounts,
    expectNoBrowserDialogs,
    filterBar,
    listRows,
    nextListRequest,
    popover,
    visitList,
} from './utils/list-table';
import {resetLayout} from './utils/user';
import {wp} from './utils/wp-cli';

const CAMPAIGNS = 'post_type=give_forms&page=give-campaigns';
const CAMPAIGNS_REQUEST = /givewp\/v3\/campaigns\/list-table\?/;
const FORMS_REQUEST = /give-api\/v2\/admin\/forms\?/;

type V3Campaign = {id: number; status: string};

/**
 * Creating a campaign, through the wizard a fundraiser actually uses.
 *
 * A campaign is where a v3 donation form comes from - `CreateDefaultCampaignForm` runs on
 * `givewp_campaign_created` - so the wizard is the first half of every donation the plugin takes.
 * The REST route behind it is covered by PHPUnit; what is not is whether the two-step modal reaches
 * that route with what the fundraiser typed, and whether the form it produces can take money.
 */
test.describe('Campaigns', () => {
    test('the list renders through the list engine without errors', async ({page, admin}) => {
        await expectListMounts(page, admin, 'post_type=give_forms&page=give-campaigns', 'Campaigns');
    });

    test('the wizard creates a campaign and opens its overview', async ({page, admin}) => {
        const title = `Wizard campaign ${Date.now()}`;

        await createCampaignThroughWizard(page, admin, title);

        await expect(page.getByRole('heading', {name: title})).toBeVisible();
        await expect(page.getByText('$1,000.00')).toBeVisible();

        // The overview names the form the campaign was given, which is what makes it able to fundraise.
        await expect(page.getByText('Default campaign form')).toBeVisible();
    });

    test('the campaign it creates comes with a form that takes a donation', async ({page, admin}) => {
        const title = `Wizard campaign ${Date.now()}`;

        const campaignId = await createCampaignThroughWizard(page, admin, title);

        /*
         * The campaign's Forms tab is where a fundraiser sees the form they just got, so read the
         * form's id back from the row rather than from the API - that way the test fails if the tab
         * stops listing it.
         */
        await page.getByText('Forms', {exact: true}).first().click();

        const formRow = page.getByRole('row', {name: new RegExp(title)});

        await expect(formRow).toContainText('Published');

        // The row's "Edit" is an action button now, so the id comes from the row's ID badge.
        const formId = Number(await formRow.locator('.idBadge').textContent());

        expect(formId, `Campaign ${campaignId} should list a default form`).toBeTruthy();

        // Donors are not logged in, and a logged-in donor gets a prefilled email and a linked account.
        await page.context().clearCookies();
        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);

        await form.getByRole('button', {name: 'Donate now'}).click();

        await fillDonorDetails(form);
        await form.getByRole('button', {name: 'Continue'}).click();

        await payWithTestGateway(form);
        await form.getByRole('button', {name: 'Donate now'}).click();

        await expectReceipt(form, '$10.00');
    });
});

/**
 * The shared list filters on Campaigns. Expected counts come from the v3 REST API, which does not
 * share the list endpoint's filter code. No test opens a browser dialog.
 */
test.describe('Campaigns list filters', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'campaigns');
    });

    test('the Status filter offers the four statuses admins set, named as the badges name them', async ({
        page,
        admin,
    }) => {
        await visitList(page, admin, CAMPAIGNS);
        await chip(page, 'Status').click();

        await expect(popover(page).getByRole('checkbox')).toHaveCount(4);

        for (const label of ['Active', 'Inactive', 'Archived', 'Draft']) {
            await expect(popover(page).getByRole('checkbox', {name: label, exact: true})).toBeVisible();
        }
    });

    test('Draft shows only the draft campaigns, all of them', async ({page, admin, requestUtils}) => {
        // A draft of its own, so the filter has a row to show on an empty site too.
        const {campaignId} = await createCampaignWithForm(requestUtils);
        // The REST route cannot set a campaign back to draft, so this goes through the model.
        wp(
            'eval',
            `$campaign = \\Give\\Campaigns\\Models\\Campaign::find(${campaignId}); $campaign->status = \\Give\\Campaigns\\ValueObjects\\CampaignStatus::DRAFT(); $campaign->save();`
        );

        const drafts = await allRecords<V3Campaign>(requestUtils, '/givewp/v3/campaigns', {status: ['draft']});

        await visitList(page, admin, CAMPAIGNS);
        await chip(page, 'Status').click();

        const filtered = nextListRequest(
            page,
            CAMPAIGNS_REQUEST,
            (url) => url.searchParams.get('statuses') === 'draft'
        );
        await popover(page).getByRole('checkbox', {name: 'Draft', exact: true}).check();
        const response = await filtered;
        await page.keyboard.press('Escape');

        expect(drafts.map((draft) => draft.id)).toContain(campaignId);
        expect(Number(((await response.json()) as {totalItems: number | string}).totalItems)).toBe(drafts.length);

        const rows = listRows(page);
        await expect(rows.first()).toBeVisible();

        for (const row of await rows.all()) {
            await expect(row).toContainText('Draft');
            await expect(row).not.toContainText(/\b(Active|Inactive|Archived)\b/);
        }
    });
});

/**
 * The shared list filters on a campaign's Forms tab, where the campaign is fixed by the page.
 */
test.describe('Campaign Forms tab filters', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'campaign-forms');
    });

    test('has no Campaign filter, and Reset keeps the campaign rows', async ({page, admin, requestUtils}) => {
        const {campaignId} = await createCampaignWithForm(requestUtils);
        const query = `${CAMPAIGNS}&id=${campaignId}&tab=forms`;
        const first = nextListRequest(page, FORMS_REQUEST);
        await visitList(page, admin, query);
        const initial = Number(((await (await first).json()) as {totalItems: number | string}).totalItems);

        await expect(chip(page, 'Campaign')).toHaveCount(0);

        const addFilterButton = filterBar(page).getByRole('button', {name: 'Add filter'});

        if (await addFilterButton.isVisible()) {
            await addFilterButton.click();
            await expect(page.getByRole('menuitem', {name: 'Campaign', exact: true})).toHaveCount(0);
            await page.keyboard.press('Escape');
        }

        await chip(page, 'Status').click();
        const trashed = nextListRequest(page, FORMS_REQUEST, (url) => url.searchParams.get('status') === 'trash');
        await popover(page).getByRole('checkbox', {name: 'Trash', exact: true}).check();
        expect(new URL((await trashed).url()).searchParams.get('campaignId')).toBe(String(campaignId));
        await page.keyboard.press('Escape');

        // The list may answer Reset from its cache, so the rows are what is checked, not a new request.
        await filterBar(page).getByRole('button', {name: 'Reset', exact: true}).click();

        await expect(chip(page, 'Status')).toHaveText('Status');
        await expect(listRows(page)).toHaveCount(Math.min(initial, 30));
        await expect(page.getByText(/match your search or filters/)).toHaveCount(0);
    });
});

/**
 * Walks the two-step create-campaign modal and returns the id of the campaign it made.
 */
async function createCampaignThroughWizard(page, admin, title: string): Promise<number> {
    await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-campaigns');

    /*
     * The trigger is an anchor with no href, so it carries no link role to match on, and a site with
     * no campaigns yet offers a second one in the list's empty state. Both open the same modal.
     */
    await page.getByText('Create campaign', {exact: true}).first().click();

    const wizard = page.locator('form.givewp-campaigns__form');
    await wizard.waitFor();

    await wizard.getByRole('textbox').first().fill(title);
    await wizard.getByRole('button', {name: 'Continue'}).click();

    await wizard.getByRole('radio', {name: 'Amount raised', exact: true}).check();
    await wizard.getByPlaceholder(/e\.g\./).fill('1000');
    await wizard.getByRole('button', {name: 'Continue'}).click();

    // Submitting hands the fundraiser to the new campaign's overview, which is where its id is.
    await page.waitForURL(/page=give-campaigns&id=\d+/, {timeout: 30_000});

    return Number(new URL(page.url()).searchParams.get('id'));
}
