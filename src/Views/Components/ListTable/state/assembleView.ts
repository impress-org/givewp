import type {Filter} from '@wordpress/dataviews';
import {filterFieldId, TEST_MODE_FILTER} from '../adapters/viewToParameters';
import type {ListTableView, ScreenDefaults, ViewLayoutPart, ViewQuery} from './types';

/**
 * The parts a view is assembled from. `layout` is the stored record on load and the current layout on
 * back/forward; `query` is only given when the screen keeps its view in the URL.
 *
 * @since TBD
 */
export type AssembleViewInput = {
    defaults: ScreenDefaults;
    layout?: ViewLayoutPart;
    query?: ViewQuery;
};

/**
 * The default filters with the URL's filters and test mode on top. A URL filter replaces the default
 * filter of the same field.
 */
function mergeFilters(defaults: Filter[], query: ViewQuery | undefined): Filter[] {
    const testModeField = filterFieldId(TEST_MODE_FILTER);
    const named = (query?.filters ?? []).map((filter) => filter.field);

    if (query?.testMode !== undefined) {
        named.push(testModeField);
    }

    return [
        ...defaults.filter((filter) => !named.includes(filter.field)),
        ...(query?.filters ?? []),
        ...(query?.testMode ? [{field: testModeField, operator: 'is' as const, value: true}] : []),
    ];
}

/**
 * Builds the view in the order of precedence: screen defaults, then the layout, then the URL. Each one
 * wins for what it names.
 *
 * @since TBD
 */
export function assembleView({defaults, layout, query}: AssembleViewInput): ListTableView {
    return {
        type: 'table',
        page: query?.page ?? 1,
        perPage: query?.perPage ?? layout?.perPage ?? defaults.perPage,
        sort: query?.sort ?? defaults.sort,
        search: query?.search ?? '',
        filters: mergeFilters(defaults.filters, query),
        fields: layout?.fields ?? defaults.fields,
        titleField: defaults.titleField,
        ...(defaults.showTitle === false ? {showTitle: false} : {}),
        layout: {density: layout?.density ?? defaults.density},
    };
}
