import type {View} from '@wordpress/dataviews';
import type {FilterByGroupedOptions, FilterConfig, FilterValues, ListTableParameters} from '../ListTablePage/types';

/**
 * @since TBD
 */
export const PER_PAGE_SIZES = [10, 20, 30, 50, 100];

/**
 * The page size used when neither the screen nor the page sets one.
 *
 * @since TBD
 */
export const DEFAULT_PER_PAGE = 30;

/**
 * The nearest offered page size. On a tie the smaller size wins. A value that is not a number gives
 * the fallback. Every page size goes through this rule, so the view only ever holds an offered size.
 *
 * @since TBD
 */
export function snapPerPage(perPage: number | undefined, fallback: number = DEFAULT_PER_PAGE): number {
    if (typeof perPage !== 'number' || !Number.isFinite(perPage)) {
        return fallback;
    }

    return PER_PAGE_SIZES.reduce((nearest, size) =>
        Math.abs(size - perPage) < Math.abs(nearest - perPage) ? size : nearest
    );
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
 * The view with a yes/no filter turned on or off. Test mode is one: on, it shows as a chip.
 *
 * @since TBD
 */
export function withToggleFilter(view: View, id: string, isOn: boolean): View {
    const field = filterFieldId(id);
    const otherFilters = (view.filters ?? []).filter((filter) => filter.field !== field);

    return {...view, filters: isOn ? [...otherFilters, {field, operator: 'is', value: true}] : otherFilters};
}

/**
 * @since TBD
 */
export function filterByGroups(filterSettings: FilterConfig[]): FilterByGroupedOptions[] {
    return filterSettings.flatMap((filter) => (filter.type === 'filterby' ? filter.groupedOptions ?? [] : []));
}

/**
 * Whether a search or filter value narrows the rows: not empty, not null and not an empty list.
 *
 * @since TBD
 */
export function isApplied(value: unknown): boolean {
    return value !== undefined && value !== null && value !== '' && !(Array.isArray(value) && value.length === 0);
}

const isEmpty = (value: unknown) => !isApplied(value);

/**
 * The applied "Filter by" values keyed by API parameter, as the groups' `isVisible` rules read them.
 * Groups that share a parameter (Donations "Show trashed" and the status list) add to one array.
 * A group that is hidden by the current values is left out. With `now`, each group's `toApiValue`
 * turns the values into what the endpoint receives; `isVisible` always reads the option values.
 *
 * @since TBD
 */
export function filterByValues(view: View, filterSettings: FilterConfig[], now?: Date): FilterValues {
    const groups = filterByGroups(filterSettings);
    const valuesOf = (included: FilterByGroupedOptions[], toApi: boolean) =>
        included.reduce<FilterValues>((values, group) => {
            const value = viewFilterValue(view, group.id);

            if (isEmpty(value)) {
                return values;
            }

            const optionValues: string[] = [].concat(value).map(String);
            const {toApiValue} = group;
            const sent =
                toApi && now && toApiValue
                    ? optionValues.map((optionValue) => toApiValue(optionValue, now))
                    : optionValues;

            return {...values, [group.apiParam]: [...(values[group.apiParam] ?? []), ...sent]};
        }, {});

    const allValues = valuesOf(groups, false);

    return valuesOf(
        groups.filter((group) => !group.isVisible || group.isVisible(allValues)),
        true
    );
}

/**
 * Builds the list endpoint's request parameters from the view. This is the only place that knows the
 * endpoints' parameter names. Empty values are left out, as the old client left them out of the URL.
 * `now` is the time "Filter by" values such as "Period" are counted from; keep it fixed per view so
 * the request URL stays the same.
 *
 * @since TBD
 */
export function viewToParameters(
    view: View,
    filterSettings: FilterConfig[],
    scope: Record<string, string | number>,
    extra: {locale: string; testMode?: boolean; now?: Date}
): ListTableParameters {
    const parameters: ListTableParameters = {
        page: view.page ?? 1,
        perPage: snapPerPage(view.perPage),
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

    Object.assign(parameters, filterByValues(view, filterSettings, extra.now ?? new Date()), scope);

    return Object.fromEntries(Object.entries(parameters).filter(([, value]) => !isEmpty(value)));
}
