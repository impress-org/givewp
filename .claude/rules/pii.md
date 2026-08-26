---
paths:
  - "src/API/REST/**/*"
  - "src/**/ViewModels/**/*"
  - "src/*/Blocks/**/*"
  - "src/*/*/Blocks/**/*"
  - "src/*/Actions/Load*.php"
  - "src/Donors/**/*"
  - "src/Log/Log.php"
---

# Donor PII

Read `docs/architecture/pii.md` before exposing donor data anywhere. Donor emails once reached the
page's JavaScript context; this is the guidance that came out of it.

**Donor data is exposed only where someone with authority chose to expose it.** Default to
exclusion, not to "nothing currently prints it."

GiveWP ships public donor display and has for years — the Donor Wall (`[give_donor_wall]`) and the
newer `CampaignDonors` / `CampaignDonations` blocks list donor names, companies, and avatars on
public pages, gated by admin-set shortcode/block attributes and each donation's anonymity flag.
That's intended behavior, not a leak; don't flag it as one. (The
maintainers' reasoning — that fundraising differs from traditional finance, and donors often want
to be seen giving — is a design position, not settled fact. The behavior above is what's
observable.)

So the question is never "is this sensitive?" but "who decided this would be visible, and where is
that decision recorded?" Two consent layers must both hold: the donor's anonymity flag, and the
admin's display configuration. Identity display is configurable; contact details — email, address,
phone, IP — are not, and adding such a setting would be a product decision.

The v3 REST API is the intended single data layer for admin **and** public consumers. Admin
surfaces should essentially always use it. Whether public routes should return partial PII is an
**open design question** — don't unilaterally tighten a public route (you may break a configured
display) or widen one because adjacent fields are exposed. Raise it.

- **`wp_localize_script` carries configuration, never records.** Whatever you pass is inlined into
  the page's HTML for anyone to read. List tables localize an API root, a nonce, and column
  schema — the rows come from REST at runtime, where permissions apply. Localizing an array of
  donors or donations is the incident shape.
- **Admin and public localization are separate classes on purpose** (`LoadCampaignAdminOptions` vs
  `LoadCampaignPublicOptions`). Don't merge them; don't add fields to the public one casually.
- **REST responses go through a ViewModel** told what the caller may see —
  `->anonymousMode()` (three-state: exclude/include/redact) and `->includeSensitiveData()`. Never
  serialize a model directly.
- **If a query selects PII to derive something** (a Gravatar URL, a hash, a comparison), drop the
  field before the data leaves the ViewModel. Don't pass query rows through to a template — see the
  `CampaignDonors` block, where donor objects still carry `->email` into the view layer.
- **`Log::redact()` matches key names only** (`card`, `password`, `secret`, `token`) and knows
  nothing about donors. Logging a model or request payload writes it verbatim.
