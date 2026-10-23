# ListTable

The admin list screens (Campaigns, Donation Forms, Donations, Donors, Subscriptions, Event Tickets, and
the list tabs of the campaign and subscription details pages) render through `ListTablePage`, which
shows the rows with the WordPress list engine, `@wordpress/dataviews`. Why it is built this way, and
the version pin rule, are in [docs/architecture/list-tables.md](../../../../docs/architecture/list-tables.md).

## Folder

| Path | What it is |
|:--|:--|
| `ListTablePage/` | The page shell: header, test-mode switch, stat tiles, notices, and the list. `types.ts` holds the public types. |
| `DataViewsListTable/` | Stock `<DataViews>` with Give's tokens. No data fetching. |
| `adapters/` | Pure functions from the screens' settings to DataViews fields, view and actions, and from the view to the request parameters. Jest tests sit next to them. |
| `hooks/` | `useListTableView`, `useListTableSelection`, `useListTableQuery` (SWR + `apiFetch`), `useListTableActionNotices`. |
| `BulkActionConfirm/` | Body of a confirm modal: message, affected records, Cancel / Confirm. |
| `SetStatusDialog/` | One "Set status" action for screens whose endpoint has `/setStatus`. |
| `EmptyStates/`, `ListTableErrorState/` | "No … match your search or filters" with Clear filters, and the failed-request state. |
| `LegacyRowActions/` | Renders an old row-actions function in its own column. |
| `api.ts` | `ListTableApi.fetchWithArgs`, for a list endpoint's actions. |

## `<ListTablePage />` props

| Prop | Type | Required | Default |
|:--|:--|:--|:--|
| `title` | `string` | Yes | — |
| `apiSettings` | `{apiRoot, apiNonce, table: {columns}}` | Yes | — |
| `listTableBlankSlate` | `ReactNode`, shown when there are no records and no filters | Yes | — |
| `singleName`, `pluralName` | `string`, lowercase, translated | No | `'item'`, `'items'` |
| `rowActions` | `ListTableRowAction[]`, or a legacy render function | No | `null` |
| `bulkActions` | `BulkActionsConfig[]` (legacy shape, still supported) | No | `null` |
| `filterSettings` | `FilterConfig[]` | No | `[]` |
| `columnFilters` | `{column, filter(item, column, data)}[]` to render a column yourself | No | `[]` |
| `statsConfig` | `Record<string, StatConfig>` for the stat tiles (`/stats` endpoint) | No | — |
| `paymentMode` | `boolean`, initial test mode | No | — |
| `contentMode` | `boolean`, no header or stats: the list sits in a details page tab | No | `false` |
| `perPage` | `number`, 1–100 | No | `30` |
| `banner`, `productRecommendation`, `children` | as before | No | — |

The `ref` exposes `refresh()`, which reloads the rows and the stat tiles.

## Row actions

```tsx
const rowActions: ListTableRowAction[] = [
    {id: 'edit', label: __('Edit', 'give'), isPrimary: true, href: (item) => `…&id=${item.id}`},
    {
        id: 'trash',
        label: __('Trash', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !parameters.status?.includes('trash'),
        confirm: {title: __('Trash donations', 'give'), body: () => <p>…</p>, confirmLabel: __('Move to trash', 'give')},
        run: (items) => api.fetchWithArgs('/trash', {ids: items.map((item) => item.id).join(',')}, 'DELETE'),
    },
];
```

- `isPrimary`: the one inline action ("Edit", or "Restore" in a trash view).
- Exactly one of `href`, `run`, `RenderModal` (a body for DataViews' modal) or `Dialog` (a component
  with its own modal, opened by the page).
- `run` gets one item from a row's menu and the selected items from the selection bar. Its reply goes
  through `normalizeActionResult` and shows as a snackbar or an error notice.
- `isEligible(item)` leaves a row out of the action; `isVisible(data, parameters)` hides the action.
