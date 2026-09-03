# Models, repositories, and the event model

Models are how modern GiveWP reads and writes its data, and — less obviously — they are the source
of the plugin's event stream. Understanding which hooks fire from where, and which ones see
everything, is the difference between a listener that works and one that silently misses half its
events.

## The layer

A model is a typed object over a domain record; a repository holds the SQL. Models delegate to
repositories rather than querying themselves.

```
src/Framework/Models/Model.php              base class
src/Framework/Models/Contracts/ModelCrud        find / create / save / delete
src/Framework/Models/Contracts/ModelReadOnly    find / query only — no save()
src/Framework/Models/Contracts/ModelHasFactory  test factories
src/Framework/Models/ModelQueryBuilder.php  query builder returning hydrated models
```

Core models are `Donation`, `Donor`, `Subscription`, `Campaign`, and `DonationForm`, each at
`src/<Domain>/Models/` with a matching `src/<Domain>/Repositories/`.

Check the contract before assuming you can write. `ModelReadOnly` has no `save()` — the v2
`DonationForm` model is the notable case, see [donation-forms.md](donation-forms.md).

Prefer models over the legacy `Give_*` classes and over raw `$wpdb`. The query builder is
documented in its own [README](../../src/Framework/QueryBuilder/README.md).

## The event model

**Repositories fire the hooks, not models.** Every core model has a full six-event lifecycle,
dispatched from its repository's `insert()`, `update()`, and `delete()`:

```
givewp_{model}_creating   →  givewp_{model}_created
givewp_{model}_updating   →  givewp_{model}_updated
givewp_{model}_deleting   →  givewp_{model}_deleted
```

`{model}` is one of `donation`, `donor`, `subscription`, `campaign`, `donation_form`. The `-ing`
events fire before the write, the `-ed` events after, and the `-ed` listeners receive the persisted
model with its `id` populated.

**These are the hooks new code should listen on.** They carry a typed model rather than a loose
array, and they're the layer the codebase is moving toward.

Dispatch goes through `Hooks::doAction()`, which calls `do_action()` and then writes a
`Hook Dispatched: {name}` entry at debug level. Useful when tracing why a listener didn't run —
and worth remembering that it means hook dispatch is visible in the log table.

## Backwards compatibility: the legacy bridge

The legacy hooks — `give_insert_payment`, `give_update_payment_status`, and friends — predate the
model layer and are relied on by add-ons, customer snippets, and code in `includes/`. They are
still fired, but **not by the modern write path directly**. A set of bridge listeners re-dispatches
them from the model events:

```
src/Donations/LegacyListeners/
    DispatchGivePreInsertPayment              ← givewp_donation_creating
    DispatchGiveInsertPayment                 ← givewp_donation_created
    DispatchGiveUpdatePaymentStatus           ← givewp_donation_updated
    DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment
src/Subscriptions/LegacyListeners/
src/Revenue/LegacyListeners/
```

Each rebuilds the legacy payload shape — `DispatchGiveInsertPayment` assembles a
`GiveInsertPaymentData` array with `price`, `formTitle`, `userInfo`, and the rest — and dispatches
the old hook. The re-dispatch is marked `@deprecated` in its docblock.

The bridge is **conditional on someone listening**:

```php
if (!has_action('give_insert_payment')) {
    return;
}
```

That guard is a performance measure — building the legacy payload loads the donor and derives price
IDs. Don't remove it on the assumption it's redundant.

## The trap: the bridge is one-way

**Model → legacy only.**

- A donation written through `Donation` / `DonationRepository` fires `givewp_donation_created`, and
  the bridge then fires `give_insert_payment`. **Both** run.
- A donation written through the legacy `give_insert_payment()` function — which uses
  `Give_Payment::save()` and never touches the repository — fires `give_insert_payment` only.
  `givewp_donation_created` does **not** fire.

The consequence is the opposite of what you'd expect from a deprecation:

> A listener on the **deprecated** `give_insert_payment` sees donations from both write paths. A
> listener on the **modern** `givewp_donation_created` sees only those written through the model.

This is why `Campaigns/Actions/CacheCampaignData` is bound to `give_insert_payment` and
`give_update_payment_status` rather than the model hooks — it needs complete coverage of donation
events. That's a deliberate choice for correctness, not an un-migrated leftover. Don't "modernize"
a legacy listener to a model hook without first confirming nothing writes that record through the
legacy path.

## Direction of travel

**[Judgement — confirmed with maintainers]** Model hooks are the target. The intent is to converge
on them completely **when coverage allows** — that is, as legacy write paths are retired or routed
through repositories, so that a model hook genuinely sees every event. Until then the legacy hooks
stay, and stay fired.

Practically, for new work:

- **New listeners go on model hooks** — unless you specifically need coverage of a legacy write
  path, in which case use the legacy hook and note why.
- **Don't add new listeners to legacy hooks casually**, and don't fire new legacy-shaped hooks.
- **Don't remove bridge listeners.** They're what keeps add-ons and customer snippets working.
- **Write new data through models and repositories**, never through `Give_Payment` and friends.
  Every write that goes through the repository is one more event the model hooks can see, which is
  how coverage improves.

## Checklist

- Listening for a donation/donor/subscription event? Model hook by default; legacy hook only if you
  need coverage of legacy writes, with a comment saying so.
- Writing data? Through the model/repository, so the model events fire.
- Touching a `LegacyListeners/` class? Understand which model hook feeds it before changing it.
- Model needs a new field? The repository's `insert()`/`update()` and the meta mapping both need
  it — and check whether a ViewModel exposes it ([pii.md](pii.md)).
- Assuming you can `save()`? Check whether the model implements `ModelCrud` or `ModelReadOnly`.

## Related

- `src/Framework/Models/` — base class, contracts, query builder integration
- `src/Framework/QueryBuilder/` — [README](../../src/Framework/QueryBuilder/README.md)
- `src/<Domain>/Repositories/` — where the events are dispatched
- `src/Donations/LegacyListeners/` — the bridge
- [donation-forms.md](donation-forms.md) — the two `DonationForm` models and their contracts
