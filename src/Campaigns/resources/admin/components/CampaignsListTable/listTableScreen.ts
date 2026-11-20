import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Campaigns list keeps its view in the URL and its search term in the tab's history. On narrow
 * screens each campaign is a card with its title, status, goal and revenue.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'campaigns',
    fieldRoles: {primary: 'title', secondary: ['status', 'goal', 'revenue']},
};

export default listTableScreen;
