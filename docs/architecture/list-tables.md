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
  `HAVING` on a joined column, forces the full join to materialize first. And group any `OR` you
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

Every list screen renders through `ListTablePage`, which hands the rows to `@wordpress/dataviews`.
Columns come from `ModelColumn::toArray()`; small pure functions in `ListTable/adapters/` turn them
and the actions into DataViews fields and actions, and turn the view back into the endpoint's request
parameters (`viewToParameters`). Filters are declared per entity (see [Filters](#filters)). The view
itself is assembled in `ListTable/state/` (see [View state](#view-state)). Sorting, filtering,
searching and paging stay on the server.

- **Stock behavior inside the list.** Toolbar, view options, row menu, selection bar, loading state
  and pagination are DataViews' own, as WordPress core uses them. Give adds the page header, stat
  tiles, the notice area, empty states, confirm bodies and action notices around it. **The
  exceptions are the search box and the filter row**, which are Give components (see [Why the
  filter bar is Give's](#why-the-filter-bar-is-gives)), **the small "More view options" menu (⋮)**
  next to the stock view options, which the pinned DataViews cannot take extra items into (see
  [Switch to Legacy View](#switch-to-legacy-view)), and **the narrow layout**, which uses the stock
  `grid` layout but picks it by window width, not by a layout switcher (see [Narrow
  screens](#narrow-screens)). Where the pinned DataViews lags
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

## Money and dates

Every list screen writes money and dates the same way, and the server does all of it. The browser
only shows the text it gets.

- **Two formatters.** `Give\Framework\ListTable\Formatting\MoneyFormatter` writes money through
  `give_currency_filter(give_format_amount())`, the same helpers receipts and reports use, so the
  currency settings (symbol, position, separators, decimals) decide the result.
  `DateFormatter::dateTime()` uses the site's date format, time format and time zone ("November 29,
  2025 at 4:16 pm"); `DateFormatter::date()` is the date-only form (Campaign start and end). Every
  column calls one of them. `tests/Unit/Framework/ListTable/ListTableFormattingCallSitesTest.php`
  fails when a column formats money or dates another way.
- **Empty values are a dash.** A missing amount or date renders `Placeholder::dash()`: a "—" hidden
  from screen readers and a `screen-reader-text` label ("No date", "No amount", "No value"). The
  client atom `DashPlaceholder` writes the same markup. Never write `-`, "Invalid date" or a 1970
  date.
- **`kind` and `rawValues`.** `ModelColumn::getKind()` says what a column holds (`money`, `date`,
  `count`, `text`, `status`; see `ColumnKind`) and goes out in the column schema. A column can also
  return `getRawValue()`; rows then carry a `rawValues` key with the unformatted value (money as
  `{amount: "1331.28", currency: "USD"}`, a date as an ISO 8601 string in UTC). Both keys are
  optional and additive. The narrow layout uses `kind` to pick card fields; nothing formats from
  `rawValues` in the browser. A raw value must reuse what the cell already computed, never run a
  new query.
- **Stats send text too.** The Donations, Donors and Subscriptions stats endpoints add a
  `formatted` map next to the numbers: counts through `number_format_i18n()`, money through
  `MoneyFormatter`. The tiles show that text as it is.
- **Why the browser never formats.** The old list sent `locale: navigator.language` and the server
  formatted with it, so a Brazilian browser on a US-dollar site saw `US$ 100,00` on some screens and
  `$100.00` on others. The request no longer sends `locale`; endpoints fall back to `get_locale()`,
  so an add-on column that still reads `$locale` gets the site's language. There is no `Intl`,
  `toLocaleString` or `navigator.language` in the list client code. One exception stays: the filter
  chip text formats a picked day with the site's date format (`filters/siteDate.ts`).
- **Two details found on the way.** The intl fallback in `CurrencyFacade::formatToLocale()` passes a
  `currency` key that `give_currency_filter()` ignores, so a donation in another currency showed the
  site's symbol. `MoneyFormatter` passes `currency_code`. The facade itself is not changed. And on
  sites with "Auto-format server currency amounts" on, Donations, Donors, Subscriptions and Event
  Tickets amounts used to follow PHP intl; they now follow the currency settings like the rest.

## Notices

Every message above a list renders in one place, `ListTableNoticeArea`, between the stat tiles and
the toolbar. Nothing renders inside the table: its body holds records only.

- **Order.** Errors of failed actions first, then `system` notices, then `onboarding` notices, then
  the daily recommendation, then the deprecated `productRecommendation` slot.
- **Config only.** A screen passes `notices` (`ListTableNotice[]`: `id`, `kind`, `title`, `message`,
  optional `link` and `onDismiss`) and `recommendations` (`options`, `dismissed`, `apiNonce`). All of
  it is known when the page loads, so the area has no loading state and does not push the table
  down after it renders. With nothing to show it renders nothing and takes no space.
- **One recommendation a day.** `pickDailyRecommendation()` drops dismissed options (or ones whose
  add-on is active) and picks `available[dayNumber % available.length]` for the site's day. Every
  reload that day shows the same tip; the next day moves to the next one.
- **Dismissals are site-wide.** The recommendation dismissals are options
  (`givewp_donations_*_recommendation_dismissed`), as before, so one admin's dismissal hides the tip
  for every admin. Dismissing hides it at once; no other tip appears until the next load. Onboarding
  notices keep the storage they had before; the screen owns the "shown" flag (the Campaigns
  "Campaign List" tip, for example, is user meta).
- **Accessible.** A notice with a title is a `group` named by its heading; the recommendation is
  named "Pro tip". Dismiss buttons read "Dismiss notice" or "Dismiss tip", and a link that opens a
  new tab says so.
- **No record data.** Notices carry product and onboarding text only (see [pii.md](pii.md)).

## Stat tiles

`StatTiles` renders one `StatTile` for every key in the screen's `statsConfig`, in order, inside a
region named after the screen ("Donations statistics").

- **States.** `loading` (a `Skeleton` and "Loading" for screen readers), `ready`, `refreshing` (the
  old value stays, dimmed, and nothing is announced), `error` (a dash, "Statistic could not be
  loaded") and `upgrade` (a message and an "Upgrade" link in place of the number when the add-on is
  missing). `useListTableStats` derives the state from the request. The tiles never unmount, so the
  row keeps its size and the table does not move when numbers land.
- **Values are `formatted`.** The tile shows the server's text from the stats `formatted` map and
  never formats a number itself.
- **Tiles that filter.** A `StatConfig` with `filter: {filterId, value}` makes the tile a toggle
  button for that list filter; `filter: {filterId}` with no value is the "Total" tile of the group,
  pressed when the filter is off. A tile is pressed only because the view's filters say so
  (`isTilePressed()`), so picking the value from the toolbar chip presses the tile too. Clicking a
  pressed tile clears the filter; clicking another tile of the group replaces it. A tile whose filter
  the screen does not declare, or an `upgrade` tile, is plain text. Donations and Donors map their
  One-Time and Recurring tiles to the "Donation type" and "Donor type" filters, which exist only with
  Give Recurring active. Subscriptions tiles are plain statistics.

## Narrow screens

There is one layout breakpoint: `(max-width: 782px)`, the same as the WordPress admin. It is defined
twice, side by side: `NARROW_LAYOUT_QUERY` in `layout/breakpoints.ts` and `$givewp-list-table-narrow`
in `layout/_breakpoints.scss`, each with a comment pointing at the other. Only the shared shell uses
it; screens do not add their own media queries.

- **The page stacks.** At 782 px and below the page reads, top to bottom: title (and test badge),
  header actions, test-mode switch, tiles, notices, toolbar (search, filters, view options), the
  list, pagination. Nothing makes the page scroll sideways down to 375 px.
- **Cards.** When `useNarrowLayout` reports 782 px or less, `ListTablePage` switches the list to the
  stock DataViews `grid` layout with one card per record. Only that layout is in `defaultLayouts`, so there is no layout switcher. Each card
  shows the primary field, a description, the ID as a badge, the secondary fields with their labels,
  the row checkbox and the actions menu, all without hover.
- **Field roles.** A screen picks its card fields in `ListTableScreen.fieldRoles` (`primary`,
  `description`, `secondary`). `resolveFieldRoles()` drops a role whose column is missing (the
  Campaign column on a campaign's Forms tab). A screen with no roles gets them from the column
  kinds: the title, the first money column, the first status and the first date column.
- **Resizing keeps state.** `toNarrowView()` builds the card view from the wide view, and
  `fromNarrowView()` maps a change back, taking only the query (page, page size, sort, filters,
  search). The card view is never stored as the layout and never written to the URL, and the
  selection is kept.
- **Scroll shadow.** Above 782 px a wide table scrolls sideways inside the list.
  `useHorizontalOverflow` watches the stock `.dataviews-wrapper` and sets
  `givewp-list-table--more-start` and `--more-end` while content is hidden at that edge, which shows
  a shadow there (logical sides, so RTL works). It reads layout, never component state; if DataViews
  renames the class the shadows stop and nothing else breaks.
- **Motion.** Any animation in the list sits inside `@media (prefers-reduced-motion: no-preference)`.

## Switch to Legacy View

Donations, Donors, Subscriptions and Donation Forms (with the option-based form editor on) offer
"Switch to Legacy View". It is no longer a button in the page header: it is the last group of the
"More view options" menu (`ListTableViewMenu`), right after the stock view options and "Reset view".
A screen passes `legacyView` to `ListTablePage`; `switchToLegacyView()` calls the domain's `/view`
route (see [the top of this page](#the-archive-screens-have-two-implementations-and-the-user-picks))
and reloads. Screens without a legacy screen (Campaigns, Event Tickets, the Forms tab) pass nothing
and get no menu.

## Filters

### One declaration per entity

Each entity declares its filters once, in a typed file next to its screen (`filters.ts`:
`campaignFilters`, `donationFormFilters({withCampaign})`, `donationsFilters`, `donorsFilters`,
`subscriptionFilters`, and the Event Tickets one). The screen passes it to `ListTablePage` as
`filters`. The shell, the filter bar, the URL code and the request builder all read that one list,
and no shared code has a branch for one screen. The types are in `ListTable/filters/types.ts`.

| Kind | Control | Value in the view | Sent as |
|:--|:--|:--|:--|
| `options` | A fixed list: checkboxes when `multiple`, radios when not. `options: 'statuses'` takes the entity's status vocabulary. | The chosen values | `parameter`, a list when `multiple` |
| `entity` | Campaigns, forms or donors searched on the server (`source`). | Ids | `parameter`; "No campaign" as `none` |
| `date` | Six presets and a start and end day. | A preset (`30`, `this-month`) or `start_end` days | `parameters.start` and `parameters.end` as whole days |
| `search` | The labeled search box with its hint. Never in the bar. | `view.search` | `parameter` |

- **Primary and secondary.** A filter with `isPrimary` always shows as a chip. The others are in the
  "Add filter" menu and show as a chip once added. With no secondary filter there is no "Add filter".
- **`whenEmpty`.** Parameters an `options` filter sends while it holds nothing. Campaigns sends
  `status=any`, which keeps archived campaigns out of the default view.
- **`exclusiveValues`.** Values that cannot be combined with any other: Trash on the Status filters.
  Choosing one clears the rest, and choosing another value clears it. An `entity` filter with
  `allowNone` adds "No campaign", which works the same way.
- **`legacyUrlKey`.** A bare name that links made before view state used, such as `donor` or
  `campaignId`. It is read on load, applied, and rewritten to the `give-` name.
- **Fixed filters (`scope`).** A list inside a details page tab gets fixed request parameters from
  its host page, such as the campaign of a campaign's "Forms" tab. They are sent with every request
  and are never a chip, a URL parameter or something Reset clears. That tab also leaves the Campaign
  filter out of its declaration (`donationFormFilters({withCampaign: false})`).
- **Link-driven filters.** Some filters are mostly set by a link from another screen, such as Donor
  on Donations (`?donor=12` from a donor's page). They are ordinary declarations with a
  `legacyUrlKey`; the link applies them and the chip names the record.
- **Test mode** is not a declaration. A screen passes `testModeToggle`; the header switch edits the
  view filter. It has no chip in the filter bar, so the switch is the one place to turn it on or off.

### The filter bar

`ListTableFilters` renders the primary chips, the added secondary chips, "Add filter"
and Reset, from `FilterChip`, `OptionsFilterControl`, `EntityFilterControl` and
`DateRangeFilterControl`. `ListTableSearch` is the labeled search box with its hint, announced with
the field. A chip lists up to three names and then says how many are chosen ("Status: 4 selected");
its accessible name starts with the visible text and then lists the names ("Status: 4 selected.
Active, Pending, Draft, Archived"), so a voice command that says the visible words still finds it.
The tooltip shows the same text: newer `@wordpress/components` Tooltips add the tooltip as a
description when it differs from the name, which would read the list twice.

- **Reset shows only when the filters differ from the screen's defaults.** The defaults are no
  filters, plus test mode on a site in test mode, plus, on a screen that still passes the old
  `filterSettings`, a `hidden` setting with an option on its own page. So an untouched list on a test site shows no Reset,
  and Reset puts test mode back on instead of turning it off. A search term, or a secondary filter
  added with no value yet, counts as a change. Reset never clears `scope`.
- **The date range** counts in whole days in the site's time zone. A preset is kept as a preset and
  worked out again from the site's day, so "Last 30 days" in a bookmark still means the last 30 days.
  An end day before the start day is explained and cannot be applied.
- **The result count is announced.** `ResultCountStatus` is one visually hidden polite status region
  in the shell. After the admin changes the filters, the search, the sort or the page, and the list
  settles, it says how many records match ("12 donations found"). It says nothing on the first load,
  nothing while loading, and adds no request.

### Why the filter bar is Give's

The rest of the list is stock DataViews (spec 1, rule R0). The search box and the filter row are the
exception, because the pinned DataViews cannot do what the filters need: it searches a filter's
options in the browser (so every option must be loaded first), shows no error or retry when options
fail to load, joins every value into the chip, prints raw ids for options it has not loaded, has no
preset-based date range, no exclusive values, and hides the search label. The Give bar is built from
the same `@wordpress/components` parts, so it looks and works like the stock row. **[Judgement]**
When a newer DataViews supports server-searched options and custom chip text, swap back to the stock
row.

### The option route

`givewp/v3/list-table-filter-options/<source>` (`campaign`, `form` or `donor`) feeds the `entity`
filters (`ListTableFilterOptionsController`).

- **Permissions, in this order:** signed in (401); a known source (403 for any other); with
  `screen`, a known screen (404) that offers this source (400) and the screen's view capability
  (403); without `screen`, the source's own view capability (403).
- **Parameters:** `search` (up to 100 characters; matches the start of any word of the label),
  `page` (20 options a page, at most page 10000) and `include` (up to 50 ids, whose labels are
  returned; search and page are ignored). The client asks for labels in chunks of 50.
- **What it returns:** `{options: [{value, label}], hasMore}`. Each query selects the id and the
  label column; the form source also selects `post_status`, only to add "(Draft)" to a draft form's
  label. The donor query reads only the id and the name, so a donor option is never an email or
  anything else.
- **Cost.** A word-start match (`LIKE 'term%' OR LIKE '% term%'`) cannot use an index, so a donor or
  campaign search scans its table. It runs only while someone types, one page at a time, and the
  Donors list search already scans the same way. The contract accepts this.

### One status vocabulary

Each entity's statuses come from one server source, a `StatusVocabulary` built by the value object
(`CampaignStatus::vocabulary()` and the others): every value with its translated label, and which of
them the Status filter offers. `ListTable::toArray()` sends it as `table.statuses`. The status badge
and the Status filter both read it, so they always use the same words. Campaigns offers Active and
Archived, the two statuses an admin can set in the interface; Inactive, Draft, Pending, Processing and
Failed show as badges only.

### Parameters the endpoints accept

The changes are additive; old callers get the same results.

- Campaigns: a `statuses` list of stored values, which wins when set. The old `status` stays and is
  marked deprecated: `status=inactive` still means archived and `status=any` still hides archived.
- Forms, Donations, Donors and Subscriptions take lists for status, campaign and form where the
  filter allows several values. Every list with a date filter (all but Event Tickets) takes `start`
  and `end` days.
- **Search now respects the other filters on Donors and Campaigns.** Their search was built as
  `a LIKE … OR b LIKE …` without brackets, so half its matches ignored every other filter. The two
  `LIKE`s are now grouped. What a search matches has not changed. This is the one exception to "the
  same results as before" (SC-005), and only when a filter is on together with a search.

### Old filter settings

`filterSettings` (`FilterConfig[]`) is deprecated and kept only so add-ons that pass it keep working.
No core screen uses it. `filterSettingsToDeclarations()` in `filters/legacyFilterSettings.ts` maps it
onto declarations, so it renders through the same bar, URL rules and request builder: `select`
becomes a single `options` filter (its first option is sent while empty), `campaignselect` and
`formselect` single `entity` filters, `search` the search box, the `toggle` checkbox the test-mode
switch, and `hidden` fixed scope in a tab or a one-choice filter on by default on its own page. The
grouped "Filter by" type (`filterby`) is gone and is skipped.

## View state

A screen opts in by passing a `screen` config to `ListTablePage` (`{key, defaults}`, kept next to the
screen in a `listTableScreen.ts` file). Without one the view lives in memory only, which is what an
add-on page that never heard of the prop still gets. The one exception: a standalone page without a
`screen` config applies the filter parameters it recognizes in the address (bare legacy names such as
`?donor=12`, and `give-` names) to its first view and first request, and never writes them back. The
code is in `ListTable/state/` and `ListTable/hooks/useListTableUrlState.ts` and
`useListTableLayoutPreferences.ts`. Most of `state/` is pure functions; three modules are not:
`listTableHistory` (the `history` entry, the address and the tab's `sessionStorage`), `layoutApi` (the
layout route and the printed bootstrap) and `layoutWriteQueue` (the page view's write queue and its
error notice).

### One view object, two projections

There is one DataViews view per page, owned by `useListTableView`. Nothing else holds a copy of the
page, sort, filters or columns. Two things are derived from it:

- **What is shown** (page, page size, sort, search, filters, test mode) is written to the address.
- **How it is shown** (visible columns and their order, density, page size) is stored per user.

`assembleView` builds the view from, in order of precedence: the screen defaults
(`resolveScreenDefaults`, from the server column schema plus the config's `hiddenFields`,
`visibleFields`, `perPage` and `density`, default `balanced`), then the stored layout, then the
address. Page size is in both projections; the address wins. On back and
forward to an entry that names no page size, the page size of the user's layout is used (the layout
hook exposes it as `layoutPerPage`).

Every change records where it came from (`ViewChangeOrigin`): a `user` change adds a history entry
only when the address query changes (the search is part of it), a `correction` (an invalid address, a page past
the last one after records were removed) replaces it, `history` (back and forward) writes nothing, and
`reset` behaves like a user change in the address. A layout-only change (columns, density) adds no
entry, so Back does not undo it. Only a `user` change to the layout, or a reset, is stored.

### The address

`viewQuery.ts` is the only code that knows the parameter names.

- **Every written parameter has the `give-` prefix**, in kebab case: `give-paged`, `give-per-page`,
  `give-orderby`, `give-order`, `give-test-mode`, `give-search`, and one per bar filter, named from its declaration
  id (`parameterName()`: `give-campaign`, `give-donation-type`). The WordPress admin uses the bare names (`page`, `paged`,
  `s`, `post_type`) itself, and the details pages that embed a list use `page`, `tab` and `id`.
- **Only what differs from the defaults is written.** A view equal to the defaults leaves the
  address bare. Test mode is written only when it differs from the site's payment mode, so a link
  without it follows the site. **Known limit:** a filter that starts with a default value cannot be
  shown as cleared, because an empty value is not written. Clearing such a filter leaves the address
  bare (and adds no history entry if nothing else changed), and a reload or a shared link brings the
  default back. No core filter has a default value today.
- **Only ids and enumerated values.** Each value must match `[A-Za-z0-9_-]{1,64}` and be one the
  screen offers (a sortable column, an option or status the filter offers, a page size in
  `PER_PAGE_SIZES`, a positive id for an `entity` filter, a valid date token). Anything else is
  dropped, the result is marked `corrected`, and the address is replaced. The writer applies the same
  pattern. Nothing read from the address is rendered as markup. A date preset keeps its token (`30`,
  `this-month`) and is turned into days per request, from the site's day.
- **Legacy bare names are read on load and rewritten.** A declaration's `legacyUrlKey`
  (`?campaignId=4`, `?donor=12`) is read with the same rules when the `give-` form is missing (the
  prefixed one wins), and the address is then rewritten with `replaceState`, so no history entry is
  added. A bare `search` is
  not read either: it is only removed. A standalone page without a `screen` config reads the same
  recognized filter parameters (bare and `give-` names) for its first view and first request, but
  never rewrites the address and ignores the page, sort and page size.
- **Other parameters are kept as they are,** in their order. The writer replaces only the screen's
  own keys (`viewQueryKeys`) and the legacy names it read.
- **Length limit.** The writer keeps the whole address under 2000 characters by dropping filter
  values from the end.

### The search term is in the address

The search is written as `give-search`, the same way WordPress core writes `?s=` on its own list
tables. It is the one value that is free text (see "Only ids and enumerated values" above for the
others): it is trimmed, cut to `MAX_SEARCH_LENGTH` (100 characters) and percent-encoded when written,
and read back as plain text, never as markup. An empty term is not written. It is never stored in the
layout, and an embedded table (`contentMode`) never writes it.

**It is personal data in the address.** The term can be a donor's name or email, so it shows in the
browser history, in server logs and in a copied link. Core does the same for `?s=`, and WordPress
sends `Referrer-Policy: strict-origin-when-cross-origin`, so the term does not leave the site in a
referrer. Someone who opens a copied link needs the same capability as the person who made it.

A search-only change within 1.5 seconds of the previous one replaces the history entry instead of
adding one (`SEARCH_COLLAPSE_MS`), so one typed term is one entry. A new term goes back to page one in
the same entry. Back and forward read the term from the address like any other parameter.

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
  user's own record. `ListTableLayoutValidator` accepts version `1`, at most 50 column ids in `visibility`
  and at most 50 in `order` (each limit on its own), every id matching `[A-Za-z0-9_-]{1,64}`, one of the three densities, and a page size from 1 to 100.
- **Writes** for an ordinary layout change wait one second after the last change; a reset sends at
  once. All writes run one after another. The queue and the
  once-per-page-view error notice live in `state/layoutWriteQueue.ts`, outside React, so a remounted
  table shares them. A change is staged in the commit phase, so a `pagehide` right after it already
  sends it. The exception to the order is the send on `pagehide`: it goes out at once with
  `keepalive` and skips the queue, so it can overtake an older write that is still in flight. A
  failed write keeps the change on screen and shows one notice per page view; the next write sends
  the whole record again.
- **Locked columns.** The identifying column (the title column, or `id` when there is none) cannot
  be hidden, and the legacy row-actions column, when a screen uses one, cannot be hidden and stays
  last (`fieldRules.ts`). A column shown again, or one a stored order does not name, goes before the
  first column that comes after it in the default order (`fieldOrder.ts`, shared by both cases).

### Embedded tables

A table inside another page (the campaign details "Forms" tab, the subscription details "Donations"
tab) passes `contentMode`. With `contentMode` the address and the tab state are never read or written
(`readInitialUrlState()` reads nothing and the URL hook writes nothing), because the host page owns
the address. Its layout is still
stored under its own key (`campaign-forms`, `subscription-donations`), separate from the standalone
screen it reuses.

### The first request already carries the view

`ListTablePage` resolves the defaults inside a `useState` initializer, before the first render, and
calls `readInitialLayout` (the stored layout) and `readInitialUrlState` (the address and the tab's
search). The shell holds no storage or address code itself. So the first list request is
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
- Money or a date? → write it with `MoneyFormatter` or `DateFormatter`, return the matching `kind`,
  and give a `getRawValue()` from what the cell already has. An empty value is `Placeholder::dash()`.

## Related

- `src/Framework/ListTable/` — `ListTable`, `ModelColumn`, and the `Columns`, `IsFilterable`,
  `ListTableData` concerns
- `src/Views/Components/ListTable/` — the React side ([README](../../src/Views/Components/ListTable/README.MD))
- `src/Framework/ListTable/Actions/RegisterListTableAssets.php` — the shared `givewp-list-table` script handle
- `src/Framework/ListTable/Layouts/` — the stored layout: screens, store, validator, route and the printed bootstrap
- `src/Framework/ListTable/FilterOptions/` — the filter option route and its sources
- `src/Framework/ListTable/StatusVocabulary.php` — the status list a badge and a Status filter share
- `src/Framework/ListTable/Views/GoalProgress.php` — the goal cell the Campaigns and Donation Forms lists share
- `src/Framework/ListTable/Formatting/` — `MoneyFormatter` and `DateFormatter`, the one way list cells write money and dates
- `src/Framework/ListTable/Views/Placeholder.php`, `TruncatedText.php` — the empty-value dash and the two-line clamp with the full text on hover
- `src/Views/Components/ListTable/layout/` — the 782 px breakpoint and the narrow card view
- `src/Framework/QueryBuilder/` — what the endpoints build queries with ([README](../../src/Framework/QueryBuilder/README.md))
