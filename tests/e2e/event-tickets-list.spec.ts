import {test} from '@wordpress/e2e-test-utils-playwright';
import {expectNoBrowserDialogs, expectStacksOnNarrowScreens} from './utils/list-table';
import {isEventTicketsEnabled} from './utils/wp-cli';

/**
 * The Event Tickets list. It is a beta feature, so these tests skip on a site that has not turned
 * it on. That it mounts is checked in `admin-pages.spec.ts`.
 */
const EVENT_TICKETS = 'post_type=give_forms&page=give-event-tickets';

test.describe('Event Tickets list on narrow screens', () => {
    expectNoBrowserDialogs();

    test('fits 782 px and 375 px with no page overflow', async ({page, admin, requestUtils}) => {
        test.skip(!isEventTicketsEnabled(), 'Event Tickets is not enabled on this site.');

        // A test site may have no events, and then shows a blank slate rather than cards.
        await expectStacksOnNarrowScreens(page, admin, requestUtils, {
            query: EVENT_TICKETS,
            key: 'event-tickets',
            hasRecords: false,
        });
    });
});
