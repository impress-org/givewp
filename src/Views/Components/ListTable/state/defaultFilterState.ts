import type {Filter, View} from '@wordpress/dataviews';
import {isApplied} from '../adapters/viewToParameters';

/**
 * One filter as plain text, so two filter lists can be compared whatever their order. A list value
 * is sorted: `['a', 'b']` and `['b', 'a']` select the same rows.
 */
function filterKey(filter: Filter): string {
    const value = Array.isArray(filter.value) ? [...filter.value].map(String).sort() : filter.value;

    return JSON.stringify([filter.field, filter.operator, value ?? null]);
}

const keysOf = (filters: Filter[]) =>
    filters
        .filter((filter) => !filter.isLocked)
        .map(filterKey)
        .sort();

/**
 * Whether the admin changed the search or the filters from the screen's defaults. A filter added with
 * no value yet counts as a change, as Reset removes it. Locked filters never count.
 *
 * @since TBD
 */
export function differsFromDefaultFilters(view: View, defaultFilters: Filter[]): boolean {
    if (isApplied(view.search)) {
        return true;
    }

    const current = keysOf(view.filters ?? []);
    const defaults = keysOf(defaultFilters);

    return current.length !== defaults.length || current.some((key, index) => key !== defaults[index]);
}

/**
 * The view with the search cleared and the filters back to the screen's defaults, on page one. Locked
 * filters stay as they are; sort, page size and columns do not change.
 *
 * @since TBD
 */
export function withDefaultFilters<T extends View>(view: T, defaultFilters: Filter[]): T {
    return {
        ...view,
        page: 1,
        search: '',
        filters: [
            ...(view.filters ?? []).filter((filter) => filter.isLocked),
            ...defaultFilters.filter((filter) => !filter.isLocked),
        ],
    };
}
