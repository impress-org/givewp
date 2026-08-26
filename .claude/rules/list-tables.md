---
paths:
  - "src/Framework/ListTable/**/*"
  - "src/Views/Components/ListTable/**/*"
  - "src/*/ListTable/**/*"
  - "src/*/*/ListTable/**/*"
  - "src/*/Endpoints/List*.php"
  - "src/Campaigns/Repositories/CampaignsDataRepository.php"
  - "src/Campaigns/Actions/CacheCampaignData.php"
  - "src/DonationForms/AsyncData/**/*"
---

# List tables

Read `docs/architecture/list-tables.md` before adding or changing a column — it covers the
rendering pipeline, both caching strategies, and the large-site failure modes.

**The four archive screens have two implementations.** Donations, Donors, Subscriptions, and
Donation Forms each ship a modern React page and the legacy WordPress screen it replaced, selected
by a per-user setting in `_give_{slug}_archive_show_legacy` user meta. They share no code, so a fix
to one is not a fix to the other — establish which UI a bug report is about before locating it.
Legacy lives under `includes/admin/`. This applies to these four screens only, not to the admin
generally; see `docs/architecture/admin-ui.md`.

The rules that matter most:

- **Never query inside `ModelColumn::getCellValue()`.** It runs once per row, so a query there is
  an N+1 that only hurts on real data. Bulk-fetch in the endpoint and pass it through
  `$listTable->setData()`, then read it with `getListTableData()`.
  `CampaignsDataRepository` is the reference implementation.
- **Cell exceptions are caught and logged per row** by `ListTable::safelyGetCellValue()`. A
  throwing column writes one log entry per rendered row — guard instead of relying on the catch.
- **Filters must reach `getWhereConditions()`**, or the separate `count()` query for pagination
  disagrees with the rows it's counting.
- Campaign stats come from an options-table cache refreshed asynchronously through Action
  Scheduler; v2 form stats come from a 5-minute transient with no invalidation. Both are stale by
  design — verify against the database before treating a number as a bug.
