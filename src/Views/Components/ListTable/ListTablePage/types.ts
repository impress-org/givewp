import type {ReactElement, ReactNode} from 'react';
import type {RenderModalProps} from '@wordpress/dataviews';
import type {StatConfig} from '../ListTableStats/ListTableStats';
import type ListTableApi from '../api';
import type {ListTableScreen} from '../state/types';

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
    /**
     * Turns an option value into the value the endpoint receives, so the option can keep a stable value
     * in the URL ("Period" keeps `30` and sends the ISO date 30 days before `now`). `now` is fixed per
     * view, so the request stays the same while the view does not change.
     *
     * @since TBD
     */
    toApiValue?: (value: string, now: Date) => string;
}

interface FilterConfigWithGroupedOptions extends FilterConfigBase {
    type: 'filterby';
    groupedOptions?: Array<FilterByGroupedOptions>;
}

export type FilterConfig = FilterConfigWithSimpleOptions | FilterConfigWithGroupedOptions;

export interface ColumnFilterConfig {
    column: string;
    /** Renders the cell in place of the server HTML. */
    filter: (item: ListTableItem, column: Column, data: ListTableResponse | undefined) => ReactNode;
}

/**
 * What a custom bulk action's own dialog can ask of the list, after it has changed records.
 *
 * @since TBD
 */
export type BulkActionDialogContext = {
    refresh: () => Promise<unknown>;
    clearSelection: () => void;
};

interface BulkActionsConfigBase {
    //required
    label: string;
    value: string | number;
    confirm: (
        selected: Array<string | number>,
        names?: Array<string>,
        isOpen?: boolean,
        setOpen?: (isOpen?: boolean) => void,
        context?: BulkActionDialogContext
    ) => JSX.Element | JSX.Element[] | string;

    //optional
    isVisible?: (data: ListTableResponse | undefined, parameters: ListTableParameters) => boolean;
    isIdSelectable?: (id: string, data: ListTableResponse | undefined) => boolean;
    type?: 'normal' | 'warning' | 'danger' | 'custom';
    /**
     * A `custom` action that needs at least this many rows (Campaigns "Merge" needs two). With fewer,
     * an error notice explains it instead of opening the dialog. The action stays offered: DataViews
     * ignores `disabled` in the selection bar, and an action left out removes the row checkboxes.
     */
    minSelected?: number;
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
    /** An error to show although no record failed, for example an action that could not start. */
    notice?: string;
    /** The snackbar text, in place of the generic "updated" message. */
    successMessage?: string;
};

/**
 * Shows the result of an action on the page: a snackbar for what changed, a notice for what failed.
 *
 * @since TBD
 */
export type ReportOutcome = (outcome: ActionOutcome, actionLabel?: string) => void;

/**
 * What running an action needs from the page: show the result, clear the selection, reload.
 *
 * @since TBD
 */
export type ListActionContext = {
    report: ReportOutcome;
    refresh: () => Promise<unknown>;
    clearSelection: () => void;
};

/**
 * An action declared as data, so the list engine can show "Edit" inline and the rest in the row menu.
 * With `supportsBulk` the same action also runs on a selection, as in WordPress core: DataViews lists
 * every bulk action in each row's menu, so a separate bulk action would show twice there.
 *
 * Exactly one of `href`, `run`, `RenderModal` or `Dialog`. `run` and `confirm.body` get one item from
 * a row's menu and the eligible selected items from the selection bar.
 *
 * @since TBD
 */
export interface ListTableRowAction<Item extends ListTableItem = ListTableItem> {
    id: string;
    label: string | ((items: Item[]) => string);
    /** Only "Edit", or "Restore" on trashed rows. */
    isPrimary?: boolean;
    supportsBulk?: boolean;
    /** Same rule as a bulk action's: checked on every render against the response and the request parameters. */
    isVisible?: (data: ListTableResponse | undefined, parameters: ListTableParameters) => boolean;
    isEligible?: (item: Item) => boolean;
    isDestructive?: boolean;
    /** The snackbar text when the action succeeds, in place of the generic "updated" message. */
    successMessage?: (items: Item[]) => string;
    href?: (item: Item) => string;
    confirm?: {
        title: string;
        body: (items: Item[]) => ReactNode;
        confirmLabel: string;
    };
    run?: (items: Item[]) => Promise<unknown>;
    RenderModal?: (props: RenderModalProps<Item>) => ReactElement;
    /** A screen dialog that renders its own modal; the page shows it outside the list engine. */
    Dialog?: (props: ListTableActionDialogProps<Item>) => ReactElement;
}

/**
 * @since TBD
 */
export type ListTableActionDialogProps<Item extends ListTableItem = ListTableItem> = {
    items: Item[];
    onClose: () => void;
    refresh: () => Promise<unknown>;
    report: ReportOutcome;
    clearSelection: () => void;
};

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
    apiSettings: {apiRoot: string; apiNonce: string; table: {id?: string; columns: Array<object>}};

    //optional
    bulkActions?: Array<BulkActionsConfig> | null;
    pluralName?: string;
    singleName?: string;
    children?: ReactNode;
    rowActions?: LegacyRowActions | Function | ListTableRowAction[] | null;
    filterSettings?: FilterConfig[];
    paymentMode?: boolean;
    listTableBlankSlate: ReactNode;
    productRecommendation?: ReactNode;
    columnFilters?: Array<ColumnFilterConfig>;
    banner?: () => ReactNode;
    contentMode?: boolean;
    perPage?: number;
    statsConfig?: Record<string, StatConfig>;
    /**
     * The screen's config. It turns on view state:
     *
     * - absent: the view lives in memory only, as before. Add-on pages that do not pass it do not change.
     * - present on a standalone page: the view is kept in the URL and the search term in the tab's history.
     * - present with `contentMode` (a list in a details page tab): the URL and history are never touched.
     *
     * @since TBD
     */
    screen?: ListTableScreen;
}

export interface ListTablePageRef {
    refresh: () => Promise<unknown>;
}
