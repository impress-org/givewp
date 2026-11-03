import type {View} from '@wordpress/dataviews';
import {filterRequestParameters} from '@givewp/components/ListTable/filters/requestParameters';
import type {FilterDeclaration} from '@givewp/components/ListTable/filters/types';
import {isDonationsTrashView} from './DonationRowActions';
import {donationFilters} from './filters';

const find = (filters: FilterDeclaration[], id: string) => filters.find((filter) => filter.id === id);

describe('donationFilters', () => {
    const filters = donationFilters({recurringDonationsEnabled: true});

    it('declares Campaign as a primary multiple entity filter with "No campaign" on campaignId', () => {
        expect(find(filters, 'campaign')).toEqual({
            kind: 'entity',
            id: 'campaign',
            label: 'Campaign',
            isPrimary: true,
            legacyUrlKey: 'campaignId',
            source: 'campaign',
            multiple: true,
            allowNone: true,
            parameter: 'campaignId',
        });
    });

    it('declares Status as a primary multiple filter fed by the vocabulary, with Trash exclusive', () => {
        expect(find(filters, 'status')).toEqual({
            kind: 'options',
            id: 'status',
            label: 'Status',
            isPrimary: true,
            multiple: true,
            options: 'statuses',
            parameter: 'status',
            exclusiveValues: ['trash'],
        });
    });

    it('declares Form as a secondary multiple entity filter on formId', () => {
        expect(find(filters, 'form')).toEqual({
            kind: 'entity',
            id: 'form',
            label: 'Form',
            legacyUrlKey: 'formId',
            source: 'form',
            multiple: true,
            parameter: 'formId',
        });
    });

    it('declares Donor as a secondary single entity filter that reads the old donor link', () => {
        expect(find(filters, 'donor')).toEqual({
            kind: 'entity',
            id: 'donor',
            label: 'Donor',
            legacyUrlKey: 'donor',
            source: 'donor',
            multiple: false,
            parameter: 'donor',
        });
    });

    it('declares Donation date as a secondary filter on start and end', () => {
        expect(find(filters, 'date')).toEqual({
            kind: 'date',
            id: 'date',
            label: 'Donation date',
            parameters: {start: 'start', end: 'end'},
        });
    });

    it('declares Donation type as a secondary single filter on donationType', () => {
        expect(find(filters, 'donationType')).toEqual({
            kind: 'options',
            id: 'donationType',
            label: 'Donation type',
            multiple: false,
            options: [
                {value: 'one-time', label: 'One-time'},
                {value: 'recurring', label: 'Recurring'},
            ],
            parameter: 'donationType',
        });
    });

    it('declares the search with the prefix-match hint', () => {
        expect(filters.find((filter) => filter.kind === 'search')).toEqual({
            kind: 'search',
            id: 'search',
            label: 'Search donations',
            hint: 'Searches donor first name, last name, email or donation ID. A name matches from the start of the first or the last name. An email needs the @ sign and matches from the start. An ID matches exactly.',
            parameter: 'search',
        });
    });

    it('lists the filters in toolbar order', () => {
        expect(filters.map((filter) => filter.id)).toEqual([
            'campaign',
            'status',
            'form',
            'donor',
            'date',
            'donationType',
            'search',
        ]);
    });

    it('leaves out Donation type when recurring is off', () => {
        expect(donationFilters({recurringDonationsEnabled: false}).map((filter) => filter.id)).toEqual([
            'campaign',
            'status',
            'form',
            'donor',
            'date',
            'search',
        ]);
    });

    it('sends the trash view as a status the trash-view check still recognizes', () => {
        const view = {
            type: 'table',
            filters: [{field: 'filter-status', operator: 'isAny', value: ['trash']}],
        } as View;
        const parameters = filterRequestParameters(filters, view, '2026-10-20');

        expect(parameters.status).toEqual(['trash']);
        expect(isDonationsTrashView(parameters)).toBe(true);
    });

    it('sends a donor from the link as one id', () => {
        const view = {type: 'table', filters: [{field: 'filter-donor', operator: 'is', value: '12'}]} as View;

        expect(filterRequestParameters(filters, view, '2026-10-20')).toEqual({donor: '12'});
    });
});

describe('donationsFilters (module level)', () => {
    const original = window.GiveDonations;

    afterEach(() => {
        window.GiveDonations = original;
    });

    const load = (recurringDonationsEnabled: boolean): FilterDeclaration[] => {
        let declarations: FilterDeclaration[] = [];

        Object.assign(window, {GiveDonations: {recurringDonationsEnabled}});
        jest.isolateModules(() => {
            // eslint-disable-next-line @typescript-eslint/no-var-requires
            declarations = require('./filters').donationsFilters;
        });

        return declarations;
    };

    it('offers Donation type when Give Recurring is active', () => {
        expect(load(true).map((filter) => filter.id)).toContain('donationType');
    });

    it('leaves out Donation type when recurring is off', () => {
        expect(load(false).map((filter) => filter.id)).not.toContain('donationType');
    });
});
