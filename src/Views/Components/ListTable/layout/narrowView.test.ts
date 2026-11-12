import type {View} from '@wordpress/dataviews';
import type {Column} from '../ListTablePage/types';
import {fromNarrowView, resolveFieldRoles, toNarrowView} from './narrowView';

const columns: Column[] = [
    {id: 'id', label: 'ID', sortable: true, kind: 'text'},
    {id: 'donor', label: 'Donor', sortable: false, kind: 'text'},
    {id: 'amount', label: 'Amount', sortable: true, kind: 'money'},
    {id: 'fee', label: 'Fee', sortable: false, kind: 'money'},
    {id: 'createdAt', label: 'Date', sortable: true, kind: 'date'},
    {id: 'status', label: 'Status', sortable: false, kind: 'status'},
    {id: 'form', label: 'Form', sortable: false, kind: 'text'},
];

const wide: View = {
    type: 'table',
    page: 1,
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    filters: [],
    search: '',
    titleField: 'donor',
    showTitle: false,
    fields: ['id', 'donor', 'amount', 'form'],
    layout: {density: 'compact'},
};

const roles = {primary: 'donor', description: 'amount', secondary: ['status', 'createdAt']};
const narrow = toNarrowView(wide, roles, true);

describe('resolveFieldRoles', () => {
    it('drops roles for columns the list does not have', () => {
        expect(
            resolveFieldRoles({primary: 'title', description: 'goal', secondary: ['status', 'formCampaign']}, [
                {id: 'title', label: 'Title', sortable: true},
                {id: 'status', label: 'Status', sortable: false},
            ])
        ).toEqual({primary: 'title', description: undefined, secondary: ['status']});
    });

    it('uses the title field when the primary field has no column', () => {
        expect(resolveFieldRoles({primary: 'missing', secondary: []}, columns, 'donor').primary).toBe('donor');
    });

    it('falls back to the column kinds without roles', () => {
        expect(resolveFieldRoles(undefined, columns, 'donor')).toEqual({
            primary: 'donor',
            description: 'amount',
            secondary: ['status', 'createdAt'],
        });
    });

    it('never repeats the primary or description field among the secondary fields', () => {
        expect(
            resolveFieldRoles(
                {primary: 'donor', description: 'amount', secondary: ['donor', 'amount', 'form']},
                columns
            )
        ).toEqual({primary: 'donor', description: 'amount', secondary: ['form']});
    });
});

describe('toNarrowView', () => {
    it('shows the records as cards with the field roles', () => {
        expect(narrow).toMatchObject({
            type: 'grid',
            titleField: 'donor',
            descriptionField: 'amount',
            mediaField: undefined,
            showMedia: false,
            showTitle: true,
            page: 1,
            sort: wide.sort,
        });
    });

    it('puts the ID in the badge', () => {
        expect(narrow.fields).toEqual(['status', 'createdAt', 'id']);
        expect((narrow as {layout: {badgeFields: string[]}}).layout.badgeFields).toEqual(['id']);
    });

    it('has no badge without an ID column', () => {
        const withoutId = toNarrowView(wide, roles, false);

        expect(withoutId.fields).toEqual(['status', 'createdAt']);
        expect((withoutId as {layout: {badgeFields: string[]}}).layout.badgeFields).toEqual([]);
    });

    it('keeps the trailing fields, such as the old row actions, last', () => {
        expect(toNarrowView(wide, roles, true, ['givewp-row-actions']).fields).toEqual([
            'status',
            'createdAt',
            'givewp-row-actions',
            'id',
        ]);
    });
});

describe('fromNarrowView', () => {
    it('fromNarrowView keeps the wide layout and takes the query', () => {
        const filters = [{field: 'status', operator: 'isAny' as const, value: ['failed']}];
        const back = fromNarrowView(
            {
                ...narrow,
                page: 3,
                perPage: 50,
                sort: {field: 'amount', direction: 'asc'},
                filters,
                search: 'smith',
                fields: ['form'],
            },
            wide
        );

        expect(back.type).toBe('table');
        expect(back.fields).toEqual(wide.fields);
        expect(back.layout).toEqual(wide.layout);
        expect(back.titleField).toBe('donor');
        expect(back.showTitle).toBe(false);
        expect(back.page).toBe(3);
        expect(back.perPage).toBe(50);
        expect(back.sort).toEqual({field: 'amount', direction: 'asc'});
        expect(back.filters).toEqual(filters);
        expect(back.search).toBe('smith');
    });
});
