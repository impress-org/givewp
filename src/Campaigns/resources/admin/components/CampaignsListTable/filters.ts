import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Campaigns list filters. Status options come from the server's vocabulary. While no status is
 * chosen the request sends `status=any`, which keeps archived campaigns out of the default view. The
 * search hint follows `GetCampaignsListTable`: digits match the campaign ID exactly, other text
 * matches the title or short description anywhere.
 *
 * @since TBD
 */
export const campaignFilters: FilterDeclaration[] = [
    {
        kind: 'options',
        id: 'status',
        label: __('Status', 'give'),
        isPrimary: true,
        legacyUrlKey: 'status',
        multiple: true,
        options: 'statuses',
        parameter: 'statuses',
        whenEmpty: {status: 'any'},
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
        label: __('Search campaigns', 'give'),
        hint: __(
            'Searches campaign title, description or campaign ID. Text matches anywhere; an ID matches exactly.',
            'give'
        ),
        parameter: 'search',
    },
];
