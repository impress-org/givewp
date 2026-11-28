import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Subscriptions list keeps its view in the URL and its search term in the tab's history. On
 * narrow screens each subscription is a card with the donor, the amount under it, then the billing
 * period, the status, the renewal date and the campaign.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'subscriptions',
    fieldRoles: {
        primary: 'donor',
        description: 'amount',
        secondary: ['billingPeriod', 'status', 'renewalDate', 'subscriptionCampaign'],
    },
};

export default listTableScreen;
