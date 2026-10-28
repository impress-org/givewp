import type {Field, View} from '@wordpress/dataviews';
import {keepPinnedLast, lockFields} from './fieldRules';
import type {ScreenDefaults} from './types';

const defaults: ScreenDefaults = {
    fields: ['amount', 'status', 'actions'],
    fieldOrder: ['donor', 'amount', 'status', 'actions'],
    density: 'balanced',
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    titleField: 'donor',
    lockedFields: ['donor', 'actions'],
    pinnedLast: 'actions',
    filters: [],
};

const view = (fields: string[]): View => ({type: 'table', fields});

describe('lockFields', () => {
    it('makes the title field and the row actions not hideable and leaves the rest alone', () => {
        const fields: Field<object>[] = [{id: 'donor'}, {id: 'amount'}, {id: 'actions'}];

        expect(lockFields(fields, defaults)).toEqual([
            {id: 'donor', enableHiding: false},
            {id: 'amount'},
            {id: 'actions', enableHiding: false},
        ]);
    });
});

describe('keepPinnedLast', () => {
    it('moves the pinned field back to the end after a move', () => {
        expect(keepPinnedLast(view(['amount', 'actions', 'status']), defaults).fields).toEqual([
            'amount',
            'status',
            'actions',
        ]);
    });

    it('returns the same view when the pinned field is already last or not shown', () => {
        const last = view(['amount', 'status', 'actions']);
        const absent = view(['amount', 'status']);

        expect(keepPinnedLast(last, defaults)).toBe(last);
        expect(keepPinnedLast(absent, defaults)).toBe(absent);
    });

    it('changes nothing on a screen without a pinned field', () => {
        const moved = view(['actions', 'amount']);

        expect(keepPinnedLast(moved, {...defaults, pinnedLast: undefined})).toBe(moved);
    });
});
