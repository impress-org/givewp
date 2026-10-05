import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {wp} from './utils/wp-cli';

/**
 * The Funds add-on, installed from its latest GitHub release by the E2E workflow.
 *
 * Funds reads no campaign data of its own, so the rest of the suite running with it active - the
 * standalone form spec included - is its compatibility check. This adds that it really is active
 * and that its own admin screen still renders against this core.
 */
const SLUG = 'give-funds';

test.describe('Funds add-on', () => {
    test.skip(!(process.env.E2E_ADDONS ?? '').split(/\s+/).includes(SLUG), `E2E_ADDONS does not name ${SLUG}`);

    test('is active', () => {
        expect(wp('plugin', 'get', SLUG, '--field=status')).toBe('active');
    });

    test('funds admin page renders', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=give-funds');

        await expect(page.getByRole('heading', {level: 1, name: /Funds/})).toBeVisible({timeout: 15_000});
        await expect(page.locator('#give-funds-list-table-form')).toBeVisible();
    });
});
