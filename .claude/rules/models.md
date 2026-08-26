---
paths:
  - "src/Framework/Models/**/*"
  - "src/*/Models/**/*"
  - "src/*/Repositories/**/*"
  - "src/*/LegacyListeners/**/*"
  - "includes/payments/**/*"
  - "includes/class-give-donor.php"
---

# Models and the event model

Read `docs/architecture/models.md` before adding a listener or changing a repository.

- **Repositories fire the events, not models.** Every core model has
  `givewp_{model}_{creating,created,updating,updated,deleting,deleted}` —
  `donation`, `donor`, `subscription`, `campaign`, `donation_form`.
- **The legacy bridge is one-way: model → legacy.** `src/*/LegacyListeners/` re-dispatch
  `give_insert_payment` and friends *from* model hooks. A write through the legacy
  `give_insert_payment()` function never fires `givewp_donation_created`.
- **So the deprecated hook has the wider coverage.** A listener on `give_insert_payment` sees both
  write paths; one on `givewp_donation_created` sees only model writes. `CacheCampaignData` uses
  the legacy hooks deliberately for that reason — don't "modernize" a legacy listener without
  confirming nothing writes that record through the legacy path.
- **New listeners go on model hooks** unless they specifically need legacy coverage (say so in a
  comment). Don't remove bridge listeners — add-ons and customer snippets depend on them. Don't
  delete the `if (!has_action(...)) return;` guard; building the legacy payload is expensive.
- **Write through models and repositories**, never `Give_Payment` and friends. Each write moved onto
  a repository is one more event the model hooks can see.
- Check `ModelCrud` vs `ModelReadOnly` before assuming `save()` exists.

Direction of travel: converge on model hooks completely **when coverage allows**. Until legacy write
paths are retired or routed through repositories, the legacy hooks stay and stay fired.
