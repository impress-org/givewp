import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Forms tab of a campaign remembers its layout. As an embedded table it never touches the URL.
 * Every form in the tab belongs to the campaign, so the Campaign column starts hidden and the cards
 * on narrow screens leave it out.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'campaign-forms',
    defaults: {hiddenFields: ['formCampaign']},
    fieldRoles: {primary: 'title', secondary: ['status', 'donationRevenue', 'donationCount']},
};

export default listTableScreen;
