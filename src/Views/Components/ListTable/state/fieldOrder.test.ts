import {insertAtDefaultPosition, withPinnedLast} from './fieldOrder';

const fieldOrder = ['id', 'amount', 'status', 'date', 'actions'];

describe('insertAtDefaultPosition', () => {
    it('adds the field before the first field that comes after it in the default order', () => {
        expect(insertAtDefaultPosition(['id', 'date'], 'amount', fieldOrder)).toEqual(['id', 'amount', 'date']);
    });

    it('skips fields the default order does not know', () => {
        expect(insertAtDefaultPosition(['id', 'custom', 'date'], 'status', fieldOrder)).toEqual([
            'id',
            'custom',
            'status',
            'date',
        ]);
    });

    it('puts the field last when nothing comes after it', () => {
        expect(insertAtDefaultPosition(['id', 'amount'], 'date', fieldOrder)).toEqual(['id', 'amount', 'date']);
    });

    it('puts an unknown field last', () => {
        expect(insertAtDefaultPosition(['date', 'id'], 'custom', fieldOrder)).toEqual(['date', 'id', 'custom']);
    });
});

describe('withPinnedLast', () => {
    it('moves the pinned field last', () => {
        expect(withPinnedLast(['actions', 'id', 'date'], 'actions')).toEqual(['id', 'date', 'actions']);
    });

    it('returns the same list when there is nothing to move', () => {
        const fields = ['id', 'actions'];

        expect(withPinnedLast(fields, 'actions')).toBe(fields);
        expect(withPinnedLast(fields, undefined)).toBe(fields);
        expect(withPinnedLast(['id'], 'actions')).toEqual(['id']);
    });
});
