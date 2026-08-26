import {expect, test} from '@wordpress/e2e-test-utils-playwright';

/**
 * Smoke coverage for the admin screens that mount a React app. These fail loudly when the build
 * output is missing, when an asset fails to enqueue, or when the v3 REST request behind a list
 * table errors - the three ways these pages break that PHPUnit cannot see.
 *
 * Assertions stay independent of the site's data so the suite passes against both a fresh CI
 * install and a developer's populated wp-env site.
 */
test.describe('GiveWP admin pages', () => {
    test('campaigns list table mounts', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-campaigns');

        await expect(page.locator('#give-admin-campaigns-root')).toBeVisible();
        await expect(page.getByRole('heading', {level: 1, name: 'Campaigns'})).toBeVisible({timeout: 15_000});
    });

    test('donations list table mounts', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-payment-history');

        await expect(page.locator('#give-admin-donations-root')).toBeVisible();
        await expect(page.getByRole('heading', {level: 1, name: 'Donations'})).toBeVisible({timeout: 15_000});
    });

    test('donors list table mounts', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-donors');

        await expect(page.getByRole('heading', {level: 1, name: 'Donors'})).toBeVisible({timeout: 15_000});
    });

    test('settings page renders the general tab', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-settings');

        /*
         * The legacy settings API renders this select, then chosen-js hides it behind its own
         * markup, so assert it is in the DOM rather than visible.
         */
        await expect(page.locator('select#success_page')).toBeAttached();
    });
});
