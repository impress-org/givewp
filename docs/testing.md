# Testing

GiveWP has two test suites. They cover different failure modes and neither replaces the other.

| Suite | Runs | Command | CI |
|:--|:--|:--|:--|
| PHPUnit | PHP, in-process, against the WordPress test library | `composer test` | `.github/workflows/wordpress.yml` |
| Playwright | A real browser against a real WordPress install | `npm run test:e2e` | `.github/workflows/tests-e2e.yml` |

## PHPUnit

The bulk of the coverage. Models, repositories, actions, REST routes, migrations — anything whose
behavior can be asserted without a browser belongs here, because it runs in seconds and fails with
a stack trace pointing at the line.

Setup and conventions are in [tests/README.md](../tests/README.md).

`composer test` runs the suite through [paratest](https://github.com/paratestphp/paratest), one
worker per CPU core. Each worker bootstraps WordPress under its own table prefix (`wptests1_`,
`wptests2_`, ...) taken from the `TEST_TOKEN` environment variable paratest sets, so workers never
see each other's rows. Two things follow from that: every test file must contain a class whose name
matches the file name, because PHPUnit's single-file loader is strict about it, and anything that
hardcodes `wptests_` must use `$wpdb->prefix` instead. `composer test:serial` runs plain PHPUnit in
one process; use it with `--filter`, which paratest does not support in this mode, and when debugging.

Add-ons run this same suite against core: an add-on's `tests/bootstrap.php` requires GiveWP's
autoloader from the sibling directory and hands its main plugin file to
`Give\Tests\Framework\Addons\Bootstrap`, which loads the add-on on `muplugins_loaded` and then
defers to core's `tests/bootstrap.php`. In CI the shared
`impress-org/givewp-github-actions/.github/workflows/addon-tests.yml` workflow checks out the
GiveWP branch named by its `givewp_branch` input, so an add-on can be tested against `develop`
before core ships.

## Playwright

E2E tests live in `tests/e2e/` and run against a WordPress install started by
[`wp-env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/).

**This suite is written for CI.** It exists to catch what PHPUnit structurally cannot see: assets
that fail to enqueue, React apps that fail to mount, and v3 REST requests that error only once a
real browser makes them with a real nonce. Running it locally is supported and useful while writing
a spec, but the environment it is designed against is the clean install the workflow builds.

Keep the specs thin. A browser test that asserts something a PHPUnit test could have asserted is a
slow test with a worse failure message.

### Layout

**One spec file per screen**, named for the screen, holding everything that screen is covered for:
`campaigns.spec.ts`, `donation-forms.spec.ts`, `donor-dashboard.spec.ts`, `form-builder.spec.ts`,
`legacy-donation-forms.spec.ts`, `reports.spec.ts`, `tools-logs.spec.ts`, `tools-migrations.spec.ts`.

A v3 donation form is two screens, not one: the form builder that edits it in wp-admin and the
form itself on the front end. They share a subject and nothing else - different apps, different
users, different failures - so they get a file each.

New coverage goes in the file for the screen it exercises. Grouping by the kind of assertion
instead — every screen's mount check in one file, every screen's REST check in another — puts one
screen's coverage in as many files as there are kinds of assertion, so nothing tells you what a
screen is actually covered for, and each new kind adds a file that re-lists every screen.
`test.describe` already groups within a file, which is where that grouping belongs.

`admin-pages.spec.ts` is the deliberate exception. It is a breadth-first smoke suite — each admin
screen that mounts a React app, one assertion each — and its job is to be pointed at environments
core does not control, which is what the add-on plan below builds on. Screens with a spec file of
their own are not repeated in it.

Shared helpers live in `tests/e2e/utils/`. `utils/campaign.ts` creates a campaign over the v3 REST
API and hands back the donation form that came with it, so a spec that needs a v3 form makes its own
rather than depending on what a site already has. `utils/form.ts` reads and writes that form over
`givewp/v3/form/<id>` - the same route the builder saves through - which is how a donor-facing spec
sets up the variation it needs without driving the builder to get there. `utils/donation-form.ts`
holds the embed iframe locator and the steps every donating spec repeats. `utils/rest.ts` records the REST calls a page makes and
asserts it reached the route it owns with nothing failing. `utils/legacy-form.ts` creates a v2 form
and enables a gateway for it through `utils/wp-cli.ts`, because neither has a REST route: a v2 form
is a post with protected meta, and GiveWP's settings are one option. WP-CLI runs in the wp-env this
checkout started, so those fixtures assume `WP_BASE_URL` points at it. Assert on that traffic rather than on
rendered records: a wrong route, a missing nonce, or a response shape the client no longer unwraps
all produce a page that builds and mounts cleanly and then shows nothing.

### Donations need the Test Donation gateway

Every spec that completes a donation pays with Test Donation (`manual`), the only gateway that can
finish one without an account somewhere else. It is enabled on a fresh install, so CI has it. A
developer site that has turned it off skips those tests rather than failing them - the message says
so. Turn it back on under Give > Settings > Payment Gateways to run them locally.

The offsite path is different: the donor leaves the site and comes back to a signed gateway route,
and no gateway that ships enabled does that. `legacy-donation-forms.spec.ts` pays with Test Gateway
(Offsite), whose "offsite" page is the return route itself. It only registers when
`GIVEWP_ENABLE_TEST_OFFSITE_GATEWAY` is defined, which `.wp-env.json` does for the environment the
suite runs against and nothing else should.

### CI

`.github/workflows/tests-e2e.yml` installs Composer and npm dependencies, runs `npm run build`,
starts wp-env on the port in `.wp-env.json`, and runs the suite. Failure artifacts (screenshot,
video, trace) upload on failure. Open a trace with `npx playwright show-trace <path to trace.zip>`.

### Running locally

```bash
# First time only
npm install
composer install
npx playwright install chromium

npm run build       # the admin screens under test are React apps served from build/
npm run env:start
npm run test:e2e
```

`npm run test:e2e:headed` opens a visible Chrome window with each action slowed to 800ms.
`npm run test:e2e:ui` opens Playwright's time-travel viewer, which lets you step through a run and
see a screenshot at every action.

If your wp-env is not on the port in `.wp-env.json` — a `.wp-env.override.json`, a TLS proxy in
front of it, `--auto-port` picking something else — point the run at it:

```bash
WP_BASE_URL=https://give.wpenv.net:9443 npm run test:e2e
```

### Two things the setup handles for you

- `@wordpress/e2e-test-utils-playwright` reads `WP_BASE_URL` from the environment once at module
  load and otherwise defaults to `http://localhost:8889`, wp-env's tests environment. The `baseURL`
  passed to `RequestUtils.setup()` never reaches it, so REST API discovery would go to 8889
  regardless of what the rest of the suite targets. `tests/e2e/environment.ts` writes the resolved
  URL back into `process.env` to keep the two in agreement.
- `RequestUtils` builds its own request context with no way to pass `ignoreHTTPSErrors`, so a
  self-signed local certificate fails the login before any spec runs — while the browser, which
  does get `ignoreHTTPSErrors`, would have been fine. `tests/e2e/global-setup.ts` relaxes Node's
  certificate check for local HTTPS runs only; CI talks to wp-env over plain HTTP with verification
  on.
- A WordPress install answering on the expected port is not necessarily the one under test. If
  another wp-env environment holds the port, every spec would run green against an unrelated site.
  `global-setup.ts` checks that the REST API exposes the `givewp/v3` namespace and fails with that
  explanation if it does not. Seeing that error means `npm run env:start` picked a different port —
  `env:start` passes `--auto-port` so it can step aside from a busy one — and the run needs
  `WP_BASE_URL`.

### Writing tests

Specs use `@wordpress/e2e-test-utils-playwright`, which supplies the `admin`, `page`, and
`requestUtils` fixtures. `tests/e2e/global-setup.ts` logs in once and saves the session to
`artifacts/storage-states/admin.json`, so no spec pays for a login.

Assertions should not depend on the site's data. CI runs against a fresh install; a developer runs
against whatever their wp-env site accumulated. Assert on structure — a root element, a page
heading, a form field — not on a row count or an empty state, unless the test seeds that state
itself through `requestUtils.rest()`.

## Add-on smoke tests in CI

`.github/workflows/tests-e2e.yml` installs the latest GitHub release of each add-on named in its
`ADDONS` env next to core before starting wp-env, so the whole Playwright suite runs with them
active. A fatal on activation, a filter typed against the wrong model, an asset that 404s: the core
specs see all of it. `tests/e2e/addon-peer-to-peer.spec.ts` adds what they cannot: that the add-on
really is active, so a failed download cannot pass as a green run, and that its own admin screen
still mounts.

### Add-ons come from their release zips, not from source

Every add-on publishes a single `<slug>.zip` asset on its GitHub release, built by the shared
`givewp-release.yml` workflow. It is what customers install: `vendor/` is there, assets are
compiled, and everything is wrapped in a `<slug>/` directory, so it unzips straight into
`addons/`. That skips the per-add-on `composer install` and `npm run build` a source checkout
would need, and tests the artifact people actually run.

`.wp-env.override.json` is gitignored and outranks `.wp-env.json`, so the job writes one naming
`.` plus each unpacked add-on. wp-env activates every plugin it names. The override also turns
`WP_DEBUG_DISPLAY` off: an add-on's PHP deprecation notice printed into a REST response breaks its
JSON and with it every spec, which is a finding for the add-on, not for this suite. The job then
exports `E2E_ADDONS` for the specs.

`addons/` is gitignored too, so reproducing a CI failure locally is the same two steps:

```sh
gh release download --repo impress-org/give-peer-to-peer --pattern give-peer-to-peer.zip --dir addons
unzip -q addons/give-peer-to-peer.zip -d addons
echo '{"plugins": [".", "./addons/give-peer-to-peer"], "config": {"WP_DEBUG_DISPLAY": false}}' > .wp-env.override.json
npm run env:start
E2E_ADDONS=give-peer-to-peer npm run test:e2e
```

### The token

The add-on repositories are private and `GITHUB_TOKEN` is scoped to this repository, so the job
mints an installation token with `actions/create-github-app-token` from the
`givewp-ci-add-on-reader` GitHub App, installed on every `impress-org` repository with
`Contents: read` and nothing else. Two repository secrets carry it: `ADDONS_APP_ID` and
`ADDONS_APP_PRIVATE_KEY`. An App rather than a personal token because it belongs to the
organization, not to whoever created it, and each run's token expires in an hour.

The installation is organization-wide on purpose, so adding an add-on never needs an org owner.
The workflow narrows each run's token to the repositories in `ADDONS`, so the wide installation
does not widen what a run can read.

Pull requests from forks get no secrets. The add-on steps skip, `E2E_ADDONS` stays unset, the
add-on specs skip themselves, and the suite runs against core alone. That is the only case that
skips: on any other trigger a missing secret fails the token step, so a deleted secret cannot
quietly turn the suite into a core-only run.

### Adding an add-on

1. Add its repository name to the comma-separated `ADDONS` env in
   `.github/workflows/tests-e2e.yml`. The name is the `impress-org` repository, which is also the
   plugin directory and the release asset name (`give-recurring` publishes `give-recurring.zip`).
2. Run the suite locally with it first (commands above, one more `gh release download` and one
   more entry in the override's `plugins`). Every core spec now runs with the add-on active, and
   whatever breaks is a finding for the add-on or for core, so sort it out before opening the PR.
3. Copy `tests/e2e/addon-peer-to-peer.spec.ts` to `addon-<slug>.spec.ts`, change `SLUG`, and point
   the admin page test at the add-on's own screen. Two assertions is the target: it is active, and
   its admin screen mounts. Add-on features get their own tests only when a core change has to be
   checked against them.
4. Open the PR. The E2E job on it runs with the add-on; the "Install the latest add-on releases"
   step log shows the release it fetched.

Nothing else. No secret, no App change, no override file in the repository.

### The other direction

Running this from each add-on's own repository needs no new secrets at all: the add-on's
`GITHUB_TOKEN` checks out the add-on, and GiveWP core is public so the same token checks that out
too. That mirrors the PHPUnit setup already in place — a reusable `addon-e2e-tests.yml` beside
`addon-tests.yml` in `impress-org/givewp-github-actions`, taking the same `givewp_branch` input.

It catches an add-on change breaking against core. It does not catch a core change breaking an
add-on, which is the direction that needs the token above.
