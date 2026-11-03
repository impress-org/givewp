import {assembleView} from './assembleView';
import type {ScreenDefaults} from './types';

const defaults: ScreenDefaults = {
    fields: ['id', 'amount', 'date'],
    fieldOrder: ['id', 'amount', 'gateway', 'date'],
    density: 'balanced',
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    titleField: 'donor',
    lockedFields: ['donor'],
    filters: [{field: 'filter-status', operator: 'isAny', value: ['publish']}],
};

describe('assembleView', () => {
    it('starts from the defaults', () => {
        expect(assembleView({defaults})).toEqual({
            type: 'table',
            page: 1,
            perPage: 30,
            sort: {field: 'id', direction: 'desc'},
            search: '',
            filters: [{field: 'filter-status', operator: 'isAny', value: ['publish']}],
            fields: ['id', 'amount', 'date'],
            titleField: 'donor',
            layout: {density: 'balanced'},
        });
    });

    it('applies the stored layout over the defaults', () => {
        const view = assembleView({defaults, layout: {fields: ['date', 'id'], density: 'compact', perPage: 50}});

        expect(view.fields).toEqual(['date', 'id']);
        expect(view.layout).toEqual({density: 'compact'});
        expect(view.perPage).toBe(50);
    });

    it('lets the URL win over the stored page size', () => {
        const view = assembleView({
            defaults,
            layout: {fields: defaults.fields, density: 'balanced', perPage: 50},
            query: {perPage: 10, filters: []},
        });

        expect(view.perPage).toBe(10);
    });

    it('keeps the stored page size when the URL does not name one', () => {
        const view = assembleView({
            defaults,
            layout: {fields: defaults.fields, density: 'balanced', perPage: 50},
            query: {page: 2, filters: []},
        });

        expect(view.perPage).toBe(50);
        expect(view.page).toBe(2);
    });

    it('applies the URL page, sort and filters', () => {
        const view = assembleView({
            defaults,
            query: {
                page: 3,
                sort: {field: 'amount', direction: 'asc'},
                filters: [{field: 'filter-donor', operator: 'is', value: '12'}],
            },
        });

        expect(view.page).toBe(3);
        expect(view.sort).toEqual({field: 'amount', direction: 'asc'});
        expect(view.filters).toEqual([
            {field: 'filter-status', operator: 'isAny', value: ['publish']},
            {field: 'filter-donor', operator: 'is', value: '12'},
        ]);
    });

    it('lets a URL filter replace the default filter of the same field', () => {
        const view = assembleView({
            defaults,
            query: {filters: [{field: 'filter-status', operator: 'isAny', value: ['failed']}]},
        });

        expect(view.filters).toEqual([{field: 'filter-status', operator: 'isAny', value: ['failed']}]);
    });

    it('turns test mode on or off as the URL says, and keeps the default when it says nothing', () => {
        const testSite = {...defaults, filters: [{field: 'filter-testMode', operator: 'is' as const, value: true}]};

        expect(assembleView({defaults, query: {filters: [], testMode: true}}).filters).toContainEqual({
            field: 'filter-testMode',
            operator: 'is',
            value: true,
        });
        expect(assembleView({defaults: testSite, query: {filters: [], testMode: false}}).filters).toEqual([]);
        expect(assembleView({defaults: testSite, query: {filters: []}}).filters).toEqual(testSite.filters);
    });

    it('applies the tab search last', () => {
        expect(assembleView({defaults, query: {filters: []}, search: 'smith'}).search).toBe('smith');
    });

    it('does not change the defaults it was given', () => {
        const before = JSON.stringify(defaults);

        assembleView({
            defaults,
            query: {filters: [{field: 'filter-status', operator: 'isAny', value: ['failed']}], testMode: true},
        });

        expect(JSON.stringify(defaults)).toBe(before);
    });
});
