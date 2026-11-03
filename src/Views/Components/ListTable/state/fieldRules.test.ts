import type {Field, View} from '@wordpress/dataviews';
import {keepPinnedLast, lockFields, placeShownFields} from './fieldRules';
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

describe('placeShownFields', () => {
    const wide: ScreenDefaults = {
        ...defaults,
        fields: ['amount', 'form', 'status', 'actions'],
        fieldOrder: ['donor', 'amount', 'form', 'status', 'actions'],
    };

    it('puts a shown column back at its default position instead of the end', () => {
        const next = view(['amount', 'status', 'actions', 'form']);

        expect(placeShownFields(next, view(['amount', 'status', 'actions']), wide).fields).toEqual([
            'amount',
            'form',
            'status',
            'actions',
        ]);
    });

    it('keeps the order the admin gave the other columns and puts the shown one before the first column that comes after it by default', () => {
        const next = view(['status', 'amount', 'actions', 'form']);

        expect(placeShownFields(next, view(['status', 'amount', 'actions']), wide).fields).toEqual([
            'form',
            'status',
            'amount',
            'actions',
        ]);
    });

    it('puts a column the screen does not know about last', () => {
        const next = view(['amount', 'actions', 'extra']);

        expect(placeShownFields(next, view(['amount', 'actions']), wide).fields).toEqual([
            'amount',
            'actions',
            'extra',
        ]);
    });

    it('returns the same view when no column was shown', () => {
        const next = view(['status', 'amount', 'actions']);

        expect(placeShownFields(next, view(['amount', 'status', 'actions']), wide)).toBe(next);
        expect(placeShownFields(view(['amount']), view(['amount', 'status']), wide).fields).toEqual(['amount']);
    });
});
