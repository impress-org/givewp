# List tables, caching, and large-data behavior

Admin list tables are where GiveWP meets sites with hundreds of thousands of donations. Almost
every performance regression in the admin has originated in a list table column. This documents
the rendering pipeline, the two different caching strategies bolted onto it, and the failure modes
that only appear at scale.

## The archive screens have two implementations, and the user picks

Donations, Donors, Subscriptions, and Donation Forms each have **two admin implementations**: the
modern React pages described below, and the legacy WordPress admin screens they replaced. Both are
shipped, both are maintained, and which one a given user sees is **their own per-user setting**.

This applies to those four archive screens only — it is not a general statement about the admin.
Settings, for instance, have no modern counterpart at all. See [admin-ui.md](admin-ui.md) for the
full map.

The choice lives in user meta — `_give_{slug}_archive_show_legacy` — and is written by a REST
endpoint per domain (`Donations/Endpoints/SwitchDonationView` and its siblings for donors,
subscriptions, and forms, at `give-api/v2/admin/{slug}/view` with an `isLegacy` param).

Each domain's `ServiceProvider` reads it on `admin_menu` and registers the modern page **only if
the meta is empty**:

```php
$showLegacy = get_user_meta($userId, '_give_donations_archive_show_legacy', true);

// only register new admin page if user hasn't chosen to use the old one
if (empty($showLegacy)) {
    give(DonationsAdminPage::class)->registerMenuItem();
}
```

Two consequences worth internalizing:

- **A bug report about "the donations page" is ambiguous** until you know which UI the reporter is
  on. The fix location is completely different, and neither implementation shares code with the
  other.
- **A fix applied to one is not applied to the other.** If a customer-reported issue exists in both,
  it needs two fixes — and if you only reproduce on the modern page, confirm the reporter wasn't on
  legacy before closing.

The legacy screens live under `includes/admin/`; the modern ones are the `src/<Domain>/` React
pages and REST endpoints this document describes.

## The pipeline

A modern list table has three parts:

1. **A `ListTable` subclass** (`src/<Domain>/ListTable/`) extending
   `Give\Framework\ListTable\ListTable`, declaring `ModelColumn` objects.
2. **A REST endpoint** (`src/<Domain>/Endpoints/List*.php`) that runs the query, calls
   `$listTable->items($models)`, and returns rows.
3. **A React page** (`src/Views/Components/ListTable/`) that renders the rows through the WordPress
   list engine, `@wordpress/dataviews`. See [The React page](#the-react-page-dataviews) below and the
   folder's [README](../../src/Views/Components/ListTable/README.MD).

Donations, Donors, Subscriptions, Campaigns, Event Tickets and v2 Donation Forms each have their
own set. The v2 forms list is a different, older system — see
[v2 forms list: async columns, not caching](#v2-forms-list-async-columns-not-caching) below.

## The N+1 trap

`ListTable::items()` loops rows, and inside each row loops columns, calling
`$column->getCellValue($model)`. **Any query inside `getCellValue()` runs once per row.** A column
that looks up a donor name, a donation count, or a goal figure turns a 30-row page into 30 extra
queries — and at 100 rows per page with several such columns, into hundreds.

This is the single most common performance mistake in this subsystem, and it doesn't show up in
local testing because a dev database has twenty donations in it.

The sanctioned fix is the **bulk-data path** added in 4.0: the endpoint fetches everything for the
whole page up front, hands it to `$listTable->setData($data)`, and columns read from it via the
`ListTableData` trait instead of querying:

```php
$column->isUsingListTableData(); // $useData, defaults to true
$column->getListTableData();     // whatever the endpoint passed to setData()
```

`CampaignsDataRepository` is the reference implementation, and its docblock says so outright:
*"Used to optimize the campaigns list table performance and to avoid n+1 problems. Instead of
doing expensive queries in multiple columns in each row, this class loads everything upfront for
multiple campaigns."*

If you add a column that needs data the model doesn't already carry, extend the bulk fetch. Don't
query in the column.

## Failures are swallowed, per row

`ListTable::safelyGetCellValue()` wraps every cell in a try/catch so one broken column can't fatal
the whole table. On exception the cell renders "Something went wrong…" and writes a
`Log::error()` entry.

Consequence: a column that throws produces **one log row per rendered table row**. A broken column
on a 100-row page writes 100 log entries per page view, into the database. Sites have filled their
log tables this way. If you're debugging slow admin pages and the log table is huge, suspect a
throwing column before anything else.

## Two caches, two designs

### Campaigns: an options-table cache

`CampaignsDataRepository::campaigns($ids)` (4.8.0) stores aggregated per-campaign stats in two
WordPress options:

- `give_campaigns_data` — `amounts`, `donationsCount`, `donorsCount`
- `give_campaigns_subscriptions_data` — the same, for recurring

Writes come from `Campaigns/Actions/CacheCampaignData`, hooked to `give_insert_payment`,
`give_update_payment_status`, `give_recurring_add_subscription_payment`, and
`givewp_campaigns_merged`. It dispatches through Action Scheduler
(`as_enqueue_async_action('givewp_cache_campaign_data', …)`), so the refresh is asynchronous — the
list table can show stale figures until the queue drains. On a site with a stuck or slow Action
Scheduler queue, campaign stats simply stop updating, and nothing surfaces that as an error.

Things to know before touching this:

- **Read-through for missing campaigns.** `campaigns()` serves cached ids from the options and
  queries only the ids that are missing, then writes them into the cache. A campaign with no
  donations gets a zero row so it counts as cached and is not re-queried on every page load.
  Before this, a warm cache returned early and a campaign absent from it showed no stats until
  `CacheCampaignData` ran for it.
- **The cache is live-mode only.** `CampaignsDataQuery` filters by `give_is_test_mode()` but the
  options carry no mode, so a test-mode write would poison the live figures. In test mode
  `campaigns()` queries every id directly and writes nothing. `CacheCampaignData` and the
  `CacheCampaignsData` migration force live mode through the `give_is_test_mode` filter while they
  run, so a refresh queued by a live donation still lands even if test mode was switched on before
  Action Scheduler picked it up.
- **Both readers and all writers use `give_campaigns_subscriptions_data`.** Until the fix, the two
  readers used a singular key, so the subscriptions cache never hit. The
  `Campaigns/Migrations/FlushCampaignsDataCache` migration drops both options once so no site
  starts reading data written while the keys disagreed.
- **The options are written with a plain `update_option()`**, no explicit `$autoload`. The plugin
  requires WP 6.6+, where WordPress applies its own autoload heuristic and keeps large values out,
  but a modest-sized value on a many-campaign site can still be autoloaded on every request. Check
  `wp_options.autoload` for these two keys before assuming.
- **Full rebuild** is the `Campaigns/Migrations/CacheCampaignsData` migration, which deletes both
  options and repopulates. That's the recovery path when the cache is wrong.

### v2 forms list: async columns, not caching

The v2 donation forms list (`src/DonationForms/AsyncData/`, 3.16.0) took the opposite approach.
Rather than pre-fetching, it renders the expensive columns — goal, donation count, revenue — as
skeleton placeholders and fills them in over AJAX after page load:

```php
AsyncDataHelpers::getSkeletonPlaceholder(); // <span class="give-skeleton js-give-async-data">
```

`Actions/GetAsyncFormDataForListView` serves those requests, caching each form's result in a
transient `give_async_data_for_list_view_form_{formId}` for **5 minutes**.

That transient has **no invalidation** — nothing deletes it on donation insert or form update. It
is TTL-only, so a new donation can take up to five minutes to appear in these columns. Either way,
it reliably generates "the numbers are wrong" reports that aren't bugs.

**[Judgement]** Whether the missing invalidation is a deliberate trade-off or an oversight isn't
recorded anywhere — nothing in the code says. Don't add invalidation on the assumption it was
forgotten; ask first, since the whole point of the transient is to keep expensive queries off the
page load.

Behavior is controlled by constants and a filter, all in `AdminFormListViewOptions`:

| Toggle | Type | Effect |
|:--|:--|:--|
| `GIVE_IS_ALL_STATS_COLUMNS_ASYNC_ON_ADMIN_FORM_LIST_VIEWS` | constant | master switch, defaults to `true` |
| `GIVE_IS_GOAL_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS` | constant | per-column override |
| `GIVE_IS_DONATIONS_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS` | constant | per-column override |
| `GIVE_IS_REVENUE_COLUMN_ASYNC_ON_ADMIN_FORM_LIST_VIEWS` | constant | per-column override |
| `givewp_use_cached_form_stats_meta_keys_on_admin_form_list_views` | filter | read stale cached meta instead of querying live |

These exist because large sites need an escape hatch. A support escalation about an unusably slow
forms list usually ends with one of these being set.

`FormGrid/` applies the same treatment to the front-end form grid shortcode.

## Pagination cost, and the donations list pattern

`List*` endpoints run two queries per page: the page of models (`limit`/`offset`) and a separate
`count()` for the total (`ListDonations::getTotalDonationsCount()`). On a large donations table
the COUNT with filters applied is frequently the slower of the two, and it runs on every page
view, including page 400.

If you add a filter, add it to *both* `getDonations()` and `getTotalDonationsCount()` — they build
their conditions through the shared `getWhereConditions()`, and bypassing it makes the total
disagree with the rows.

**Do not page the fully joined model query.** `DonationRepository::prepareQuery()` LEFT JOINs
`give_donationmeta` once per model property (about 30 joins). Applying `ORDER BY ... LIMIT ...
OFFSET` to that makes the database build the whole joined set before it can skip rows: at a
million donations that was two minutes for page one and longer for the last page. The donations
endpoint instead does what `WP_Query` always did for the legacy screen:

1. Select the page of IDs from `posts` with only the meta the filters and the sort expression
   need attached (`getWhereConditions()` returns those dependencies; `getSortDependencies()` adds
   the sort's).
2. Hydrate those IDs through `prepareQuery()->whereIn('ID', $ids)` and re-apply the sort.

Two rules that fall out of the same profiling, and apply to any endpoint that filters on meta:

- **Filter on meta through an indexed subquery, not a joined column with an `OR`.** Test mode is
  `ID NOT IN (SELECT donation_id FROM give_donationmeta WHERE meta_key = '_give_payment_mode' AND
  meta_value = 'test')`, and name or email search is `ID IN (... WHERE meta_key IN (first, last)
  AND meta_value LIKE 'term%')`. A `LEFT JOIN ... WHERE meta IS NULL OR meta <> 'test'`, or a
  `HAVING` on a joined column, forces the full join to materialise first. And group any `OR` you
  do write with a closure `where(function ($q) { ... })`: an ungrouped `orWhere()` binds looser
  than the preceding `AND`s and silently drops them, which is how trashed donations once leaked
  into the live list.
- **Search is prefix match.** `LIKE 'term%'` walks the `(meta_key(100), meta_value(82))` index;
  `LIKE '%term%'` scans every row for that key. Typing "smi" finds Smith; "mit" does not.

`give_donationmeta` carries two composite indexes for this, `(donation_id, meta_key(100))` for the
per-property joins and `(meta_key(100), meta_value(82))` for value lookups, added by
`Donations/Migrations/AddIndexesToDonationMetaTable`, which also drops the single-column
`donation_id` and `meta_key` indexes they make redundant. The same indexes are what let
`CampaignsDataQuery`, `CampaignDonationQuery`, and `DonorStatisticsQuery` answer in milliseconds
at a million rows without code changes. They do nothing for `ORDER BY` on a meta value or an
expression over several (sorting the list by amount is still a full scan), and nothing for
aggregates that must touch every row; those remain the case for moving donations into their own
table.

## The React page: DataViews

Every list screen renders through `ListTablePage`, which hands the rows to `@wordpress/dataviews`. The
screen's settings stay the same shape (columns from `ModelColumn::toArray()`, filter settings, bulk
actions); small pure functions in `ListTable/adapters/` turn them into DataViews fields, default
filters and actions, and turn the view back into the endpoint's request parameters
(`viewToParameters` is the only place that knows those names). The view itself is assembled in
`ListTable/state/` (see [View state](#view-state)). Sorting, filtering, searching and paging stay on
the server.

- **Stock behavior inside the list.** Toolbar, filter chips, row menu, selection bar, loading state
  and pagination are DataViews' own, as WordPress core uses them. Give adds the page header, stat
  tiles, empty states, confirm bodies and action notices around it. Where the pinned DataViews lags
  the current WordPress release, a short commented CSS rule in `DataViewsListTable/styles.scss`
  matches the newer behavior (row checkboxes always visible).
- **One layout change: the selection bar sits above the table.** In the Site Editor the list fills
  the window, so the footer with the selection bar is always in view. On Give's pages the list is
  part of a longer page, and the footer ends up below the last row. `DataViewsListTable` uses the
  DataViews sub-components to also show the stock `BulkActionToolbar` in place of the search row
  while rows are selected; the stock footer keeps its own copy next to the pagination.
- **Version pin.** DataViews is bundled, not provided by WordPress, while the `@wordpress/components`
  and other `wp-*` packages it uses come from the site. So the pin is the DataViews line the
  **oldest supported** WordPress ships (`readme.txt` "Requires at least"), currently 10.1.x for 6.9.
  A newer line needs script handles and components the floor release does not have. Move the pin
  only when the floor moves.
- **One shared chunk.** Webpack puts DataViews and the shared list code in `build/givewp-list-table.js`
  (`splitChunks.cacheGroups.listTable`). It has no `.asset.php` dependencies of its own, so
  `Give\Framework\ListTable\Actions\RegisterListTableAssets` registers the `givewp-list-table`
  handle and every list screen's script depends on it. `bin/list-table-bundle-size.mjs` checks each
  screen's JavaScript against the 100 KB gzip budget.
- **Actions are data.** Screens pass `ListTableRowAction[]`: "Edit" (or "Restore" in a trash view) is
  the inline action, the rest are in the row menu, and `supportsBulk` makes one action serve rows and
  selections. DataViews lists every bulk action in each row's menu, so a separate bulk action for the
  same job would show twice. An action with its own modal uses `Dialog`; the dialog gets the request
  as a prop and never calls the API itself. Every action and dialog runs through `runListAction`, so
  results, selection clearing and reloading behave the same everywhere: a snackbar for what changed,
  an error notice for what failed. Lists with a trash view build their actions with
  `trashRowActions` and `setStatusRowAction`. Old-style `bulkActions` configs and a row-actions
  render function still work through the adapters.
- **Add-on bulk actions.** `window.GiveDonations.addonsBulkActions` (localized as `[]` and filled by
  add-ons) is read by the Donations screen and passed as its `bulkActions`. The shared page reads no
  screen's globals.
- **No preloaded rows.** The list fetches its own first page; do not localize rows into the page (see
  [pii.md](pii.md)).

## View state

A screen opts in by passing a `screen` config to `ListTablePage` (`{key, defaults}`, kept next to the
screen in a `listTableScreen.ts` file). Without one the view lives in memory only, which is what an
add-on page that never heard of the prop still gets. The code is in `ListTable/state/` (pure
functions) and `ListTable/hooks/useListTableUrlState.ts` and `useListTableLayoutPreferences.ts`.

### One view object, two projections

There is one DataViews view per page, owned by `useListTableView`. Nothing else holds a copy of the
page, sort, filters or columns. Two things are derived from it:

- **What is shown** (page, page size, sort, filters, test mode) is written to the address.
- **How it is shown** (visible columns and their order, density, page size) is stored per user.

`assembleView` builds the view from, in order of precedence: the screen defaults
(`resolveScreenDefaults`, from the server column schema plus the config's `hiddenFields`,
`visibleFields`, `perPage` and `density`, default `balanced`), then the stored layout, then the
address, then the tab's search. Page size is in both projections; the address wins.

Every change records where it came from (`ViewChangeOrigin`): a `user` change adds a history entry, a
`correction` (an invalid address, a page past the last one after records were removed) replaces it,
`history` (back and forward) writes nothing, and `reset` behaves like a user change in the address.
Only a `user` change to the layout, or a reset, is stored.

### The address

`viewQuery.ts` is the only code that knows the parameter names.

- **Every written parameter has the `give-` prefix**, in kebab case: `give-paged`, `give-per-page`,
  `give-orderby`, `give-order`, `give-test-mode`, and one per filter (`parameterName()`, for example
  `give-campaign-id` for `campaignId`). The WordPress admin uses the bare names (`page`, `paged`,
  `s`, `post_type`) itself, and the details pages that embed a list use `page`, `tab` and `id`.
- **Only what differs from the defaults is written.** A view equal to the defaults leaves the
  address bare. Test mode is written only when it differs from the site's payment mode, so a link
  without it follows the site.
- **Only ids and enumerated values.** Each value must match `[A-Za-z0-9_-]{1,64}` and be one the
  screen offers (a sortable column, a filter option, a page size in `PER_PAGE_SIZES`, an id for a
  campaign or form filter). Anything else is dropped, the result is marked `corrected`, and the
  address is replaced. Nothing read from the address is rendered as markup. "Filter by" options whose
  real value is a moment in time keep a stable option value (`7`, `30`, `90` for "Period") and turn
  it into the request value with `toApiValue`, fixed per view so the request does not change on
  every render.
- **Legacy bare names are read on load and rewritten.** Links made before the prefix used the filter's
  own name for `select`, `campaignselect`, `formselect` and `hidden` filters (`?campaignId=4`). Those
  are read with the same rules when the `give-` form is missing (the prefixed one wins), and the
  address is then rewritten with `replaceState`, so no history entry is added. A bare `search` is
  never read: it is only removed.
- **Other parameters are kept as they are,** in their order. The writer replaces only the screen's
  own keys (`viewQueryKeys`) and the legacy names it read.
- **Length limit.** The writer keeps the whole address under 2000 characters by dropping filter
  values from the end.

### The search term stays in the tab

The search can be a donor's name or email, so it never goes in the address, a bookmark, a shared
link or the stored layout. It lives in the tab's history entry (`history.state.giveListTable`, keys
belonging to others are kept), so reload, back and forward in that tab restore it and a new tab
usually opens without it. A tab that the page opens itself (`window.open`, or a link without
`noopener`) starts with a copy of this tab's `sessionStorage`, so it can restore the search when it
opens the same address.

**WordPress clears `history.state` on every admin page load.** Core's admin canonical-URL script
runs `history.replaceState(null, …)` after the page loads, so after a reload the entry's state is
gone. `listTableHistory.ts` therefore also writes the term to `sessionStorage`, keyed by screen and
address (`give-list-search:<screen>:<path and query>`), and reads that copy only when the entry has
no state for the screen. `sessionStorage` belongs to the tab and dies with it, so the term still
never outlives the tab. Blocked storage only loses the copy.

A search-only change within 1.5 seconds of the previous one replaces the history entry instead of
adding one (`SEARCH_COLLAPSE_MS`), so one typed term is one entry.

### The stored layout

`src/Framework/ListTable/Layouts/` stores one record per user, per screen, per site: a user option
named `give_list_table_layout_<screen>`, which WordPress prefixes with the site's table prefix, so a
multisite network keeps a separate record on each site.

- **The screens are a closed list.** `ListTableScreens` maps each key to the permission of the admin
  page that shows it (an embedded table uses its host details page's). There is no filter. The
  client's `LIST_TABLE_SCREEN_KEYS` in `state/types.ts` must hold the same keys, and
  `screenKeys.test.ts` checks it. Shared code in `ListTable/` never names a key;
  `coreScreens.test.ts` checks that too.
- **The record holds only differences from the defaults** (`toStoredLayout`): the visibility of the
  columns the user changed, the full order only when it differs, density and page size only when
  they differ. A column the user never touched keeps following its default, so a later change to the
  server schema or the config still reaches that user. A layout equal to the defaults deletes the
  record.
- **The route** is `givewp/v3/list-table-layouts/<screen>` (GET, PUT, DELETE): signed in (401), a
  known screen (404), the screen's view capability (403). It only ever reads or writes the signed-in
  user's own record. `ListTableLayoutValidator` accepts version `1`, at most 50 column ids matching
  `[A-Za-z0-9_-]{1,64}`, one of the three densities, and a page size from 1 to 100.
- **Writes** wait one second after the last change, run one after another, and are sent at once with
  `keepalive` on `pagehide`. A failed write keeps the change on screen and shows one notice per page
  view; the next write sends the whole record again.
- **Locked columns.** The identifying column (the title column, or `id` when there is none) cannot
  be hidden, and the legacy row-actions column, when a screen uses one, cannot be hidden and stays
  last (`fieldRules.ts`).

### Embedded tables

A table inside another page (the campaign details "Forms" tab, the subscription details "Donations"
tab) passes `contentMode`. With `contentMode` the address and the tab state are never read or written
(`urlScreenKey()` returns nothing), because the host page owns the address. Its layout is still
stored under its own key (`campaign-forms`, `subscription-donations`), separate from the standalone
screen it reuses.

### The first request already carries the view

`ListTablePage` resolves the defaults, applies the stored layout and reads the address and the tab's
search inside a `useState` initializer, before the first render. So the first list request is
already the URL's view, and there is no request for the default view followed by a second one. The
stored layout reaches the page the same way: `PrintListTableLayouts` (on `admin_enqueue_scripts`,
priority 6, right after the `givewp-list-table` handle is registered at 5) prints
`window.GiveListTableLayouts = {path, layouts}` with `wp_add_inline_script(…, 'before')`. Only the
signed-in user's own records for the screens they can open are printed, each validated again, and
WordPress prints inline data only with its handle, so it appears only on list pages. See
[pii.md](pii.md) for why this is allowed.

### Reset view

`ListTableViewReset` is a button next to the stock view options, shown only while the layout
differs from the defaults. After a confirmation it puts the columns, density and page size back to
the defaults and deletes the stored record. Filters, sort and search stay. When the page size
changes, the page becomes the one that holds the row the current page starts with (page 3 of 50 rows
becomes page 4 of 30), and that change adds a history entry like any other. Without a `screen`
config it still resets the layout for the current page view; nothing is stored.

- **[Judgement]** The closed screen list means an add-on's own list page cannot keep its view in
  the URL or store a layout. Opening the list to add-ons (a filter on the PHP list and a way to
  register the client key) is a product decision, not something to add as part of a screen change.

## Checklist for a new column

- Does it need data beyond what the model already loaded? → extend the endpoint's bulk fetch and
  read it via `getListTableData()`. Never query in `getCellValue()`.
- Can it throw? → it will be caught and logged once per row. Guard instead of relying on the
  catch.
- Is it expensive and non-essential? → consider the async placeholder pattern rather than blocking
  the page.
- Sortable or filterable? → the filter must reach `getWhereConditions()` so the count query sees it
  too.

## Related

- `src/Framework/ListTable/` — `ListTable`, `ModelColumn`, and the `Columns`, `IsFilterable`,
  `ListTableData` concerns
- `src/Views/Components/ListTable/` — the React side ([README](../../src/Views/Components/ListTable/README.MD))
- `src/Framework/ListTable/Actions/RegisterListTableAssets.php` — the shared `givewp-list-table` script handle
- `src/Framework/ListTable/Layouts/` — the stored layout: screens, store, validator, route and the printed bootstrap
- `src/Framework/ListTable/Views/GoalProgress.php` — the goal cell the Campaigns and Donation Forms lists share
- `src/Framework/QueryBuilder/` — what the endpoints build queries with ([README](../../src/Framework/QueryBuilder/README.md))
