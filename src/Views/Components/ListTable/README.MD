# ListTable

The admin list screens (Campaigns, Donation Forms, Donations, Donors, Subscriptions, Event Tickets, and
the list tabs of the campaign and subscription details pages) render through `ListTablePage`, which
shows the rows with the WordPress list engine, `@wordpress/dataviews`. Why it is built this way, and
the version pin rule, are in [docs/architecture/list-tables.md](../../../../docs/architecture/list-tables.md).

## Folder

| Path | What it is |
|:--|:--|
| `ListTablePage/` | The page shell: `ListTablePageHeader` (title, header buttons, test-mode switch), then the list sheet with the stat tiles at its top, the notices and the list (the sheet and the tiles are on the full page only). `switchToLegacyView` saves the legacy view choice for the screens that pass `legacyView`. `types.ts` holds the public types. |
| `DataViewsListTable/` | Stock `<DataViews>` with Give's tokens. No data fetching. |
| `adapters/` | Pure functions from the columns and actions to DataViews fields and actions, and from the view to the request parameters (`viewToParameters`), plus the page size rule (`snapPerPage`). Jest tests sit next to them. |
| `hooks/` | `useListTableView` (the view and where its last change came from), `useListTableUrlState` (the view in the address, the search in the tab), `useListTableLayoutPreferences` (stores the layout, and Reset view), `useListTableSelection`, `useListTableQuery` (SWR + `apiFetch`), `useListTableActions` (the DataViews actions and the dialogs they open), `useListTableActionNotices`, and `useListActionSubmit` for a confirm modal or dialog. |
| `state/` | View state, with Jest tests next to it. Pure functions: `types.ts` (the `ListTableScreen` config and the closed list of screen keys), `screenDefaults` (a screen's defaults from its config and the column schema), `viewQuery` (reads and writes the `give-` URL parameters), `storedLayout` (the stored layout record), `assembleView`, `fieldOrder` (a column's default position, the pinned column last) and `fieldRules` (locked and pinned columns). Not pure, because they talk to the browser or the route: `listTableHistory` (`history`, the address and the tab's search copy), `layoutApi` (the layout route and the printed bootstrap) and `layoutWriteQueue` (the ordered layout writes and the once-per-page-view error notice). |
| `filters/` | The filter model, pure functions: declaration types, value text, date ranges, chip text, exclusive values, request parameters, the option route client, and the deprecated `filterSettingsToDeclarations` adapter (`legacyFilterSettings.ts`). |
| `ListTableFilters/` | The filter bar: primary filters, "Add filter", chips and Reset, read from a screen's `filters` declaration. Built from the `ListTableSearch`, `FilterChip`, `OptionsFilterControl`, `DateRangeFilterControl` and `EntityFilterControl` molecules. |
| `ListTableViewReset/` | The "Reset view" button before the stock view options, with its confirmation. |
| `ListTableViewMenu/` | The small "More view options" menu after the stock view options. Holds "Switch to Legacy View" on the screens that pass `legacyView`. |
| `ListTableNoticeArea/`, `notices/` | The one notice area above the list (action errors, system, onboarding, the daily recommendation) and the pure `pickDailyRecommendation`. |
| `StatTiles/`, `StatTile/` | The row of stat tiles and one tile. Tiles show the server's `formatted` text and can toggle a list filter (`tileFilters.ts`). |
| `GiveMark/`, `TestModeBadge/`, `HelpTip/` | The Give mark (atom), the "Test mode" badge in the page header and the small help button with its popover (molecules). |
| `ListTableSearch/` | The labeled search box, its `SearchHint` (the hint line and its help tip), and `useSearchHint`, which gives both the same ids so the box can sit in the toolbar row and the hint under it. |
| `InterweaveSSR/` | Renders server markup in a cell. Its `styles.scss` also styles the server-rendered status badge. |
| `Skeleton/`, `DashPlaceholder/` | Atoms: the loading block (use it instead of a spinner in the list area) and the empty-value dash with a screen reader label. |
| `layout/` | The 782 px breakpoint (`breakpoints.ts` and `_breakpoints.scss`, kept equal) and the narrow card view (`narrowView.ts`). |
| `actions/` | `runListAction`: runs an action, reports it, clears the selection when everything succeeded, reloads. Every action and dialog goes through it. |
| `rowActions/` | Ready-made actions: `trashRowActions` (Edit, Restore, Trash, Delete permanently) and `setStatusRowAction`. |
| `BulkActionConfirm/` | Body of a confirm modal: message, affected records, Cancel / Confirm. |
| `SetStatusDialog/` | One "Set status" action for screens whose endpoint has `/setStatus`. |
| `EmptyStates/` | One component for "No … match your search or filters" with Clear filters, and for the failed request with Try again. |
| `AsyncEntitySelect/` | The campaign and form entities the "Assign campaign" picker loads from. |
| `LegacyRowActions/` | Renders an old row-actions function in its own column (`useLegacyRowActionsField`). |
| `api.ts` | `ListTableApi.fetchWithArgs`, for a list endpoint's actions. |

## Styling scope

The A+ look is kept small and in a few known places. Keep it that way.

- **Tokens.** `layout/_tokens.scss` holds every color the list pages need that has no Give token. It
  has two mixins: `givewp-list-table-tokens` (included in `.page` of `ListTablePage`, and in
  `.givewp-list-table`) and `givewp-status-tone-tokens` (included in the status badge). A popover
  renders outside those roots, so popover content must not rely on these tokens.
  `layout/tokens.contrast.test.ts` measures the contrast of each pair. To fix a color, change it in
  `_tokens.scss`, not in the rule that uses it.
- **Table overrides.** They live only in `DataViewsListTable/_wp-look.scss`. Every rule starts at
  `.givewp-list-table .dataviews-view-table`. `wpLookScope.test.ts` compiles the file and checks the
  selectors.
- **The DataViews footer.** The A+ look adds no footer colors, type, sizes or controls. Nothing in
  `_wp-look.scss` may reach the footer or the pager. The footer layout rules that already exist
  (the sticky pin, the side padding and the total count) are in `DataViewsListTable/styles.scss`.
  The total count rules are to be removed "with `ListTableFooter` when the `@wordpress/dataviews` pin
  moves to 12.0.1 or later". `wpLookScope.test.ts` guards only `_wp-look.scss`, not the whole
  stylesheet.
- **The pin.** `@wordpress/dataviews` is pinned to `10.1.7` in `package.json`. The overrides in
  `_wp-look.scss` were written for that version. When the pin moves, check them again in the browser.
- **Rows.** Rows keep the stock DataViews height on purpose.
- **Status badge.** It is server-rendered markup. It is restyled in `InterweaveSSR/styles.scss`, with
  no PHP change and no class rename.
- **Page layout.** On the full page the list sits in one bordered white sheet (`.sheet` in
  `ListTablePage.module.scss`), with the stat tile band at its top, and the page fills the window
  height. The toolbar is one row: the search box, the filter bar, then the view options, with the
  search hint under the row. The search label sits out of the flow, above the box; its size is in
  `ListTableSearch/_label.scss`, and the toolbar row keeps that space above itself. Only the full page article carries the stable class
  `givewp-list-table-page` (an embedded list does not); the rule that paints the admin wrapper the page
  color uses `:has()` on it.
- **Embedded lists.** With `contentMode` there is no page header around the list, no sheet, no tiles
  and no window height. A rule for the shared list must still look right there.
- **Narrow screens.** Our own search input, chip toggle and chip remove button are 40 px tall at 782
  px and below (`$givewp-list-table-narrow`). The chip itself is 42 px tall there, because of its 1 px
  border. Stock buttons and selects are not changed.
- **Filter bar height.** "Add filter" and "Reset" (stock buttons) are 32 px tall above 782 px, so the chips, the buttons and the search box share one 32 px line. At 782 px and below they keep the stock size.

Components added for the A+ look:

| Level | Component |
|:--|:--|
| Atom | `GiveMark` |
| Molecule | `TestModeBadge`, `HelpTip`, `SearchHint` |
| Changed | `StatTile`, `FilterChip`, `ListTableSearch`, `EmptyStates`, `ListTableNoticeArea` (styles the shared `Notice` only through its `className`) |

Open items for the owner:

- The upgrade tile is left unchanged.
- The `#ff7b42` highlight is skipped.

## `<ListTablePage />` props

| Prop | Type | Required | Default |
|:--|:--|:--|:--|
| `title` | `string` | Yes | — |
| `apiSettings` | `{apiRoot, apiNonce, table: {columns}}` | Yes | — |
| `listTableBlankSlate` | `ReactNode`, shown when there are no records and no filters | Yes | — |
| `singleName`, `pluralName` | `string`, lowercase, translated | No | `'item'`, `'items'` |
| `rowActions` | `ListTableRowAction[]`, or a legacy render function | No | `null` |
| `bulkActions` | `BulkActionsConfig[]` (legacy shape, still supported) | No | `null` |
| `filters` | `FilterDeclaration[]`: the entity's filter declaration, shown as Give's labeled search and filter bar (see below) | No | — |
| `scope` | `Record<string, string \| number>`: fixed request parameters set by the host page (never a chip, never reset) | No | — |
| `testModeToggle` | `{label, ariaLabel}`: the header test-mode badge and switch | No | — |
| `filterSettings` | **Deprecated.** `FilterConfig[]`, kept so add-ons that pass it keep working. Mapped onto declarations; read only without `filters`. The `filterby` type is skipped | No | `[]` |
| `columnFilters` | `{column, filter(item, column, data)}[]` to render a column yourself | No | `[]` |
| `statsConfig` | `Record<string, StatConfig>` for the stat tiles (`/stats` endpoint). Tiles stay mounted with skeletons while loading and show the server's `formatted` text; `{label, filter?, upgrade?}` per key, where `filter: {filterId, value?}` makes the tile toggle that list filter | No | — |
| `paymentMode` | `boolean`, initial test mode | No | — |
| `contentMode` | `boolean`, no header or stats: the list sits in a details page tab | No | `false` |
| `perPage` | `number`, 1–100, snapped to the offered sizes (10, 20, 30, 50, 100) | No | `20` |
| `screen` | `ListTableScreen`: turns on view state (see below); its `fieldRoles` pick the card fields at 782 px and below | No | — |
| `notices` | `ListTableNotice[]`: system and onboarding notices for the notice area | No | — |
| `recommendations` | `{options, dismissed, apiNonce}`: one product tip a day in the notice area | No | — |
| `legacyView` | Adds "Switch to Legacy View" to the "More view options" menu | No | — |
| `banner`, `children` | as before | No | — |
| `productRecommendation` | **Deprecated.** Pass `recommendations`. Still rendered, last in the notice area | No | — |

The `ref` exposes `refresh()`, which reloads the rows and the stat tiles.

## Filters: the `filters` prop

A screen declares its filters once, in a `filters.ts` file next to it, and passes the list as
`filters`:

```ts
export const donationsFilters: FilterDeclaration[] = [
    {kind: 'entity', id: 'campaign', label: __('Campaign', 'give'), isPrimary: true, source: 'campaign',
        multiple: true, parameter: 'campaignId', allowNone: true, legacyUrlKey: 'campaignId'},
    {kind: 'options', id: 'status', label: __('Status', 'give'), isPrimary: true, multiple: true,
        options: 'statuses', parameter: 'status', exclusiveValues: ['trash']},
    {kind: 'date', id: 'date', label: __('Donation date', 'give'), parameters: {start: 'start', end: 'end'}},
    {kind: 'search', id: 'search', label: __('Search donations', 'give'), hint: __('Searches …', 'give'),
        parameter: 'search'},
];
```

- Kinds: `options` (a fixed list, or `'statuses'` for the server's vocabulary in
  `apiSettings.table.statuses`), `entity` (campaigns, forms or donors searched on the server), `date`
  (presets and a day range) and `search` (the labeled search box; its `hint` must say what is searched
  and how it matches).
- `isPrimary` filters are always shown; the others are in "Add filter". `whenEmpty`,
  `exclusiveValues`, `allowNone` and `legacyUrlKey` are described in
  [docs/architecture/list-tables.md](../../../../docs/architecture/list-tables.md#filters).
- Build the list at module level, not inside the component, so it keeps one identity.
- Reset shows only when the filters differ from the screen's defaults (test mode on a test site).
- An add-on that still passes `filterSettings` gets them mapped by `filterSettingsToDeclarations()`.
  Move to `filters` when you can.

## View state: the `screen` prop

A screen passes one small config, kept next to the screen in a `listTableScreen.ts` file:

```ts
const listTableScreen: ListTableScreen = {key: 'donations', defaults: {hiddenFields: ['paymentType']}};
```

- `key` is one of the closed list in `state/types.ts`, which must match
  `src/Framework/ListTable/Layouts/ListTableScreens.php` (`screenKeys.test.ts` checks it). Shared code
  in this folder never names a key (`coreScreens.test.ts` checks it).
- `defaults` only overrides the server column schema: `hiddenFields`, `visibleFields`, `perPage` and
  `density` (default `balanced`).

What the prop turns on:

| | No `screen` | `screen` on a standalone page | `screen` with `contentMode` |
|:--|:--|:--|:--|
| Page, page size, sort and filters in the URL (`give-` parameters) | Recognized filter parameters (bare legacy names and `give-` names) apply to the first view and first request; never written | Yes | No |
| Search term kept in the tab (history entry and `sessionStorage`) | No | Yes | No |
| Columns, order, density and page size stored per user | No | Yes | Yes |
| Reset view | Yes, for this page view only | Yes | Yes |

The identifying column (the title column, or `id`) is locked visible, and a legacy row-actions column
is locked and always last. Add-on pages that pass no `screen` behave as before. The full rules are in
[docs/architecture/list-tables.md](../../../../docs/architecture/list-tables.md#view-state).

## Row actions

```tsx
const rowActions: ListTableRowAction[] = [
    {id: 'edit', label: __('Edit', 'give'), isPrimary: true, href: (item) => `…&id=${item.id}`},
    {
        id: 'trash',
        label: __('Trash', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !hasStatus(parameters, 'trash'),
        confirm: {title: __('Trash donations', 'give'), body: () => <p>…</p>, confirmLabel: __('Move to trash', 'give')},
        run: (items) => api.fetchWithArgs('/trash', {ids: idsOf(items)}, 'DELETE'),
    },
];
```

- `isPrimary`: the one inline action ("Edit", or "Restore" in a trash view).
- Exactly one of `href`, `run`, `RenderModal` (a body for DataViews' modal) or `Dialog` (a component
  with its own modal, opened by the page).
- `run` gets one item from a row's menu and the selected items from the selection bar. Its reply goes
  through `normalizeActionResult` and shows as a snackbar or an error notice.
- `isEligible(item)` leaves a row out of the action; `isVisible(data, parameters)` hides the action.
- A list with a trash view gets these four actions from `trashRowActions`, and "Set status" from
  `setStatusRowAction`. A `Dialog` that changes records submits with `useListActionSubmit`, and gets
  the request as a prop, so it never calls the API itself.
