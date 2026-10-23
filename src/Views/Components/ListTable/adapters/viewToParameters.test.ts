import type {View} from '@wordpress/dataviews';
import type {FilterConfig} from '../ListTablePage/types';
import {clampPerPage, filterByValues, filterFieldId, viewToParameters} from './viewToParameters';

const donationFilters: FilterConfig[] = [
    {name: 'campaignId', type: 'campaignselect', options: [{value: 'none', text: 'No campaign'}]},
    {name: 'formId', type: 'formselect'},
    {name: 'search', type: 'search'},
    {name: 'toggle', type: 'checkbox', text: 'Test'},
    {name: 'donor', type: 'hidden'},
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: [
            {id: 'showTrashed', apiParam: 'status', name: 'Status', type: 'toggle', options: [{text: 'Show trashed', value: 'trash'}]},
            {
                id: 'status',
                apiParam: 'status',
                name: 'Status',
                type: 'checkbox',
                isVisible: (values) => !values?.status?.includes('trash'),
                options: [
                    {text: 'Completed', value: 'publish'},
                    {text: 'Pending', value: 'pending'},
                ],
            },
            {id: 'period', apiParam: 'start', name: 'Period', type: 'radio', options: [{text: 'Last 7 days', value: '2026-10-13'}]},
        ],
    },
];

const formFilters: FilterConfig[] = [
    {name: 'status', type: 'select', options: [{value: 'any', text: 'All'}, {value: 'trash', text: 'Trash'}]},
    {name: 'search', type: 'search'},
    {name: 'campaign', type: 'campaignselect'},
    {name: 'campaignId', type: 'hidden'},
];

const view = (overrides: Partial<View> = {}): View =>
    ({type: 'table', page: 1, perPage: 30, sort: {field: 'id', direction: 'desc'}, search: '', filters: [], ...overrides}) as View;

const is = (id: string, value: unknown) => ({field: filterFieldId(id), operator: 'is' as const, value});

describe('viewToParameters', () => {
    it('matches the old request for the default Donations view, without empty values', () => {
        expect(viewToParameters(view(), donationFilters, {}, {locale: 'en-US', testMode: false})).toEqual({
            page: 1,
            perPage: 30,
            sortColumn: 'id',
            sortDirection: 'desc',
            locale: 'en-US',
            testMode: false,
        });
    });

    it('leaves out an undefined test mode instead of sending "undefined"', () => {
        expect(viewToParameters(view(), donationFilters, {}, {locale: 'en-US'})).not.toHaveProperty('testMode');
    });

    it('reads sort, page, search and filters from the view', () => {
        const parameters = viewToParameters(
            view({
                page: 3,
                perPage: 50,
                sort: {field: 'amount', direction: 'asc'},
                search: 'jane',
                filters: [
                    is('campaignId', 4),
                    is('donor', '12'),
                    {field: filterFieldId('status'), operator: 'isAny', value: ['publish', 'pending']},
                    is('period', '2026-10-13'),
                ],
            }),
            donationFilters,
            {},
            {locale: 'en-US', testMode: true}
        );

        expect(parameters).toEqual({
            page: 3,
            perPage: 50,
            sortColumn: 'amount',
            sortDirection: 'asc',
            locale: 'en-US',
            testMode: true,
            search: 'jane',
            campaignId: 4,
            donor: '12',
            status: ['publish', 'pending'],
            start: ['2026-10-13'],
        });
    });

    it('sends the first select option when the select filter is not applied, as the old default', () => {
        expect(viewToParameters(view(), formFilters, {}, {locale: 'en-US'})).toMatchObject({status: 'any'});
        expect(viewToParameters(view({filters: [is('status', 'trash')]}), formFilters, {}, {locale: 'en-US'})).toMatchObject({
            status: 'trash',
        });
    });

    it('merges the table scope into every request', () => {
        expect(viewToParameters(view(), formFilters, {campaignId: 7}, {locale: 'en-US'})).toMatchObject({campaignId: 7});
    });

    it('caps the page size at 100', () => {
        expect(viewToParameters(view({perPage: 500}), [], {}, {locale: 'en-US'})).toMatchObject({perPage: 100});
    });
});

describe('filterByValues', () => {
    it('adds groups that share an API parameter to one array', () => {
        const values = filterByValues(
            view({filters: [is('showTrashed', 'trash'), {field: filterFieldId('period'), operator: 'is', value: 'x'}]}),
            donationFilters
        );

        expect(values).toEqual({status: ['trash'], start: ['x']});
    });

    it('drops the values of a group the current values hide', () => {
        const values = filterByValues(
            view({
                filters: [
                    is('showTrashed', 'trash'),
                    {field: filterFieldId('status'), operator: 'isAny', value: ['publish']},
                ],
            }),
            donationFilters
        );

        expect(values).toEqual({status: ['trash']});
    });
});

describe('clampPerPage', () => {
    it('keeps the page size between 1 and 100, 30 when missing', () => {
        expect(clampPerPage(undefined)).toBe(30);
        expect(clampPerPage(0)).toBe(30);
        expect(clampPerPage(-4)).toBe(1);
        expect(clampPerPage(250)).toBe(100);
        expect(clampPerPage(20)).toBe(20);
    });
});
