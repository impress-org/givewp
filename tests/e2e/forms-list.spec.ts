import {expect, Locator, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {chooseOption, openOptions} from './utils/async-select';
import {createCampaignWithForm} from './utils/campaign';
import {createStandaloneForm} from './utils/standalone-form';
import {expectListMounts, listRows, listTable, rowWithText, runRowAction} from './utils/list-table';
import {isLayoutWrite, resetLayout} from './utils/user';

/**
 * Moving a form between campaigns, from the Forms list and from a campaign's Forms tab.
 *
 * `CampaignRepository` and the associate and detach routes are covered by PHPUnit; what is not is
 * whether the row menu action opens the modal, whether the picker reaches the route with the form and
 * campaign the admin chose, and whether the list shows the result. Every fixture is made inside the
 * test: a standalone form through the Add form button and campaigns over REST, so a run means the
 * same thing on a fresh install and on a developer site.
 */
const FORMS_LIST = 'post_type=give_forms&page=give-forms';

test.describe('Forms list', () => {
    test('renders through the list engine without errors', async ({page, admin}) => {
        await expectListMounts(page, admin, FORMS_LIST, 'Donation Forms');
    });

    test('assigns a form with no campaign to a campaign, then moves it to another', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const first = await createCampaignWithForm(requestUtils);
        const second = await createCampaignWithForm(requestUtils);
        const {title} = await createStandaloneForm(page, admin);

        await admin.visitAdminPage('edit.php', FORMS_LIST);

        const row = formRow(page, title);
        await expect(row).toContainText('No campaign');

        const assign = await openCampaignDialog(page, row, 'Assign campaign');
        await chooseOption(page, assign.getByRole('combobox', {name: 'Campaign'}), first.title);
        await assign.getByRole('button', {name: 'Assign campaign'}).click();

        await expect(assign.getByRole('status')).toContainText(`${title} now belongs to ${first.title}`);
        await assign.getByRole('button', {name: 'Done'}).click();
        await expect(row).toContainText(first.title);

        const move = await openCampaignDialog(page, row, 'Change campaign');
        await expect(move).toContainText(`Current campaign${first.title}`);
        await chooseOption(page, move.getByRole('combobox', {name: 'Move to'}), second.title);
        await move.getByRole('button', {name: 'Move form'}).click();

        // The old campaign keeps what the form raised before the move, and the message says so.
        await expect(move.getByRole('status')).toContainText(`now belongs to ${second.title}`);
        await expect(move.getByRole('status')).toContainText(`past donations stay with ${first.title}`);
        await move.getByRole('button', {name: 'Done'}).click();

        await expect(row).toContainText(second.title);
        await expect(row).not.toContainText(first.title);
    });

    test('removes a form from its campaign and leaves it standalone', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);

        await requestUtils.rest({
            method: 'POST',
            path: '/givewp/v3/associate-forms-with-campaign',
            data: {campaignId: campaign.campaignId, formIDs: [formId]},
        });

        await admin.visitAdminPage('edit.php', FORMS_LIST);

        const row = formRow(page, title);
        await expect(row).toContainText(campaign.title);

        const dialog = await openCampaignDialog(page, row, 'Change campaign');
        await dialog.getByRole('button', {name: 'Remove from campaign'}).click();

        // Removing is a confirmation step of its own, so a stray click does not detach a form.
        await expect(dialog).toContainText(`Remove ${title} from ${campaign.title}?`);
        await dialog.getByRole('button', {name: 'Remove from campaign'}).click();

        await expect(dialog.getByRole('status')).toContainText(`${title} is no longer part of ${campaign.title}`);
        await dialog.getByRole('button', {name: 'Done'}).click();

        await expect(row).toContainText('No campaign');

        // A standalone form is offered "Assign campaign" again, in the row's menu.
        await row.getByRole('button', {name: 'Actions', exact: true}).click();
        await expect(page.getByRole('menuitem', {name: 'Assign campaign'})).toBeVisible();
        await page.keyboard.press('Escape');
    });

    test("will not move a campaign's default form", async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const other = await createCampaignWithForm(requestUtils);

        await admin.visitAdminPage('edit.php', FORMS_LIST);

        // A campaign's default form is named for the campaign, so its row is the one with that title.
        const row = formRow(page, campaign.title);
        const dialog = await openCampaignDialog(page, row, 'Change campaign');

        // A default form cannot be removed either, only moved, and moving it is what is refused.
        await expect(dialog.getByRole('button', {name: 'Remove from campaign'})).toHaveCount(0);

        await chooseOption(page, dialog.getByRole('combobox', {name: 'Move to'}), other.title);
        await dialog.getByRole('button', {name: 'Move form'}).click();

        await expect(dialog).toContainText(`default form for the campaign "${campaign.title}"`);
        await expect(dialog).toContainText('Choose a new default form for that campaign first');

        await dialog.getByRole('button', {name: 'Cancel'}).click();
        await expect(row).not.toContainText(other.title);
    });

    test('offers neither an archived campaign nor the one the form is already in', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const current = await createCampaignWithForm(requestUtils);
        const archived = await createCampaignWithForm(requestUtils);
        const open = await createCampaignWithForm(requestUtils);

        await requestUtils.rest({
            method: 'PATCH',
            path: `/givewp/v3/campaigns/${archived.campaignId}`,
            data: {status: 'archived'},
        });

        await admin.visitAdminPage('edit.php', FORMS_LIST);

        const dialog = await openCampaignDialog(page, formRow(page, current.title), 'Change campaign');
        const picker = dialog.getByRole('combobox', {name: 'Move to'});

        // The open campaign is offered, which is what makes the two empty searches below mean something. The
        // text is looked for among the menu's options: its own default form's row is on the page behind the dialog.
        await picker.fill(open.title);
        await expect(openOptions(page).filter({hasText: open.title})).toBeVisible();

        await picker.fill(archived.title);
        await expect(dialog.getByText('No options')).toBeVisible();

        await picker.fill(current.title);
        await expect(dialog.getByText('No options')).toBeVisible();
    });

    test("moves a form from a campaign's Forms tab", async ({page, admin, requestUtils}) => {
        const from = await createCampaignWithForm(requestUtils);
        const to = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);

        await requestUtils.rest({
            method: 'POST',
            path: '/givewp/v3/associate-forms-with-campaign',
            data: {campaignId: from.campaignId, formIDs: [formId]},
        });

        await admin.visitAdminPage(
            'edit.php',
            `post_type=give_forms&page=give-campaigns&id=${from.campaignId}&tab=forms`
        );

        const row = formRow(page, title);
        await expect(row).toBeVisible();

        const dialog = await openCampaignDialog(page, row, 'Change campaign');
        await chooseOption(page, dialog.getByRole('combobox', {name: 'Move to'}), to.title);
        await dialog.getByRole('button', {name: 'Move form'}).click();

        await expect(dialog.getByRole('status')).toContainText(`now belongs to ${to.title}`);
        await dialog.getByRole('button', {name: 'Done'}).click();

        // The form is no longer one of this campaign's, so its row leaves the tab.
        await expect(row).toHaveCount(0);
    });
});

/**
 * The campaign Forms tab is the Forms list embedded in another page. It remembers its layout under
 * its own screen key, starts with the Campaign column hidden (every form there is the campaign's),
 * and never reads or writes the address, which belongs to the details page (`page`, `id`, `tab`).
 */
test.describe('Campaign Forms tab', () => {
    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'campaign-forms');
    });

    test.afterEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'campaign-forms');
    });

    test('hides the Campaign column by default and leaves the address untouched', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const query = `post_type=give_forms&page=give-campaigns&id=${campaign.campaignId}&tab=forms`;

        await admin.visitAdminPage('edit.php', query);
        await expect(listRows(page).first()).toBeVisible({timeout: 15_000});
        const address = page.url();

        await expect(page.locator('.dataviews-view-table thead th', {hasText: /^Campaign$/})).toHaveCount(0);

        // A page size change is stored as the tab's layout, not as the standalone Forms list's.
        const stored = page.waitForResponse(
            (response) => response.url().includes('list-table-layouts/campaign-forms') && isLayoutWrite(response.request())
        );
        await listTable(page).getByRole('button', {name: 'View options'}).click();
        await page.getByRole('radio', {name: '10', exact: true}).click();
        await page.keyboard.press('Escape');
        expect((await stored).ok()).toBe(true);

        expect(page.url()).toBe(address);
        const params = new URL(page.url()).searchParams;
        expect(params.get('page')).toBe('give-campaigns');
        expect(params.get('id')).toBe(String(campaign.campaignId));
        expect(params.get('tab')).toBe('forms');
        expect(Array.from(params.keys()).filter((key) => key.startsWith('give-'))).toEqual([]);
    });
});

/**
 * The list row for the form called `title`.
 */
function formRow(page: Page, title: string): Locator {
    return rowWithText(page, title);
}

/**
 * Opens the modal behind a row's campaign action - "Assign campaign" on a form with no campaign,
 * "Change campaign" on one with a campaign - from the row's menu, and returns it.
 */
async function openCampaignDialog(page: Page, row: Locator, action: string): Promise<Locator> {
    await runRowAction(page, row, action);

    const dialog = page.getByRole('dialog', {name: action});
    await expect(dialog).toBeVisible();

    return dialog;
}
