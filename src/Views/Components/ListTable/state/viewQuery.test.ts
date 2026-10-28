import type {FilterByGroupedOptions, FilterConfig} from '../ListTablePage/types';
import {
    MAX_ADDRESS_LENGTH,
    parameterName,
    parseViewQuery,
    serializeViewQuery,
    viewQueryKeys,
    writeViewQuery,
} from './viewQuery';
import type {ListTableView, ScreenDefaults, ViewQueryContext} from './types';

const option = (value: string) => ({value, text: value});

const showTrashed: FilterByGroupedOptions = {
    id: 'showTrashed',
    apiParam: 'status',
    name: 'Status',
    type: 'toggle',
    options: [option('trash')],
};
const status: FilterByGroupedOptions = {
    id: 'status',
    apiParam: 'status',
    name: 'Status',
    type: 'checkbox',
    options: ['publish', 'pending', 'failed'].map(option),
};
const period: FilterByGroupedOptions = {
    id: 'period',
    apiParam: 'start',
    name: 'Period',
    type: 'radio',
    options: ['90', '30', '7'].map(option),
};

const donationsFilters: FilterConfig[] = [
    {name: 'campaignId', type: 'campaignselect', options: [option('none')]},
    {name: 'formId', type: 'formselect'},
    {name: 'search', type: 'search'},
    {name: 'toggle', type: 'checkbox'},
    {name: 'donor', type: 'hidden'},
    {name: 'filterBy', type: 'filterby', groupedOptions: [showTrashed, status, period]},
];

const formsFilters: FilterConfig[] = [
    {name: 'campaign', type: 'campaignselect', options: [option('none')]},
    {name: 'status', type: 'select', options: ['any', 'publish', 'pending', 'draft', 'trash', 'upgraded'].map(option)},
    {name: 'search', type: 'search'},
];

const defaults: ScreenDefaults = {
    fields: ['id', 'amount'],
    fieldOrder: ['id', 'amount'],
    density: 'balanced',
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    titleField: 'donor',
    lockedFields: ['donor'],
    filters: [],
};

const donations: ViewQueryContext = {
    filterSettings: donationsFilters,
    sortableFields: ['id', 'amount', 'date'],
    defaults,
    siteTestMode: false,
};

const forms: ViewQueryContext = {...donations, filterSettings: formsFilters};

const parse = (search: string, context: ViewQueryContext = donations) =>
    parseViewQuery(new URLSearchParams(search), context);

const view = (extra: Partial<ListTableView> = {}): ListTableView => ({
    type: 'table',
    page: 1,
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    search: '',
    filters: [],
    fields: ['id', 'amount'],
    layout: {density: 'balanced'},
    ...extra,
});

describe('parameterName', () => {
    it.each([
        ['donor', 'give-donor'],
        ['campaignId', 'give-campaign-id'],
        ['showTrashed', 'give-show-trashed'],
        ['form_id', 'give-form-id'],
    ])('names %p %p', (id, expected) => {
        expect(parameterName(id)).toBe(expected);
    });
});

describe('viewQueryKeys', () => {
    const screens: Array<[string, FilterConfig[]]> = [
        ['donations', donationsFilters],
        [
            'donors',
            [
                {name: 'campaignId', type: 'campaignselect'},
                {name: 'filterBy', type: 'filterby', groupedOptions: [period]},
            ],
        ],
        [
            'subscriptions',
            [
                {name: 'campaignId', type: 'campaignselect'},
                {name: 'toggle', type: 'checkbox'},
                {name: 'filterBy', type: 'filterby', groupedOptions: [showTrashed, status, period]},
            ],
        ],
        ['campaigns', [{name: 'status', type: 'select', options: ['any', 'active', 'inactive'].map(option)}]],
        ['donation-forms', formsFilters],
        ['event-tickets', [{name: 'search', type: 'search'}]],
    ];

    it.each(screens)('gives unique give- names on %s', (_, filterSettings) => {
        const keys = viewQueryKeys({...donations, filterSettings});

        expect(new Set(keys).size).toBe(keys.length);
        keys.forEach((key) => expect(key).toMatch(/^give-[a-z0-9]+(-[a-z0-9]+)*$/));
    });

    it('lists every parameter of the Donations screen', () => {
        expect(viewQueryKeys(donations)).toEqual([
            'give-campaign-id',
            'give-form-id',
            'give-donor',
            'give-show-trashed',
            'give-status',
            'give-period',
            'give-test-mode',
            'give-orderby',
            'give-order',
            'give-paged',
            'give-per-page',
        ]);
    });

    it('has no test mode parameter on a screen without the toggle', () => {
        expect(viewQueryKeys(forms)).not.toContain('give-test-mode');
    });
});

describe('serializeViewQuery', () => {
    it('writes nothing for the default view', () => {
        expect(serializeViewQuery(view(), donations)).toEqual({});
    });

    it('writes every give- parameter in a fixed order', () => {
        const parameters = serializeViewQuery(
            view({
                page: 3,
                perPage: 50,
                sort: {field: 'amount', direction: 'asc'},
                filters: [
                    {field: 'filter-status', operator: 'isAny', value: ['failed', 'pending']},
                    {field: 'filter-campaignId', operator: 'is', value: 4},
                    {field: 'filter-formId', operator: 'is', value: 9},
                    {field: 'filter-donor', operator: 'is', value: '123'},
                    {field: 'filter-showTrashed', operator: 'is', value: 'trash'},
                    {field: 'filter-period', operator: 'is', value: '30'},
                    {field: 'filter-testMode', operator: 'is', value: true},
                ],
            }),
            donations
        );

        expect(Object.entries(parameters)).toEqual([
            ['give-campaign-id', '4'],
            ['give-form-id', '9'],
            ['give-donor', '123'],
            ['give-show-trashed', '1'],
            ['give-status', 'failed,pending'],
            ['give-period', '30'],
            ['give-test-mode', '1'],
            ['give-orderby', 'amount'],
            ['give-order', 'asc'],
            ['give-paged', '3'],
            ['give-per-page', '50'],
        ]);
    });

    it('writes a select filter by its name', () => {
        const parameters = serializeViewQuery(
            view({filters: [{field: 'filter-status', operator: 'is', value: 'draft'}]}),
            forms
        );

        expect(parameters).toEqual({'give-status': 'draft'});
    });

    it('writes both sort parameters when only the direction differs', () => {
        expect(serializeViewQuery(view({sort: {field: 'id', direction: 'asc'}}), donations)).toEqual({
            'give-orderby': 'id',
            'give-order': 'asc',
        });
    });

    it('omits filters with no value', () => {
        const parameters = serializeViewQuery(
            view({
                filters: [
                    {field: 'filter-status', operator: 'isAny', value: []},
                    {field: 'filter-donor', operator: 'is', value: ''},
                ],
            }),
            donations
        );

        expect(parameters).toEqual({});
    });

    it('omits a filter equal to its default', () => {
        const withDefault = {
            ...donations,
            defaults: {
                ...defaults,
                filters: [{field: 'filter-status', operator: 'isAny' as const, value: ['publish']}],
            },
        };

        expect(
            serializeViewQuery(
                view({filters: [{field: 'filter-status', operator: 'isAny', value: ['publish']}]}),
                withDefault
            )
        ).toEqual({});
    });

    it('writes test mode only when it differs from the site mode', () => {
        const testOn = view({filters: [{field: 'filter-testMode', operator: 'is', value: true}]});
        const testSite = {...donations, siteTestMode: true};

        expect(serializeViewQuery(testOn, donations)).toEqual({'give-test-mode': '1'});
        expect(serializeViewQuery(testOn, testSite)).toEqual({});
        expect(serializeViewQuery(view(), testSite)).toEqual({'give-test-mode': '0'});
        expect(serializeViewQuery(view(), donations)).toEqual({});
    });

    it('treats a view without a filter list as test mode off', () => {
        const noFilters = {...view(), filters: undefined};

        expect(serializeViewQuery(noFilters, donations)).toEqual({});
        expect(serializeViewQuery(noFilters, {...donations, siteTestMode: true})).toEqual({'give-test-mode': '0'});
    });

    it('never writes test mode on a screen without the toggle', () => {
        expect(serializeViewQuery(view(), {...forms, siteTestMode: true})).toEqual({});
    });

    it('never writes the search term', () => {
        expect(serializeViewQuery(view({search: 'smith'}), donations)).toEqual({});
    });

    it('never writes a value that is not an id or an enumerated value', () => {
        const parameters = serializeViewQuery(
            view({
                filters: [
                    {field: 'filter-donor', operator: 'is', value: 'jane@example.com'},
                    {field: 'filter-status', operator: 'isAny', value: ['failed', '<b>x</b>']},
                ],
            }),
            donations
        );

        expect(parameters).toEqual({'give-status': 'failed'});
    });

    it('ignores filters that are not this screen', () => {
        expect(
            serializeViewQuery(view({filters: [{field: 'filter-other', operator: 'is', value: '1'}]}), donations)
        ).toEqual({});
    });
});

describe('parseViewQuery', () => {
    it('reads nothing from an address without list parameters', () => {
        expect(parse('page=give-payment-history&post_type=give_forms')).toEqual({
            query: {filters: []},
            corrected: false,
            legacyKeys: [],
        });
    });

    it('round-trips a serialized view', () => {
        const original = view({
            page: 3,
            perPage: 50,
            sort: {field: 'amount', direction: 'asc'},
            filters: [
                {field: 'filter-campaignId', operator: 'is', value: 4},
                {field: 'filter-donor', operator: 'is', value: '123'},
                {field: 'filter-showTrashed', operator: 'is', value: 'trash'},
                {field: 'filter-status', operator: 'isAny', value: ['failed', 'pending']},
                {field: 'filter-period', operator: 'is', value: '30'},
            ],
        });
        const {query, corrected} = parse(new URLSearchParams(serializeViewQuery(original, donations)).toString());

        expect(corrected).toBe(false);
        expect(query).toEqual({
            page: 3,
            perPage: 50,
            sort: {field: 'amount', direction: 'asc'},
            filters: original.filters,
        });
    });

    describe('give-paged', () => {
        it('reads an integer of 1 or more', () => {
            expect(parse('give-paged=4')).toMatchObject({query: {page: 4}, corrected: false});
        });

        it.each(['0', '-2', '1.5', 'two', ''])('drops %p', (value) => {
            const {query, corrected} = parse(`give-paged=${value}`);

            expect(query.page).toBeUndefined();
            expect(corrected).toBe(true);
        });
    });

    describe('give-per-page', () => {
        it('reads an offered size', () => {
            expect(parse('give-per-page=50')).toMatchObject({query: {perPage: 50}, corrected: false});
        });

        it.each([
            ['45', 50],
            ['7', 10],
            ['1000', 100],
        ])('snaps %p to %p', (value, expected) => {
            expect(parse(`give-per-page=${value}`)).toMatchObject({query: {perPage: expected}, corrected: true});
        });

        it.each(['abc', '-5', '2.5', ''])('drops %p', (value) => {
            const {query, corrected} = parse(`give-per-page=${value}`);

            expect(query.perPage).toBeUndefined();
            expect(corrected).toBe(true);
        });
    });

    describe('give-orderby and give-order', () => {
        it('reads a sortable column and a direction', () => {
            expect(parse('give-orderby=amount&give-order=asc')).toMatchObject({
                query: {sort: {field: 'amount', direction: 'asc'}},
                corrected: false,
            });
        });

        it('drops the whole sort for a column that is not sortable', () => {
            const {query, corrected} = parse('give-orderby=gateway&give-order=asc');

            expect(query.sort).toBeUndefined();
            expect(corrected).toBe(true);
        });

        it('uses desc for an unknown direction', () => {
            expect(parse('give-orderby=amount&give-order=up')).toMatchObject({
                query: {sort: {field: 'amount', direction: 'desc'}},
                corrected: true,
            });
        });

        it('uses desc when the direction is missing', () => {
            expect(parse('give-orderby=amount').query.sort).toEqual({field: 'amount', direction: 'desc'});
        });

        it('drops a direction without a column', () => {
            const {query, corrected} = parse('give-order=asc');

            expect(query.sort).toBeUndefined();
            expect(corrected).toBe(true);
        });
    });

    describe('select filter', () => {
        it('reads one of its options', () => {
            expect(parse('give-status=draft', forms)).toMatchObject({
                query: {filters: [{field: 'filter-status', operator: 'is', value: 'draft'}]},
                corrected: false,
            });
        });

        it.each(['any', 'archived'])('drops the "all" option and unknown values: %p', (value) => {
            expect(parse(`give-status=${value}`, forms)).toMatchObject({query: {filters: []}, corrected: true});
        });
    });

    describe('campaignselect and formselect filters', () => {
        it('reads a positive integer as a number', () => {
            expect(parse('give-campaign-id=4&give-form-id=12').query.filters).toEqual([
                {field: 'filter-campaignId', operator: 'is', value: 4},
                {field: 'filter-formId', operator: 'is', value: 12},
            ]);
        });

        it('reads a static option', () => {
            expect(parse('give-campaign=none', forms).query.filters).toEqual([
                {field: 'filter-campaign', operator: 'is', value: 'none'},
            ]);
        });

        it.each(['0', '-3', 'abc', '04', 'none'])('drops %p where it is not allowed', (value) => {
            expect(parse(`give-form-id=${value}`)).toMatchObject({query: {filters: []}, corrected: true});
        });
    });

    describe('hidden filter', () => {
        it('reads a token', () => {
            expect(parse('give-donor=123').query.filters).toEqual([
                {field: 'filter-donor', operator: 'is', value: '123'},
            ]);
        });

        it.each(['jane%40example.com', 'a%20b', 'x'.repeat(65), ''])('drops %p', (value) => {
            expect(parse(`give-donor=${value}`)).toMatchObject({query: {filters: []}, corrected: true});
        });
    });

    describe('filterby groups', () => {
        it('reads checkbox values', () => {
            expect(parse('give-status=failed,pending').query.filters).toEqual([
                {field: 'filter-status', operator: 'isAny', value: ['failed', 'pending']},
            ]);
        });

        it('drops only the checkbox values that are not options', () => {
            expect(parse('give-status=failed,bogus,%3Cb%3E')).toMatchObject({
                query: {filters: [{field: 'filter-status', operator: 'isAny', value: ['failed']}]},
                corrected: true,
            });
        });

        it('drops repeated checkbox values', () => {
            expect(parse('give-status=failed,failed')).toMatchObject({
                query: {filters: [{field: 'filter-status', operator: 'isAny', value: ['failed']}]},
                corrected: true,
            });
        });

        it('drops a checkbox group with no valid value', () => {
            expect(parse('give-status=bogus')).toMatchObject({query: {filters: []}, corrected: true});
        });

        it('keeps only the first radio value', () => {
            expect(parse('give-period=30,7')).toMatchObject({
                query: {filters: [{field: 'filter-period', operator: 'is', value: '30'}]},
                corrected: true,
            });
        });

        it('reads the stable period values', () => {
            ['7', '30', '90'].forEach((days) => {
                expect(parse(`give-period=${days}`)).toMatchObject({
                    query: {filters: [{field: 'filter-period', operator: 'is', value: days}]},
                    corrected: false,
                });
            });
        });

        it.each(['45', '2026-01-01T00:00:00.000Z', ''])('drops the period %p', (value) => {
            expect(parse(`give-period=${encodeURIComponent(value)}`)).toMatchObject({
                query: {filters: []},
                corrected: true,
            });
        });

        it('reads a toggle set to 1 as its option value', () => {
            expect(parse('give-show-trashed=1').query.filters).toEqual([
                {field: 'filter-showTrashed', operator: 'is', value: 'trash'},
            ]);
        });

        it.each(['0', 'true', 'trash'])('drops a toggle set to %p', (value) => {
            expect(parse(`give-show-trashed=${value}`)).toMatchObject({query: {filters: []}, corrected: true});
        });
    });

    describe('give-test-mode', () => {
        it('reads 1 and 0', () => {
            expect(parse('give-test-mode=1')).toMatchObject({query: {testMode: true}, corrected: false});
            expect(parse('give-test-mode=0')).toMatchObject({query: {testMode: false}, corrected: false});
        });

        it.each(['yes', 'true', ''])('drops %p so the site mode applies', (value) => {
            const {query, corrected} = parse(`give-test-mode=${value}`);

            expect(query.testMode).toBeUndefined();
            expect(corrected).toBe(true);
        });

        it('is not read on a screen without the toggle', () => {
            expect(parse('give-test-mode=1', forms)).toEqual({query: {filters: []}, corrected: false, legacyKeys: []});
        });
    });

    describe('legacy bare names', () => {
        it.each([
            ['donor=123', 'donor', {field: 'filter-donor', operator: 'is', value: '123'}, donations],
            ['campaignId=4', 'campaignId', {field: 'filter-campaignId', operator: 'is', value: 4}, donations],
            ['formId=9', 'formId', {field: 'filter-formId', operator: 'is', value: 9}, donations],
            ['campaign=none', 'campaign', {field: 'filter-campaign', operator: 'is', value: 'none'}, forms],
            ['status=trash', 'status', {field: 'filter-status', operator: 'is', value: 'trash'}, forms],
        ])('reads %p and marks it corrected', (search, legacyKey, filter, context) => {
            expect(parse(search, context)).toEqual({
                query: {filters: [filter]},
                corrected: true,
                legacyKeys: [legacyKey],
            });
        });

        it('applies the same rules to a bare name', () => {
            expect(parse('donor=jane%40example.com')).toEqual({
                query: {filters: []},
                corrected: true,
                legacyKeys: ['donor'],
            });
        });

        it('lets the prefixed name win', () => {
            expect(parse('donor=1&give-donor=2')).toEqual({
                query: {filters: [{field: 'filter-donor', operator: 'is', value: '2'}]},
                corrected: true,
                legacyKeys: ['donor'],
            });
        });

        it('does not read bare names of filterby groups, sort or page', () => {
            expect(parse('status=failed&paged=2&orderby=amount&order=asc')).toEqual({
                query: {filters: []},
                corrected: false,
                legacyKeys: [],
            });
        });
    });

    it('never reads a bare search, and marks it for removal', () => {
        expect(parse('search=smith')).toEqual({query: {filters: []}, corrected: true, legacyKeys: ['search']});
    });

    it('drops markup in any value', () => {
        expect(parse(`give-donor=${encodeURIComponent('<script>alert(1)</script>')}`)).toMatchObject({
            query: {filters: []},
            corrected: true,
        });
    });

    it('leaves parameters of other screens alone', () => {
        expect(parse('give-other=1&tab=forms&id=3')).toEqual({query: {filters: []}, corrected: false, legacyKeys: []});
    });
});

describe('writeViewQuery', () => {
    const owned = viewQueryKeys(donations);

    it('keeps other parameters exactly as they are, in their order', () => {
        expect(
            writeViewQuery('?post_type=give_forms&page=give-payment-history&x=a%20b', {'give-paged': '2'}, {owned})
        ).toBe('?post_type=give_forms&page=give-payment-history&x=a%20b&give-paged=2');
    });

    it('replaces the screen parameters and removes the ones the view no longer has', () => {
        expect(
            writeViewQuery('?page=p&give-paged=3&give-status=failed&give-other=1', {'give-status': 'pending'}, {owned})
        ).toBe('?page=p&give-other=1&give-status=pending');
    });

    it('removes the legacy names that were read', () => {
        expect(
            writeViewQuery(
                '?page=p&donor=123&search=smith',
                {'give-donor': '123'},
                {owned, legacyKeys: ['donor', 'search']}
            )
        ).toBe('?page=p&give-donor=123');
    });

    it('joins list values with plain commas', () => {
        expect(writeViewQuery('', {'give-status': 'failed,pending'}, {owned})).toBe('?give-status=failed,pending');
    });

    it('gives an empty string when no parameter is left', () => {
        expect(writeViewQuery('?give-paged=2', {}, {owned})).toBe('');
    });

    it('drops filter values from the end until the address fits', () => {
        const ids = Array.from({length: 40}, (_, index) => `value-${index}-${'x'.repeat(40)}`);
        const parameters = {
            'give-donor': '123',
            'give-status': ids.join(','),
            'give-orderby': 'amount',
            'give-order': 'asc',
            'give-paged': '2',
        };
        const base = 'https://example.org/wp-admin/admin.php';
        const search = writeViewQuery('?page=give-payment-history', parameters, {owned, addressLength: base.length});
        const result = new URLSearchParams(search);

        expect(base.length + search.length).toBeLessThan(MAX_ADDRESS_LENGTH);
        expect(result.get('give-orderby')).toBe('amount');
        expect(result.get('give-paged')).toBe('2');
        expect(result.get('give-donor')).toBe('123');
        expect(result.get('give-status')).toBe(ids.slice(0, result.get('give-status').split(',').length).join(','));
        expect(result.get('page')).toBe('give-payment-history');
    });

    it('removes a filter parameter whose values are all dropped', () => {
        const search = writeViewQuery(
            '',
            {'give-donor': '123', 'give-status': 'failed', 'give-paged': '2'},
            {owned, addressLength: MAX_ADDRESS_LENGTH - 30}
        );

        expect(search).toBe('?give-donor=123&give-paged=2');
    });
});
