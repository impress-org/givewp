import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation, SEEDED_LAST_NAME} from './utils/donation';
import {
    DATE_TIME,
    expectCell,
    expectLastColumnReachable,
    expectNoBrowserDialogs,
    expectStacksOnNarrowScreens,
    MONEY,
    nextListRequest,
    rowWithText,
    useUsDateFormats,
    visitList,
} from './utils/list-table';
import {resetLayout} from './utils/user';
import {wp} from './utils/wp-cli';

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

test.describe('Donors list money and dates', () => {
    test.use({locale: 'pt-BR'});
    useUsDateFormats();

    test('writes money and dates in the site format in a pt-BR browser', async ({page, admin, requestUtils}) => {
        expect(wp('eval', 'echo give_get_currency();'), 'This test expects a site in US dollars').toBe('USD');
        // A stored layout can reorder or hide the columns read below.
        await resetLayout(requestUtils, 'donors');

        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);
        // The REST route leaves the email out by default, so it is read from the model.
        const email = wp('eval', `echo \\Give\\Donations\\Models\\Donation::find(${donationId})->email;`);
        expect(email).toContain('@');

        await visitList(page, admin, DONORS);
        // The Donors list shows no ID column by default; the email is in the Name cell.
        const donor = rowWithText(page, email);
        await expectCell(donor, 'Total Given', MONEY);
        await expectCell(donor, 'Latest Donation', DATE_TIME);
    });
});

test.describe('Donors list on wide and narrow screens', () => {
    expectNoBrowserDialogs();

    test('the last column is reachable at 1024 px', async ({page, admin, requestUtils}) => {
        await resetLayout(requestUtils, 'donors');
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await expectLastColumnReachable(page, admin, DONORS);
    });

    test('stacks as cards at 782 px and 375 px, with no page overflow', async ({page, admin, requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await expectStacksOnNarrowScreens(page, admin, requestUtils, {query: DONORS, key: 'donors', hasRecords: true});
    });
});
