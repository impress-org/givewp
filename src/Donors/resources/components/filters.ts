import {__} from '@wordpress/i18n';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';

/**
 * The Donors list filters. Donor type is offered only with Give Recurring: without it no donor can
 * be a subscriber. The search hint follows `ListDonors`: digits match the donor ID exactly, other
 * text matches the name or email anywhere.
 *
 * Call it at module level or memoize the result: a new array on every render recomputes what depends on it.
 *
 * @since TBD
 */
export function donorFilters({recurringDonationsEnabled}: {recurringDonationsEnabled: boolean}): FilterDeclaration[] {
    const filters: FilterDeclaration[] = [
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
            kind: 'date',
            id: 'date',
            label: __('Date created', 'give'),
            parameters: {start: 'start', end: 'end'},
        },
    ];

    if (recurringDonationsEnabled) {
        filters.push({
            kind: 'options',
            id: 'donorType',
            label: __('Donor type', 'give'),
            multiple: false,
            options: [
                {value: 'one-time', label: __('One-time donor', 'give')},
                {value: 'subscriber', label: __('Subscriber', 'give')},
            ],
            parameter: 'donorType',
        });
    }

    filters.push({
        kind: 'search',
        id: 'search',
        label: __('Search donors', 'give'),
        hint: __(
            'Searches donor name, email or donor ID. Names and emails match anywhere in the text; an ID matches exactly.',
            'give'
        ),
        parameter: 'search',
    });

    return filters;
}

/**
 * The Donors list filters for this site, built once.
 *
 * @since TBD
 */
export const donorsFilters: FilterDeclaration[] = donorFilters({
    recurringDonationsEnabled: Boolean(window.GiveDonors?.recurringDonationsEnabled),
});
