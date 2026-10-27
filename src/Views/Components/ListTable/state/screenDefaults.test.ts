import type {Column} from '../ListTablePage/types';
import {resolveScreenDefaults, snapPerPage} from './screenDefaults';
import type {ListTableScreen} from './types';

const columns: Column[] = [
    {id: 'id', label: 'ID', sortable: true},
    {id: 'donor', label: 'Donor', sortable: true},
    {id: 'amount', label: 'Amount', sortable: true},
    {id: 'paymentType', label: 'Payment Type', sortable: false},
    {id: 'gateway', label: 'Gateway', sortable: false, visible: false},
    {id: 'date', label: 'Date', sortable: true},
];

const screen: ListTableScreen = {key: 'donations'};

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
});

describe('resolveScreenDefaults', () => {
    it('keeps the server order and visibility, without the title field', () => {
        const defaults = resolveScreenDefaults(screen, columns, 'donor');

        expect(defaults.fields).toEqual(['id', 'amount', 'paymentType', 'date']);
        expect(defaults.fieldOrder).toEqual(['id', 'amount', 'paymentType', 'gateway', 'date']);
        expect(defaults.titleField).toBe('donor');
    });

    it('uses the shared defaults: 30 per page, balanced, newest first, no filters', () => {
        const defaults = resolveScreenDefaults(screen, columns, 'donor');

        expect(defaults.perPage).toBe(30);
        expect(defaults.density).toBe('balanced');
        expect(defaults.sort).toEqual({field: 'id', direction: 'desc'});
        expect(defaults.filters).toEqual([]);
    });

    it('hides the hiddenFields and shows the visibleFields on top of the schema', () => {
        const defaults = resolveScreenDefaults(
            {key: 'donations', defaults: {hiddenFields: ['paymentType'], visibleFields: ['gateway']}},
            columns,
            'donor'
        );

        expect(defaults.fields).toEqual(['id', 'amount', 'gateway', 'date']);
    });

    it('ignores override ids that are not columns', () => {
        const defaults = resolveScreenDefaults(
            {key: 'donations', defaults: {visibleFields: ['unknown'], hiddenFields: ['other']}},
            columns,
            'donor'
        );

        expect(defaults.fields).toEqual(['id', 'amount', 'paymentType', 'date']);
    });

    it('locks the title field', () => {
        expect(resolveScreenDefaults(screen, columns, 'donor').lockedFields).toEqual(['donor']);
    });

    it('locks the id column when there is no title field, and keeps it visible', () => {
        const defaults = resolveScreenDefaults(
            {key: 'subscription-donations', defaults: {hiddenFields: ['id']}},
            columns,
            undefined
        );

        expect(defaults.lockedFields).toEqual(['id']);
        expect(defaults.fields[0]).toBe('id');
        expect(defaults.fieldOrder).toContain('donor');
    });

    it('locks the legacy actions field and pins it last', () => {
        const defaults = resolveScreenDefaults(screen, columns, 'donor', {pinnedField: 'givewp-row-actions'});

        expect(defaults.fields[defaults.fields.length - 1]).toBe('givewp-row-actions');
        expect(defaults.fieldOrder[defaults.fieldOrder.length - 1]).toBe('givewp-row-actions');
        expect(defaults.lockedFields).toEqual(['donor', 'givewp-row-actions']);
        expect(defaults.pinnedLast).toBe('givewp-row-actions');
    });

    it('snaps the screen page size to an offered size', () => {
        expect(resolveScreenDefaults({key: 'donors', defaults: {perPage: 40}}, columns, 'donor').perPage).toBe(30);
        expect(resolveScreenDefaults({key: 'donors', defaults: {perPage: 50}}, columns, 'donor').perPage).toBe(50);
    });

    it('uses the page size prop when the screen sets none', () => {
        expect(resolveScreenDefaults(screen, columns, 'donor', {perPage: 20}).perPage).toBe(20);
        expect(
            resolveScreenDefaults({key: 'donors', defaults: {perPage: 50}}, columns, 'donor', {perPage: 20}).perPage
        ).toBe(50);
    });

    it('uses the screen density', () => {
        expect(resolveScreenDefaults({key: 'donors', defaults: {density: 'compact'}}, columns, 'donor').density).toBe(
            'compact'
        );
    });

    it('works without a screen config', () => {
        expect(resolveScreenDefaults(undefined, columns, 'donor').fields).toEqual(['id', 'amount', 'paymentType', 'date']);
    });

    it('carries the default filters it is given', () => {
        const filters = [{field: 'filter-testMode', operator: 'is' as const, value: true}];

        expect(resolveScreenDefaults(screen, columns, 'donor', {filters}).filters).toEqual(filters);
    });
});
