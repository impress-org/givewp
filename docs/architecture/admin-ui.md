# Admin UI

The WordPress admin surface has three generations living side by side. Knowing which one you're in
determines everything else — which framework, which data layer, and whether a fix needs applying
twice.

## Three generations

| | Where | Status |
|:--|:--|:--|
| **Legacy screens** | `includes/admin/` | Maintained, still selectable by users |
| **Modern React pages** | `src/<Domain>/` + `src/Views/Components/` | Where new work goes |
| **Settings** | `includes/admin/settings/`, `src/Settings/` | PHP only — no React equivalent exists |

**Only the four archive screens have two versions.** Donations, Donors, Subscriptions, and
Donation Forms each ship a modern React page *and* the legacy screen it replaced, selected per user
— see [list-tables.md](list-tables.md) for the switching mechanism.

**Settings are a different story.** There is no React settings page anywhere in the plugin. Every
settings tab is PHP, built on `includes/admin/settings/class-settings-*.php` and the
`abstract-admin-settings-page.php` base. `src/Settings/Security/SecuritySettingsPage.php` is modern
*code* following the legacy settings *pattern* — don't read its location under `src/` as meaning
it's React. Adding a settings field means working in the PHP settings API.

## Registering an admin page

Modern pages are a PHP class per domain — `src/<Domain>/<Domain>AdminPage.php` — with a consistent
shape:

```php
public function registerMenuItem()          // add_submenu_page, capability check
public function loadScripts()               // enqueue the built bundle, localize config
public function render()                    // print the mount point for React
public static function isShowing(): bool    // are we on this page right now?
public static function isShowingDetailsPage(): bool
```

The domain's `ServiceProvider` wires `registerMenuItem()` to `admin_menu`. For the four archive
screens that registration is conditional on the user's legacy preference.

`isShowing()` / `isShowingDetailsPage()` matter more than they look — they gate asset loading, and
enqueueing a heavy admin bundle unconditionally is a real performance cost on every admin page.

`loadScripts()` is where localization happens, and it is subject to the rules in
[pii.md](pii.md): pass configuration and an API root, never records.

## The details page framework

`src/Views/Components/AdminDetailsPage/` is a **generic, reusable details page**, used by Campaign,
Donation, Donor, Subscription, and Event Tickets. If you're building a sixth, use it — don't start
from a copy of one of the five.

```tsx
<AdminDetailsPage
    objectId={donation.id}
    objectType="donation"
    objectTypePlural="donations"
    useObjectEntityRecord={useDonationEntityRecord}
    tabDefinitions={tabDefinitions}
    breadcrumbUrl={…} pageTitle={…}
    StatusBadge={…} PrimaryActionButton={…} ContextMenuItems={…}
/>
```

What it provides, and what that implies:

- **Schema-driven validation.** It fetches the object from `/givewp/v3/{objectTypePlural}/{id}`,
  derives an `ajv` resolver from the REST schema, and feeds react-hook-form. Validation rules come
  from the API, not from the component — so a field's validation is changed on the PHP route, not
  in the React form.
- **Tabs** via `Tabs/Router`, `TabList`, `TabPanels`, declared through `tabDefinitions`.
- **Slot-based extension** — it renders inside a `SlotFillProvider` with a `PluginArea`, which is
  how add-ons inject sections without patching core.
- Error boundary, notifications store, confirmation dialog, and `AdminSection` /
  `AdminSectionField` layout primitives.

## Data: core-data entities, and the PII split

Admin React pages don't hand-roll fetching. GiveWP registers WordPress **core-data entities** so
`useEntityRecord` / `useDispatch(coreStore)` work against the v3 REST API:

```ts
dispatch(coreStore).addEntities([
    {name: 'donation', kind: 'givewp', baseURL: '/givewp/v3/donations', plural: 'donations', …},
    // campaign, donor, subscription, form
]);
```

Each domain exposes a thin hook over this — `useDonationEntityRecord`, and siblings — which is what
`useObjectEntityRecord` receives.

**There are two entity bundles, and the difference is PII.** `RegisterAdminEntities` loads
`entities-admin.ts`; `RegisterPublicEntities` loads `entities-public.ts`. They register the same
entities with different `baseURLParams`:

| | admin bundle | public bundle |
|:--|:--|:--|
| `includeSensitiveData` | `true` | `false` |
| `anonymousDonations` / `anonymousDonors` | `include` | `redact` |

This is the admin/public boundary from [pii.md](pii.md) enforced at the data layer, with safe
defaults on the public side. Two consequences:

- **Don't load the admin entity bundle on a public page.** It requests full PII by default. The
  route's own permission checks are the real gate, but the request shape is wrong and the intent is
  wrong.
- **Don't "fix" the public bundle's params** to make a public feature show more data. Those values
  are the guard, not an oversight.

## Shared components

`src/Admin/` is a component library, not a domain — 38 TSX files, no PHP. Look here before writing
anything from scratch:

- `components/` — `Charts`, `StatWidget`, `SummaryTable`, `OverviewPanel`, `PrivateNotes`,
  `Notices`, `Header`, `Grid`, `Spinner`
- `fields/` — `Status`, `AssociatedDonor`, `DateTimeLocalField`, `LockedTextInput`
- `hooks/`, `providers/` (including the Emotion styles provider)

Styling uses the design system foundation tokens rather than hardcoded values — see the Styles
section of [AGENTS.md](../../AGENTS.md).

## Extension surface

`AdminDetailsPage` publishes a small API onto `window.givewp.admin` for add-ons:

```js
window.givewp.admin.components  // AdminSection, AdminSectionField
window.givewp.admin.hooks       // useFormContext, useFormState
```

Combined with the `PluginArea` slot, that's how an add-on adds a section to a details page. Treat
both as public API — renaming or removing an export there breaks add-ons silently.

## Checklist

- Which generation are you in? Settings means PHP; an archive screen means checking whether the
  legacy version needs the same fix.
- Building a details page? Use `src/Views/Components/AdminDetailsPage/`, don't copy an existing one.
- Need a component? Check `src/Admin/components` and `src/Admin/fields` first.
- Fetching data? Use the registered core-data entity and the domain's entity-record hook, not a
  bare `apiFetch`.
- Enqueuing assets? Gate on `isShowing()` so the bundle doesn't load across the whole admin.
- Localizing? Configuration only — see [pii.md](pii.md).
- Changing a details-page field's validation? That lives in the REST schema, not the React form.

## Related

- [list-tables.md](list-tables.md) — the archive screens and their legacy/modern switch
- [pii.md](pii.md) — what may cross to the client, and the entity bundle split
- `src/API/REST/V3/Entities/` — entity registration and the two bundles
- `src/Views/Components/AdminDetailsPage/` — the details page framework
- `src/Admin/` — shared components, fields, hooks
