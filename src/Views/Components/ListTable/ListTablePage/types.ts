import type {ReactElement, ReactNode} from 'react';
import type {RenderModalProps} from '@wordpress/dataviews';
import type {StatConfig} from '../ListTableStats/ListTableStats';
import type ListTableApi from '../api';

/**
 * A column of a list table, as `ModelColumn::toArray()` sends it.
 *
 * @since TBD
 */
export interface Column {
    id: string;
    label: string;
    sortable: boolean;
    visible?: boolean;
}

/**
 * One row of a list table. Cells are server-rendered HTML keyed by column id; some screens add raw
 * values (`name`, `titleRaw`, `status_raw`, …).
 *
 * @since TBD
 */
export type ListTableItem = {id: string | number; [columnId: string]: unknown};

/**
 * @since TBD
 */
export type ListTableResponse = {
    items: ListTableItem[];
    totalItems: number | string;
    totalPages: number;
    [key: string]: unknown;
};

/**
 * The request parameters of a list endpoint, in the shape the screens' `isVisible` rules read.
 * Arrays are joined with commas only when the request URL is built.
 *
 * @since TBD
 */
export type ListTableParameters = Record<string, string | number | boolean | Array<string | number> | undefined>;

/**
 * Applied "Filter by" values keyed by their API parameter, the shape `FilterByGroupedOptions.isVisible` reads.
 *
 * @since TBD
 */
export type FilterValues = Record<string, string[]>;

interface FilterConfigBase {
    // required
    name: string;

    // optional
    ariaLabel?: string;
    inlineSize?: string;
    text?: string;
}

interface FilterConfigWithSimpleOptions extends FilterConfigBase {
    type: 'select' | 'campaignselect' | 'formselect' | 'search' | 'checkbox' | 'hidden';
    options?: Array<{text: string; value: string}>;
}

export interface FilterByGroupedOptions {
    id: string;
    apiParam: string;
    name: string;
    type: 'checkbox' | 'radio' | 'toggle';
    options: Array<{text: string; value: string}>;
    defaultValue?: string | string[];
    isVisible?: (values: Record<string, string[]>) => boolean;
    showTitle?: boolean;
}

interface FilterConfigWithGroupedOptions extends FilterConfigBase {
    type: 'filterby';
    groupedOptions?: Array<FilterByGroupedOptions>;
}

export type FilterConfig = FilterConfigWithSimpleOptions | FilterConfigWithGroupedOptions;

export interface ColumnFilterConfig {
    column: string;
    filter: Function;
}

interface BulkActionsConfigBase {
    //required
    label: string;
    value: string | number;
    confirm: (
        selected: Array<string | number>,
        names?: Array<string>,
        isOpen?: boolean,
        setOpen?: (isOpen?: boolean) => void
    ) => JSX.Element | JSX.Element[] | string;

    //optional
    isVisible?: (data: any, parameters: any) => boolean;
    isIdSelectable?: (id: string, data: any) => boolean;
    type?: 'normal' | 'warning' | 'danger' | 'custom';
}

// Makes the "action" property required for the standard types
interface BulkActionsConfigWithAction extends BulkActionsConfigBase {
    type: 'normal' | 'warning' | 'danger';
    action: (selected: Array<string | number>) => Promise<{errors: string | number; successes: string | number}>;
}

// Makes the "action" property required for the undefined type
interface BulkActionsConfigWithoutType extends BulkActionsConfigBase {
    type?: undefined;
    action: (selected: Array<string | number>) => Promise<{errors: string | number; successes: string | number}>;
}

// Makes the "action" property forbidden for the custom type
export interface BulkActionsConfigWithoutAction extends BulkActionsConfigBase {
    type: 'custom';
}

export type BulkActionsConfig =
    | BulkActionsConfigWithAction
    | BulkActionsConfigWithoutType
    | BulkActionsConfigWithoutAction;

/**
 * What an action did, counted from whatever the action endpoint replied.
 *
 * @since TBD
 */
export type ActionOutcome = {
    changed: number;
    failed: number;
    message?: string;
};

/**
 * Shows the result of an action on the page: a snackbar for what changed, a notice for what failed.
 *
 * @since TBD
 */
export type ReportOutcome = (outcome: ActionOutcome, actionLabel?: string) => void;

/**
 * A row action declared as data, so the list engine can show "Edit" inline and the rest in the row menu.
 *
 * Exactly one of `href`, `run` or `RenderModal`.
 *
 * @since TBD
 */
export interface ListTableRowAction<Item extends ListTableItem = ListTableItem> {
    id: string;
    label: string;
    /** Only "Edit", or "Restore" on trashed rows. */
    isPrimary?: boolean;
    isEligible?: (item: Item) => boolean;
    isDestructive?: boolean;
    href?: (item: Item) => string;
    confirm?: {
        title: string;
        body: (item: Item) => ReactNode;
        confirmLabel: string;
    };
    run?: (item: Item) => Promise<unknown>;
    RenderModal?: (props: RenderModalProps<Item>) => ReactElement;
}

/**
 * The arguments today's row-actions render function receives.
 *
 * @since TBD
 */
export type LegacyRowActionArgs = {
    data: ListTableResponse | undefined;
    item: ListTableItem;
    removeRow: (callback: (id: string) => Promise<unknown>) => (event: {target: EventTarget}) => Promise<void>;
    addRow: (callback: (id: string) => Promise<unknown>) => (event: {target: EventTarget}) => Promise<void>;
    setUpdateErrors: (response: unknown) => void;
    parameters: ListTableParameters;
    listTableApi: ListTableApi;
};

/**
 * Today's row-actions input: a function that renders the actions of one row.
 *
 * @since TBD
 */
export type LegacyRowActions = (args: LegacyRowActionArgs) => ReactNode;

export interface ListTablePageProps {
    //required
    title: string;
    apiSettings: {apiRoot: string; apiNonce: string; table: {id?: string; columns: Array<object>}; [key: string]: any};

    //optional
    bulkActions?: Array<BulkActionsConfig> | null;
    pluralName?: string;
    singleName?: string;
    children?: ReactNode;
    rowActions?: LegacyRowActions | Function | ListTableRowAction[] | null;
    filterSettings?: FilterConfig[];
    align?: 'start' | 'center' | 'end';
    paymentMode?: boolean;
    listTableBlankSlate: ReactNode;
    productRecommendation?: ReactNode;
    columnFilters?: Array<ColumnFilterConfig>;
    banner?: () => ReactNode;
    contentMode?: boolean;
    perPage?: number;
    statsConfig?: Record<string, StatConfig>;
}

export interface ListTablePageRef {
    refresh: () => Promise<any>;
}
