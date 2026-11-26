# DataViews polyfill

Code here exists only to make the pinned `@wordpress/dataviews` (10.1.7) look and behave like the one in
newer WordPress. The comparison target is WordPress 7.1. Nothing else belongs here: Give's own design choices
stay next to their components.

**Review every entry each time the `@wordpress/dataviews` version changes.** `polyfillVersion.test.ts` fails
until you do. The removal steps for most entries are in
[list-tables.md](../../../../../docs/architecture/list-tables.md#dataviews-polyfill-review-at-every-dataviews-upgrade).

| What | Where | At the upgrade |
|:--|:--|:--|
| Free width shared between all columns | `_polyfill.scss` | Keep. Rename `col-primary` to `col-first-data` and measure again. |
| 24px edges: cells, header, footer, states, cards | `_polyfill.scss` | Remove if the new stock spacing matches. |
| Row checkboxes always visible | `_polyfill.scss` | Remove if stock shows them without hover. |
| Footer total ("30 of 464 Items") | `ListTableTotal.tsx`, `__total` in `_polyfill.scss` | Remove with `ListTableFooter` (12.0.1 or later). Check the locale format. |
| "Reset view" inside View options | `ListTableViewReset/` | Pass the stock `onReset` prop (12 or newer) and delete the folder. |
| Line on the pinned Actions column, with no delay | `useActionsColumnStuck.ts` | Remove if stock follows scroll and late rows by itself. |
