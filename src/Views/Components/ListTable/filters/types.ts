/**
 * A choice in a fixed option list or an option loaded from the server.
 *
 * @since TBD
 */
export type FilterOption = {value: string; label: string};

/**
 * One entry of the server's status vocabulary (`apiSettings.table.statuses`).
 *
 * @since TBD
 */
export type StatusVocabularyEntry = {value: string; label: string; filterable: boolean};

/**
 * @since TBD
 */
export type FilterEntitySource = 'campaign' | 'form' | 'donor';

interface FilterDeclarationBase {
    /** Stable id. View field `filter-<id>`; URL parameter `give-<id in kebab case>`. */
    id: string;
    /** Translated label, shown on the chip and in "Add filter". */
    label: string;
    /** Always visible. Default false (listed in "Add filter"). */
    isPrimary?: boolean;
    /** A bare URL name used by links made before view state existed (for example `donor`). Read, applied, rewritten. */
    legacyUrlKey?: string;
}

/**
 * A fixed list: the status set, or a short list such as "Donation type".
 *
 * @since TBD
 */
export interface OptionsFilterDeclaration extends FilterDeclarationBase {
    kind: 'options';
    multiple: boolean;
    /** `'statuses'`: the filterable entries of the server vocabulary, in its order. */
    options: 'statuses' | FilterOption[];
    /** Request parameter. A multiple value is sent as a list. */
    parameter: string;
    /** Values that cannot be combined with any other (Trash). */
    exclusiveValues?: string[];
    /** Parameters sent while the filter holds no value (Campaigns: `{status: 'any'}`). */
    whenEmpty?: Record<string, string>;
}

/**
 * Options searched on the server: campaign, form, donor. Values are ids.
 *
 * @since TBD
 */
export interface EntityFilterDeclaration extends FilterDeclarationBase {
    kind: 'entity';
    source: FilterEntitySource;
    multiple: boolean;
    parameter: string;
    /** Adds the "No campaign" choice (value `none`), exclusive of ids. */
    allowNone?: boolean;
}

/**
 * @since TBD
 */
export interface DateFilterDeclaration extends FilterDeclarationBase {
    kind: 'date';
    /** Request parameters for the first and last day. */
    parameters: {start: string; end: string};
}

/**
 * The search box. Always visible, never in "Add filter".
 *
 * @since TBD
 */
export interface SearchFilterDeclaration {
    kind: 'search';
    id: 'search';
    label: string;
    /** Names the fields searched and how they match. */
    hint: string;
    parameter: string;
}

/**
 * @since TBD
 */
export type FilterDeclaration =
    | OptionsFilterDeclaration
    | EntityFilterDeclaration
    | DateFilterDeclaration
    | SearchFilterDeclaration;

/**
 * Every kind except search: the filters the bar renders.
 *
 * @since TBD
 */
export type BarFilterDeclaration = Exclude<FilterDeclaration, SearchFilterDeclaration>;

/**
 * What a bar filter holds in the view. Always plain text, so it can go in the URL.
 *
 * @since TBD
 */
export type FilterValue = string | string[];

/**
 * @since TBD
 */
export type DatePreset = '7' | '30' | '90' | 'this-month' | 'last-month' | 'this-year';

/**
 * @since TBD
 */
export const DATE_PRESETS: readonly DatePreset[] = ['7', '30', '90', 'this-month', 'last-month', 'this-year'];

/**
 * The meaning of a date token. Dates are `YYYY-MM-DD` in the site's calendar.
 *
 * @since TBD
 */
export type DateRangeValue = {preset: DatePreset} | {start?: string; end?: string};

/**
 * @since TBD
 */
export type ParsedFilterValue = {value?: FilterValue; corrected: boolean};
