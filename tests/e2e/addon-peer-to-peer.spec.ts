import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {wp} from './utils/wp-cli';

/**
 * The Peer-to-Peer add-on, installed from its latest GitHub release by the E2E workflow.
 *
 * Most of the compatibility signal comes from the rest of the suite running with the add-on active:
 * a fatal on activation or a filter typed against the wrong model takes a core screen down, and the
 * core specs see that. This spec adds the two things they cannot: that the add-on really is active,
 * so a missing release or a failed download does not pass as a green run, and that the add-on's own
 * admin screen still mounts against this core.
 */
const SLUG = 'give-peer-to-peer';

test.describe('Peer-to-Peer add-on', () => {
    // E2E_ADDONS is set by the workflow after the release is installed and is empty on a fork
    // pull request, which has no secret to read the add-on repository with.
    test.skip(!(process.env.E2E_ADDONS ?? '').split(/\s+/).includes(SLUG), `E2E_ADDONS does not name ${SLUG}`);

    test('is active', () => {
        expect(wp('plugin', 'get', SLUG, '--field=status')).toBe('active');
    });

    test('campaigns admin page mounts', async ({page, admin}) => {
        await admin.visitAdminPage('edit.php', 'post_type=give_forms&page=p2p-campaigns');

        await expect(page.locator('#give-p2p-campaigns-app')).toBeVisible();
        await expect(page.getByRole('heading', {level: 1, name: /P2P Campaigns/})).toBeVisible({timeout: 15_000});
    });
});
