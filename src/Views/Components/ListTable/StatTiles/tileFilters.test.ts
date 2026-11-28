import type {Filter} from '@wordpress/dataviews';
import type {StatConfig} from '../ListTablePage/types';
import {isTilePressed, tileFilterOf, toggleTileFilter} from './tileFilters';

const donationType = (value: string): Filter => ({field: 'filter-donationType', operator: 'is', value});
const total = {filterId: 'donationType'};
const oneTime = {filterId: 'donationType', value: 'one-time'};
const recurring = {filterId: 'donationType', value: 'recurring'};

describe('tileFilters', () => {
    it('sets the value, replacing another tile of the group', () => {
        expect(toggleTileFilter([donationType('one-time')], recurring)).toEqual([donationType('recurring')]);
        expect(toggleTileFilter([], oneTime)).toEqual([donationType('one-time')]);
    });

    it('clears when the pressed tile is activated again', () => {
        expect(toggleTileFilter([donationType('one-time')], oneTime)).toEqual([]);
    });

    it('Total clears the group and is pressed when the group is empty', () => {
        expect(toggleTileFilter([donationType('recurring')], total)).toEqual([]);
        expect(isTilePressed(total, [])).toBe(true);
        expect(isTilePressed(total, [{field: 'filter-donationType', operator: 'is', value: undefined}])).toBe(true);
        expect(isTilePressed(total, [donationType('recurring')])).toBe(false);
    });

    it('the pressed tile follows a value set from the toolbar', () => {
        const filters = [donationType('recurring')];

        expect(isTilePressed(oneTime, filters)).toBe(false);
        expect(isTilePressed(recurring, filters)).toBe(true);
    });

    it('an upgrade tile or a filter the screen does not declare never filters', () => {
        const plain: StatConfig = {label: 'One-Time', filter: oneTime};
        const upgrade: StatConfig = {...plain, upgrade: {href: 'https://example.test', tooltip: 'Upgrade'}};

        expect(tileFilterOf(plain, ['donationType'])).toEqual(oneTime);
        expect(tileFilterOf(plain, ['status'])).toBeUndefined();
        expect(tileFilterOf(upgrade, ['donationType'])).toBeUndefined();
        expect(tileFilterOf({label: 'Subscriptions'}, ['donationType'])).toBeUndefined();
    });

    it('keeps other filters untouched', () => {
        const status: Filter = {field: 'filter-status', operator: 'isAny', value: ['publish']};

        expect(toggleTileFilter([status, donationType('one-time')], recurring)).toEqual([
            status,
            donationType('recurring'),
        ]);
        expect(toggleTileFilter([status, donationType('one-time')], total)).toEqual([status]);
    });
});
