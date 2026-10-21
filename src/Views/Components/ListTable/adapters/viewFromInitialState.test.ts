import type {FilterConfig} from '../ListTablePage/types';
import {scopeFromFilterSettings, viewFromInitialState} from './viewFromInitialState';
import {filterFieldId} from './viewToParameters';

const filters: FilterConfig[] = [
    {name: 'status', type: 'select', options: [{value: 'any', text: 'All'}, {value: 'trash', text: 'Trash'}]},
    {name: 'search', type: 'search'},
    {name: 'campaignId', type: 'campaignselect'},
    {name: 'donor', type: 'hidden'},
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: [
            {id: 'status', apiParam: 'status', name: 'Status', type: 'checkbox', defaultValue: 'publish', options: []},
            {id: 'period', apiParam: 'start', name: 'Period', type: 'radio', options: []},
        ],
    },
];

const defaults = {fields: ['id', 'amount'], titleField: 'donor'};

describe('viewFromInitialState', () => {
    it('starts on page one, newest first, with the screen page size and columns', () => {
        expect(viewFromInitialState(filters, {...defaults, perPage: 20}, new URLSearchParams())).toEqual({
            type: 'table',
            page: 1,
            perPage: 20,
            sort: {field: 'id', direction: 'desc'},
            search: '',
            filters: [{field: filterFieldId('status'), operator: 'isAny', value: ['publish']}],
            fields: ['id', 'amount'],
            titleField: 'donor',
            layout: {},
        });
    });

    it('uses 30 rows per page when the screen sets none', () => {
        expect(viewFromInitialState([], defaults, new URLSearchParams()).perPage).toBe(30);
    });

    it('reads the search term and the link-driven filters from the URL', () => {
        const view = viewFromInitialState(
            filters,
            defaults,
            new URLSearchParams('search=jane&donor=12&campaignId=4&status=trash')
        );

        expect(view.search).toBe('jane');
        expect(view.filters).toEqual(
            expect.arrayContaining([
                {field: filterFieldId('status'), operator: 'is', value: 'trash'},
                {field: filterFieldId('campaignId'), operator: 'is', value: 4},
                {field: filterFieldId('donor'), operator: 'is', value: '12'},
            ])
        );
    });

    it('does not apply a select filter set to its default first option', () => {
        const view = viewFromInitialState(filters, defaults, new URLSearchParams('status=any'));

        expect(view.filters.some((filter) => filter.field === filterFieldId('status') && filter.operator === 'is')).toBe(
            false
        );
    });

    it('leaves hidden filters out of the view in content mode', () => {
        const view = viewFromInitialState(filters, {...defaults, contentMode: true}, new URLSearchParams('donor=12'));

        expect(view.filters.some((filter) => filter.field === filterFieldId('donor'))).toBe(false);
    });
});

describe('scopeFromFilterSettings', () => {
    const scoped: FilterConfig[] = [
        {name: 'campaignId', type: 'hidden', options: [{value: '7', text: ''}]},
        {name: 'search', type: 'search'},
    ];

    it('sends hidden filters as table scope in content mode only', () => {
        expect(scopeFromFilterSettings(scoped, true, new URLSearchParams())).toEqual({campaignId: '7'});
        expect(scopeFromFilterSettings(scoped, false, new URLSearchParams())).toEqual({});
    });

    it('prefers the URL value', () => {
        expect(scopeFromFilterSettings(scoped, true, new URLSearchParams('campaignId=9'))).toEqual({campaignId: '9'});
    });
});
