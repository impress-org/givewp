# Donation forms: v2 and v3

Two generations of donation form live side by side in this codebase, on the same post type, with
overlapping class names. Nearly every mistake in this subsystem comes from working on the wrong
one. Read this before changing anything under `src/DonationForms/`.

## How to tell them apart

Both are `give_forms` posts. The only discriminator is a meta key:

```php
Give\Helpers\Form\Utils::isV3Form($formId);
// => (bool) give()->form_meta->get_meta($formId, 'formBuilderSettings', true)
```

A form is **v3** if it has `formBuilderSettings` meta. Otherwise it's **v2**. There is no column,
post status, or taxonomy that tells you — always go through `isV3Form()`.

| | v2 (legacy) | v3 (form builder) |
|:--|:--|:--|
| Namespace | `Give\DonationForms\V2\` | `Give\DonationForms\` (root) |
| Model | `V2\Models\DonationForm` | `Models\DonationForm` |
| Model can write? | **No** — `ModelReadOnly` | Yes — `ModelCrud` |
| Editor | Classic WP metaboxes | React form builder (`src/FormBuilder/`) |
| Field storage | Individual `give_formmeta` rows | Blocks, in `formBuilderFields` meta |
| Settings storage | Individual meta rows | One serialized blob in `formBuilderSettings` |
| Front-end render | PHP templates (`src/Views/Form/Templates/`) | React app (`src/DonationForms/resources/app/`) |

## v2 is behind a feature flag

The v2 editor — "option-based forms" in current terminology — is gated by
`Give\FeatureFlags\OptionBasedFormEditor\OptionBasedFormEditor::isEnabled()`, reading the
`option_based_form_editor` setting.

The flag **self-initializes on first read**. If the option is unset, it queries whether any
option-based forms exist in the database, persists the answer, and returns it:

```php
$option = give_get_option('option_based_form_editor', '');

if (empty($option)) {
    $option = self::existOptionBasedFormsOnDb();
    give_update_option('option_based_form_editor', $option ? 'enabled' : 'disabled');
    return $option;
}
```

So a fresh install has it **off**, and a site upgraded from a version with v2 forms has it **on** —
without anyone touching a setting. That's why a v2 code path can look dead on your local install
and be live on a customer's.

What the flag actually gates, per
`FeatureFlags/OptionBasedFormEditor/ServiceProvider::maybeDisableOptionBasedFormEditorSettings()`,
is **admin settings visibility**: it filters v2 options out of the General, Default Options, and
Advanced settings tabs, and removes the `v2` group from the payment gateways tab.
`V2\DonationFormsAdminPage` also passes `isOptionBasedFormEditorEnabled` through to its UI.

It is a settings-and-affordances flag, not a kill switch — don't assume "flag off" means no v2
forms are rendering on the site.

## The traps

**Two classes named `DonationForm`.** `Give\DonationForms\Models\DonationForm` is v3 and supports
create/update/delete. `Give\DonationForms\V2\Models\DonationForm` is v2 and is **read-only** — it
implements `ModelReadOnly`, so there is no `save()`. If you're reaching for a v2 write, you're on
the wrong path; v2 forms are written through legacy meta functions, not the model.

Check the `use` statement before assuming which model a file is holding. Some files import both,
aliased — `src/DonationForms/V2/Models/DonationForm.php` itself imports the v3 model as
`ModelsDonationForm`.

**v3 queries silently exclude v2 forms.** `DonationFormRepository::prepareQuery()` adds
`whereIsNotNull` on both the `formBuilderSettings` and `formBuilderFields` meta joins. So
`DonationForm::query()` returns v3 forms only. The docblock records this as deliberate — *"@since
3.12.1 Prevent returning forms without the `formBuilderSettings` and `formBuilderFields` meta
keys"* — but it means "the form exists but my query returns nothing" is almost always a v2 form
hitting a v3 query.

**"Legacy" means two different things.** `Utils::isLegacyForm()` does *not* mean "is a v2 form" —
it checks whether the form's *template* is the `legacy` template, and only v2 forms have
templates at all. Templates are registered in `src/Form/Templates.php` via the
`give_register_form_template` filter — `sequoia` (multi-step), `classic`, `legacy` — with the
classes under `src/Views/Form/Templates/`. So there are effectively three generations in the wild:

1. v2 form on the `legacy` template — the pre-2.7 form markup
2. v2 form on a form template — `sequoia` or `classic`
3. v3 form built in the form builder

`isLegacyForm()` distinguishes 1 from 2. `isV3Form()` distinguishes 3 from 1-and-2. They answer
different questions and are not inverses of each other.

**v3 writes v2-shaped meta on every save.** `StoreBackwardsCompatibleFormMeta` runs on
`givewp_donation_form_created` and `givewp_donation_form_updated`, projecting v3 blocks back into
legacy meta keys — donation levels, goal, recurring settings. Add-ons and legacy reporting read
those keys.

Consequence: if you add a v3 field that legacy code needs to see, the field alone isn't enough —
the projection in `StoreBackwardsCompatibleFormMeta` has to learn about it too. And if legacy meta
looks stale for a v3 form, suspect this action rather than the reader.

## Where a request enters

**v3, embedded in a page** — the `[give_form]` shortcode (`Shortcodes/GiveFormShortcode.php`) and
the donation form block both route to `BlockRenderController`. Note the shortcode returns `$output`
unchanged when the form isn't v3, handing off to the legacy shortcode handler. If
`BlockRenderController` renders nothing, it falls back to an iframe pointed at the view route.

**v3, standalone view** — `Controllers/DonationFormViewController::show()` for the real form and
`::preview()` for the builder preview. Both build a `DonationFormViewModel` and render the React
app. `preview()` takes blocks and settings from the request rather than the database, which is how
unsaved builder state renders.

**Donation submission** — `Controllers/DonateController.php`, reached via `Routes/DonateRoute.php`.
Routes are signed; see `Routes/DonateRouteSignature.php` and the `Generate*RouteUrl` actions in
`Actions/`.

Legacy v2 submission goes through `includes/process-donation.php`, which branches on
`FormUtils::isV3Form()` near the top. Same for `includes/gateways/actions.php`.

## Migration (v2 → v3)

`src/FormMigration/` converts a v2 form into a v3 one. It's a pipeline (`Pipeline.php`) of steps
in `Steps/`, one per concern — `DonationGoal`, `PaymentGateways`, `FormFields`, plus a step per
supported add-on (`Mailchimp`, `GiftAid`, `FeeRecovery`, …). `FormMetaDecorator.php` wraps raw v2
meta access for the steps.

If you add a v2 setting that has a v3 equivalent, it needs a migration step, or migrated forms
lose it silently.

`V2/Actions/ConvertV2FormToV3Form.php` is a separate, narrower conversion used for display
purposes — don't confuse it with the migration pipeline.

## Relationship to campaigns

Since 4.0 every form belongs to a campaign, joined through the `give_campaign_forms` table
(`src/Campaigns/Migrations/Tables/CreateCampaignFormsTable.php`). Campaigns own the goal, and
`Campaigns/Actions/FormInheritsCampaignGoal.php` pushes it down to the form.

So a form's goal may not be the form's own setting. Creating a form outside a campaign context
produces an orphan — see `src/DonationForms/OrphanedForms/` and
`Campaigns/Actions/CreateDefaultCampaignForm.php`.

## Which one do I touch?

- **Bug affecting existing sites' forms** → check whether the report is about a v2 or v3 form
  first. The fix location differs completely, and "works on my form" usually means the two of you
  tested different generations.

**[Judgement]** The rest of this section is inferred from the codebase's direction — v2 is
read-only at the model layer, has a migration pipeline pointing away from it, and receives no new
capability — not from a stated policy. On that reading: new donor-facing form features go in v3,
and v2 changes are bug fixes or migration steps rather than features. Confirm with a maintainer
before acting on it for anything substantial.

## Related

- `src/FormBuilder/` — the React form builder that produces v3 forms
- `src/Framework/FieldsAPI/` — the field schema v3 forms compile down to ([README](../../src/Framework/FieldsAPI/README.md))
- `src/Framework/Blocks/` — block collection stored in `formBuilderFields`
- `src/DonationForms/resources/app/` — the front-end React form
