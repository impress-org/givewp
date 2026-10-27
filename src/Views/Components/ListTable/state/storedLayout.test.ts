import {applyStoredLayout, isDefaultLayout, toStoredLayout} from './storedLayout';
import type {ListTableView, ScreenDefaults} from './types';

const PINNED = 'givewp-row-actions';

const defaults: ScreenDefaults = {
    fields: ['id', 'amount', 'paymentType', 'date', PINNED],
    fieldOrder: ['id', 'amount', 'paymentType', 'gateway', 'date', PINNED],
    density: 'balanced',
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    titleField: 'donor',
    lockedFields: ['donor', PINNED],
    pinnedLast: PINNED,
    filters: [],
};

const viewOf = (fields: string[], extra: Partial<ListTableView> = {}): ListTableView => ({
    type: 'table',
    fields,
    perPage: 30,
    layout: {density: 'balanced'},
    ...extra,
});

describe('toStoredLayout', () => {
    it('stores nothing but the version when the layout equals the defaults', () => {
        expect(toStoredLayout(viewOf(defaults.fields), defaults)).toEqual({version: 1});
    });

    it('stores only the columns whose visibility differs from the default', () => {
        const view = viewOf(['id', 'amount', 'gateway', 'date', PINNED]);

        expect(toStoredLayout(view, defaults)).toEqual({version: 1, visibility: {paymentType: false, gateway: true}});
    });

    it('treats a column hidden and shown again as never chosen', () => {
        expect(toStoredLayout(viewOf([...defaults.fields]), defaults).visibility).toBeUndefined();
    });

    it('does not store the order when only visibility changed', () => {
        expect(toStoredLayout(viewOf(['id', 'date', PINNED]), defaults).order).toBeUndefined();
    });

    it('stores the full visible order, without the pinned field, when it differs', () => {
        const view = viewOf(['date', 'id', 'amount', 'paymentType', PINNED]);

        expect(toStoredLayout(view, defaults)).toEqual({version: 1, order: ['date', 'id', 'amount', 'paymentType']});
    });

    it('stores the density and page size only when they differ', () => {
        expect(toStoredLayout(viewOf(defaults.fields, {perPage: 50, layout: {density: 'compact'}}), defaults)).toEqual({
            version: 1,
            density: 'compact',
            perPage: 50,
        });
    });

    it('never stores a locked field as hidden', () => {
        expect(toStoredLayout(viewOf(['id', 'amount', 'paymentType', 'date']), defaults).visibility).toBeUndefined();
    });
});

describe('applyStoredLayout', () => {
    const defaultLayout = {fields: defaults.fields, density: 'balanced', perPage: 30};

    it('gives the defaults when nothing is stored', () => {
        expect(applyStoredLayout(defaults, undefined)).toEqual(defaultLayout);
        expect(applyStoredLayout(defaults, null)).toEqual(defaultLayout);
    });

    it.each([[{}], [{version: 2, density: 'compact'}], [{version: '1', density: 'compact'}], ['text'], [[]]])(
        'ignores a record with another version or shape: %p',
        (stored) => {
            expect(applyStoredLayout(defaults, stored)).toEqual(defaultLayout);
        }
    );

    it('applies stored visibility and keeps the default for columns it does not name', () => {
        expect(applyStoredLayout(defaults, {version: 1, visibility: {paymentType: false, gateway: true}}).fields).toEqual(
            ['id', 'amount', 'gateway', 'date', PINNED]
        );
    });

    it('ignores unknown ids and values that are not booleans', () => {
        const stored = {version: 1, visibility: {unknown: true, amount: 'no', date: 0}, order: ['ghost', 'date']};

        expect(applyStoredLayout(defaults, stored).fields).toEqual(['id', 'amount', 'paymentType', 'date', PINNED]);
    });

    it('ignores a visibility that is not an object and an order that is not an array', () => {
        expect(applyStoredLayout(defaults, {version: 1, visibility: ['amount'], order: 'date'}).fields).toEqual(
            defaults.fields
        );
    });

    it('keeps locked fields visible', () => {
        expect(applyStoredLayout(defaults, {version: 1, visibility: {[PINNED]: false}}).fields).toContain(PINNED);

        const noTitle = {...defaults, titleField: undefined, lockedFields: ['id', PINNED]};
        expect(applyStoredLayout(noTitle, {version: 1, visibility: {id: false}}).fields).toContain('id');
    });

    it('applies the stored order and inserts missing visible columns at their default position', () => {
        const stored = {version: 1, order: ['date', 'id']};

        expect(applyStoredLayout(defaults, stored).fields).toEqual(['date', 'id', 'amount', 'paymentType', PINNED]);
    });

    it('places a new column after the nearest column before it in the default order', () => {
        const withNew = {
            ...defaults,
            fields: ['id', 'amount', 'newColumn', 'paymentType', 'date', PINNED],
            fieldOrder: ['id', 'amount', 'newColumn', 'paymentType', 'gateway', 'date', PINNED],
        };

        expect(applyStoredLayout(withNew, {version: 1, order: ['paymentType', 'amount', 'date', 'id']}).fields).toEqual(
            ['paymentType', 'amount', 'newColumn', 'date', 'id', PINNED]
        );
    });

    it('puts a new first column at the start', () => {
        const withNew = {
            ...defaults,
            fields: ['first', ...defaults.fields],
            fieldOrder: ['first', ...defaults.fieldOrder],
        };

        expect(applyStoredLayout(withNew, {version: 1, order: ['date', 'id']}).fields[0]).toBe('first');
    });

    it('drops hidden and duplicate ids from the stored order', () => {
        const stored = {version: 1, visibility: {amount: false}, order: ['date', 'amount', 'date', 'id']};

        expect(applyStoredLayout(defaults, stored).fields).toEqual(['date', 'id', 'paymentType', PINNED]);
    });

    it('moves the pinned field back to the end', () => {
        expect(applyStoredLayout(defaults, {version: 1, order: [PINNED, 'date', 'id']}).fields).toEqual([
            'date',
            'id',
            'amount',
            'paymentType',
            PINNED,
        ]);
    });

    it('applies a valid density and falls back on an invalid one', () => {
        expect(applyStoredLayout(defaults, {version: 1, density: 'comfortable'}).density).toBe('comfortable');
        expect(applyStoredLayout(defaults, {version: 1, density: 'huge'}).density).toBe('balanced');
    });

    it('snaps the page size and falls back when it is not a number', () => {
        expect(applyStoredLayout(defaults, {version: 1, perPage: 50}).perPage).toBe(50);
        expect(applyStoredLayout(defaults, {version: 1, perPage: 45}).perPage).toBe(50);
        expect(applyStoredLayout(defaults, {version: 1, perPage: '50'}).perPage).toBe(30);
    });

    it('round-trips a stored layout', () => {
        const view = viewOf(['date', 'id', 'gateway', PINNED], {perPage: 100, layout: {density: 'compact'}});
        const layout = applyStoredLayout(defaults, toStoredLayout(view, defaults));

        expect(layout).toEqual({fields: view.fields, density: 'compact', perPage: 100});
    });
});

describe('isDefaultLayout', () => {
    it('is true for the defaults', () => {
        expect(isDefaultLayout(viewOf(defaults.fields), defaults)).toBe(true);
    });

    it('is true when the view has no density set', () => {
        expect(isDefaultLayout(viewOf(defaults.fields, {layout: {}}), defaults)).toBe(true);
    });

    it.each([
        ['a hidden column', viewOf(['id', 'amount', 'date', PINNED])],
        ['another order', viewOf(['amount', 'id', 'paymentType', 'date', PINNED])],
        ['another density', viewOf(defaults.fields, {layout: {density: 'compact'}})],
        ['another page size', viewOf(defaults.fields, {perPage: 10})],
    ])('is false with %s', (_, view) => {
        expect(isDefaultLayout(view, defaults)).toBe(false);
    });
});
