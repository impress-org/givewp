---
paths:
  - "src/DonationForms/**/*"
  - "src/FormBuilder/**/*"
  - "src/FormMigration/**/*"
  - "src/Form/**/*"
---

# Donation forms (v2/v3)

Two generations of donation form share the `give_forms` post type. Before changing anything here,
read `docs/architecture/donation-forms.md` — it covers the split, the migration pipeline, and the
campaign relationship.

The three that cause the most damage:

- **There are two classes named `DonationForm`.** `Give\DonationForms\Models\DonationForm` is v3
  and writable; `Give\DonationForms\V2\Models\DonationForm` is v2 and **read-only**. Check the
  `use` statement before assuming which one a file holds.
- **`Utils::isV3Form($formId)` is the only discriminator** — it tests for `formBuilderSettings`
  meta. `Utils::isLegacyForm()` is a different question (form *template*, v2 only), not its
  inverse.
- **`DonationForm::query()` returns v3 forms only.** The repository filters on the v3 meta keys,
  so a v2 form hitting a v3 query returns nothing rather than erroring.

- **v2 is behind a self-initializing feature flag.** `OptionBasedFormEditor::isEnabled()` reads the
  `option_based_form_editor` setting; when unset it checks whether option-based forms exist in the
  database and persists the answer. Fresh installs are off, upgraded sites are on — so a v2 path
  can look dead locally and be live for customers. It gates admin settings visibility, not form
  rendering.

New donor-facing form features go in v3. v2 changes should be bug fixes or migration steps.
