import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Donations list keeps its view in the URL and its search term in the tab's history. Without
 * recurring giving every donation is one-time, so the Payment Type column starts hidden.
 *
 * @since TBD
 */
export function donationsListTableScreen(recurringDonationsEnabled: boolean): ListTableScreen {
    return recurringDonationsEnabled ? {key: 'donations'} : {key: 'donations', defaults: {hiddenFields: ['paymentType']}};
}

/**
 * @since TBD
 */
const listTableScreen: ListTableScreen = donationsListTableScreen(Boolean(window.GiveDonations?.recurringDonationsEnabled));

export default listTableScreen;
