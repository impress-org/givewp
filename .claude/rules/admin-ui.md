---
paths:
  - "src/Admin/**/*"
  - "src/Views/Components/**/*"
  - "src/*/resources/admin/**/*"
  - "src/*AdminPage.php"
  - "src/*/*AdminPage.php"
  - "src/API/REST/V3/Entities/**/*"
  - "src/Settings/**/*"
  - "includes/admin/**/*"
---

# Admin UI

Read `docs/architecture/admin-ui.md` before building or changing an admin page.

- **Three generations coexist**: legacy screens in `includes/admin/`, modern React pages in
  `src/<Domain>/`, and **settings, which are PHP only** — there is no React settings page. Code
  under `src/Settings/` follows the legacy settings pattern despite its location.
- **Building a details page? Use `src/Views/Components/AdminDetailsPage/`.** It's generic and
  already backs five pages. Don't copy an existing one.
- **Its validation comes from the REST schema**, not the React form — it derives an ajv resolver
  from `/givewp/v3/{plural}/{id}`. Change validation on the PHP route.
- **Fetch through the registered core-data entities**, via the domain's `use*EntityRecord` hook,
  not a bare `apiFetch`.
- **Two entity bundles exist and the difference is PII**: `entities-admin.ts` sets
  `includeSensitiveData: true` / anonymous `include`; `entities-public.ts` sets `false` / `redact`.
  Don't load the admin bundle publicly, and don't loosen the public one's params to surface more
  data — they're the guard.
- **Check `src/Admin/components` and `src/Admin/fields` before writing a component.** Charts,
  StatWidget, SummaryTable, OverviewPanel, PrivateNotes, Notices, Header, Grid, Status,
  AssociatedDonor, DateTimeLocalField, LockedTextInput already exist.
- **Gate asset loading on `isShowing()`** so admin bundles don't load across every admin page.
- `window.givewp.admin.components` / `.hooks` are add-on-facing API — removing an export breaks
  add-ons silently.
