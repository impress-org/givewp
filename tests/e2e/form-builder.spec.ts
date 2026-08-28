import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {donationForm, fillDonorDetails, waitForForm} from './utils/donation-form';

/**
 * The form builder - the admin half of a v3 donation form.
 *
 * It is a block editor of its own rather than a settings screen, mounted from `formBuilderApp.js`
 * into a bare `#root`, and everything it changes reaches donors only after a save. So the tests
 * that matter run the round trip: change something in the builder, save, then look at the form a
 * donor would see. Nothing below that line is reachable from PHPUnit.
 */

const BUILDER_TIMEOUT = 30_000;

test.describe('Form builder', () => {
    test('loads the form it was asked to edit', async ({page, admin, requestUtils}) => {
        const {title, formId} = await createCampaignWithForm(requestUtils);

        await visitBuilder(page, admin, formId);

        /*
         * The title comes from the form the builder loaded, so it separates an app that mounted
         * from an app that mounted and read the right form.
         */
        await expect(formTitle(page)).toHaveValue(title, {timeout: BUILDER_TIMEOUT});
    });

    /*
     * `CreateDefaultCampaignForm` builds the form with `DonationFormStatus::PUBLISHED()` but hands
     * `FormSettings::fromArray()` a payload with no `formStatus` key, so the settings fall back to
     * their `draft` default while the post is published. The builder reads the settings, so it
     * offers "Publish" and "Save as Draft" on a form that is already live and taking donations, and
     * shows no "View form" link. The first save writes `publish` into the settings and the symptom
     * never comes back, which is why it only shows on a form nobody has opened yet.
     *
     * `createCampaignWithForm` squares the two before every other spec, so this is the one place
     * the form is looked at as the campaign left it.
     */
    test.fixme('shows a campaign form as published without saving it first', async ({page, admin, requestUtils}) => {
        const campaign = await requestUtils.rest<{defaultFormId: number}>({
            method: 'POST',
            path: '/givewp/v3/campaigns',
            data: {title: `Unsaved campaign form ${Date.now()}`, goal: 1000, goalType: 'amount'},
        });

        await visitBuilder(page, admin, campaign.defaultFormId);
        await formTitle(page).waitFor({timeout: BUILDER_TIMEOUT});

        await expect(page.getByRole('button', {name: 'Update'})).toBeVisible();
        await expect(page.getByRole('button', {name: 'Switch to Draft'})).toBeVisible();
    });

    test('renames the form and the change survives a reload', async ({page, admin, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);
        const renamed = 'Renamed by the builder';

        await visitBuilder(page, admin, formId);
        await formTitle(page).waitFor({timeout: BUILDER_TIMEOUT});

        await formTitle(page).fill(renamed);
        await save(page);

        await visitBuilder(page, admin, formId);

        await expect(formTitle(page)).toHaveValue(renamed, {timeout: BUILDER_TIMEOUT});
    });

    test('adds a field that the donor then sees on the form', async ({page, admin, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await visitBuilder(page, admin, formId);
        await tab(page, 'Build').click();

        /*
         * Inserting a field with no section selected appends it to the end of the form wrapped in a
         * section of its own, which a multi-step form then shows as one more step after payment.
         */
        await page.getByRole('option', {name: 'Donor Comments'}).click();

        await expect(page.getByRole('document', {name: 'Block: Donor Comments'})).toBeVisible();

        await save(page);

        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);

        await form.getByRole('button', {name: 'Donate now'}).click();
        await fillDonorDetails(form);
        await form.getByRole('button', {name: 'Continue'}).click();

        // Wait for the payment step before its own button, which is busy while the step validates.
        await expect(form.locator('.givewp-fields-gateways__list')).toBeVisible();
        await form.getByRole('button', {name: 'Continue'}).click();

        await expect(form.getByLabel('Comment')).toBeVisible();
    });

    test('changes the layout, previews it, and the donor sees the new design', async ({page, admin, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await visitBuilder(page, admin, formId);
        await tab(page, 'Design').click();

        /*
         * The preview is not a client-side approximation - the builder posts the unsaved blocks and
         * settings to the `donation-form-view-preview` route and renders what comes back, so the
         * request is what proves the preview is showing this form rather than a stale one.
         */
        const preview = page.waitForResponse(
            (response) =>
                response.url().includes('givewp-route=donation-form-view-preview') && response.status() === 200
        );

        await page.getByRole('combobox', {name: 'Form layout'}).selectOption({label: 'Classic'});

        await preview;

        await save(page);

        await page.goto(`/?post_type=give_forms&p=${formId}`);

        const form = donationForm(page);
        await waitForForm(form);

        // Classic puts every section on one page, so the donor fields are there without stepping.
        await expect(form.getByLabel('First name')).toBeVisible();
        await expect(form.locator('.givewp-fields-gateways__list')).toBeVisible();
    });

    test('switching a form to draft takes it off the front end', async ({page, admin, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await visitBuilder(page, admin, formId);
        await formTitle(page).waitFor({timeout: BUILDER_TIMEOUT});

        await page.getByRole('button', {name: 'Switch to Draft'}).click();

        await expect(page.locator('.components-snackbar__content')).toContainText('Draft saved.', {
            timeout: BUILDER_TIMEOUT,
        });

        /*
         * An admin can still see a draft form, so ask for it as the public, which is who being off
         * the front end is about.
         */
        await page.context().clearCookies();

        const response = await page.goto(`/?post_type=give_forms&p=${formId}`);

        expect(response.status()).toBe(404);
    });

    test('refuses to save a form that is missing a required block', async ({page, admin, pageUtils, requestUtils}) => {
        const {formId} = await createCampaignWithForm(requestUtils);

        await visitBuilder(page, admin, formId);
        await tab(page, 'Build').click();

        const emailBlock = page.getByRole('document', {name: 'Block: Email'});
        await emailBlock.click();
        await page.keyboard.press('Escape');
        await pageUtils.pressKeys('access+z');

        await expect(emailBlock).toBeHidden();

        await page.getByRole('button', {name: 'Update'}).click();

        /*
         * A form without an email field cannot take a donation, so the save is rejected server-side
         * rather than left to produce a form that mounts and then fails at the first step.
         */
        const error = page.getByLabel('Error saving form');

        await expect(error).toBeVisible({timeout: BUILDER_TIMEOUT});
        await expect(error).toContainText('Email');
    });
});

async function visitBuilder(page, admin, formId: number): Promise<void> {
    await admin.visitAdminPage('edit.php', `post_type=give_forms&page=givewp-form-builder&donationFormID=${formId}`);
}

function formTitle(page) {
    return page.locator('.givewp-form-title input');
}

/**
 * The Build / Design / Settings tabs, which share their names with buttons elsewhere in the header.
 */
function tab(page, name: string) {
    return page.locator('.givewp-header-tabs').getByRole('button', {name});
}

async function save(page): Promise<void> {
    await page.getByRole('button', {name: 'Update'}).click();

    // The same text is announced to screen readers, so match the snackbar rather than the page.
    await expect(page.locator('.components-snackbar__content')).toContainText('Form updated.', {
        timeout: BUILDER_TIMEOUT,
    });
}
