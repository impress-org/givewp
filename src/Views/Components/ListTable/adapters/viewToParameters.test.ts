import type {View} from '@wordpress/dataviews';
import type {FilterConfig} from '../ListTablePage/types';
import {filterSettingsToDeclarations} from '../filters/legacyFilterSettings';
import type {FilterDeclaration} from '../filters/types';
import {filterFieldId, isApplied, snapPerPage, viewToParameters} from './viewToParameters';

const donationFilters: FilterDeclaration[] = [
    {kind: 'search', id: 'search', label: 'Search donations', hint: '', parameter: 'search'},
    {
        kind: 'entity',
        id: 'campaign',
        label: 'Campaign',
        source: 'campaign',
        multiple: true,
        parameter: 'campaignId',
        allowNone: true,
    },
    {kind: 'entity', id: 'donor', label: 'Donor', source: 'donor', multiple: false, parameter: 'donor'},
    {
        kind: 'options',
        id: 'status',
        label: 'Status',
        multiple: true,
        options: 'statuses',
        parameter: 'status',
        exclusiveValues: ['trash'],
    },
    {kind: 'date', id: 'date', label: 'Donation date', parameters: {start: 'start', end: 'end'}},
];

const view = (overrides: Partial<View> = {}): View =>
    ({
        type: 'table',
        page: 1,
        perPage: 30,
        sort: {field: 'id', direction: 'desc'},
        search: '',
        filters: [],
        ...overrides,
    } as View);

const is = (id: string, value: unknown) => ({field: filterFieldId(id), operator: 'is' as const, value});

describe('viewToParameters', () => {
    it('matches the old request for the default Donations view, without empty values', () => {
        expect(viewToParameters(view(), donationFilters, {}, {testMode: false})).toEqual({
            page: 1,
            perPage: 30,
            sortColumn: 'id',
            sortDirection: 'desc',
            testMode: false,
        });
    });

    it('never sends a locale', () => {
        // A caller that still passes a locale must not get it sent.
        const options = {testMode: false, locale: 'pt-BR'};

        expect(viewToParameters(view(), [], {}, options)).not.toHaveProperty('locale');
    });

    it('leaves out an undefined test mode instead of sending "undefined"', () => {
        expect(viewToParameters(view(), donationFilters, {}, {})).not.toHaveProperty('testMode');
    });

    it('reads sort, page, search and filters from the view', () => {
        const parameters = viewToParameters(
            view({
                page: 3,
                perPage: 50,
                sort: {field: 'amount', direction: 'asc'},
                search: 'jane',
                filters: [
                    {field: filterFieldId('campaign'), operator: 'isAny', value: ['4']},
                    is('donor', '12'),
                    {field: filterFieldId('status'), operator: 'isAny', value: ['publish', 'pending']},
                    is('date', '2026-10-01_2026-10-15'),
                ],
            }),
            donationFilters,
            {},
            {testMode: true, today: '2026-10-28'}
        );

        expect(parameters).toEqual({
            page: 3,
            perPage: 50,
            sortColumn: 'amount',
            sortDirection: 'asc',
            testMode: true,
            search: 'jane',
            campaignId: ['4'],
            donor: '12',
            status: ['publish', 'pending'],
            start: '2026-10-01T00:00:00',
            end: '2026-10-15T23:59:59',
        });
    });

    it('merges the table scope into every request', () => {
        expect(viewToParameters(view(), donationFilters, {campaignId: 7}, {})).toMatchObject({
            campaignId: 7,
        });
    });

    it('caps the page size at 100', () => {
        expect(viewToParameters(view({perPage: 500}), [], {}, {})).toMatchObject({perPage: 100});
    });

    it('gives the same parameters for the same view and day', () => {
        const build = () =>
            viewToParameters(
                view({filters: [is('date', '30')]}),
                donationFilters,
                {},
                {today: '2026-10-28'}
            );

        expect(build()).toEqual(build());
    });

    describe('an add-on screen that passes only the old filter settings', () => {
        // The shape an add-on passed before filter declarations existed.
        const settings: FilterConfig[] = [
            {name: 'campaignId', type: 'campaignselect', options: [{value: 'none', text: 'No campaign'}]},
            {name: 'search', type: 'search', text: 'Search'},
            {
                name: 'status',
                type: 'select',
                text: 'Status',
                options: [
                    {value: 'any', text: 'All'},
                    {value: 'active', text: 'Active'},
                ],
            },
            {name: 'toggle', type: 'checkbox', text: 'Test'},
        ];
        const {filters} = filterSettingsToDeclarations(settings, false);

        it('sends the first select option while the select is not applied, as the old default', () => {
            expect(viewToParameters(view(), filters, {}, {})).toMatchObject({status: 'any'});
        });

        it('sends the old parameter names with the chosen values', () => {
            const parameters = viewToParameters(
                view({
                    search: 'jane',
                    filters: [is('campaignId', '4'), is('status', 'active')],
                }),
                filters,
                {},
                {testMode: true}
            );

            expect(parameters).toMatchObject({campaignId: '4', status: 'active', search: 'jane', testMode: true});
        });

        it('sends "No campaign" as none', () => {
            expect(
                viewToParameters(view({filters: [is('campaignId', 'none')]}), filters, {}, {})
            ).toMatchObject({campaignId: 'none'});
        });
    });
});

describe('snapPerPage', () => {
    it.each([
        [10, 10],
        [30, 30],
        [1, 10],
        [24, 20],
        [26, 30],
        [45, 50],
        [80, 100],
        [500, 100],
    ])('snaps %p to %p', (input, expected) => {
        expect(snapPerPage(input)).toBe(expected);
    });

    it('picks the smaller size on a tie', () => {
        expect(snapPerPage(25)).toBe(20);
    });

    it('falls back to the given default when the size is not a number', () => {
        expect(snapPerPage(Number.NaN, 50)).toBe(50);
        expect(snapPerPage(undefined)).toBe(30);
    });

    it('sends the nearest offered page size in the request', () => {
        expect(viewToParameters({type: 'table', perPage: 25}, [], {}, {}).perPage).toBe(20);
        expect(viewToParameters({type: 'table'}, [], {}, {}).perPage).toBe(30);
    });
});

describe('isApplied', () => {
    it.each([[undefined], [null], [''], [[]]])('treats %p as not applied', (value) => {
        expect(isApplied(value)).toBe(false);
    });

    it.each([[0], [false], ['trash'], [['publish']]])('treats %p as applied', (value) => {
        expect(isApplied(value)).toBe(true);
    });
});
