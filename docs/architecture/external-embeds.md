# External embeds

A v3 donation form can be embedded on a site that is not the WordPress site hosting it. The
embed is two pieces: a script the third-party page loads, and a custom element the script
registers. Everything else, the form, the payment, the receipt, still runs on the WordPress site
inside an iframe.

```html
<script src="https://example.org/give/embed/donation-form/script.js?form-id=42" defer></script>
<givewp-donation-form form-id="42"></givewp-donation-form>
```

The form builder generates that snippet (`src/FormBuilder/resources/js/form-builder/src/components/EmbedForm/`).
The script source is `src/DonationForms/resources/externalEmbed/index.ts`, built to
`build/donationFormExternalEmbed.js`. It is self-contained: no `@wordpress/*` packages, iframe-resizer v4
inlined. It has to run on pages that have none of the WordPress runtime.

## The script URL is a contract

The snippet is copied once and pasted into sites we do not control. Whatever URL it carries has
to keep working for as long as those pages exist, across plugin updates, build changes, and
changes to the site's own settings. So the snippet never points at the build file. It points at
a URL the plugin owns and resolves at request time.

`Route::script($uri, $file)` in `src/Framework/Routes/Router.php` registers such a URL, and
`Route::scriptUrl($uri)` builds it. The embed script is registered in
`src/DonationForms/ServiceProvider.php`; the URI lives on
`src/DonationForms/Actions/GenerateExternalEmbedScriptUrl.php` so the route and the snippet
cannot drift apart.

The shape depends on the permalink setting, which is the one WordPress setting that changes
which URLs reach PHP at all:

| Permalink setting | URL |
|:--|:--|
| Pretty (`/%postname%/` etc.) | `/give/embed/donation-form/script.js` |
| Pretty with `index.php` (no `mod_rewrite`) | `/index.php/give/embed/donation-form/script.js` |
| Plain | `/?givewp-route=embed/donation-form/script.js` |

`scriptUrl()` reads `$wp_rewrite->using_permalinks()` and `using_index_permalinks()` and builds
on `home_url()`, so subdirectory installs, `WP_HOME` differing from `WP_SITEURL`, multisite, and
the scheme all come out right without special cases.

The `give` prefix is a constant on the router, not the "Form Page URL Prefix" setting. It shares
the setting's default on purpose, but it must not follow the setting: a site admin changing that
prefix would otherwise break every snippet already pasted elsewhere.

### No rewrite rule

The pretty URL is not a rewrite rule. `Route::script()` hooks `parse_request` and compares
`$wp->request`, the path WordPress has already resolved relative to the home URL, against
`give/{uri}`; the plain form is matched on the `givewp-route` query var, the same one every other
route in the router uses. A match hands off to `ScriptResponse` in the same directory, which sends the file and exits
before the main query runs. Routing stays on the router; headers and revalidation live there.

This is deliberate. A rewrite rule lives in the `rewrite_rules` option and only exists after a
flush. Flushes happen on activation and when permalink settings are saved, and a plugin update
that adds a rule does not trigger one. A path that depends on a flush works on fresh installs
and fails on updated ones until someone visits the permalinks screen. Matching `$wp->request`
has no such state.

It also sidesteps the legacy form route in `src/Route/Form.php`, whose `give/(.+?)/?$` rule
would otherwise claim `give/embed/...` as a form slug. `parse_request` fires before that rule's
result is acted on.

### Caching

The URL is stable, so the version has to travel in the response rather than in a `?ver=` query,
which would freeze at whatever the snippet was copied with.

- `ETag` is the build hash from the `.asset.php` file next to the script, read through `ScriptAsset`. It changes
  when the bundle changes and is the same value `wp_enqueue_script` would use as `ver`. When data
  is localized (below), a hash of that data is appended, so a translation change revalidates too.
- `Cache-Control: public, max-age=3600`. A third-party page reuses the script for an hour, then
  revalidates. A matching `If-None-Match` gets a `304` with no body.
- The `If-None-Match` comparison ignores the `W/` weak prefix and the `-gzip` suffix Apache's
  `mod_deflate` appends to outbound `ETag`s, so a compressed response still revalidates.

Raising `max-age` trades update latency on other people's sites for fewer requests. An hour is
the ceiling a plugin update should tolerate; do not go to a year.

### The site's data travels with the script

The bundle runs on pages without WordPress, so it cannot call `__()` or `home_url()`. Instead the
route localizes it: `Route::script(...)->localize('givewpDonationFormEmbed', new GetExternalEmbedScriptData())`
prints `var givewpDonationFormEmbed = {...};` ahead of the file, the same shape
`wp_localize_script()` gives an enqueued script. The callable runs at request time, after the
text domain is loaded, so the strings are in the site's locale and the URLs are the site's
current ones. The object carries:

- `homeUrl`, which the element uses as the trusted origin for `postMessage` and iframe-resizer.
- `formViewUrl` and `receiptViewUrl`, the two routes the iframe loads, from `Route::url()`. The
  element appends `form-id` or `receipt-id` and its own per-embed params.
- `formPageUrl`, the form's own page from `GenerateDonationFormPageUrl`, which the block's new-tab
  launcher also uses. The element appends `p`.
- `receiptReturn`, the offsite-gateway return parameters as `GenerateDonationConfirmationReceiptUrl`
  builds them: the `match` params that must equal fixed values, and the names of the embed id and
  receipt id params.
- `i18n`, the default donor-facing labels. Attributes still override per element (`button-text`
  for an admin-chosen label), but the snippet no longer has to carry translations.

Nothing about the site is hardcoded in the bundle. The English literals in the source are reached
only when the file is loaded from somewhere other than the route, and without the object the
element logs an error and renders nothing, since it has no URLs to embed. Loading the built file
directly is not supported.

The element's `locale` attribute does not reach these strings. It is forwarded to the iframe URL,
so it changes the language of the form inside the frame only; the launcher button, spinner label,
and dialog labels stay in the site's locale unless the text attributes override them.

### The form id on the script URL

The URL is the same for every form on a site, with one optional addition: `?form-id=42`. Without
it the response is site-level. With it, the response also carries that form's server-rendered
skeleton (`RenderFormSkeleton`, the same markup the on-site block prints), keyed by id under
`skeletons`, so the element can draw the form's shape at connect, before the form page has
answered. The builder writes the id on both lines of the snippet; the element attribute stays
the contract for which form loads, and the URL id only unlocks the early skeleton. A wrong or
missing id degrades to a spinner until the form page's own skeleton (below), never to a wrong
form.

`Router::scriptRequest()` hands the cleaned query string to the localize callable, so
`GetExternalEmbedScriptData` reads `form-id`: a comma list is accepted for pages with several
forms, ids are `absint`ed, deduplicated and capped at ten, and only published forms get an entry.
The ETag already hashes the localized data, so a skeleton change revalidates on the next request
like a translation change does. Nothing in the response is per-visitor, so it stays `public`.

The costs this trades against the earlier per-form URL idea are accepted for the skeleton:

- A form load per script request and revalidation. The route already bootstraps WordPress and
  runs the localize callable per request; this adds one `DonationForm::find` and its settings.
- One cached copy per form instead of one per site. A page with two forms downloads the script
  twice. `customElements.define()` is guarded, and the bundle merges every instance's skeletons
  into `window.givewpDonationFormEmbedSkeletons` at module scope, then calls `applySkeleton()`
  on every element already on the page, because defining the element in the first script upgraded
  all of them before the second script ran.
- The id appears twice in the snippet. The builder writes it, nobody types it.

A path form, `/give/embed/donation-form/42/script.js`, serves the identical response.
`scriptRequest()` matches an optional numeric segment before the file name on both the pretty
path and the `givewp-route` query var and returns it as `id`. It exists for caches configured to
drop query strings from their key, which would otherwise hand one form's skeleton to another
form's page (cosmetic: the form page's skeleton corrects it at the shell message). The builder
does not emit it; support can point a customer on such a host at it.

What the URL still does not carry: anything per-embed. Display style, labels, and the launcher
color are attributes on the element; the form's own design and settings are resolved inside the
iframe on every load.

### Known ceiling

Some nginx configurations serve `*.js` straight from disk and return `404` for a missing file
without ever reaching `index.php`. On such a host the pretty URL fails while the plain
`?givewp-route=` form works, because static-extension `location` blocks match the path, not the
query string. If this shows up in support, the fix is to drop the `.js` extension from the URI
(a `<script src>` does not need one); the query-string fallback already avoids it.

## The custom element

`<givewp-donation-form>` requires only `form-id`. The WordPress site's URLs come from the
localized object above, never from the snippet. Optional attributes: `display-style` (`onpage`,
`modal`, `newTab`), `button-text`, `primary-color`, `form-title`, `loading-text`, `fallback-text`,
`close-text`, `locale`.

### What the snippet bakes in, and why

The form inside the iframe resolves its own colors, labels, and settings on every load, so a
change in the builder reaches every embed immediately. Nothing about the form's design is copied
into the snippet for the form's sake.

The one exception is host-page chrome. For the `modal` and `newTab` styles the script draws a
launcher button on the other site, and that button exists before any WordPress response does, so
its color has to come from the snippet. The builder's external tab exposes it as a **Button
color** field, seeded with the form's resolved primary color, written as `primary-color`, and
labeled as a per-embed choice that does not follow the form's design. The stylesheet reads it as
the `--givewp-primary-color` custom property, so a host page can also set it in its own CSS. The
fallback is the same `#2271b1` the on-site block uses. There is no `secondary-color`; nothing in
the embed reads one.

### The styles are the block's styles

The launcher and the modal use the on-site block's class names (`givewp-donation-form-link`,
`givewp-donation-form-modal__*`) and its SCSS: the launcher partial at
`src/DonationForms/Blocks/DonationFormBlock/resources/styles/launcher.scss` and the modal at
`src/Campaigns/Blocks/shared/components/ModalForm/styles.scss`. `externalEmbed/styles.scss`
imports both and adds only what the host page needs that WordPress does not: a `display: block` on
the element, higher z-indexes, the onpage loading spinner, and focus guards. The launcher's pending
state is shared too: both render a `__label` span and a `__spinner` span and set `data-pending` on
the button, which is what react-aria's `Button` emits for `isPending`, so the block and the embed
draw the same CSS spinner.

The route serves one JS file and nothing else, so the stylesheet cannot be a separate asset. The
script imports it as `./styles.scss?inline`; the `?inline` resource query is matched by a rule in
`webpack.config.js` that compiles the SCSS to a string (`type: 'asset/source'`) instead of
extracting it, and the script writes that string into a `<style>` element once per page. Change
the block's styles and the embed follows on the next build.

The modal also follows the block's loading behavior: the launcher shows a spinner and the overlay
stays hidden until the iframe-resizer handshake, then the overlay fades in and the dialog zooms.

`display-style` and `button-text` are likewise per-embed choices, not form settings. `form-title`
labels the iframe and dialog for assistive tech, and a stale one is harmless.

The `newTab` launcher and the load-failure fallback link open the form's own page, the
`formPageUrl` from the localized object with `p={id}` appended. `GenerateDonationFormPageUrl`
builds it for the block's new-tab launcher too, so the two cannot drift. The bare
`donation-form-view` route is only ever an iframe `src`; it has no theme header or footer and is
not meant to be landed on.

The iframe loads the same `donation-form-view` route the on-site block uses, with `origin-url`
set to the host page (origin and path only, never its query string or fragment) and an
`embed-id` that is a DOM-order counter. The counter is deliberate: an offsite gateway returns the
donor to the host page with the embed id in the URL, and the element whose id matches swaps
itself to the `donation-confirmation-receipt-view` route. A random id would not survive the round
trip.

### What the element trusts

- The iframe is revealed on the iframe-resizer handshake, not on `load`, because browsers fire
  `load` for error pages too. If the handshake has not happened in ten seconds the element
  degrades to a plain "Open donation form" link. That covers frame-blocking headers, ad blockers,
  and network failure.
- On the `onpage` style the iframe is also revealed earlier, on a `givewp-embed-shell` message,
  when the script URL did not name the form and the element is still showing a spinner. (With the
  skeleton from the script data in place the shell is ignored: the iframe draws the same markup,
  so the reveal waits for the handshake as the block's does.) The form view prints the form's
  skeleton (`RenderFormSkeleton`, the same markup the on-site block prints in its own page)
  inside the root element, followed by one inline script that posts the document's height to the
  parent. That arrives once the iframe's HTML and head stylesheets
  have loaded, before the app bundles, which is the earliest the element can know anything about
  the form without a request of its own. The element accepts the message only from the WordPress
  origin and its own iframe's window, requires a finite positive height and caps it, sets that
  height on the iframe and shows it; the handshake then hands height control to iframe-resizer as
  before. The payload is that number and nothing else, which is why the form view addresses it to
  `*`. The ten-second fallback keeps running after the shell message, and the modal ignores it: the
  launcher keeps its spinner and the overlay stays hidden until the handshake. Until the shell
  message the iframe is hidden but laid out at full width (`visibility: hidden`, absolutely
  positioned), not `display: none`, so the form view measures its skeleton at the width it will be
  shown at.
- The form app may ask the host page to navigate (`givewp-navigate`) when it cannot reach
  `window.top` itself. The element honors that only when the message origin is exactly the
  WordPress origin, the source is its own iframe's window, and the URL parses as `http(s)`.
  The form addresses that message to the host origin from `origin-url` (`navigateTop.ts`), not
  `*`, because the URL can carry a gateway approval token or a receipt key.
- The iframe carries `allow="payment"`. The Payment Request API, which Apple Pay, Google Pay,
  and Link go through, is disabled in cross-origin frames unless the embedding page delegates
  it. The on-site block's iframe is same-origin and inherits it.
- On a receipt return the element also strips the return params from the host page's address
  bar, so a reload or a shared link does not replay the receipt view.

### Login inside a cross-origin iframe

The on-site login flow redirects to `wp-login.php` with a `redirect_to` back to the form page.
WordPress rejects a `redirect_to` on another host, so cross-origin embeds cannot use it. When
`isCrossOriginEmbed` is set (`Authentication.tsx`), the form shows its inline login instead and
opens lost-password in a new tab. Signing in from that inline form without third-party cookies is
the subject of the next layer of this feature (the embed token flow).

## Related

- [donation-forms.md](donation-forms.md) — where the `donation-form-view` and receipt routes the
  iframe loads are registered
- [payment-gateways.md](payment-gateways.md) — the offsite return flow the receipt swap mirrors
- `tests/e2e/external-embeds.spec.ts` — serves a fictitious external origin and loads the script
  through the route, so the iframe crosses origins for real
