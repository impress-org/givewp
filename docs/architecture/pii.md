# Donor PII: what may cross to the client

This document exists because donor emails once reached the page's JavaScript context. That class of
failure is easy to reintroduce, because the data is legitimately present server-side at every step
— the leak is never a query that shouldn't have run, it's an object that travelled one layer
further than it needed to.

**The rule: donor data is exposed only where someone with authority chose to expose it, through an
output shape built for that purpose.** The default has to be exclusion, not omission — "nothing
currently prints it" is not a boundary.

PII here means at minimum: email address, physical address, phone, IP, full name in a context where
the donor chose anonymity, and any custom field a form collected.

## Why this is not a blanket rule

**What the product does** — verifiable from the code: GiveWP ships public donor display, and has
for a long time.

The **Donor Wall** (`[give_donor_wall]`, `includes/donors/class-give-donor-wall.php`) is the
canonical case. Customers put it on public pages specifically to show off who is giving, and its
shortcode attributes are the consent model in miniature — `anonymous`, `show_name`, `show_avatar`,
`show_company_name`, `show_total`, `show_comments`, `show_tributes`, `show_time`. Every one is a
site owner's decision about what appears.

The newer `CampaignDonors` and `CampaignDonations` blocks do the same thing with block attributes
(`showAnonymous`, `showAvatar`, `showCompanyName`, `sortBy`, `donorsPerPage`).

This is intended, configured behavior across both the legacy and modern stacks. A rule of "no donor
data on public pages" would flag two of the plugin's long-standing features as bugs, and an agent
applying it would break them.

**Why it's built that way** — **[Judgement]**, a design position held by the maintainers rather
than a neutral fact:
fundraising is treated as a different category from traditional finance. Donors frequently *want*
to be seen giving; the public donor list is understood as a feature of the category, as on
GoFundMe, so a donor's name on the front end is often the point rather than a leak. This is
informed by domain experience and it drives the current design, but it is a judgement call and a
reasonable person could weigh it differently. It's recorded here because it explains decisions you
will otherwise read as carelessness — not because it settles the argument.

The rest of this document follows from the first paragraph, which holds regardless of whether you
accept the second.

The actual invariant is consent, and it has two layers that both have to hold:

1. **The donor's choice.** A donation carries an anonymous flag. Where it's set, that donor's
   identity is not displayed regardless of configuration.
2. **The admin's configuration.** Displaying donors at all is something a site owner opts into and
   tunes, via shortcode attributes on the Donor Wall or block attributes on the campaign blocks.
   Nothing about a donor appears publicly because code decided it should.

**GiveWP never exposes PII without admin consent.** So the question for any new surface is not "is
this data sensitive?" but "who decided this would be visible, and where is that decision
recorded?" If the answer is "nobody, it's just what the query returned," that's the bug — whether
or not the field looks sensitive.

Note the asymmetry: the two consent layers cover *identity display* — name, company, avatar. They
have never been a licence for contact details. Email, address, phone, and IP have no display
configuration, and adding one would be a product decision rather than a code change.

## The v3 REST API is the intended data layer

The v3 REST API (`src/API/REST/V3/`) was built to be the **single way data is fetched, for both
admin and public consumers** — blocks and shortcodes included, not just the admin React apps. That
is the direction of travel, and new work should follow it.

**Admin surfaces should essentially always go through REST.** There is no reason for an admin page
to inline records into HTML when it can fetch them behind a permission check.

**Public surfaces are less settled.** Some render server-side today; some fetch. Either can be
correct for now, but the public side carries the higher cost of a mistake, so the bar is: be
deliberate about what reaches the page, and prefer fetching over inlining when there's a choice.

The split is already enforced at the data layer for anything using core-data entities. GiveWP ships
two entity bundles — `entities-admin.ts` and `entities-public.ts` — registering the same entities
with different defaults:

| | admin | public |
|:--|:--|:--|
| `includeSensitiveData` | `true` | `false` |
| `anonymousDonations` / `anonymousDonors` | `include` | `redact` |

Those values are the guard. Don't load the admin bundle on a public page, and don't loosen the
public one's params to make a public feature show more — that's the decision this document says
belongs to an admin setting, not to code. See [admin-ui.md](admin-ui.md).

### An open question, deliberately unresolved

Public routes currently return partial PII in places, and whether that is the right long-term shape
is **an open design question, not a settled decision**. One direction under consideration is
modelling admin consent in the REST API itself, so that what a public route may return is driven by
configuration rather than by each route's own judgement.

Treat this as unfinished. If you're working near it: don't "fix" a public route's PII handling by
tightening it unilaterally — you may break a configured, intended display. And don't widen one on
the grounds that adjacent fields are already exposed. Raise it instead.

## Three ways data reaches the client

Each has its own boundary, and they fail differently.

### 1. Localized script data (`wp_localize_script`)

This is the vector with the worst blast radius — whatever you pass is inlined into the HTML source
of the page, readable by anyone who can view it, cached by anything that caches pages.

**The rule: localize configuration, never records.** The list tables are the pattern to copy —
`LoadDonorsListTableAssets` passes an API root, a nonce, column definitions, and admin URLs. It
does **not** pass a single donor row. The React app fetches rows at runtime through the REST API,
where permission checks apply per request.

```php
wp_localize_script($handleName, 'GiveDonors', [
    'apiRoot'  => esc_url_raw(rest_url('give-api/v2/admin/donors')),
    'apiNonce' => wp_create_nonce('wp_rest'),
    'table'    => give(DonorsListTable::class)->toArray(),  // column schema, not data
    // …
]);
```

If you find yourself localizing an array of donors, donations, or subscriptions, stop — that is the
incident shape.

**Admin and public localization are separate classes on purpose.** Campaigns has
`LoadCampaignAdminOptions` and `LoadCampaignPublicOptions`, and the public one is deliberately
minimal — currency and an `isAdmin` flag. Don't merge them, and don't add a field to the public one
without asking who can read the page it lands on.

### 2. The REST API

PII leaves through a **ViewModel that has been told what the caller may see**. Never serialize a
model straight into a response.

```php
(new DonorViewModel($donor))
    ->anonymousMode($donorAnonymousMode)   // exclude | include | redact
    ->includeSensitiveData($includeSensitiveData)
    ->exports();
```

- `DonorAnonymousMode` / `DonationAnonymousMode` are three-state, not boolean: `exclude` drops the
  record, `include` returns it as-is, `redact` returns it with identifying fields removed. Picking
  the wrong one is a silent leak.
- `includeSensitiveData` gates custom fields — the ones a form collected, whose contents nobody
  audited.
- Permission checks live in `permission_callback` and `src/API/REST/V3/Routes/*/Permissions/`.

A new field on a ViewModel is a disclosure decision. Ask which callers reach that route
unauthenticated before adding one.

### 3. Server-rendered templates

The subtlest of the three, because nothing looks like serialization.

The public `CampaignDonors` and `CampaignDonations` blocks select `donors.email` in their queries.
That is legitimate — the email is needed to derive a Gravatar URL. But
`CampaignDonorsBlockViewModel::formatDonorsData()` **mutates and returns the same objects** rather
than building a clean output shape, so every donor object handed to the template still carries
`->email`.

The template doesn't print it, so nothing leaks today. But the only thing standing between a public
page and a list of donor emails is that template's discipline. Any of these reintroduces the
incident:

- a `wp_json_encode($donors)` added for a JS-driven variant of the block
- a debug `var_dump` that survives review
- a third party hooking a filter that receives the donor array
- someone copying the ViewModel as the basis for a new block

**When a query pulls PII for a derived value, drop it before the data leaves the ViewModel.** Build
the output array explicitly; don't hand the query rows onward.

## Gravatar URLs contain a hash of the email

`get_avatar_url($entry->email)` produces a Gravatar URL containing the MD5 of the donor's email
address, and that URL is rendered on a **public** campaign page for every non-anonymous donor
shown. Email hashes are reversible for common addresses via precomputed tables, so this is a weaker
disclosure than plaintext but not a non-disclosure.

Anonymous donors are handled correctly — `get_avatar_url(0)` is used, which involves no email. The
exposure is limited to donors who did not choose anonymity, which is a defensible position, but it
should be a decision someone made rather than a side effect of using the WordPress default.

## Logs

`Log::redact()` replaces values whose **key** contains `card`, `password`, `secret`, or `token`
(filterable via `give_log_redaction_list`) with `[[redacted]]`.

Note what that does and doesn't cover: it matches on key names, not values, and the default list
contains nothing about donors. Logging `['donor' => $donor]` or `['formData' => $data]` writes
whatever those hold straight into the log table, which is readable in the admin and included in
support exports. `ValidationRoute::logError()` logs `$formData->toArray()` on validation failures —
worth knowing before adding fields to that DTO.

## Checklist

- **Who decided this would be visible?** If the answer is "nobody, it's what the query returned,"
  that's the bug — regardless of how sensitive the field looks.
- Building an admin surface? Fetch through the REST API rather than inlining records.
- Localizing anything? Is it configuration, or is it records? Records belong behind the REST API.
- Adding a field to a public localization or a public block? Who can read that page, and which
  admin setting turns it on?
- Identity display (name, company, avatar) can be admin-configured. Contact details (email,
  address, phone, IP) have no such setting, and adding one is a product decision, not a code change.
- New REST field? Does it pass through a ViewModel, and is it gated on permission and anonymous
  mode?
- Does a query pull PII for a derived value (avatar, hash, comparison)? Drop it before the data
  leaves the ViewModel — don't pass query rows to a template.
- Does the donor's anonymity choice reach every place their data is rendered?
- Logging a model, a DTO, or a request payload? Redaction matches key names only, and knows nothing
  about donors.

## Related

- `src/API/REST/V3/Routes/*/ViewModels/` — the REST output boundary
- `src/Donors/Actions/LoadDonorsListTableAssets.php` — the localization pattern to copy
- `src/Campaigns/Actions/LoadCampaignPublicOptions.php` — the deliberately minimal public surface
- `src/Campaigns/Blocks/CampaignDonors/` — the near-miss described above
- `includes/donors/class-give-donor-wall.php` — the Donor Wall, the long-standing public donor
  display and the clearest example of the consent model
- `src/Log/Log.php` — `redact()` and the redaction list
