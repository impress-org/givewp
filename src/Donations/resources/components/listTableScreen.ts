import type {ListTableFieldRoles, ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * On narrow screens each donation is a card with the donor, the amount under it, then the status, the
 * date, the campaign and the form.
 *
 * @since TBD
 */
const fieldRoles: ListTableFieldRoles = {
    primary: 'donor',
    description: 'amount',
    secondary: ['status', 'createdAt', 'campaign', 'form'],
};

/**
 * The Donations list keeps its view in the URL and its search term in the tab's history. Without
 * recurring giving every donation is one-time, so the Payment Type column starts hidden.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
export function donationsListTableScreen(recurringDonationsEnabled: boolean): ListTableScreen {
    return recurringDonationsEnabled
        ? {key: 'donations', fieldRoles}
        : {key: 'donations', defaults: {hiddenFields: ['paymentType']}, fieldRoles};
}

/**
 * @since TBD
 */
const listTableScreen: ListTableScreen = donationsListTableScreen(
    Boolean(window.GiveDonations?.recurringDonationsEnabled)
);

export default listTableScreen;
