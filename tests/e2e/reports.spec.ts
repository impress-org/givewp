import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {failedCalls, restCallsAfter, watchRestCalls} from './utils/rest';

/**
 * Give > Reports. One page view fans out into a request per widget, all sharing the same REST
 * client and the same date range, so a single visit covers every report route at once.
 */
test.describe('Reports', () => {
    test('page mounts and reads every report route', async ({page, admin}) => {
        const calls = watchRestCalls(page);

        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-reports');

        await expect(page.locator('#reports-app')).toBeVisible();

        const reportCalls = await restCallsAfter(calls, '/give-api/v2/reports/income');

        expect(failedCalls(reportCalls)).toEqual([]);

        /*
         * The date range is what the client puts on the query string. Asserting it here is what
         * separates "the route answered" from "the route answered with the range asked for",
         * which is the half a serializer change would break silently.
         */
        const income = reportCalls.find((call) => call.url.includes('/give-api/v2/reports/income?'));
        expect(income?.url).toMatch(/[?&]start=\d{4}-\d{2}-\d{2}&end=\d{4}-\d{2}-\d{2}/);
    });
});
