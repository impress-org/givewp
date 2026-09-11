# External embeds

A v3 donation form can be embedded on a site that is not the WordPress site hosting it. The
embed is two pieces: a script the third-party page loads, and a custom element the script
registers. Everything else, the form, the payment, the receipt, still runs on the WordPress site
inside an iframe.

```html
<script src="https://example.org/give/embed/donation-form/script.js" defer></script>
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

`Route::script($uri, $asset)` in `src/Framework/Routes/Router.php` registers such a URL, and
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

- `ETag` is the build hash from `build/{asset}.asset.php`, read through `ScriptAsset`. It changes
  when the bundle changes and is the same value `wp_enqueue_script` would use as `ver`.
- `Cache-Control: public, max-age=3600`. A third-party page reuses the script for an hour, then
  revalidates. A matching `If-None-Match` gets a `304` with no body.
- The `If-None-Match` comparison ignores the `W/` weak prefix and the `-gzip` suffix Apache's
  `mod_deflate` appends to outbound `ETag`s, so a compressed response still revalidates.

Raising `max-age` trades update latency on other people's sites for fewer requests. An hour is
the ceiling a plugin update should tolerate; do not go to a year.

### Why the path has no dynamic segments

The URL is the same for every form on a site, on purpose. A per-form URL such as
`/give/embed/donation-form/42/script.js` would let the server bake that form's colors and labels
into the script, but it costs more than it saves:

- The custom element is registered once per page. A page embedding two forms would load two
  scripts, and the second `customElements.define()` throws.
- The response would depend on a form lookup, so every request and every revalidation hits the
  database, and the ETag could no longer be the build hash alone.
- The browser would cache one copy per form instead of one per site.

Everything a per-form URL could supply is already on the element: the snippet generator reads the
form's colors and translated labels at copy time and writes them as attributes. What the host page
needs from the server is the script and the site location, and both come from the URL as it is.

If a script variant is ever needed, add a query parameter (`?locale=fr`), read it through
`Router::getRequestDataByType()` for the same sanitizing the other routes get, and fold its value
into the ETag. The path stays put.

### Known ceiling

Some nginx configurations serve `*.js` straight from disk and return `404` for a missing file
without ever reaching `index.php`. On such a host the pretty URL fails while the plain
`?givewp-route=` form works, because static-extension `location` blocks match the path, not the
query string. If this shows up in support, the fix is to drop the `.js` extension from the URI
(a `<script src>` does not need one); the query-string fallback already avoids it.

## The custom element

`<givewp-donation-form>` requires only `form-id`. The element finds the WordPress site by
stripping the route tail from `document.currentScript.src`, which works for all three URL shapes
above and keeps the home path of a subdirectory install. `wp-url` overrides that for a page that
loads the script from somewhere else; it must be the full home URL, not just the origin, and only
`http:` and `https:` are accepted. Optional attributes: `display-style` (`onpage`, `modal`,
`newTab`), `primary-color`, `secondary-color`, `button-text`, `form-title`, `loading-text`,
`fallback-text`, `close-text`, `locale`.

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
- The form app may ask the host page to navigate (`givewp-navigate`) when it cannot reach
  `window.top` itself. The element honors that only when the message origin is exactly the
  WordPress origin, the source is its own iframe's window, and the URL parses as `http(s)`.
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
