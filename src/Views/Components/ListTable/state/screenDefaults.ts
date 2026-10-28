import type {Filter} from '@wordpress/dataviews';
import type {Column} from '../ListTablePage/types';
import {snapPerPage} from '../adapters/viewToParameters';
import type {ListTableScreen, ScreenDefaults} from './types';

/**
 * The id column, locked when a screen has no title field.
 *
 * @since TBD
 */
const ID_FIELD = 'id';

/**
 * What the page adds to the screen config: its `perPage` prop, the legacy row-actions field when the
 * screen uses one, and the filters applied by default.
 *
 * @since TBD
 */
export type ScreenDefaultsOptions = {perPage?: number; pinnedField?: string; filters?: Filter[]};

/**
 * Resolves a screen's defaults from its config and the server column schema. The schema gives the
 * order and the default visibility; the config only overrides them. Locked fields always stay visible.
 *
 * @since TBD
 */
export function resolveScreenDefaults(
    screen: ListTableScreen | undefined,
    columns: Column[],
    titleField: string | undefined,
    options: ScreenDefaultsOptions = {}
): ScreenDefaults {
    const {hiddenFields = [], visibleFields = [], perPage, density = 'balanced'} = screen?.defaults ?? {};
    const pinned = options.pinnedField ? [options.pinnedField] : [];
    const lockedFields = [titleField ?? ID_FIELD, ...pinned];
    const shownColumns = columns.filter((column) => column.id !== titleField);
    const isVisible = (column: Column) =>
        lockedFields.includes(column.id) ||
        visibleFields.includes(column.id) ||
        (column.visible !== false && !hiddenFields.includes(column.id));

    return {
        fields: [...shownColumns.filter(isVisible).map((column) => column.id), ...pinned],
        fieldOrder: [...shownColumns.map((column) => column.id), ...pinned],
        density,
        perPage: snapPerPage(perPage ?? options.perPage),
        sort: {field: ID_FIELD, direction: 'desc'},
        titleField,
        lockedFields,
        pinnedLast: options.pinnedField,
        filters: options.filters ?? [],
    };
}
