import type {Filter, View} from '@wordpress/dataviews';
import type {FilterConfig} from '../ListTablePage/types';
import {clampPerPage, filterFieldId} from './viewToParameters';

/**
 * Campaign and form ids arrive from the URL as text; their filter options use numbers.
 */
const toId = (value: string): string | number => (/^\d+$/.test(value) ? Number(value) : value);

/**
 * Reads a filter value from the URL the way the old client did. A missing parameter is undefined.
 */
function urlValue(search: URLSearchParams, name: string): string | undefined {
    if (!search.has(name)) {
        return undefined;
    }

    return decodeURI(search.get(name) ?? '');
}

/**
 * Builds the first view of a list screen: page one, the screen's page size, newest first, and the
 * filters and search term a link into the screen carries in its URL. Table-scope filters (hidden
 * filters in content mode) are not view filters; see `scopeFromFilterSettings`.
 *
 * @since TBD
 */
export function viewFromInitialState(
    filterSettings: FilterConfig[],
    defaults: {perPage?: number; fields: string[]; titleField?: string; contentMode?: boolean},
    search: URLSearchParams
): View {
    const filters: Filter[] = [];
    let searchTerm = '';

    filterSettings.forEach((filter) => {
        const value = urlValue(search, filter.name);
        const is = (applied: unknown) => filters.push({field: filterFieldId(filter.name), operator: 'is', value: applied});

        switch (filter.type) {
            case 'search':
                searchTerm = value ?? '';
                break;
            case 'select':
                if (value !== undefined && value !== '' && value !== filter.options?.[0]?.value) {
                    is(value);
                }
                break;
            case 'campaignselect':
            case 'formselect':
                if (value) {
                    is(toId(value));
                }
                break;
            case 'hidden': {
                const linked = value ?? filter.options?.[0]?.value;

                if (!defaults.contentMode && linked) {
                    is(linked);
                }
                break;
            }
            case 'filterby':
                (filter.groupedOptions ?? [])
                    .filter((group) => group.defaultValue !== undefined && group.defaultValue.length > 0)
                    .forEach((group) => {
                        const defaultValues = [].concat(group.defaultValue);

                        filters.push(
                            group.type === 'checkbox'
                                ? {field: filterFieldId(group.id), operator: 'isAny', value: defaultValues}
                                : {field: filterFieldId(group.id), operator: 'is', value: defaultValues[0]}
                        );
                    });
                break;
        }
    });

    return {
        type: 'table',
        page: 1,
        perPage: clampPerPage(defaults.perPage),
        sort: {field: 'id', direction: 'desc'},
        search: searchTerm,
        filters,
        fields: defaults.fields,
        titleField: defaults.titleField,
        layout: {},
    };
}

/**
 * The fixed filters that define a table's scope, such as the campaign on the Campaign details
 * "Forms" tab. They are hidden filters in content mode: sent with every request, never shown as a
 * chip and never cleared.
 *
 * @since TBD
 */
export function scopeFromFilterSettings(
    filterSettings: FilterConfig[],
    contentMode: boolean,
    search: URLSearchParams
): Record<string, string | number> {
    if (!contentMode) {
        return {};
    }

    return Object.fromEntries(
        filterSettings
            .map((filter) => [
                filter.name,
                filter.type === 'hidden' ? urlValue(search, filter.name) ?? filter.options?.[0]?.value ?? '' : '',
            ])
            .filter(([, value]) => value !== '')
    );
}
