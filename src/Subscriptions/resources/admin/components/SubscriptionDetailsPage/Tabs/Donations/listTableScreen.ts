import type {ListTableFieldRoles, ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * On narrow screens each donation is a card with its type as the title, the amount under it, then the
 * status, the date and the campaign. The tab has no donor column, so the card needs its own fields.
 *
 * @since TBD
 */
const fieldRoles: ListTableFieldRoles = {
    primary: 'subscriptionDonationType',
    description: 'amount',
    secondary: ['status', 'createdAt', 'campaign'],
};

/**
 * The Donations tab of a subscription remembers its layout. As an embedded table it never touches the URL.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {key: 'subscription-donations', fieldRoles};

export default listTableScreen;
