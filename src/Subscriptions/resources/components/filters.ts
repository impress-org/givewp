import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Subscriptions list filters. Status options come from the server's vocabulary; Trashed is a
 * view of its own, so it cannot be combined with other statuses. Test mode is the page's switch,
 * not a filter. The search hint follows `ListSubscriptions`: digits match the subscription ID
 * exactly, other text matches the donor's name or email anywhere.
 *
 * @since TBD
 */
export const subscriptionFilters: FilterDeclaration[] = [
    {
        kind: 'entity',
        id: 'campaign',
        label: __('Campaign', 'give'),
        isPrimary: true,
        legacyUrlKey: 'campaignId',
        source: 'campaign',
        multiple: true,
        parameter: 'campaignId',
    },
    {
        kind: 'options',
        id: 'status',
        label: __('Status', 'give'),
        isPrimary: true,
        multiple: true,
        options: 'statuses',
        parameter: 'status',
        exclusiveValues: ['trashed'],
    },
    {
        kind: 'date',
        id: 'date',
        label: __('Date created', 'give'),
        parameters: {start: 'start', end: 'end'},
    },
    {
        kind: 'search',
        id: 'search',
        label: __('Search subscriptions', 'give'),
        hint: __(
            'Searches donor name, email or subscription ID. Names and emails match anywhere in the text; an ID matches exactly.',
            'give'
        ),
        parameter: 'search',
    },
];
