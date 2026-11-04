import type {Filter} from '@wordpress/dataviews';
import {filterFieldId, isApplied} from '../filters/viewFilterValue';
import type {StatConfig} from '../ListTablePage/types';

/**
 * The filter a stat tile stands for. With no `value`, the tile is the "Total" of its group: it
 * shows every row, so it clears the group's filter.
 *
 * @since TBD
 */
export type StatTileFilter = {filterId: string; value?: string};

/**
 * The filter a tile toggles, or undefined when it must stay a plain statistic: an upgrade tile, a
 * tile with no mapping, or a filter the screen does not declare (the add-on is off).
 *
 * @since TBD
 */
export function tileFilterOf(config: StatConfig, availableFilterIds: string[]): StatTileFilter | undefined {
    if (config.upgrade || !config.filter || !availableFilterIds.includes(config.filter.filterId)) {
        return undefined;
    }

    return config.filter;
}

/**
 * Whether the view's filters say this tile is on. The view is the only source: a value set from the
 * toolbar presses the matching tile too.
 *
 * @since TBD
 */
export function isTilePressed(filter: StatTileFilter, filters: Filter[]): boolean {
    const current = filters.find((entry) => entry.field === filterFieldId(filter.filterId))?.value;

    if (filter.value === undefined) {
        return !isApplied(current);
    }

    return current === filter.value;
}

/**
 * The filters after a click on the tile. Total, or the tile that is already on, clears the group;
 * another tile replaces the group's value. Other filters are untouched.
 *
 * @since TBD
 */
export function toggleTileFilter(filters: Filter[], filter: StatTileFilter): Filter[] {
    const field = filterFieldId(filter.filterId);
    const others = filters.filter((entry) => entry.field !== field);

    if (filter.value === undefined || isTilePressed(filter, filters)) {
        return others;
    }

    return [...others, {field, operator: 'is', value: filter.value}];
}
