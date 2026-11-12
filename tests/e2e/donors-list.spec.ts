import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation, SEEDED_LAST_NAME} from './utils/donation';
import {expectNoBrowserDialogs, nextListRequest, visitList} from './utils/list-table';
import {resetLayout} from './utils/user';

/**
 * The Donors list.
 *
 * Its search matches a name anywhere, unlike the Donations search, and the hint under the box says
 * so. The test makes its own donation, so it proves something on a clean site.
 */
const DONORS = 'post_type=give_forms&page=give-donors';
const DONORS_REQUEST = /give-api\/v2\/admin\/donors\?/;

test.describe('Donors list filters', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'donors');
    });

    test('search matches a name anywhere, as the hint says', async ({page, admin, requestUtils}) => {
        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);
        const {donorId} = await requestUtils.rest<{donorId: number}>({path: `/givewp/v3/donations/${donationId}`});

        await visitList(page, admin, DONORS);

        const search = page.getByLabel('Search donors');
        await expect(search).toHaveAccessibleDescription(/anywhere/);

        // Letters from the middle of the name, not its start.
        const middle = SEEDED_LAST_NAME.slice(1, 6);
        const searched = nextListRequest(page, DONORS_REQUEST, (url) => url.searchParams.get('search') === middle);
        await search.fill(middle);
        const items = ((await (await searched).json()) as {items: Array<{id: number | string}>}).items;

        expect(items.map((item) => Number(item.id))).toContain(donorId);
    });
});
