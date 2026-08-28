import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {failedCalls, restCallsAfter, watchRestCalls} from './utils/rest';

/**
 * Give > Tools > Data. A React list table that reads the migration log from the REST API.
 */
test.describe('Migrations', () => {
    test('list table mounts and reads its route', async ({page, admin}) => {
        const calls = watchRestCalls(page);

        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-tools&tab=data');

        await expect(page.locator('#give_migrations_table_app')).toBeVisible();
        expect(failedCalls(await restCallsAfter(calls, '/give-api/v2/migrations/get-migrations'))).toEqual([]);
    });
});
