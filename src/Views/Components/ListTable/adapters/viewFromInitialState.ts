import type {Filter} from '@wordpress/dataviews';
import type {FilterConfig} from '../ListTablePage/types';
import {TEST_MODE_FILTER} from './filterSettingsToFields';
import {filterFieldId} from './viewToParameters';

/**
 * The filters a screen applies before the admin changes anything: each "Filter by" group's default,
 * a hidden filter's own option (a standalone screen only), and test mode on a site in test mode. The
 * address is never read here; `parseViewQuery` reads it. Table-scope filters (hidden filters in
 * content mode) are not view filters; see `scopeFromFilterSettings`.
 *
 * @since TBD
 */
export function defaultFilters(
    filterSettings: FilterConfig[],
    {contentMode = false, testMode = false}: {contentMode?: boolean; testMode?: boolean} = {}
): Filter[] {
    const filters: Filter[] = [];

    filterSettings.forEach((filter) => {
        if (filter.type === 'hidden') {
            const linked = filter.options?.[0]?.value;

            if (!contentMode && linked) {
                filters.push({field: filterFieldId(filter.name), operator: 'is', value: linked});
            }
        }

        if (filter.type === 'filterby') {
            (filter.groupedOptions ?? [])
                .filter((group) => group.defaultValue !== undefined && group.defaultValue.length > 0)
                .forEach((group) => {
                    const defaultValues = ([] as string[]).concat(group.defaultValue);

                    filters.push(
                        group.type === 'checkbox'
                            ? {field: filterFieldId(group.id), operator: 'isAny', value: defaultValues}
                            : {field: filterFieldId(group.id), operator: 'is', value: defaultValues[0]}
                    );
                });
        }
    });

    if (testMode && filterSettings.some((filter) => filter.name === 'toggle')) {
        filters.push({field: filterFieldId(TEST_MODE_FILTER), operator: 'is', value: true});
    }

    return filters;
}

/**
 * The fixed filters that define a table's scope, such as the campaign on the Campaign details
 * "Forms" tab. They are hidden filters in content mode: sent with every request, never shown as a
 * chip and never cleared. The value is the filter's own option, set by the screen from its host page.
 *
 * @since TBD
 */
export function scopeFromFilterSettings(
    filterSettings: FilterConfig[],
    contentMode: boolean
): Record<string, string | number> {
    if (!contentMode) {
        return {};
    }

    return Object.fromEntries(
        filterSettings
            .map((filter) => [filter.name, filter.type === 'hidden' ? filter.options?.[0]?.value ?? '' : ''])
            .filter(([, value]) => value !== '')
    );
}
