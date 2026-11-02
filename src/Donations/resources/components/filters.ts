import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Donations list filters. Status options come from the server's vocabulary; Trash is a view of
 * its own, so it cannot be combined with other statuses. The Donor filter also reads the old
 * `donor` link from a donor's page. Donation type is offered only with Give Recurring: without it
 * every donation is one-time. Test mode is the page's switch, not a filter.
 *
 * The search hint follows `ListDonations`: digits match the donation ID exactly, text with `@`
 * matches the email from the start, other text matches the first or last name from the start.
 *
 * Call it at module level or memoize the result: a new array on every render recomputes what depends on it.
 *
 * @since TBD
 */
export function donationFilters({
    recurringDonationsEnabled,
}: {
    recurringDonationsEnabled: boolean;
}): FilterDeclaration[] {
    const filters: FilterDeclaration[] = [
        {
            kind: 'entity',
            id: 'campaign',
            label: __('Campaign', 'give'),
            isPrimary: true,
            legacyUrlKey: 'campaignId',
            source: 'campaign',
            multiple: true,
            allowNone: true,
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
            exclusiveValues: ['trash'],
        },
        {
            kind: 'entity',
            id: 'form',
            label: __('Form', 'give'),
            legacyUrlKey: 'formId',
            source: 'form',
            multiple: true,
            parameter: 'formId',
        },
        {
            kind: 'entity',
            id: 'donor',
            label: __('Donor', 'give'),
            legacyUrlKey: 'donor',
            source: 'donor',
            multiple: false,
            parameter: 'donor',
        },
        {
            kind: 'date',
            id: 'date',
            label: __('Donation date', 'give'),
            parameters: {start: 'start', end: 'end'},
        },
    ];

    if (recurringDonationsEnabled) {
        filters.push({
            kind: 'options',
            id: 'donationType',
            label: __('Donation type', 'give'),
            multiple: false,
            options: [
                {value: 'one-time', label: __('One-time', 'give')},
                {value: 'recurring', label: __('Recurring', 'give')},
            ],
            parameter: 'donationType',
        });
    }

    filters.push({
        kind: 'search',
        id: 'search',
        label: __('Search donations', 'give'),
        hint: __(
            'Searches donor first name, last name, email or donation ID. Names and emails match from the start; an ID matches exactly.',
            'give'
        ),
        parameter: 'search',
    });

    return filters;
}

/**
 * The Donations list filters for this site, built once.
 *
 * @since TBD
 */
export const donationsFilters: FilterDeclaration[] = donationFilters({
    recurringDonationsEnabled: Boolean(window.GiveDonations?.recurringDonationsEnabled),
});
