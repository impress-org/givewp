import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Donors list keeps its view in the URL and its search term in the tab's history. On narrow
 * screens each donor is a card with the donor information, total given, donations and latest donation.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'donors',
    fieldRoles: {primary: 'donorInformation', secondary: ['donationRevenue', 'donationCount', 'latestDonation']},
};

export default listTableScreen;
