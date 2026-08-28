import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {donationForm, expectReceipt, fillDonorDetails, payWithTestGateway, waitForForm} from './utils/donation-form';

/**
 * Creating a campaign, through the wizard a fundraiser actually uses.
 *
 * A campaign is where a v3 donation form comes from - `CreateDefaultCampaignForm` runs on
 * `givewp_campaign_created` - so the wizard is the first half of every donation the plugin takes.
 * The REST route behind it is covered by PHPUnit; what is not is whether the two-step modal reaches
 * that route with what the fundraiser typed, and whether the form it produces can take money.
 */
test.describe('Campaigns', () => {
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

        const editHref = await formRow.getByRole('link', {name: 'Edit'}).getAttribute('href');
        const formId = Number(new URL(editHref).searchParams.get('post'));

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
