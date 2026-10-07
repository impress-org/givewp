import {expect, Locator, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {chooseOption} from './utils/async-select';
import {createCampaignWithForm} from './utils/campaign';
import {createStandaloneForm} from './utils/standalone-form';

/**
 * Moving a form between campaigns, from the Forms list and from a campaign's Forms tab.
 *
 * `CampaignRepository` and the associate and detach routes are covered by PHPUnit; what is not is
 * whether the row action opens the modal, whether the picker reaches the route with the form and
 * campaign the admin chose, and whether the list shows the result. Every fixture is made inside the
 * test: a standalone form through the Add form button and campaigns over REST, so a run means the
 * same thing on a fresh install and on a developer site.
 */
const FORMS_LIST = 'post_type=give_forms&page=give-forms';

test.describe('Forms list', () => {
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
        await expect(row.getByRole('button', {name: /Assign campaign/})).toBeVisible();
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

        // The open campaign is offered, which is what makes the two empty searches below mean something.
        await picker.fill(open.title);
        await expect(page.getByText(open.title, {exact: true}).last()).toBeVisible();

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
 * The list row for the form called `title`.
 */
function formRow(page: Page, title: string): Locator {
    return page.getByRole('row', {name: new RegExp(title)});
}

/**
 * Opens the modal behind a row's campaign action - "Assign campaign" on a form with no campaign,
 * "Change campaign" on one with a campaign - and returns it.
 */
async function openCampaignDialog(page: Page, row: Locator, action: string): Promise<Locator> {
    await row.getByRole('button', {name: new RegExp(action)}).click();

    const dialog = page.getByRole('dialog', {name: action});
    await expect(dialog).toBeVisible();

    return dialog;
}
