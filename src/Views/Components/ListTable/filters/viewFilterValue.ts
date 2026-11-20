import type {View} from '@wordpress/dataviews';

/**
 * Filter fields get their own id space, so a filter never collides with a column of the same name
 * (Donations has both a `status` column and a `status` filter).
 *
 * @since TBD
 */
export function filterFieldId(id: string): string {
    return `filter-${id}`;
}

/**
 * The value of a filter in the view, or undefined when the filter is not applied.
 *
 * @since TBD
 */
export function viewFilterValue(view: View, id: string): unknown {
    return view.filters?.find((filter) => filter.field === filterFieldId(id))?.value;
}

/**
 * Whether a search or filter value narrows the rows: not empty, not null and not an empty list.
 *
 * @since TBD
 */
export function isApplied(value: unknown): boolean {
    return value !== undefined && value !== null && value !== '' && !(Array.isArray(value) && value.length === 0);
}
