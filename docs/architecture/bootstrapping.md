# Bootstrapping: service providers, the container, and hooks

Every feature in `src/` reaches the rest of WordPress through the same three mechanisms: a service
provider registers it, the container resolves it, and `Hooks` binds it to a WordPress hook. Getting
any of the three subtly wrong produces code that loads without error and simply never runs.

## The boot sequence

1. `give.php` ends with `give()->boot()`, executed when WordPress includes the plugin file.
2. `Give::boot()` defines constants and registers `add_action('plugins_loaded', [$this, 'init'], 0)`
   — **priority 0**, so GiveWP initializes before most other plugins' `plugins_loaded` handlers.
3. `Give::init()` fires `before_give_init`, binds a couple of core classes, installs the uncaught
   exception handler, then calls `loadServiceProviders()`, loads form templates and routes, and
   fires `give_init`.
4. `loadServiceProviders()` walks `$serviceProviders` and runs the two-phase cycle below.

`loadServiceProviders()` is also called from `Give::install()` on plugin activation, and is guarded
by a `$providersLoaded` flag so it runs once per request.

## Service providers

A provider implements `Give\ServiceProviders\ServiceProvider` — two methods, and the split between
them is load-bearing:

```php
public function register()  // bind things into the container. Nothing else exists yet.
public function boot()      // wire up hooks. Every provider has registered by now.
```

`loadServiceProviders()` runs **every** `register()` first, then **every** `boot()`:

```php
foreach ($this->serviceProviders as $serviceProvider) {
    $serviceProvider = new $serviceProvider();
    $serviceProvider->register();
    $providers[] = $serviceProvider;
}

foreach ($providers as $serviceProvider) {
    $serviceProvider->boot();
}
```

That's the whole reason for two phases. In `register()` you cannot rely on another domain's
bindings existing yet. By `boot()`, all of them do. Resolving a cross-domain dependency in
`register()` works only by accident of array order, and breaks when someone reorders the list.

### The trap: the array in give.php

`$serviceProviders` is a **hardcoded, hand-maintained array** at `give.php:203`. A provider not in
that array never loads. There is no autodiscovery, no directory scan, and no warning — the feature
just doesn't exist, with no error to explain why.

Creating `src/MyFeature/ServiceProvider.php` is half the job. Adding it to that array is the other
half, and it's the half that gets forgotten.

Two supporting details:

- The loop validates `is_subclass_of($serviceProvider, ServiceProvider::class)` and throws
  `InvalidArgumentException` if not, so a provider that forgets the interface fails loudly. That is
  the *only* failure mode that announces itself.
- The array is hand-maintained enough that `LegacySubscriptionsServiceProvider::class` currently
  appears in it twice.

**Order matters only for `register()`.** If you find yourself needing a specific position in the
array, that's a sign the work belongs in `boot()` instead.

### Add-ons

Add-ons don't edit the array. They call `give()->registerServiceProvider(MyProvider::class)`, which
appends to it — necessarily before `plugins_loaded` priority 0 has run.

## The container

`give()` returns the `Give` instance; `give(SomeClass::class)` resolves out of the container
(`Give\Container\Container`, a Laravel-style container).

```php
give(DonationRepository::class);            // resolve, autowiring constructor dependencies
give()->singleton('forms', DonationFormRepository::class);
give()->bind(Foo::class, fn() => new Foo());
give()->singleton(TemplateHandler::class, function () { /* … */ });
```

`Give::__call` forwards unknown methods to the container, which is why `give()->singleton()` and
`give()->bind()` work even though `Give` defines neither. `Give::__get` forwards property access to
`$this->container->get()`, which is how the legacy accessors resolve:

```php
give()->form_meta->get_meta($formId, 'formBuilderSettings', true);
give()->payment_meta->update_meta(/* … */);
```

Constructor dependencies are autowired, so type-hinting a class is usually enough — explicit
binding is for interfaces, string aliases, and anything needing construction logic.

## Hooks

Use `Give\Helpers\Hooks`, not raw `add_action`/`add_filter`, when the callback is a class method:

```php
Hooks::addAction('wp_head', PrintFormMetaTags::class);                  // defaults to __invoke
Hooks::addAction('init', DonationFormBlock::class, 'register');
Hooks::addAction('give_insert_payment', CacheCampaignData::class, '__invoke', 11, 1);
Hooks::addFilter('give_donation_total', AdjustTotal::class, 'filter');
```

Three things this buys you over `add_action`:

- **Lazy instantiation.** The class is resolved from the container when the hook fires, not when it
  is registered. A provider's `boot()` can wire up a hundred handlers without constructing any of
  them.
- **Fail-fast on typos.** `method_exists($class, $method)` is checked at registration time and
  throws `InvalidArgumentException`, rather than producing a silent no-op at runtime.
- **A disable filter, per hook and per handler:**
  ```php
  add_filter('give_disable_hook-wp_head', '__return_true');
  add_filter('give_disable_hook-init:Give\…\DonationFormBlock@register', '__return_true');
  ```
  Useful for third-party compatibility work and for tests that need one handler out of the way.

Raw `add_action` is still correct for closures and plain functions.

### Hooks are how modern code reaches legacy code

`includes/` is legacy and shouldn't be extended — new behavior belongs in `src/`. When that new
behavior has to participate in something legacy does, the intended route is to hook a modern class
onto an action or filter the legacy code already fires, rather than editing `includes/`.

`Give\Campaigns\Actions\CacheCampaignData` is a representative example: a modern action class,
bound to the legacy `give_insert_payment` and `give_update_payment_status` hooks, with no change to
the legacy payment code at all.

Editing `includes/` directly is still right when the bug is genuinely there. The rule is about not
putting new behavior in legacy files, and not reaching for an edit when a hook is available.

## Checklist for a new feature

- Provider created **and** added to `$serviceProviders` in `give.php`? (Add-ons:
  `registerServiceProvider()`.)
- Bindings in `register()`, hooks in `boot()` — nothing resolving another domain in `register()`?
- Class-method callbacks going through `Hooks::` so they stay lazy?
- Anything needed before `plugins_loaded` priority 0? If so, it can't live in a provider.

## Related

- `give.php` — `$serviceProviders` (line ~203), `loadServiceProviders()`, `init()`, `give()`
- `src/ServiceProviders/ServiceProvider.php` — the interface, with docblocks on the two phases
- `src/Container/Container.php` — `bind`, `singleton`, `alias`, `tag`, `extend`, `make`
- `src/Helpers/Hooks.php` — `addAction`, `addFilter`, `doAction`
