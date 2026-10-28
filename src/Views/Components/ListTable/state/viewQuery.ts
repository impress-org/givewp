import type {Filter} from '@wordpress/dataviews';
import type {FilterByGroupedOptions, FilterConfig} from '../ListTablePage/types';
import {TEST_MODE_FILTER} from '../adapters/filterSettingsToFields';
import {filterFieldId} from '../adapters/viewToParameters';
import {snapPerPage} from './screenDefaults';
import type {ListTableView, ParseResult, ViewQuery, ViewQueryContext} from './types';

/**
 * The longest address the writer produces. Filter values are dropped from the end to stay under it.
 *
 * @since TBD
 */
export const MAX_ADDRESS_LENGTH = 2000;

/**
 * The only values the URL carries: ids and enumerated values.
 *
 * @since TBD
 */
const VALUE_PATTERN = /^[A-Za-z0-9_-]{1,64}$/;

const PAGE = 'give-paged';
const PER_PAGE = 'give-per-page';
const ORDER_BY = 'give-orderby';
const ORDER = 'give-order';
const TEST_MODE = 'give-test-mode';
const BARE_SEARCH = 'search';
const COMMON_KEYS = [PAGE, PER_PAGE, ORDER_BY, ORDER, TEST_MODE];
const TOGGLE_ON = '1';

/**
 * A filter value read from the URL, and whether anything was dropped while reading it.
 */
type ReadResult = {filter?: Filter; corrected: boolean};

/**
 * How one filter maps to one URL parameter.
 */
type FilterParameter = {
    key: string;
    /** The bare name links used before the `give-` prefix. */
    legacyKey?: string;
    field: string;
    read: (raw: string) => ReadResult;
    write: (value: unknown) => string | undefined;
};

const isToken = (value: string) => VALUE_PATTERN.test(value);

const isApplied = (value: unknown) =>
    value !== undefined && value !== null && value !== '' && !(Array.isArray(value) && value.length === 0);

/**
 * The URL parameter of a filter or group id: `give-` plus the id in kebab case.
 *
 * @since TBD
 */
export function parameterName(id: string): string {
    return `give-${id
        .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
        .replace(/_/g, '-')
        .toLowerCase()}`;
}

/**
 * A filter that holds one value, read when `parse` accepts the raw text.
 */
function singleValue(id: string, parse: (raw: string) => unknown, legacy: boolean): FilterParameter {
    const field = filterFieldId(id);

    return {
        key: parameterName(id),
        legacyKey: legacy ? id : undefined,
        field,
        read: (raw) => {
            const value = isToken(raw) ? parse(raw) : undefined;

            return value === undefined ? {corrected: true} : {filter: {field, operator: 'is', value}, corrected: false};
        },
        write: (value) => (isToken(String(value)) ? String(value) : undefined),
    };
}

const oneOf = (allowed: string[]) => (raw: string) => allowed.includes(raw) ? raw : undefined;

const idOrOneOf = (allowed: string[]) => (raw: string) => /^[1-9]\d*$/.test(raw) ? Number(raw) : oneOf(allowed)(raw);

/**
 * A "Filter by" group with a list of values (checkbox) or one value (radio).
 */
function listGroup(group: FilterByGroupedOptions): FilterParameter {
    const field = filterFieldId(group.id);
    const allowed = group.options.map((groupOption) => groupOption.value);
    const isCheckbox = group.type === 'checkbox';

    return {
        key: parameterName(group.id),
        field,
        read: (raw) => {
            const parts = raw.split(',');
            const valid = parts.filter(
                (part, index) => isToken(part) && allowed.includes(part) && parts.indexOf(part) === index
            );
            const kept = isCheckbox ? valid : valid.slice(0, 1);
            const corrected = kept.length !== parts.length;

            if (kept.length === 0) {
                return {corrected: true};
            }

            return {
                filter: isCheckbox ? {field, operator: 'isAny', value: kept} : {field, operator: 'is', value: kept[0]},
                corrected,
            };
        },
        write: (value) => {
            const values = ([] as unknown[]).concat(value).map(String).filter(isToken);

            return values.length > 0 ? values.join(',') : undefined;
        },
    };
}

/**
 * A "Filter by" toggle: `1` in the URL, the group's single option value in the view.
 */
function toggleGroup(group: FilterByGroupedOptions): FilterParameter {
    const field = filterFieldId(group.id);
    const onValue = group.options[0]?.value;

    return {
        key: parameterName(group.id),
        field,
        read: (raw) =>
            raw === TOGGLE_ON && onValue !== undefined
                ? {filter: {field, operator: 'is', value: onValue}, corrected: false}
                : {corrected: true},
        write: () => TOGGLE_ON,
    };
}

/**
 * The URL parameters of one filter setting. `search` and the test-mode checkbox have none.
 */
function parametersOf(filter: FilterConfig): FilterParameter[] {
    const staticValues = filter.type === 'filterby' ? [] : (filter.options ?? []).map((item) => item.value);

    switch (filter.type) {
        case 'select':
            return [singleValue(filter.name, oneOf(staticValues.slice(1)), true)];
        case 'campaignselect':
        case 'formselect':
            return [singleValue(filter.name, idOrOneOf(staticValues), true)];
        case 'hidden':
            return [singleValue(filter.name, (raw) => raw, true)];
        case 'filterby':
            return (filter.groupedOptions ?? []).map((group) =>
                group.type === 'toggle' ? toggleGroup(group) : listGroup(group)
            );
        default:
            return [];
    }
}

const filterParameters = (context: ViewQueryContext) => context.filterSettings.flatMap(parametersOf);

const hasTestModeToggle = (context: ViewQueryContext) =>
    context.filterSettings.some((filter) => filter.name === 'toggle');

/**
 * Every `give-` parameter the screen reads and writes, in the order they are written.
 *
 * @since TBD
 */
export function viewQueryKeys(context: ViewQueryContext): string[] {
    return [
        ...filterParameters(context).map((parameter) => parameter.key),
        ...(hasTestModeToggle(context) ? [TEST_MODE] : []),
        ORDER_BY,
        ORDER,
        PAGE,
        PER_PAGE,
    ];
}

const sameValue = (a: unknown, b: unknown) => JSON.stringify(a) === JSON.stringify(b);

/**
 * The URL parameters of the view's filters, leaving out empty values and values equal to the default.
 */
function writeFilters(view: ListTableView, context: ViewQueryContext): Array<[string, string]> {
    const valueOf = (filters: Filter[] | undefined, field: string) =>
        filters?.find((filter) => filter.field === field)?.value;

    return filterParameters(context)
        .map((parameter): [string, string | undefined] => {
            const value = valueOf(view.filters, parameter.field);
            const isDefault = sameValue(value, valueOf(context.defaults.filters, parameter.field));

            return [parameter.key, isApplied(value) && !isDefault ? parameter.write(value) : undefined];
        })
        .filter((entry): entry is [string, string] => entry[1] !== undefined);
}

/**
 * The test-mode parameter, written only when it differs from the site's payment mode.
 */
function writeTestMode(view: ListTableView, context: ViewQueryContext): Array<[string, string]> {
    // A view without filters has test mode off, the same as one whose filter list does not hold it.
    const isOn =
        view.filters?.some((filter) => filter.field === filterFieldId(TEST_MODE_FILTER) && filter.value === true) ??
        false;

    return hasTestModeToggle(context) && isOn !== context.siteTestMode ? [[TEST_MODE, isOn ? '1' : '0']] : [];
}

/**
 * The sort parameters, both written whenever the sort differs from the default.
 */
function writeSort(view: ListTableView, context: ViewQueryContext): Array<[string, string]> {
    const sort = view.sort ?? context.defaults.sort;
    const isDefault = sort.field === context.defaults.sort.field && sort.direction === context.defaults.sort.direction;

    return isDefault || !isToken(sort.field)
        ? []
        : [
              [ORDER_BY, sort.field],
              [ORDER, sort.direction],
          ];
}

/**
 * The page and page size, written when they differ from page one and the screen's default size.
 */
function writePaging(view: ListTableView, context: ViewQueryContext): Array<[string, string]> {
    const page = view.page ?? 1;
    const perPage = view.perPage ?? context.defaults.perPage;

    return [
        ...(page > 1 ? [[PAGE, String(page)] as [string, string]] : []),
        ...(perPage !== context.defaults.perPage ? [[PER_PAGE, String(perPage)] as [string, string]] : []),
    ];
}

/**
 * The view's `give-` URL parameters. A view equal to the defaults gives none. The search term, the
 * columns and the density are never written.
 *
 * @since TBD
 */
export function serializeViewQuery(view: ListTableView, context: ViewQueryContext): Record<string, string> {
    return Object.fromEntries([
        ...writeFilters(view, context),
        ...writeTestMode(view, context),
        ...writeSort(view, context),
        ...writePaging(view, context),
    ]);
}

/**
 * Reads `give-paged`: an integer of 1 or more.
 */
function readPage(raw: string | null): {page?: number; corrected: boolean} {
    if (raw === null) {
        return {corrected: false};
    }

    return /^\d+$/.test(raw) && Number(raw) >= 1 ? {page: Number(raw), corrected: false} : {corrected: true};
}

/**
 * Reads `give-per-page`: an integer, snapped to the nearest offered size.
 */
function readPerPage(raw: string | null): {perPage?: number; corrected: boolean} {
    if (raw === null) {
        return {corrected: false};
    }

    if (!/^\d+$/.test(raw)) {
        return {corrected: true};
    }

    const perPage = snapPerPage(Number(raw));

    return {perPage, corrected: perPage !== Number(raw)};
}

/**
 * Reads `give-orderby` (a sortable column, else no sort) and `give-order` (`asc` or `desc`, else `desc`).
 */
function readSort(params: URLSearchParams, sortableFields: string[]): {sort?: ViewQuery['sort']; corrected: boolean} {
    const field = params.get(ORDER_BY);
    const direction = params.get(ORDER);

    if (field === null) {
        return {corrected: direction !== null};
    }

    if (!isToken(field) || !sortableFields.includes(field)) {
        return {corrected: true};
    }

    const isDirection = direction === 'asc' || direction === 'desc';

    return {
        sort: {field, direction: isDirection ? direction : 'desc'},
        corrected: direction !== null && !isDirection,
    };
}

/**
 * Reads `give-test-mode`: `1` or `0`, only on a screen with the test-mode toggle.
 */
function readTestMode(raw: string | null, context: ViewQueryContext): {testMode?: boolean; corrected: boolean} {
    if (raw === null || !hasTestModeToggle(context)) {
        return {corrected: false};
    }

    return raw === '1' || raw === '0' ? {testMode: raw === '1', corrected: false} : {corrected: true};
}

/**
 * Reads the filter parameters. A legacy bare name is read with the same rules when its `give-` form is
 * missing, and is always listed so the writer removes it.
 */
function readFilters(
    params: URLSearchParams,
    context: ViewQueryContext
): {filters: Filter[]; corrected: boolean; legacyKeys: string[]} {
    const filters: Filter[] = [];
    const legacyKeys: string[] = [];
    let corrected = false;

    filterParameters(context).forEach(({key, legacyKey, read}) => {
        const legacy = legacyKey !== undefined && params.has(legacyKey) ? legacyKey : undefined;
        const raw = params.has(key) ? params.get(key) : legacy !== undefined ? params.get(legacy) : null;
        const result: ReadResult = raw === null ? {corrected: false} : read(raw);

        if (result.filter) {
            filters.push(result.filter);
        }

        if (legacy !== undefined) {
            legacyKeys.push(legacy);
        }

        corrected = corrected || result.corrected || legacy !== undefined;
    });

    return {filters, corrected, legacyKeys};
}

/**
 * Reads the screen's view from the address. Every value is checked against what the screen offers;
 * invalid parts are dropped and the result is marked `corrected`. Parameters that are not this
 * screen's are never read. A bare `search` is never read but is listed in `legacyKeys` for removal.
 *
 * @since TBD
 */
export function parseViewQuery(params: URLSearchParams, context: ViewQueryContext): ParseResult {
    const page = readPage(params.get(PAGE));
    const perPage = readPerPage(params.get(PER_PAGE));
    const sort = readSort(params, context.sortableFields);
    const testMode = readTestMode(params.get(TEST_MODE), context);
    const filters = readFilters(params, context);
    const hasBareSearch = params.has(BARE_SEARCH);

    return {
        query: {
            ...(page.page !== undefined && {page: page.page}),
            ...(perPage.perPage !== undefined && {perPage: perPage.perPage}),
            ...(sort.sort !== undefined && {sort: sort.sort}),
            filters: filters.filters,
            ...(testMode.testMode !== undefined && {testMode: testMode.testMode}),
        },
        corrected:
            page.corrected ||
            perPage.corrected ||
            sort.corrected ||
            testMode.corrected ||
            filters.corrected ||
            hasBareSearch,
        legacyKeys: [...filters.legacyKeys, ...(hasBareSearch ? [BARE_SEARCH] : [])],
    };
}

/**
 * The decoded name of one `name=value` pair of a query string.
 */
function pairName(pair: string): string {
    const name = pair.split('=')[0].replace(/\+/g, ' ');

    try {
        return decodeURIComponent(name);
    } catch {
        return name;
    }
}

/**
 * Drops the last value of the last filter parameter. Returns false when no filter value is left.
 */
function dropLastFilterValue(entries: Array<[string, string]>): boolean {
    const index = entries.map(([key]) => COMMON_KEYS.includes(key)).lastIndexOf(false);

    if (index === -1) {
        return false;
    }

    const values = entries[index][1].split(',').slice(0, -1);

    if (values.length > 0) {
        entries[index] = [entries[index][0], values.join(',')];
    } else {
        entries.splice(index, 1);
    }

    return true;
}

/**
 * What the writer needs besides the parameters: the screen's own keys (`viewQueryKeys`), the legacy
 * names to remove, and the length of the rest of the address (origin, path and hash).
 *
 * @since TBD
 */
export type WriteViewQueryOptions = {owned: string[]; legacyKeys?: string[]; addressLength?: number};

/**
 * The new query string (`?…`, or empty) for the view's parameters. The screen's own parameters and the
 * legacy names are replaced; every other parameter stays exactly as it was, in its order. Filter values
 * are dropped from the end until the whole address is under the length limit.
 *
 * @since TBD
 */
export function writeViewQuery(
    search: string,
    parameters: Record<string, string>,
    {owned, legacyKeys = [], addressLength = 0}: WriteViewQueryOptions
): string {
    const removed = [...owned, ...legacyKeys];
    const kept = search
        .replace(/^\?/, '')
        .split('&')
        .filter((pair) => pair !== '' && !removed.includes(pairName(pair)));
    const entries = Object.entries(parameters);
    const build = () => {
        const pairs = [...kept, ...entries.map(([key, value]) => `${key}=${value}`)];

        return pairs.length > 0 ? `?${pairs.join('&')}` : '';
    };

    let query = build();

    while (addressLength + query.length >= MAX_ADDRESS_LENGTH && dropLastFilterValue(entries)) {
        query = build();
    }

    return query;
}
