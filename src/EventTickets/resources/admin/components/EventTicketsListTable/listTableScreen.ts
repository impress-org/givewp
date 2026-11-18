import type {ListTableScreen} from '@givewp/components/ListTable/state/types';

/**
 * The Event Tickets list keeps its view in the URL and its search term in the tab's history. On
 * narrow screens each event is a card with its title, date, tickets sold and sales amount.
 *
 * @since TBD Add the card fields.
 * @since TBD
 */
const listTableScreen: ListTableScreen = {
    key: 'event-tickets',
    fieldRoles: {primary: 'title', secondary: ['startDateTime', 'salesCount', 'salesAmount']},
};

export default listTableScreen;
