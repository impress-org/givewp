import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Event Tickets list filters: the search box only. The hint follows `GetEventsListTable`:
 * digits match the event ID exactly, other text matches the title or description anywhere.
 *
 * @since TBD
 */
export const eventTicketFilters: FilterDeclaration[] = [
    {
        kind: 'search',
        id: 'search',
        label: __('Search events', 'give'),
        hint: __(
            'Searches event title, description or event ID. Text matches anywhere; an ID matches exactly.',
            'give'
        ),
        parameter: 'search',
    },
];
