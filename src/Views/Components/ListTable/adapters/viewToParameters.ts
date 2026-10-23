import type {View} from '@wordpress/dataviews';
import type {FilterByGroupedOptions, FilterConfig, FilterValues, ListTableParameters} from '../ListTablePage/types';

/**
 * @since TBD
 */
export const PER_PAGE_SIZES = [10, 20, 30, 50, 100];

/**
 * The list endpoints accept any page size; the client keeps it to the largest offered size.
 *
 * @since TBD
 */
export function clampPerPage(perPage: number | undefined): number {
    const size = Math.round(Number(perPage)) || 30;

    return Math.min(Math.max(size, 1), PER_PAGE_SIZES[PER_PAGE_SIZES.length - 1]);
}

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
 * @since TBD
 */
export function filterByGroups(filterSettings: FilterConfig[]): FilterByGroupedOptions[] {
    return filterSettings.flatMap((filter) => (filter.type === 'filterby' ? filter.groupedOptions ?? [] : []));
}

const isEmpty = (value: unknown) =>
    value === undefined || value === null || value === '' || (Array.isArray(value) && value.length === 0);

/**
 * The applied "Filter by" values keyed by API parameter, as the groups' `isVisible` rules read them.
 * Groups that share a parameter (Donations "Show trashed" and the status list) add to one array.
 * A group that is hidden by the current values is left out.
 *
 * @since TBD
 */
export function filterByValues(view: View, filterSettings: FilterConfig[]): FilterValues {
    const groups = filterByGroups(filterSettings);
    const valuesOf = (included: FilterByGroupedOptions[]) =>
        included.reduce<FilterValues>((values, group) => {
            const value = viewFilterValue(view, group.id);

            if (isEmpty(value)) {
                return values;
            }

            return {...values, [group.apiParam]: [...(values[group.apiParam] ?? []), ...[].concat(value).map(String)]};
        }, {});

    const allValues = valuesOf(groups);

    return valuesOf(groups.filter((group) => !group.isVisible || group.isVisible(allValues)));
}

/**
 * Builds the list endpoint's request parameters from the view. This is the only place that knows the
 * endpoints' parameter names. Empty values are left out, as the old client left them out of the URL.
 *
 * @since TBD
 */
export function viewToParameters(
    view: View,
    filterSettings: FilterConfig[],
    scope: Record<string, string | number>,
    extra: {locale: string; testMode?: boolean}
): ListTableParameters {
    const parameters: ListTableParameters = {
        page: view.page ?? 1,
        perPage: clampPerPage(view.perPage),
        sortColumn: view.sort?.field ?? 'id',
        sortDirection: view.sort?.direction ?? 'desc',
        locale: extra.locale,
        testMode: extra.testMode,
    };

    filterSettings.forEach((filter) => {
        switch (filter.type) {
            case 'search':
                parameters[filter.name] = view.search;
                break;
            case 'select':
                parameters[filter.name] = (viewFilterValue(view, filter.name) as string) ?? filter.options?.[0]?.value;
                break;
            case 'campaignselect':
            case 'formselect':
            case 'hidden':
                parameters[filter.name] = viewFilterValue(view, filter.name) as string;
                break;
        }
    });

    Object.assign(parameters, filterByValues(view, filterSettings), scope);

    return Object.fromEntries(Object.entries(parameters).filter(([, value]) => !isEmpty(value)));
}
