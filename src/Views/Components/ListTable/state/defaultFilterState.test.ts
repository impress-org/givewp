import type {Filter, View} from '@wordpress/dataviews';
import {differsFromDefaultFilters, differsFromDefaultFiltersIgnoring, withDefaultFilters} from './defaultFilterState';

const testMode: Filter = {field: 'filter-testMode', operator: 'is', value: true};
const view = (filters: Filter[], search = ''): View => ({type: 'table', page: 3, search, filters});

describe('differsFromDefaultFilters', () => {
    it('is false when the view holds exactly the default filters', () => {
        expect(differsFromDefaultFilters(view([testMode]), [testMode])).toBe(false);
    });

    it('is false with no filters and no defaults', () => {
        expect(differsFromDefaultFilters(view([]), [])).toBe(false);
    });

    it('is true when a filter is added', () => {
        const status: Filter = {field: 'filter-status', operator: 'isAny', value: ['pending']};

        expect(differsFromDefaultFilters(view([testMode, status]), [testMode])).toBe(true);
    });

    it('is true when a default filter is removed', () => {
        expect(differsFromDefaultFilters(view([]), [testMode])).toBe(true);
    });

    it('is true for an added filter that holds no value yet', () => {
        expect(differsFromDefaultFilters(view([{field: 'filter-date', operator: 'is', value: undefined}]), [])).toBe(
            true
        );
    });

    it('is true with a search term', () => {
        expect(differsFromDefaultFilters(view([testMode], 'smith'), [testMode])).toBe(true);
    });

    it('ignores locked filters and the order of list values', () => {
        const locked: Filter = {field: 'filter-campaignId', operator: 'is', value: '7', isLocked: true};
        const ab: Filter = {field: 'filter-status', operator: 'isAny', value: ['a', 'b']};
        const ba: Filter = {field: 'filter-status', operator: 'isAny', value: ['b', 'a']};

        expect(differsFromDefaultFilters(view([locked, ba]), [ab])).toBe(false);
    });
});

describe('differsFromDefaultFiltersIgnoring', () => {
    const status: Filter = {field: 'filter-status', operator: 'isAny', value: ['pending']};

    it('is false when only the ignored filter was removed from the defaults', () => {
        expect(differsFromDefaultFiltersIgnoring(view([]), [testMode], 'filter-testMode')).toBe(false);
    });

    it('is false when only the ignored filter was added', () => {
        expect(differsFromDefaultFiltersIgnoring(view([testMode]), [], 'filter-testMode')).toBe(false);
    });

    it('is false when only the value of the ignored filter changed', () => {
        expect(
            differsFromDefaultFiltersIgnoring(view([{...testMode, value: false}]), [testMode], 'filter-testMode')
        ).toBe(false);
    });

    it('is true when another filter is added, also with the ignored filter changed', () => {
        expect(differsFromDefaultFiltersIgnoring(view([status]), [], 'filter-testMode')).toBe(true);
        expect(differsFromDefaultFiltersIgnoring(view([status]), [testMode], 'filter-testMode')).toBe(true);
    });

    it('is true with a search term', () => {
        expect(differsFromDefaultFiltersIgnoring(view([testMode], 'smith'), [testMode], 'filter-testMode')).toBe(true);
    });
});

describe('withDefaultFilters', () => {
    it('clears the search, restores the defaults, keeps locked filters and goes to page one', () => {
        const locked: Filter = {field: 'filter-campaignId', operator: 'is', value: '7', isLocked: true};
        const status: Filter = {field: 'filter-status', operator: 'isAny', value: ['pending']};

        expect(withDefaultFilters(view([locked, status], 'smith'), [testMode])).toEqual({
            type: 'table',
            page: 1,
            search: '',
            filters: [locked, testMode],
        });
    });
});
