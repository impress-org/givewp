import type {Density, Filter, ViewTable} from '@wordpress/dataviews';
import type {FilterDeclaration, StatusVocabularyEntry} from '../filters/types';

/**
 * The view of a list table. The list always renders the DataViews table layout.
 *
 * @since TBD
 */
export type ListTableView = ViewTable;

/**
 * The screens that can keep a view in the URL and store a layout. Keep this list equal to the keys in
 * `src/Framework/ListTable/Layouts/ListTableScreens.php`; `screenKeys.test.ts` checks it.
 *
 * @since TBD
 */
export const LIST_TABLE_SCREEN_KEYS = [
    'campaigns',
    'donation-forms',
    'campaign-forms',
    'donations',
    'donors',
    'subscriptions',
    'event-tickets',
    'subscription-donations',
] as const;

/**
 * @since TBD
 */
export type ListTableScreenKey = (typeof LIST_TABLE_SCREEN_KEYS)[number];

/**
 * The density of the table, as DataViews offers it.
 *
 * @since TBD
 */
export type ListTableDensity = Density;

/**
 * One config object per screen, kept next to the screen. The server column schema is the base; the
 * defaults here only override it.
 *
 * @since TBD
 */
export interface ListTableScreen {
    key: ListTableScreenKey;
    defaults?: {
        /** Default 30, snapped to the offered page sizes. */
        perPage?: number;
        /** Default `balanced`. */
        density?: ListTableDensity;
        /** Hidden by default, on top of the server schema. */
        hiddenFields?: string[];
        /** Shown by default, on top of the server schema. */
        visibleFields?: string[];
    };
}

/**
 * How it is shown: the part of the view kept in the stored layout.
 *
 * @since TBD
 */
export type ViewLayoutPart = Required<Pick<ListTableView, 'fields' | 'perPage'>> & {density: ListTableDensity};

/**
 * The defaults of one screen, resolved once per page from its config and the server column schema.
 *
 * @since TBD
 */
export interface ScreenDefaults {
    /** Visible field ids, in order. */
    fields: string[];
    /** Every field id that can be shown, in server order, with the pinned field last. */
    fieldOrder: string[];
    density: ListTableDensity;
    perPage: number;
    sort: Required<ListTableView>['sort'];
    titleField?: string;
    /** Set to false when the server schema hides the title column; absent otherwise. */
    showTitle?: false;
    /** Fields that can never be hidden. */
    lockedFields: string[];
    /** A field that always stays last. */
    pinnedLast?: string;
    /** Filters applied by default (test mode on a test site, a standalone hidden filter of the old settings). */
    filters: Filter[];
}

/**
 * The view parts read from or written to the URL.
 *
 * @since TBD
 */
export interface ViewQuery {
    page?: ListTableView['page'];
    perPage?: ListTableView['perPage'];
    sort?: ListTableView['sort'];
    filters: Filter[];
    testMode?: boolean;
}

/**
 * What the URL parser and serializer need to know about a screen.
 *
 * @since TBD Read the filters only from the declarations; drop `filterSettings`.
 * @since TBD Add the filter declarations, the status vocabulary and the test-mode switch.
 * @since TBD
 */
export interface ViewQueryContext {
    /** The screen's filter declarations: one URL parameter per bar filter. */
    filters: FilterDeclaration[];
    /** The server's status vocabulary, which checks `options: 'statuses'` values. Default none. */
    statuses?: StatusVocabularyEntry[];
    /** The screen has the test-mode switch. Default false. */
    testModeToggle?: boolean;
    /** Ids of the columns with `sortable: true`. */
    sortableFields: string[];
    defaults: ScreenDefaults;
    /** The site's payment mode (`paymentMode` prop): true when the site is in test mode. */
    siteTestMode: boolean;
}

/**
 * The parsed URL. `corrected` means the address must be replaced: an invalid part was dropped or a
 * legacy bare name was read. `legacyKeys` are the bare names to remove when the address is written.
 *
 * @since TBD
 */
export type ParseResult = {query: ViewQuery; corrected: boolean; legacyKeys: string[]};

/**
 * The layout stored for a user and a screen. It holds only what differs from the screen defaults.
 *
 * @since TBD
 */
export interface StoredLayout {
    version: 1;
    /** Only fields whose visibility differs from their default. */
    visibility?: Record<string, boolean>;
    /** The full visible order, only when it differs from the default order. */
    order?: string[];
    density?: ListTableDensity;
    perPage?: number;
}
