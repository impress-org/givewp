import {donationFormFilters} from './filters';

describe('donationFormFilters', () => {
    it('declares Campaign as a primary multiple entity filter with "No campaign"', () => {
        expect(donationFormFilters({withCampaign: true}).find((filter) => filter.id === 'campaign')).toEqual({
            kind: 'entity',
            id: 'campaign',
            label: 'Campaign',
            isPrimary: true,
            legacyUrlKey: 'campaign',
            source: 'campaign',
            multiple: true,
            allowNone: true,
            parameter: 'campaign',
        });
    });

    it('declares Status as a primary multiple filter fed by the vocabulary, with Trash exclusive', () => {
        expect(donationFormFilters({withCampaign: true}).find((filter) => filter.id === 'status')).toEqual({
            kind: 'options',
            id: 'status',
            label: 'Status',
            isPrimary: true,
            legacyUrlKey: 'status',
            multiple: true,
            options: 'statuses',
            parameter: 'status',
            exclusiveValues: ['trash'],
        });
    });

    it('declares Date created as a secondary filter on start and end', () => {
        expect(donationFormFilters({withCampaign: true}).find((filter) => filter.id === 'date')).toEqual({
            kind: 'date',
            id: 'date',
            label: 'Date created',
            parameters: {start: 'start', end: 'end'},
        });
    });

    it('declares the search with the per-word title hint', () => {
        expect(donationFormFilters({withCampaign: true}).find((filter) => filter.kind === 'search')).toEqual({
            kind: 'search',
            id: 'search',
            label: 'Search donation forms',
            hint: 'Searches form title or form ID. Each word can match anywhere in the title; an ID matches exactly.',
            parameter: 'search',
        });
    });

    it('leaves the campaign out when the campaign is fixed by the page', () => {
        const filters = donationFormFilters({withCampaign: false});

        expect(filters.map((filter) => filter.id)).toEqual(['status', 'date', 'search']);
    });
});
