import {campaignFilters} from './filters';

describe('campaignFilters', () => {
    it('declares Status as a primary multiple filter fed by the server vocabulary', () => {
        expect(campaignFilters.find((filter) => filter.id === 'status')).toEqual({
            kind: 'options',
            id: 'status',
            label: 'Status',
            isPrimary: true,
            legacyUrlKey: 'status',
            multiple: true,
            options: 'statuses',
            parameter: 'statuses',
            whenEmpty: {status: 'any'},
        });
    });

    it('declares Date created as a secondary filter on start and end', () => {
        expect(campaignFilters.find((filter) => filter.id === 'date')).toEqual({
            kind: 'date',
            id: 'date',
            label: 'Date created',
            parameters: {start: 'start', end: 'end'},
        });
    });

    it('declares the search with a hint that says text matches anywhere and an ID exactly', () => {
        expect(campaignFilters.find((filter) => filter.kind === 'search')).toEqual({
            kind: 'search',
            id: 'search',
            label: 'Search campaigns',
            hint: 'Searches campaign title, description or campaign ID. Text matches anywhere; an ID matches exactly.',
            parameter: 'search',
        });
    });

    it('declares nothing else', () => {
        expect(campaignFilters.map((filter) => filter.id)).toEqual(['status', 'date', 'search']);
    });
});
