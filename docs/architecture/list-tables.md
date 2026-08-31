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

Two consequences worth internalising:

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
3. **A React page** (`src/Views/Components/ListTable/`) that fetches from the endpoint with SWR.
   See its [README](../../src/Views/Components/ListTable/README.MD) for the component props.

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

- **The cache is authoritative once warm.** `campaigns()` computes which ids are *not* cached, but
  then, if the cache holds any data at all, returns early with only what's cached — the uncached
  ids are never fetched on that path. Stats for a campaign missing from the cache come from
  `CacheCampaignData` running later, not from a read-time fallback.
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

## Pagination cost

`List*` endpoints run two queries per page: the page of models (`limit`/`offset`) and a separate
`count()` for the total (`ListDonations::getTotalDonationsCount()`). On a large donations table
the COUNT with filters applied is frequently the slower of the two, and it runs on every page
view, including page 400.

If you add a filter, add it to *both* `getDonations()` and `getTotalDonationsCount()` — they build
their conditions through the shared `getWhereConditions()`, and bypassing it makes the total
disagree with the rows.

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
- `src/Framework/QueryBuilder/` — what the endpoints build queries with ([README](../../src/Framework/QueryBuilder/README.md))
