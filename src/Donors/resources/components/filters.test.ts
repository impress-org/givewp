import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';
import {donorFilters} from './filters';

const find = (filters: FilterDeclaration[], id: string) => filters.find((filter) => filter.id === id);

describe('donorFilters', () => {
    const filters = donorFilters({recurringDonationsEnabled: true});

    it('declares Campaign as a primary multiple entity filter on campaignId, without "No campaign"', () => {
        expect(find(filters, 'campaign')).toEqual({
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

    it('declares Date created as a secondary filter on start and end', () => {
        expect(find(filters, 'date')).toEqual({
            kind: 'date',
            id: 'date',
            label: 'Date created',
            parameters: {start: 'start', end: 'end'},
        });
    });

    it('declares Donor type as a secondary single filter on donorType', () => {
        expect(find(filters, 'donorType')).toEqual({
            kind: 'options',
            id: 'donorType',
            label: 'Donor type',
            multiple: false,
            options: [
                {value: 'one-time', label: 'One-time donor'},
                {value: 'subscriber', label: 'Subscriber'},
            ],
            parameter: 'donorType',
        });
    });

    it('declares the search with the match-anywhere hint', () => {
        expect(filters.find((filter) => filter.kind === 'search')).toEqual({
            kind: 'search',
            id: 'search',
            label: 'Search donors',
            hint: 'Searches donor name, email or donor ID. Names and emails match anywhere in the text; an ID matches exactly.',
            parameter: 'search',
        });
    });

    it('lists the filters in toolbar order', () => {
        expect(filters.map((filter) => filter.id)).toEqual(['campaign', 'date', 'donorType', 'search']);
    });

    it('leaves out Donor type when recurring is off', () => {
        expect(donorFilters({recurringDonationsEnabled: false}).map((filter) => filter.id)).toEqual([
            'campaign',
            'date',
            'search',
        ]);
    });
});

describe('donorsFilters (module level)', () => {
    const original = window.GiveDonors;

    afterEach(() => {
        window.GiveDonors = original;
    });

    const load = (recurringDonationsEnabled: boolean): FilterDeclaration[] => {
        let declarations: FilterDeclaration[] = [];

        Object.assign(window, {GiveDonors: {recurringDonationsEnabled}});
        jest.isolateModules(() => {
            // eslint-disable-next-line @typescript-eslint/no-var-requires
            declarations = require('./filters').donorsFilters;
        });

        return declarations;
    };

    it('offers Donor type when Give Recurring is active', () => {
        expect(load(true).map((filter) => filter.id)).toContain('donorType');
    });

    it('leaves out Donor type when recurring is off', () => {
        expect(load(false).map((filter) => filter.id)).not.toContain('donorType');
    });
});
