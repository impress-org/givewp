import {Admin, expect, Page} from '@wordpress/e2e-test-utils-playwright';

const BUILDER_TIMEOUT = 30_000;

/**
 * Makes a v3 form with no campaign the way an admin does: the forms list's Add form button, then
 * Publish in the builder. Returns the new form's id and title.
 *
 * There is no REST route that creates a form outside a campaign - `CreateFormRoute` runs on
 * `admin_init` and redirects to the builder - so the button is the setup as well as the thing
 * under test.
 */
export async function createStandaloneForm(page: Page, admin: Admin): Promise<{formId: number; title: string}> {
    const title = `Standalone form ${Date.now()}`;

    await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-forms');
    await page.getByRole('link', {name: 'Add form', exact: true}).click();

    await page.waitForURL(/donationFormID=\d+/, {timeout: BUILDER_TIMEOUT});
    const formId = Number(new URL(page.url()).searchParams.get('donationFormID'));

    // A new form has no design yet, so the builder opens on a layout picker. Multi-step is what a
    // campaign's default form gets, which keeps the donor steps the same as the other specs.
    await page.getByText('Choose your form layout').waitFor({timeout: BUILDER_TIMEOUT});
    await page.getByText('Multi-step', {exact: true}).click();
    await page.getByRole('button', {name: 'Proceed'}).click();

    const formTitle = page.locator('.givewp-form-title input');
    await formTitle.fill(title);

    // The header button opens the prepublish panel, which has a Publish button of its own.
    await page.getByRole('button', {name: 'Publish', exact: true}).click();
    await page
        .locator('.givewp-next-gen-prepublish-panel__header-actions')
        .getByRole('button', {name: 'Publish', exact: true})
        .click();

    await expect(page.locator('.components-snackbar__content')).toContainText('Form published.', {
        timeout: BUILDER_TIMEOUT,
    });

    return {formId, title};
}
