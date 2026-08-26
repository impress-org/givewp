---
paths:
  - "give.php"
  - "src/*/ServiceProvider.php"
  - "src/*/*/ServiceProvider.php"
  - "src/ServiceProviders/**/*"
  - "src/Container/**/*"
  - "src/Helpers/Hooks.php"
---

# Bootstrapping

Read `docs/architecture/bootstrapping.md` when adding a feature or changing how one loads.

- **A service provider must be added to the `$serviceProviders` array in `give.php` (~line 203).**
  It is hand-maintained — no autodiscovery. A provider missing from it never loads, with no error.
  Add-ons append via `give()->registerServiceProvider()` instead.
- **Bindings go in `register()`, hooks go in `boot()`.** Every provider's `register()` runs before
  any provider's `boot()`, so `register()` cannot rely on another domain's bindings. Depending on
  array order works by accident and breaks on reorder.
- **Use `Hooks::addAction()` / `Hooks::addFilter()` for class-method callbacks**, not raw
  `add_action`. The class resolves from the container lazily when the hook fires, `method_exists`
  is verified at registration, and each handler gets a `give_disable_hook-{tag}` /
  `give_disable_hook-{tag}:{class}@{method}` escape hatch. Raw `add_action` is fine for closures.
- `give(SomeClass::class)` resolves from the container with autowiring; `give()->singleton()` and
  `give()->bind()` work via `__call` forwarding.

- **New behavior goes in `src/`, not `includes/`.** To make modern code participate in something
  legacy does, hook a class onto an action or filter the legacy code already fires — see
  `CacheCampaignData` bound to `give_insert_payment`. Editing `includes/` is still right when the
  bug is actually there; the rule is against putting new behavior in legacy files, and against
  editing when a hook would do.

GiveWP initializes on `plugins_loaded` **priority 0**. Anything that must run earlier cannot live
in a service provider.
