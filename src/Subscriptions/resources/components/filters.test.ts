import type {View} from '@wordpress/dataviews';
import {filterRequestParameters} from '@givewp/components/ListTable/filters/requestParameters';
import {hasStatus} from '@givewp/components/ListTable/adapters/hasStatus';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';
import {subscriptionFilters} from './filters';

const find = (id: string) => subscriptionFilters.find((filter: FilterDeclaration) => filter.id === id);

describe('subscriptionFilters', () => {
    it('declares Campaign as a primary multiple entity filter on campaignId', () => {
        expect(find('campaign')).toEqual({
            kind: 'entity',
            id: 'campaign',
            label: 'Campaign',
            isPrimary: true,
            legacyUrlKey: 'campaignId',
            source: 'campaign',
            multiple: true,
            parameter: 'campaignId',
        });
    });

    it('declares Status as a primary multiple filter fed by the vocabulary, with Trashed exclusive', () => {
        expect(find('status')).toEqual({
            kind: 'options',
            id: 'status',
            label: 'Status',
            isPrimary: true,
            multiple: true,
            options: 'statuses',
            parameter: 'status',
            exclusiveValues: ['trashed'],
        });
    });

    it('declares Date created as a secondary filter on start and end', () => {
        expect(find('date')).toEqual({
            kind: 'date',
            id: 'date',
            label: 'Date created',
            parameters: {start: 'start', end: 'end'},
        });
    });

    it('declares the search with the match-anywhere hint', () => {
        expect(subscriptionFilters.find((filter) => filter.kind === 'search')).toEqual({
            kind: 'search',
            id: 'search',
            label: 'Search subscriptions',
            hint: 'Searches donor name, email or subscription ID. Names and emails match anywhere in the text; an ID matches exactly.',
            parameter: 'search',
        });
    });

    it('lists the filters in toolbar order', () => {
        expect(subscriptionFilters.map((filter) => filter.id)).toEqual(['campaign', 'status', 'date', 'search']);
    });

    it('sends the trashed view as a status the trash-view check still recognizes', () => {
        const view = {
            type: 'table',
            filters: [{field: 'filter-status', operator: 'isAny', value: ['trashed']}],
        } as View;

        expect(hasStatus(filterRequestParameters(subscriptionFilters, view, '2026-10-20'), 'trashed')).toBe(true);
    });
});
