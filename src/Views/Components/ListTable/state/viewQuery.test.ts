import type {FilterConfig} from '../ListTablePage/types';
import {filterSettingsToDeclarations} from '../filters/legacyFilterSettings';
import type {BarFilterDeclaration, FilterDeclaration, StatusVocabularyEntry} from '../filters/types';
import {
    declarationUrlParameter,
    MAX_ADDRESS_LENGTH,
    parameterName,
    parseViewQuery,
    serializeViewQuery,
    viewQueryKeys,
    writeViewQuery,
} from './viewQuery';
import type {ListTableView, ScreenDefaults, ViewQueryContext} from './types';

const statuses: StatusVocabularyEntry[] = [
    {value: 'publish', label: 'Completed', filterable: true},
    {value: 'pending', label: 'Pending', filterable: true},
    {value: 'trash', label: 'Trash', filterable: true},
    {value: 'processing', label: 'Processing', filterable: false},
];

// The Donations catalog row (FR-009), the screen with every kind.
const declarations: FilterDeclaration[] = [
    {kind: 'search', id: 'search', label: 'Search donations', hint: 'Hint', parameter: 'search'},
    {
        kind: 'entity',
        id: 'campaign',
        label: 'Campaign',
        isPrimary: true,
        source: 'campaign',
        multiple: true,
        parameter: 'campaignId',
        allowNone: true,
        legacyUrlKey: 'campaignId',
    },
    {
        kind: 'options',
        id: 'status',
        label: 'Status',
        isPrimary: true,
        multiple: true,
        options: 'statuses',
        parameter: 'status',
        exclusiveValues: ['trash'],
    },
    {
        kind: 'entity',
        id: 'form',
        label: 'Form',
        source: 'form',
        multiple: true,
        parameter: 'formId',
        legacyUrlKey: 'formId',
    },
    {
        kind: 'entity',
        id: 'donor',
        label: 'Donor',
        source: 'donor',
        multiple: false,
        parameter: 'donor',
        legacyUrlKey: 'donor',
    },
    {kind: 'date', id: 'date', label: 'Donation date', parameters: {start: 'start', end: 'end'}},
    {
        kind: 'options',
        id: 'donationType',
        label: 'Donation type',
        multiple: false,
        options: [
            {value: 'one-time', label: 'One-time'},
            {value: 'recurring', label: 'Recurring'},
        ],
        parameter: 'donationType',
    },
];

const option = (value: string) => ({value, text: value});

// The old filter settings of an add-on screen, mapped by the deprecated adapter.
const legacySettings: FilterConfig[] = [
    {name: 'campaign', type: 'campaignselect', options: [option('none')]},
    {name: 'formId', type: 'formselect'},
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
    filters: declarations,
    statuses,
    testModeToggle: true,
    sortableFields: ['id', 'amount', 'date'],
    defaults,
    siteTestMode: false,
};

const legacy: ViewQueryContext = {
    ...donations,
    filters: filterSettingsToDeclarations(legacySettings, false).filters,
    testModeToggle: false,
};

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

const filter = (field: string, operator: 'is' | 'isAny', value: string | string[]) => ({
    field: `filter-${field}`,
    operator,
    value,
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
    it.each([
        ['donations', donations],
        ['old filter settings', legacy],
    ])('gives unique give- names on %s', (_, context) => {
        const keys = viewQueryKeys(context);

        expect(new Set(keys).size).toBe(keys.length);
        keys.forEach((key) => expect(key).toMatch(/^give-[a-z0-9]+(-[a-z0-9]+)*$/));
    });

    it('lists every parameter of the Donations screen, never the search', () => {
        expect(viewQueryKeys(donations)).toEqual([
            'give-campaign',
            'give-status',
            'give-form',
            'give-donor',
            'give-date',
            'give-donation-type',
            'give-test-mode',
            'give-orderby',
            'give-order',
            'give-paged',
            'give-per-page',
        ]);
    });

    it('has no test mode parameter on a screen without the toggle', () => {
        expect(viewQueryKeys(legacy)).not.toContain('give-test-mode');
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
                    filter('status', 'isAny', ['publish', 'pending']),
                    filter('campaign', 'isAny', ['4']),
                    filter('form', 'isAny', ['9']),
                    filter('donor', 'is', '123'),
                    filter('date', 'is', '30'),
                    filter('donationType', 'is', 'recurring'),
                    {field: 'filter-testMode', operator: 'is', value: true},
                ],
            }),
            donations
        );

        expect(Object.entries(parameters)).toEqual([
            ['give-campaign', '4'],
            ['give-status', 'publish,pending'],
            ['give-form', '9'],
            ['give-donor', '123'],
            ['give-date', '30'],
            ['give-donation-type', 'recurring'],
            ['give-test-mode', '1'],
            ['give-orderby', 'amount'],
            ['give-order', 'asc'],
            ['give-paged', '3'],
            ['give-per-page', '50'],
        ]);
    });

    it('writes an old select filter by its name', () => {
        expect(serializeViewQuery(view({filters: [filter('status', 'is', 'draft')]}), legacy)).toEqual({
            'give-status': 'draft',
        });
    });

    it('writes both sort parameters when only the direction differs', () => {
        expect(serializeViewQuery(view({sort: {field: 'id', direction: 'asc'}}), donations)).toEqual({
            'give-orderby': 'id',
            'give-order': 'asc',
        });
    });

    it('omits filters with no value', () => {
        const parameters = serializeViewQuery(
            view({filters: [filter('status', 'isAny', []), filter('donor', 'is', '')]}),
            donations
        );

        expect(parameters).toEqual({});
    });

    it('omits a filter equal to its default', () => {
        const withDefault = {
            ...donations,
            defaults: {...defaults, filters: [filter('status', 'isAny', ['publish'])]},
        };

        expect(serializeViewQuery(view({filters: [filter('status', 'isAny', ['publish'])]}), withDefault)).toEqual({});
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
        expect(serializeViewQuery(view(), {...legacy, siteTestMode: true})).toEqual({});
    });

    it('never writes the search term', () => {
        expect(serializeViewQuery(view({search: 'smith'}), donations)).toEqual({});
    });

    it('never writes a value that is not an id or an enumerated value', () => {
        const parameters = serializeViewQuery(
            view({
                filters: [
                    filter('donor', 'is', 'jane@example.com'),
                    filter('status', 'isAny', ['pending', '<b>x</b>']),
                ],
            }),
            donations
        );

        expect(parameters).toEqual({'give-status': 'pending'});
    });

    it('ignores filters that are not this screen', () => {
        expect(serializeViewQuery(view({filters: [filter('other', 'is', '1')]}), donations)).toEqual({});
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
                filter('campaign', 'isAny', ['4']),
                filter('status', 'isAny', ['publish', 'pending']),
                filter('donor', 'is', '123'),
                filter('date', 'is', '30'),
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

    describe('old filter settings, mapped onto declarations', () => {
        it('reads one of the select options', () => {
            expect(parse('give-status=draft', legacy)).toMatchObject({
                query: {filters: [filter('status', 'is', 'draft')]},
                corrected: false,
            });
        });

        it.each(['any', 'archived'])('drops the "all" option and unknown values: %p', (value) => {
            expect(parse(`give-status=${value}`, legacy)).toMatchObject({query: {filters: []}, corrected: true});
        });

        it('reads a campaign id and "No campaign"', () => {
            expect(parse('give-campaign=4', legacy).query.filters).toEqual([filter('campaign', 'is', '4')]);
            expect(parse('give-campaign=none', legacy).query.filters).toEqual([filter('campaign', 'is', 'none')]);
        });

        it.each(['0', '-3', 'abc', '04', 'none'])('drops the form value %p', (value) => {
            expect(parse(`give-form-id=${value}`, legacy)).toMatchObject({query: {filters: []}, corrected: true});
        });
    });

    describe('an id filter', () => {
        it('reads an id', () => {
            expect(parse('give-donor=123').query.filters).toEqual([filter('donor', 'is', '123')]);
        });

        it.each(['jane%40example.com', 'a%20b', 'x'.repeat(65), '', 'abc'])('drops %p', (value) => {
            expect(parse(`give-donor=${value}`)).toMatchObject({query: {filters: []}, corrected: true});
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
            expect(parse('give-test-mode=1', legacy)).toEqual({query: {filters: []}, corrected: false, legacyKeys: []});
        });
    });

    describe('legacy bare names', () => {
        it.each([
            ['donor=123', 'donor', filter('donor', 'is', '123'), donations],
            ['campaignId=4', 'campaignId', filter('campaign', 'isAny', ['4']), donations],
            ['formId=9', 'formId', filter('form', 'isAny', ['9']), donations],
            ['campaign=none', 'campaign', filter('campaign', 'is', 'none'), legacy],
            ['status=trash', 'status', filter('status', 'is', 'trash'), legacy],
        ])('reads %p and marks it corrected', (search, legacyKey, expected, context) => {
            expect(parse(search, context)).toEqual({
                query: {filters: [expected]},
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
                query: {filters: [filter('donor', 'is', '2')]},
                corrected: true,
                legacyKeys: ['donor'],
            });
        });

        it('does not read bare names of filters without a legacy name, sort or page', () => {
            expect(parse('status=pending&paged=2&orderby=amount&order=asc')).toEqual({
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

describe('declared filters in the URL', () => {
    const declared = donations;
    const parseDeclared = (search: string) => parse(search, declared);

    it.each([
        ['campaign', filter('campaign', 'isAny', ['12', '15']), 'give-campaign', '12,15'],
        ['no campaign', filter('campaign', 'isAny', ['none']), 'give-campaign', 'none'],
        ['status', filter('status', 'isAny', ['publish', 'pending']), 'give-status', 'publish,pending'],
        ['form', filter('form', 'isAny', ['5']), 'give-form', '5'],
        ['donor', filter('donor', 'is', '123'), 'give-donor', '123'],
        ['date', filter('date', 'is', '30'), 'give-date', '30'],
        ['donation type', filter('donationType', 'is', 'recurring'), 'give-donation-type', 'recurring'],
    ])('writes and reads back a %s value', (_, applied, key, text) => {
        const written = serializeViewQuery(view({filters: [applied]}), declared);

        expect(written).toEqual({[key]: text});

        const parsed = parseDeclared(new URLSearchParams(written).toString());

        expect(parsed.query.filters).toEqual([applied]);
        expect(parsed.corrected).toBe(false);
    });

    it.each([
        '7',
        '30',
        '90',
        'this-month',
        'last-month',
        'this-year',
        '2026-09-01_2026-09-15',
        '2026-09-01_',
        '_2026-09-15',
    ])('reads give-date=%s', (token) => {
        expect(parseDeclared(`give-date=${token}`)).toMatchObject({
            query: {filters: [filter('date', 'is', token)]},
            corrected: false,
        });
    });

    it.each(['2026-02-30_', '2026-09-15_2026-09-01', 'yesterday'])('drops give-date=%s', (token) => {
        expect(parseDeclared(`give-date=${token}`)).toMatchObject({query: {filters: []}, corrected: true});
    });

    it('keeps Trash alone when it comes with other statuses', () => {
        expect(parseDeclared('give-status=trash,pending')).toMatchObject({
            query: {filters: [filter('status', 'isAny', ['trash'])]},
            corrected: true,
        });
    });

    it('drops a status the filter does not offer', () => {
        expect(parseDeclared('give-status=processing,pending')).toMatchObject({
            query: {filters: [filter('status', 'isAny', ['pending'])]},
            corrected: true,
        });
    });

    it('keeps the first 50 of more ids', () => {
        const ids = Array.from({length: 60}, (_, index) => String(index + 1));
        const parsed = parseDeclared(`give-form=${ids.join(',')}`);

        expect(parsed.query.filters).toEqual([filter('form', 'isAny', ids.slice(0, 50))]);
        expect(parsed.corrected).toBe(true);
    });

    it('reads the legacy donor and campaignId names and lists them for removal', () => {
        const parsed = parseDeclared('donor=123&campaignId=12');

        expect(parsed.query.filters).toEqual([filter('campaign', 'isAny', ['12']), filter('donor', 'is', '123')]);
        expect(parsed.legacyKeys).toEqual(['campaignId', 'donor']);
        expect(parsed.corrected).toBe(true);
    });

    it('writes nothing for an empty filter added from "Add filter"', () => {
        const empty = {field: 'filter-date', operator: 'is' as const, value: undefined};

        expect(serializeViewQuery(view({filters: [empty]}), declared)).toEqual({});
    });

    it('names one parameter per declared filter, never the search, and never the old names', () => {
        const keys = viewQueryKeys({...declared, testModeToggle: false});

        expect(keys).toEqual([
            'give-campaign',
            'give-status',
            'give-form',
            'give-donor',
            'give-date',
            'give-donation-type',
            'give-orderby',
            'give-order',
            'give-paged',
            'give-per-page',
        ]);
        expect(new Set(keys).size).toBe(keys.length);
    });

    it('gives a declaration the operator of its kind', () => {
        const [campaign, , , donor] = declarations.filter(
            (declaration): declaration is BarFilterDeclaration => declaration.kind !== 'search'
        );

        expect(declarationUrlParameter(campaign, statuses).read('12')).toEqual({
            filter: filter('campaign', 'isAny', ['12']),
            corrected: false,
        });
        expect(declarationUrlParameter(donor, statuses).read('9')).toEqual({
            filter: filter('donor', 'is', '9'),
            corrected: false,
        });
    });
});
