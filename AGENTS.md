# AGENTS.md

Instructions for AI coding agents working in this repository. `CLAUDE.md` is a symlink to this
file — the content is tool-agnostic and lives here.

GiveWP — a WordPress donation/fundraising plugin. PHP + React, distributed on WordPress.org.

## Commands

| Task | Command |
|:--|:--|
| Run tests | `composer test` |
| Run E2E tests | `npm run test:e2e` (needs `npm run env:start`) |
| Run one test | `composer test -- --filter DonationRepositoryTest` or `--filter ClassName::methodName` |
| Build assets (dev) | `npm run dev` |
| Watch assets | `npm run watch` |
| Build assets (production) | `npm run build` |
| Start local WordPress | `npm run env:start` (wp-env, needs Docker) |
| Add a changelog entry | `composer run changelog:add` |

First-time setup: `composer install && npm install && npm run build && npm run env:start`.

Test environment config lives in `tests/wp-tests-config.php` (copied from `.dist.php` by
`composer install`). See [tests/README.md](tests/README.md).

## Required for every change

These are the two things that get missed most often:

1. **`@since TBD` docblocks** on every new or changed method, class, and function. `TBD` is
   replaced with the real version number at release time — never write an actual version number
   yourself.

   ```php
   /**
    * @since TBD
    */
   public function myNewMethod()
   ```

   When changing existing behavior, add a `@since TBD` line describing the change beneath the
   original `@since`:

   ```php
   /**
    * @since TBD Skip readonly schema properties when applying PATCH updates.
    * @since 4.12.0
    */
   ```

2. **A changelog entry.** Run `composer run changelog:add`, which writes a YAML file into
   `changelog/`. Commit it with the change. It gets compiled into `readme.txt` and
   `changelog.txt` at release time.

## Repository conventions

- **Never pass donor records to `wp_localize_script`.** It inlines them into the page HTML for
  anyone to read. Localize configuration — an API root, a nonce, column schema — and let the client
  fetch records through the v3 REST API, which is the intended data layer for admin and public
  alike. REST responses expose donor data only through a ViewModel. Public donor display is a real
  feature, but it is always admin-configured — never a side effect of what a query returned. See
  [docs/architecture/pii.md](docs/architecture/pii.md).
- A new `ServiceProvider` must be added to the `$serviceProviders` array in `give.php` by hand —
  there is no autodiscovery, and a provider missing from it loads nothing and reports no error. See
  [docs/architecture/bootstrapping.md](docs/architecture/bootstrapping.md).
- PRs target the `develop` branch, never `master`.
- PR titles are prefixed with the change type: `Feature:`, `Enhancement:`, `Fix:`, or
  `Chore:`. Use the same type for the changelog entry. Small copy or behavior adjustments
  are enhancements, not a separate type. A PR that only adds or changes tests is prefixed
  `Tests:` and needs no changelog entry.
- No leftover debug code (`var_dump()`, `error_log()`, `console.log()`), and `debug.log` should
  stay empty while testing a change.

## PHP standards

- **PSR-12**, not the WordPress coding standards.
- Must run on **PHP 7.4** — no arrow-function-only syntax like `match`, constructor property
  promotion, enums, or union types.
- Laravel-ish naming: `camelCase` methods and properties, `PascalCase` classes.
- Global functions are prefixed `give_` — no exceptions in the codebase.
- **New hooks are prefixed `givewp_`** (`givewp_donation_form_created`, `givewp_cache_campaign_data`,
  `givewp_list_table_cell_value_{id}`). The older `give_` prefix is still the majority in `src/` and
  stays put for backwards compatibility — don't rename existing hooks, but don't add new ones with
  it either.
- Short array syntax `[]`.
- Bail early with guard clauses instead of nesting.
- Verify nonces and capabilities on anything handling a request.
- PHPStan runs in CI only (level 0 over `src/`) — it isn't installed locally. See `phpstan.neon`
  for the excluded paths and why.

## Models and repositories

Models are the preferred way to read and write GiveWP data — always prefer them over the legacy
`Give_*` classes and over raw `$wpdb` queries.

- Framework: `src/Framework/Models/Model.php`, query builder in `src/Framework/QueryBuilder/`
  (see its [README](src/Framework/QueryBuilder/README.md)).
- Core models: `Donation`, `Donor`, `Subscription`, `Campaign`, `DonationForm` — each at
  `src/<Domain>/Models/`.
- Core repositories: same domains, at `src/<Domain>/Repositories/`. Models delegate the messy
  data access to these.
- Legacy models kept only for backwards compatibility: `Give_Payment`, `Give_Donor`,
  `Give_Donate_Form` (in `includes/`) and `Give_Subscription` (in `src/LegacySubscriptions/`).
  Don't use them in new code.
- Repositories dispatch the model events —
  `givewp_{model}_{creating,created,updating,updated,deleting,deleted}`. New listeners go there.
  Note that the legacy hook bridge is **one-way**, so `give_insert_payment` currently has wider
  coverage than `givewp_donation_created`. See [docs/architecture/models.md](docs/architecture/models.md)
  before adding either.

## Payment gateways

The Gateway API connects donation forms to external payment processors. Read
[docs/architecture/payment-gateways.md](docs/architecture/payment-gateways.md) before writing
gateway code — payments have a strict client/server trust boundary.

- Framework: `src/Framework/PaymentGateways/` — the `PaymentGateway` abstract class, the
  `PaymentGateway` interface, and `PaymentGatewayRegister`.
- Reference implementations: `src/PaymentGateways/Gateways/TestGateway/` (simplest) and
  `src/PaymentGateways/Gateways/Stripe/StripePaymentElementGateway/` (full-featured).
- Subscriptions go through `SubscriptionModule`; modes are in
  `src/Subscriptions/ValueObjects/SubscriptionMode.php`.
- Public docs: https://givewp.com/documentation/developers/how-to-build-a-gateway-add-on-for-givewp/

## JavaScript and React

- TypeScript over plain JavaScript for anything new.
- Modern ES6+, WordPress JS conventions, `camelCase` functions.
- WordPress data stores for state, `@wordpress/*` packages for components and hooks. Follow
  WordPress component patterns and accessibility guidelines.
- All user-facing strings translatable via `@wordpress/i18n` with the correct text domain.
- Formatting follows `.prettierrc.json`: 4-space indent, single quotes, no bracket spacing,
  120-column width.

## Styles

- SCSS over plain CSS.
- BEM naming, prefixed `givewp-`. Nest elements and modifiers inside the block
  (`&__list`, `&--dark`).
- Build reusable atomic pieces (notices, cards, badges) rather than one-off styles.
- Use design system variables rather than hardcoded values:
  - Spacing: `node_modules/@givewp/design-system-foundation/css/spacing.css`
  - Colors: `node_modules/@givewp/design-system-foundation/css/colors.css`
  - Typography: `node_modules/@givewp/design-system-foundation/css/typography.css`

## Layout

**`src/` is modern code. `includes/` is legacy. Work in `src/`.**

This is the most important structural fact about the codebase. `includes/` is largely legacy
procedural code and `Give_*` classes, still loaded and still relied on by customer sites and
add-ons. It is maintained, not extended.

When new behavior needs to interact with legacy code, **hook into `includes/` from `src/` rather
than editing it.** `Give\Helpers\Hooks` exists for exactly this — the legacy code fires plenty of
actions and filters, and attaching a modern class to one is almost always available as an option.
Editing legacy code directly risks breaking add-ons and sites that depend on its current behavior,
and it grows the surface that eventually has to be migrated.

Modifying `includes/` is still correct when the bug is genuinely there — a fix in legacy code
belongs in legacy code. The rule is about not putting *new* behavior there, and not reaching for an
edit when a hook would do.

- `src/` — modern code, organized by domain (`Donations/`, `Donors/`, `Campaigns/`,
  `DonationForms/`, `Subscriptions/`, …). Each domain typically has `Models/`, `Repositories/`,
  `Actions/`, `ValueObjects/`, and a `ServiceProvider.php`.
- `src/Framework/` — shared infrastructure: models, query builder, fields API, migrations,
  payment gateways, HTTP routes, validation.
- `includes/` — legacy procedural code, `Give_*` classes, the settings API, and the legacy admin
  screens.
- `blocks/` — Gutenberg blocks. `assets/` — source styles and images. `templates/` — front-end
  templates.
- `tests/Unit/` and `tests/Feature/` — PHPUnit tests, mirroring the `src/` layout.

## Architecture docs

Deeper per-subsystem notes live in [docs/architecture/](docs/architecture/) — the traps and
rationale that aren't visible from reading the code. Read the relevant one before making
non-trivial changes to that subsystem.

- [bootstrapping.md](docs/architecture/bootstrapping.md) — service providers, the container, the
  `Hooks` helper; start here when adding a feature
- [admin-ui.md](docs/architecture/admin-ui.md) — the three admin generations, the details page
  framework, core-data entities, shared components
- [donation-forms.md](docs/architecture/donation-forms.md) — the v2/v3 split, migration pipeline,
  campaign relationship
- [list-tables.md](docs/architecture/list-tables.md) — column rendering, the N+1 trap, the campaign
  and async-data caches, large-site behavior
- [models.md](docs/architecture/models.md) — models and repositories, the model event lifecycle, and
  the one-way legacy hook bridge
- [payment-gateways.md](docs/architecture/payment-gateways.md) — the Gateway API, the client/server
  trust boundary, webhook verification
- [pii.md](docs/architecture/pii.md) — what donor data may cross to the client, and through which
  boundary

These docs are traced from the code. Where a passage is instead a judgement about what *should*
happen — team convention, or a guess at intent — it is marked **[Judgement]**. Those lines carry no
more authority than the person who wrote them; check with a maintainer before relying on one, and
correct it if the team's position differs. Everything unmarked is observable in the codebase.

If you change a subsystem's architecture, update its doc in the same PR.

## Further reading

- [CONTRIBUTING.md](CONTRIBUTING.md) — contribution and PR process
- [README.md](README.md) — setup and build details
- [RELEASING.md](RELEASING.md) — the release process
- [docs/testing.md](docs/testing.md) — the two suites, what each is for, and how to run them
- [tests/README.md](tests/README.md) — PHPUnit environment and writing tests
