import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Donation Forms list filters. Status options come from the server's vocabulary. The search hint
 * follows `ListDonationForms`: digits match the form ID exactly, other text must have every word
 * somewhere in the title.
 *
 * Without the campaign filter the page fixes the campaign itself (a campaign's "Forms" tab).
 *
 * Call it at module level or memoize the result: a new array on every render recomputes what depends on it.
 *
 * @since TBD
 */
export function donationFormFilters({withCampaign}: {withCampaign: boolean}): FilterDeclaration[] {
    const filters: FilterDeclaration[] = [];

    if (withCampaign) {
        filters.push({
            kind: 'entity',
            id: 'campaign',
            label: __('Campaign', 'give'),
            isPrimary: true,
            legacyUrlKey: 'campaign',
            source: 'campaign',
            multiple: true,
            allowNone: true,
            parameter: 'campaign',
        });
    }

    filters.push(
        {
            kind: 'options',
            id: 'status',
            label: __('Status', 'give'),
            isPrimary: true,
            legacyUrlKey: 'status',
            multiple: true,
            options: 'statuses',
            parameter: 'status',
            exclusiveValues: ['trash'],
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
            label: __('Search donation forms', 'give'),
            hint: __(
                'Searches form title or form ID. Each word can match anywhere in the title; an ID matches exactly.',
                'give'
            ),
            parameter: 'search',
        }
    );

    return filters;
}
