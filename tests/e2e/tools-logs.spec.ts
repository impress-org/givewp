import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {failedCalls, restCallsAfter, watchRestCalls} from './utils/rest';

/**
 * Give > Tools > Logs. A React list table that reads its rows from the REST API.
 */
test.describe('Logs', () => {
    test('list table mounts and reads its route', async ({page, admin}) => {
        const calls = watchRestCalls(page);

        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-tools&tab=logs');

        await expect(page.locator('#give-logs-list-table-app')).toBeVisible();
        expect(failedCalls(await restCallsAfter(calls, '/give-api/v2/logs/get-logs'))).toEqual([]);
    });
});
