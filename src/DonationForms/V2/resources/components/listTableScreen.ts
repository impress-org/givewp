import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Donation Forms list keeps its view in the URL and its search term in the tab's history. On
 * narrow screens each form is a card with its name, status, campaign, revenue and donations.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'donation-forms',
    fieldRoles: {primary: 'title', secondary: ['status', 'formCampaign', 'donationRevenue', 'donationCount']},
};

export default listTableScreen;
