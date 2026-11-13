import type {View} from '@wordpress/dataviews';
import type {ListTableParameters} from '../ListTablePage/types';
import {filterRequestParameters} from '../filters/requestParameters';
import {siteToday} from '../filters/siteDate';
import type {FilterDeclaration} from '../filters/types';
import {filterFieldId, isApplied} from '../filters/viewFilterValue';

export {filterFieldId, isApplied, viewFilterValue} from '../filters/viewFilterValue';

/**
 * @since TBD
 */
export const PER_PAGE_SIZES = [10, 20, 30, 50, 100];

/**
 * The page size used when neither the screen nor the page sets one.
 *
 * @since TBD
 */
export const DEFAULT_PER_PAGE = 20;

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
 * The filter id of test mode, set by the header switch or its chip.
 *
 * @since TBD
 */
export const TEST_MODE_FILTER = 'testMode';

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
 * Builds the list endpoint's request parameters from the view. This is the only place that knows the
 * endpoints' parameter names. Empty values are left out, as the old client left them out of the URL.
 * The filters come from the declarations; `today` (the site's day, fixed per view) is what date
 * presets count from. `scope` is sent with every request.
 *
 * @since TBD Do not send a locale: the server formats money and dates with the site settings.
 * @since TBD Take only filter declarations; the old filter settings are mapped onto them first.
 * @since TBD Build the filter parameters from the filter declarations when they are given.
 * @since TBD
 */
export function viewToParameters(
    view: View,
    declarations: FilterDeclaration[],
    scope: Record<string, string | number>,
    extra: {testMode?: boolean; today?: string}
): ListTableParameters {
    const parameters: ListTableParameters = {
        page: view.page ?? 1,
        perPage: snapPerPage(view.perPage),
        sortColumn: view.sort?.field ?? 'id',
        sortDirection: view.sort?.direction ?? 'desc',
        testMode: extra.testMode,
        ...filterRequestParameters(declarations, view, extra.today ?? siteToday()),
        ...scope,
    };

    return Object.fromEntries(Object.entries(parameters).filter(([, value]) => isApplied(value)));
}
