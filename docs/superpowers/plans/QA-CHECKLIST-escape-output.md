# SMNTC-2779: escape-output rollup — QA package

**PR:** `SMNTC-2779/base` → `develop`
**Phase PRs rolled up:** #8350 – #8371 (22 PRs, all merged into `SMNTC-2779/base`)
**Size:** 242 files changed (216 production files, plus 23 changelog entries and 3 test files), +2119 / −1348

---

## 0. Read this first

### 0.1 Blocker found while writing this doc — FIXED

**Settings: the "Recurring Donations" upsell tab showed raw HTML.**

- File: `includes/admin/views/html-admin-settings.php`, in the nav-tab loop.
  The rollup had changed `. $label .` to `. esc_html( $label ) .`.
- The label for the `recurring` tab comes from `includes/admin/settings/class-settings-recurring.php`. It is built from HTML: an `<img ... black-external-icon.svg>` plus `<span class="givewp-upsells-recurring-recommended"><strong>RECOMMENDED</strong></span>`.
- The tab is registered only when the Recurring Donations add-on is **not** active (`includes/admin/admin-pages.php`, `if ( !defined('GIVE_RECURRING_VERSION') )`). That is every core-only install.
- Result: every Donations > Settings screen would have shown the tab text as `<img style="display: inline-block; ...` and `<span class=...><strong>RECOMMENDED</strong></span>`.
- `tests/e2e/admin-pages.spec.ts` did not catch this. It only checks that `select#success_page` is in the DOM.

**Fix (commit `77df48d4d`):** the label is echoed raw with a documented `phpcs:ignore`, not run through `wp_kses_post()`. `$label` is a settings page's own developer-authored tab label (plain text for every tab except `recurring`, a fixed icon+badge HTML fragment for that one) — never user input. An earlier version of this fix (`728c8678d`) used `wp_kses_post()`, but per Gustavo's guidance in #8531 ("Be careful with spamming wp_kses, it's expensive to run"), that was replaced with the cheaper, equally-correct plain echo, matching the pattern already used throughout this ticket for trusted, developer-authored markup (the Stripe SVG icon, the shortcode button icon, etc.).

Verified empirically before merging: rendered the label through both `esc_html()` (showed raw tags as text — the bug) and the final approach (shows the icon and badge correctly). Plain-text tab labels are unaffected either way.

### 0.2 Watch items: correct escaping, but visible to some sites

These are not bugs. Each is a place where HTML that used to render will now show as text. QA should know they are expected. Support may hear about them.

| Where | What changed | What a site may notice |
|---|---|---|
| Legacy donation details > Donation Notes (`give_get_payment_note_html()` in `includes/payments/functions.php`) | Note author and content are now `esc_html()`'d (`nl2br()` is kept) | A note that contains HTML (for example an add-on note with a link) shows the tags as text. Core only writes plain-text notes. |
| Sequoia template > "Payment Amount > Content" and "Payment Information > Description" (textarea options, `src/Views/Form/Templates/Sequoia/Actions.php`) | Now `esc_html()` | HTML typed into these textareas shows as text |
| Classic template > main heading (`views/header.php`) | Now `esc_html()` | HTML in the heading shows as text |
| Fields API fields on legacy forms (`src/Form/LegacyConsumer/templates/*.html.php`) | Labels and option labels now `esc_html()` | A link inside a checkbox label (for example a consent checkbox) shows as text |
| Legacy donation details > donation comment textarea | Now `esc_textarea()` | Double-encodes entities if a comment was stored already encoded |
| Sequoia loading spinner color (`views/loading.php`) | Now `sanitize_hex_color()`, falling back to `#28C77B` | A non-hex primary color value falls back to the default green |

### 0.3 Which "high-risk" items were real bugs on `develop`

Some items in section 2 were **real bugs on `develop`** that this PR fixes. Others were **mistakes made partway through this series and fixed before merge**. They never reached `develop`. Both kinds are worth checking. The second kind shows exactly how this type of change breaks things.

| Item | State on `develop` today | State in this rollup |
|---|---|---|
| WP Cron tooltip on System Info | **Broken.** It called `render()` with a string, so no icon or tooltip showed | Fixed (`print_render_help()`) |
| Tooltip `aria-label` escaping (`give_get_attribute_str()`) | **Unescaped.** Only the `value` attribute was escaped | Every attribute value is escaped, and invalid attribute names are dropped |
| Stripe Checkout `session_id` in inline JS (`CheckoutHelper.php`) | **Unsafe.** It came raw from `$_GET['session']` | `esc_js()` |
| Legacy form iframe URL (`IframeView::getIframeURL()`) | **Unsafe.** `$_SERVER['QUERY_STRING']` was reflected into `src`/`data-src` | `esc_url()` after `add_query_arg()` runs |
| Donor wall amount | **Wrong argument escaped.** The donation ID was escaped, the amount was not | Fixed |
| Address and donation-note HTML, `give_price()`, `give_goal()` | **Unescaped PII in returned strings** | Fixed |
| Date format passed through `esc_html()` before `date_i18n()` (System Info Stripe webhook row, Stripe settings) | **Pre-existing bug** | Fixed |
| Sequoia loader primary color | **Not validated** | `sanitize_hex_color()` |
| Onboarding nonce fields stripped by `wp_kses_post()` | Works on `develop` | Broken mid-series and fixed (`25d0abfe1`). Must still work. |
| `esc_js()`/`esc_url()` on URLs inside `<script>` (success redirect, donor-register preview) | `develop` prints the URL raw (works, but unsafe with quotes) | Now `wp_json_encode()`. Broken mid-series and fixed. |
| Custom CSS run through `esc_html()` (v3 form and receipt) | Works on `develop` | Broken mid-series and restored |
| Classic header background image via `esc_url()` inside CSS | Works on `develop` | Broken mid-series. Now `esc_url_raw()`. |
| IframeView URL escaped before `add_query_arg()` | n/a | Broken mid-series and fixed |
| IntlTelInput hidden input name via `esc_js()` | Works on `develop` | Broken mid-series. Now `wp_json_encode()`. |
| `target="_blank"` fragment, `display:none` fragment, Stripe SVG, shortcode-button icon, Chosen `data-*` attributes, Donation Summary `onclick`, `register_notice()` `description_html`, Stripe modal `data-is_legacy_form`, Elementor receipt double-escape | Work on `develop` | Each was broken mid-series by over-eager escaping, then fixed |
| Settings nav tab labels (Recurring Donations upsell) | Works on `develop` | Broken mid-series by `esc_html()`, then `wp_kses_post()`, then fixed with a plain echo (see 0.1) |

---

## 1. Summary

A WordPress Plugin Check scan reported about 1,540 `WordPress.Security.EscapeOutput.OutputNotEscaped` findings across about 220 files. This ticket fixes all of them. Each output point now goes through the escaping function that fits its context:

- `esc_html()` for text
- `esc_attr()` for attributes
- `esc_url()` for links
- `wp_kses_post()` for trusted HTML fragments, used sparingly — for a small, closed fragment where a caller could plausibly need real formatting, not as a default wrapper
- `wp_json_encode()` or `esc_js()` inside inline scripts

Some output was already safe by construction, and an escaper would have broken it. Examples are form controls, SVG icons, data URIs, raw CSS and export file streams. Those spots got a `phpcs:ignore` comment with a specific reason instead.

Reading every touched file also turned up real bugs the sniff cannot see. Examples are unescaped values inside `return`-ed strings, the wrong argument being escaped, a code path with no `esc_url()`, and a tooltip that never rendered. See 0.3.

**Why one big PR.** The work was split into 22 phase PRs so each part could be reviewed. Jon asked that we bucket them into one epic branch and ship a single roll-up PR into `develop`, with one big checklist for QA. This document is that checklist.

**What regression risk looks like here.** This PR adds no features. Every change either adds an escaping call or fixes a place where escaping was wrong. So a regression will almost always look like one of these:

- Visible HTML entities in text, such as `&amp;`, `&#039;`, `&quot;` or `&#038;`. This is double-escaping.
- Visible HTML tags shown as text, such as `<span ...>` or `<img ...>`. This is HTML that was escaped as text.
- A missing icon or image. This is an `<svg>`, `<img>` or data URI stripped by `wp_kses_post()` or `esc_url()`.
- A form that silently stops working. This is an `<input>`, `<select>` or nonce field stripped by `wp_kses_post()`.
- A broken link or redirect. This is `&#038;` inside a JS string, or a URL escaped before query args were added.
- A broken attribute. This is a pre-built `attr="value"` fragment run through `esc_attr()`, which turns it into `attr=&quot;value&quot;`.
- A new JavaScript console error, usually from a missing element or a bad JS string.

---

## 2. QA checklist

### 2.0 Setup and general rules

**Environment**
- WordPress 6.9 or newer (the plugin's minimum), GiveWP from `SMNTC-2779/base`, test mode on.
- An email catcher (Mailpit or similar) for email checks.
- Browser DevTools open on every page, with the Console tab visible.

**Optional, needed for some sections:** Stripe test account, PayPal sandbox, the Recurring Donations add-on (for Donation Summary), Elementor, and the Classic Editor plugin (for the shortcode button).

**"Nasty data" to set up once.** It exercises escaping everywhere:
- Site Title: `QA & Friends' "Test" Site`
- A form or campaign title: `QA Form & "Friends"`
- A donor with first name `O'Brien`, company `Smith & Sons "Ltd"`, and address line `12 Main St & 3rd`
- A donation comment: `Thanks & good luck — it's "great"`

**How to judge the result.** Plain text should show exactly as typed, for example `QA & Friends' "Test" Site`. If you see `&amp;`, `&#039;`, `&quot;` or `&#038;` as text, that is a bug. In places that allow HTML (form descriptions, email body, terms), formatting such as bold and links should still render.

**Legacy (v2) forms can't be created in wp-admin any more.** Use WP-CLI. Set `_give_form_template` to `legacy`, `classic` or `sequoia`:

```
wp post create --post_type=give_forms --post_status=publish --post_title="QA Sequoia" \
  --meta_input='{"_give_form_template":"sequoia","_give_price_option":"set","_give_set_price":"10.00","_give_show_register_form":"none"}' --porcelain
```

Embed the form on a page with `[give_form id="<ID>"]`, or open it directly at `/?post_type=give_forms&p=<ID>`.

**Legacy admin list and detail views.** Turn them on for your user:

```
wp user meta update <admin-user-id> _give_donations_archive_show_legacy 1
wp user meta update <admin-user-id> _give_donors_archive_show_legacy 1
```

The legacy donation details page is always reachable at:
`edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=<ID>`

---

### 2.1 Tooltips and the attribute helper (shared, touches every admin screen) — [HIGH RISK]

**What changed**
- `give_get_attribute_str()` (`includes/misc-functions.php`) now `esc_attr()`s every attribute value. Before, it only escaped `value`, so `aria-label` was unescaped. It also skips attribute names that are not valid.
- `Give_Tooltips::render()` now runs the tag name through `tag_escape()`.
- New method `Give_Tooltips::print_render_help()` (`includes/class-give-tooltips.php`) is now used everywhere a help tooltip used to be `wp_kses_post()`-wrapped:
  - all 60 System Info rows
  - 16 legacy donation form fields
  - the email notification table
  - the platform-fee System Info row
  - the Fields API help text
- The Test Donation badge (`render_span()`) and the Reports refresh link (`render_link()`) are echoed directly again.

**Check on each of these screens.** Hover each `?` icon, or inspect the `<span rel="tooltip">` element.
- [ ] Donations > Tools > System Info. All rows. See 2.2.
- [ ] Donations > Settings > Emails. The list table has a `?` next to each notification description.
- [ ] Donations > Settings > Payment Gateways > Gateways table. The `?` next to gateways that have an admin tooltip.
- [ ] A legacy form on the front end (see 2.9). There are `?` tooltips next to First Name, Last Name, Company, Email, Anonymous donation, Comment, all credit-card fields, all billing fields, and "Create an account".
- [ ] Legacy donation details for a **test-mode** donation. The "Test Donation" badge shows the label "This donation was made in test mode." on hover.
- [ ] Legacy reports (`edit.php?post_type=give_forms&page=give-reports&legacy=true`). The refresh-reports icon link has a tooltip and the link still works.

**Correct:** the `?` icon is visible, and the hover text is readable. For example "The URL of your site's homepage." has a real apostrophe, not `&#039;`.
**Regression:** no icon, an empty tooltip bubble, `&#039;` or `&amp;` inside the bubble, or tooltip markup visible as text.

---

### 2.2 System Info — [HIGH RISK: WP Cron row]

**Path:** Donations > Tools > System Info (`edit.php?post_type=give_forms&page=give-tools&tab=system-info`)
**File:** `includes/admin/tools/views/html-admin-page-system-info.php` (121 findings, the largest single file)

- [ ] **WP Cron row.** A `?` icon is now shown, and hovering it reads "Displays whether or not WP Cron Jobs are enabled." On `develop` this cell was empty or invisible, because it called `render()` with a plain string. This is a real, visible fix.
- [ ] Every other row has a working `?` tooltip (see 2.1).
- [ ] Warning cells still render as a highlighted `<mark>` with a warning dashicon and a clickable link. You can force these by lowering the memory limit, or check them on a host with an old MySQL or PHP. Correct means a formatted warning with a working link. A regression means `<a href=...>` shows as text.
- [ ] "Database Tables" and "GiveWP Emails" show as bullet lists with icons, not raw `<ul>`/`<li>` text.
- [ ] "Stripe Webhook" and "PayPal IPN" rows show a readable date. The date format is no longer run through `esc_html()` before `date_i18n()`.
- [ ] "Theme" > Child Theme row. On a parent theme, the "How to Create a Child Theme" link is clickable.
- [ ] Must-use and active plugin rows show plugin names and authors. A plugin name that contains a link still shows as a link.
- [ ] "Get System Report" button: the copied report text has no HTML entities.
- [ ] No console errors.

---

### 2.3 Onboarding Setup Guide — [HIGH RISK: nonce fields]

**Path:** Donations > Setup (`edit.php?post_type=give_forms&page=give-setup`)
To force it to show: `wp option patch update give_settings setup_page_enabled enabled`
**File:** `src/Onboarding/Setup/templates/index.html.php`

Why this is high risk: during the series, both `wp_nonce_field()` outputs were wrapped in `wp_kses_post()`. That strips `<input>`. The result was that license activation and "Dismiss Setup Screen" silently failed their nonce checks. It is fixed (`25d0abfe1`), and this is the single most important thing to confirm.

- [ ] Inspect the page. There is `<input type="hidden" id="give_license_activator_nonce" name="give_license_activator_nonce" ...>` inside `form.give-license-activation-form`.
- [ ] Inspect the page. There is `<input type="hidden" name="_wpnonce" ...>` inside `.section-dismiss form`.
- [ ] **License activation.** This link only shows when you have no valid license. In Step 3, click "Activate your license". The dialog opens and the close `X` icon (an SVG) is visible. Enter any fake key and submit.
  - Correct: the button changes to "Verifying License..." and then a license error message shows in the dialog.
  - Regression: nothing happens, or a JS error such as `Cannot read properties of undefined (reading 'trim')` from `admin-add-ons.js`, or an admin-ajax response of `-1` or 403.
- [ ] **Dismiss.** Click "Dismiss Setup Screen" at the bottom.
  - Correct: you are redirected to the Campaigns page, and the Setup menu item is gone.
  - Regression: the page reloads and nothing changes.
- [ ] Step 1, 2 and 3 sections render with titles, badges ("Completed" / "Not Completed" / "Optional"), icons, and "Customize campaign" / "Configure GiveWP" buttons that open in a new tab.
- [ ] Step 2: the "Connect to Stripe" and "Connect to PayPal" links work, the Stripe fee docs link is clickable, and the "View all gateways" footer link works.
- [ ] Step 3 add-on rows (Recurring, Fee Recovery, PDF Receipts, and so on, depending on wizard answers) show icon, title, description and a "Get ..." link.
- [ ] If there is a Stripe connect error, `?give_setup_stripe_error=...` shows as a notice with plain text.
- [ ] No console errors.

**Also in this area**
- [ ] Onboarding wizard form preview (`admin.php?page=give-onboarding-wizard`). The preview iframe shows the donation form (`src/Onboarding/Wizard/templates/form-preview.php`).
- [ ] Usage tracking opt-in notice (shows until you opt in or dismiss). The icon, title, "Learn more" link and all three buttons ("Glad to Help", "Not Right Now", "Dismiss Forever") render and work (`src/Tracking/UsageTrackingOnBoarding.php`).

---

### 2.4 Settings (core tabs)

**Path:** Donations > Settings (`edit.php?post_type=give_forms&page=give-settings`)
**Files:** `includes/admin/class-admin-settings.php`, `includes/admin/views/html-admin-settings.php`, `includes/admin/settings/class-settings-*.php`

- [ ] **Tab bar.** The "Recurring Donations" tab shows the external-link icon, the words "Recurring Donations", and a "RECOMMENDED" badge. No HTML text. (This was the 0.1 blocker — confirmed fixed.)
- [ ] **[HIGH RISK] The same tab opens `https://docs.givewp.com/recurring-link` in a NEW browser tab.** The `target="_blank"` fragment was briefly broken by `esc_attr()` during the series. Inspect the `<a>`: it must say `target="_blank"`, not `target=&quot;_blank&quot;`.
- [ ] The "ADD-ONS" star icon and link in the header render.
- [ ] Each tab (General, Payment Gateways, Default Options, Emails, Add-ons, Licenses, Advanced): field titles, descriptions (links inside descriptions are clickable), radio labels, select options, and repeater "Add" buttons all render.
- [ ] Section and sub-group navigation (the `|`-separated links) render with a `&nbsp;|&nbsp;` separator, not the literal text `&nbsp;`.
- [ ] **[HIGH RISK] Title Prefixes (Chosen field).** Go to Default Options, set Name Title Prefix to Required or Optional, and the "Title Prefixes" field appears. Type a new value such as `Rev.` and press Enter.
  - Correct: it is added as a new chip. Inspect the `<select>`: it has a literal `data-allows-new-values="true"`.
  - Regression: you can't add new values, or the attribute shows as `&quot;true&quot;`.
- [ ] Save settings on each tab. A success notice shows and the values persist.
- [ ] Emails tab: the SendWP connect and disconnect buttons (if shown) still work. Their nonces are now `esc_js()`'d.
- [ ] No console errors on any tab.

---

### 2.5 Payment gateway settings — [HIGH RISK: Stripe connect icon]

**Paths:** Settings > Payment Gateways, including the Gateways table, Stripe, PayPal > PayPal Donations, and PayPal Standard.
**Files:** `includes/admin/settings/class-settings-gateways.php`, `src/PaymentGateways/Stripe/Admin/AccountManagerSettingField.php`, `CreditCardSettingField.php`, `CustomizeAccountField.php`, `src/PaymentGateways/PayPalCommerce/AdminSettingFields.php`, `includes/gateways/stripe/includes/admin/*`

- [ ] **Stripe "Connect" button shows its Stripe SVG icon.** It is wrapped in `wp_kses_post()` nowhere now. Check it in three places:
  - the Stripe settings section when not connected
  - the Gateways table row for Stripe
  - a legacy form's Stripe per-form account option
  - Regression: the button shows only text, or the button is missing.
- [ ] Stripe connected: account name, connection type, statement descriptor, the "Edit" link, the "Disconnect" link and its confirm modal (title and detail texts) all show. The Stripe fees and statement descriptor docs links are clickable.
- [ ] Stripe webhooks: the webhook URL field shows `https://<site>/?give-listener=stripe`. "Last webhook received on" shows a readable date. The docs links work.
- [ ] Stripe credit card and customize-account fields: descriptions with links render as links.
- [ ] PayPal Donations: the connect or disconnect buttons show and work. The banners, the admin guidance notice, the account error list, and the "Go to gateway settings" link render as formatted text, not tags.
- [ ] Gateways table: the column headers, each gateway's label input, the "Default" radio, the "Enabled" checkbox, and the helper text under a gateway all render. Save and confirm the enabled and default choices persist.
- [ ] No console errors.

---

### 2.6 Donations admin — [HIGH RISK: display:none placeholder]

**Paths:**
- React list: Donations > Donations. Covered by E2E for mounting only.
- Legacy details: `edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=<ID>`
- Legacy list: after turning on the legacy view (2.0).

**Files:** `includes/admin/payments/view-payment-details.php`, `class-payments-table.php`, `payments-history.php`, `actions.php`, `includes/payments/functions.php`

- [ ] **[HIGH RISK] Notes box.** On a donation **with** notes, the italic "No donation notes" line must be **hidden**. On a donation **without** notes it must show. The hardcoded `style="display:none;"` fragment was briefly corrupted by `esc_attr()`. Inspect it: the attribute must be `style="display:none;"`, not `style=&quot;display:none;&quot;`.
- [ ] Add a note with `Tom & Jerry's "note"` and a line break.
  - Correct: it appears via AJAX showing exactly that text, the line break is kept, and the author and date show. The "Delete" link works.
  - See watch item 0.2 for HTML inside notes.
- [ ] Test-mode donation: the "Test Donation" badge and its tooltip show (2.1).
- [ ] Header: "Donation #<number>" and the delete link show, and the delete confirmation works.
- [ ] Amount, currency symbol (try a non-USD currency such as EUR or BRL), gateway label, donation key, transaction ID and its tooltip, and date all show without entities.
- [ ] The form dropdown, campaign dropdown, donor dropdown, and country and state selects all render and save. These print `<select>` controls, which must not be stripped.
- [ ] The donor company name and billing address (nasty data) show as typed.
- [ ] The comment textarea shows the comment as typed (see watch item 0.2).
- [ ] "View all donations for this donor »" link works.
- [ ] Update the donation and confirm the values save.
- [ ] Legacy donations list: the date filters, form filter, status counts, row actions and bulk actions work.
- [ ] On the legacy list, the "Switch to new view" button works. It sends `X-WP-Nonce` via `esc_js(wp_create_nonce('wp_rest'))`. The same button exists on the legacy Donors, Subscriptions and Donation Forms lists, so check all four.
- [ ] Permission errors (log in as a user without the capability) show plain `wp_die` messages.

---

### 2.7 Donors admin

**Paths:** Donations > Donors, legacy view (2.0). Open a donor profile.
**Files:** `includes/admin/donors/donors.php`, `donor-actions.php`, `class-donor-table.php`

- [ ] Profile header: "Edit Donor: O'Brien ..." shows as typed.
- [ ] **[HIGH RISK] First and last name inputs are editable** after clicking "Edit Donor". They become `readonly` only when intended. The `readonly="readonly"` fragment must not be `esc_attr()`'d. Inspect it: there must be no `&quot;`.
- [ ] Avatar (Gravatar image) shows.
- [ ] Address list: the addresses (nasty data) show formatted, with a label per address, and the edit and remove buttons work. This covers `give_get_format_address()`.
- [ ] The linked user shows as `#<id> - <display name>`. The user search dropdown and the phone field (international phone input) work.
- [ ] Country and state selects work, and adding an address saves.
- [ ] Stats: "N Completed Donations" and "$X Lifetime Donations" show, with the right currency symbol.
- [ ] Recent donations table: number, amount, date and status show, and the "View Details" link works.
- [ ] Notes tab: add a note with nasty data. It appears as typed.
- [ ] Emails: add, remove and make-primary all work.
- [ ] Donors list (legacy): the date filters, form dropdown, and search box render and filter.
- [ ] Tools > Export > Donors export row (`src/Exports/resources/views/export-donors-table-row.php`) renders.

---

### 2.8 Legacy donation form editor (metabox)

**Path:** `post.php?post=<legacy form ID>&action=edit`
**Files:** `includes/admin/give-metabox-functions.php`, `includes/admin/forms/class-metabox-form-data.php`, `includes/admin/forms/dashboard-columns.php`, `src/Views/Admin/Form/Metabox-Settings.php`, `FormGrid-Settings.php`, `src/Helpers/Form/Template/Utils/Admin.php`

- [ ] All tabs (Form Template, Donation Options, Form Fields, Form Display, Donation Goal, Terms, Emails, Offline Donations, Form Grid, Stripe) open, and every field label, description, input, select, radio, color picker, WYSIWYG editor and repeater renders.
- [ ] Form Template tab: the template cards show preview images, names, and "Activate" / "Deactivate" buttons. Activating a template shows its option panels with section headings.
- [ ] Donation Options: the multi-level repeater. Add, remove and reorder rows, and the row headers show the level amount and label.
- [ ] **[HIGH RISK] Form Fields > Title Prefixes (Chosen with custom values).** Same check as 2.4. Also check the placeholder on Chosen fields: the `data-placeholder` value is escaped.
- [ ] Emails tab: the per-form "Preview Email" and "Send Test Email" buttons work.
- [ ] Save the form and confirm the values persist.
- [ ] Donation Forms list table (legacy view): the price, goal, donations and shortcode columns render.
- [ ] Donation Options tab upsell (`src/Promotions/InPluginUpsells/resources/views/donation-options-form-editor.php`) renders if shown.

---

### 2.9 Legacy (v2) donation forms on the front end — [HIGH RISK area]

Make three legacy forms, one each with the Legacy, Classic and Sequoia (Multi-Step) templates (2.0). Give them multi-level prices, a goal, terms, Name Title Prefix, the company field and the anonymous option.

**Files:** `includes/forms/template.php` (116 findings), `includes/forms/functions.php`, `includes/forms/widget.php`, `src/Views/Form/Templates/{Classic,Sequoia,Legacy}/*`, `src/Views/IframeView.php`, `src/Views/IframeContentView.php`, `src/Views/Form/*.php`, `src/Controller/Form.php`, `src/Form/LegacyConsumer/*`, `src/Helpers/IntlTelInput.php`, `src/DonationSummary/resources/views/summary.php`

**Rendering (all three templates)**
- [ ] Form title, description, level buttons or dropdown or radios, custom amount, currency symbol (try EUR, GBP, BRL, INR, and the "after" position), goal bar and text, terms text (formatting kept), and the "Donation Total" label all render.
- [ ] Every field `?` tooltip works (2.1).
- [ ] Choosing a level updates the amount. The form `<form>` tag has `data-currency_symbol` and the other `data-*` values. JS amount formatting works.
- [ ] An error notice (submit with a bad email or below the minimum) shows formatted, with "Error:" in bold, and links in messages still work.
- [ ] No console errors.

**Classic template**
- [ ] Header: title, description (HTML allowed), secure badge with lock icon, stats (raised, count, goal) and the progress meter.
- [ ] **[HIGH RISK] Header background image.** Set one under Visual Appearance and confirm it shows. It is now `esc_url_raw()` inside CSS. `esc_url()` broke it during the series.
- [ ] **Primary color.** Set `#e02424`. Buttons and accents use it. The loading spinner on form load is red.
- [ ] Donation amount heading shows.
- [ ] Receipt (after an Offline or Test donation): badge icon, title, description, detail sections with icons (Font Awesome `<i>` icons), values, and the PDF receipt link if that add-on is present.

**Sequoia template**
- [ ] Introduction (headline, description, image), income stats, progress bar, the "Donate Now" and "Continue" buttons with chevron icons, the choose-amount content, and the payment section heading and description (see watch item 0.2).
- [ ] **[HIGH RISK] Loading spinner color.** Set Primary Color to `#e02424` and reload. The spinner is red. It is now validated with `sanitize_hex_color()`.
- [ ] Receipt: headline, message, social sharing, detail rows with icons, and the "Donation Failed" state if you trigger a failure.

**Iframe embed and query strings — [HIGH RISK]**
- [ ] Embed a Sequoia form with `[give_form id="X"]` on a page and open `https://<site>/<page>/?utm_source=qa&foo=bar&x=1`. Inspect `iframe[name="give-embed-form"]`:
  - Correct: `src` (or `data-src` in modal mode) contains `giveDonationFormInIframe=1` and all three of your params. In "View Source" they appear joined by `&#038;`, which is correct HTML. In the DevTools Elements panel they appear as `&`.
  - Regression: params missing, or a `&amp;#038;` style double encoding.
- [ ] Complete a donation with **Test Gateway (Offsite)** (enabled by `GIVEWP_ENABLE_TEST_OFFSITE_GATEWAY` in wp-env). You return to the page with `giveDonationAction=showReceipt` and the receipt shows inside the iframe. E2E already covers this: `legacy-donation-forms.spec.ts`.
- [ ] Modal display mode: the "Donate" button opens the form.

**Success-page redirects inside `<script>` — [HIGH RISK]**

Setup: make the success page URL carry two or more query args, which is what exposes `&` bugs. Add a tiny mu-plugin:
`add_filter('give_get_success_page_uri', fn($u) => add_query_arg(['qa' => '1', 'second' => 'two'], $u));`

- [ ] **Legacy iframe "processing" screen** (`src/Views/Form/defaultFormDonationProcessing.php`). This shows when a Classic or Sequoia form returns to its receipt while the donation is still Pending, for example with Offline Donations or PayPal Standard sandbox before the IPN.
  - View source. The line should read `window.location = "https:\/\/...\/?qa=1&second=two";`. Here `&` is correct: it is JSON's safe form of `&`.
  - After about 5 seconds the browser lands on the success page with both `qa=1` and `second=two` in the URL.
  - Regression: `&#038;` in the source, or `second` missing from the final URL.
- [ ] **PayPal Standard processing screen** (`templates/payment-processing.php`, shown on the success page while a PayPal Standard donation is Pending). Same check, about 9 seconds.
- [ ] **Offsite redirect handler** (`src/Views/Form/defaultRedirectHandlerTemplate.php`). When a form in an iframe sends the donor to an offsite page, the top window, not the iframe, navigates to the right URL with all params.

**Stripe fragments on legacy forms** (needs a Stripe test account) — `src/PaymentGateways/Gateways/Stripe/Traits/*`, `Helpers/CheckoutHelper.php`, `includes/gateways/stripe/includes/payment-methods/*`
- [ ] Credit card (single-line and multi-line field formats) renders the Stripe Elements fields, and a donation succeeds.
- [ ] SEPA: IBAN field and mandate acceptance text (formatted). BECS: bank account field and mandate text.
- [ ] **[HIGH RISK] Stripe Checkout "Modal" type.** Test on a **Sequoia** form and a **Legacy** form. Click Donate.
  - Correct: the modal opens with the header (site name, amount, form title) and card fields. On Sequoia the Sequoia-style loading shows, not the legacy one.
  - Background: `data-is_legacy_form` must be `"1"` or `""`, never `"0"`. JS treats `"0"` as true.
- [ ] Stripe Checkout "Redirect" type: you are sent to `checkout.stripe.com`, and the session loads. It uses the `esc_js()`'d session ID and publishable key.
- [ ] Legacy donation details for a Stripe donation show "Stripe Account: <name>".

**Other fields**
- [ ] International phone field (if enabled): country flag dropdown, validation messages, and the submitted value arrives in the donation. The hidden input name is now built with `wp_json_encode()`, which is a mid-series fix.
- [ ] Fields API fields (from an add-on or custom code): text, textarea (default value), select (placeholder), radio, checkbox group, single checkbox, file and hidden fields render and submit. The help `?` tooltip works. See watch item 0.2 about HTML in labels.
- [ ] **[HIGH RISK] Donation Summary** (needs the Recurring add-on, Sequoia form, summary enabled). The "Consider making this donation **recurring**" line has a clickable "recurring" button that takes you back to the amount step. Its `onclick` must survive. "Cover Donation Fees" row and info icons show.
- [ ] Legacy donation form widget (Appearance > Widgets): the form dropdown in the widget settings, and the front-end widget title and form.

---

### 2.10 v3 donation forms and routes

**Files:** `src/DonationForms/ViewModels/DonationFormViewModel.php`, `DonationConfirmationReceiptViewModel.php`, `resources/views/form-skeleton*.php`, `Routes/DonateRoute.php`, `Routes/ValidationRoute.php`, `FormPage/templates/form-single.php`, `src/Framework/Routes/ScriptResponse.php`

- [ ] **[HIGH RISK] Custom CSS.** In the Form Builder > Design > Custom CSS, add a rule with a child combinator, for example `.givewp-layouts > .givewp-layouts-section { outline: 3px solid red; }`. Preview and view the live form. The outline shows.
  - Regression: no effect. View source shows `&gt;` inside `<style id="root-givewp-donation-form-style">`.
- [ ] Same rule on the confirmation receipt page: the style applies. Primary and secondary colors also apply there.
- [ ] Skeleton while loading, for the Classic, Two-panel steps and Multi-step designs (E2E covers most of this).
- [ ] The form page (`/?givewp-route=donation-form-view...` or the form's public page) shows the form.
- [ ] A forbidden request (for example a form that requires login, as a guest) shows a plain-text `wp_die` message.
- [ ] External embed script: E2E covers this (`external-embeds.spec.ts`).

---

### 2.11 Shortcodes and front-end templates

**Files:** `templates/*.php`, `includes/shortcodes.php`, `includes/template-functions.php`, `includes/misc-functions.php`, `includes/donors/frontend-donor-functions.php`

Put each shortcode on its own page, using the nasty data.

- [ ] `[give_form_grid]`:
  - the card title, excerpt, image, category and tag labels (colored term chips — the term name was previously unescaped)
  - the progress bar and "X of Y raised", "% funded", donors and donations counts
  - pagination links
  - the "Donate Now" modal and redirect display styles
- [ ] **[HIGH RISK] `[give_donor_wall]`:**
  - avatar image or initials circle, name, company, comment with "Read more"
  - form name (full and truncated)
  - **amount shows the right donation amount with currency** (the escaped argument was wrong before)
  - tribute line with its SVG icon (must not be stripped)
  - "Load more" button
- [ ] `[donation_history]`: table headers, number, date, amount, status, gateway, and the "View Receipt »" link (the » sign shows, not `&raquo;`). Pagination. The not-logged-in notice. The email-access "confirm your email" box, where the email shows in the text and in the button's `data-email`.
- [ ] `[give_receipt]`: the receipt table and status notice. An invalid receipt shows an error notice box, not raw HTML.
- [ ] `[give_goal id="X"]`: the income and goal text (formatted spans), the progress bar with its custom color, and the closed-goal message.
- [ ] `[give_totals total_goal="1000" ids="..."]`: the message with its link, and the progress bar with its color.
- [ ] `[give_login]`, `[give_register]`, `[give_profile_editor]`: fields, "Lost password" link, nonce inputs present, and submitting works. Profile editor: update the name to `O'Brien` and save.
- [ ] Email access login form (`templates/email-login-form.php`, when you visit donation history with email access on): renders and sends.
- [ ] Single form page (`templates/single-give-form/*`): featured image, content, and the left sidebar wrapper markup.
- [ ] `<meta name="generator" content="Give v...">` is in the page head.

---

### 2.12 Emails — [HIGH RISK: rendered email]

**Files:** `templates/emails/header-default.php`, `footer-default.php`, `header.php`, `includes/emails/template.php`, `class-give-email-tags.php`, `includes/admin/emails/*`, `src/FormBuilder/EmailPreview/*`

- [ ] Settings > Emails > Donation Receipt > **Send Test Email**. In Mailpit, check each part:
  - `<title>` and footer show the site name `QA & Friends' "Test" Site` exactly (the footer name is a link)
  - the header image, if set, shows
  - the heading shows as typed, with no `&amp;`
  - body bold text, links and paragraphs render
  - the email layout (the white box on a gray background) is intact, meaning the `style="..."` attributes survived
- [ ] Same for a donation you make: donor receipt and admin notification.
- [ ] **Preview Email** (opens in a browser tab): same visual check.
- [ ] Email tags list under each email body editor: `{tag}` and description show.
- [ ] Emails list table: labels, recipients (formatted), the `?` tooltip per row, and row actions.
- [ ] **[HIGH RISK] Donor-register preview dropdown.** Open the Preview for "User Registration Information" (donor) or "New User Registration" (admin). At the top, pick a different user from the dropdown.
  - Correct: the page reloads with `...&user_id=<n>` and shows that user.
  - Background: the JS URL is now built with `wp_json_encode()`, and `esc_url()` there broke it during the series.
- [ ] **v3 Form Builder email preview** (Form Builder > Emails > Preview). Formatting such as bold and links is kept. The message is now run through `wp_kses_post()` before rendering. A `<script>` typed into the email body must **not** run in the preview.
- [ ] Per-form email preview and test buttons in the legacy form editor (2.8).

---

### 2.13 Elementor widgets — [HIGH RISK: receipt widget text]

**Needs Elementor.** Edit a page with Elementor.
**Files:** `src/ThirdPartySupport/Elementor/Widgets/V1/*`, `V2/*`

- [ ] **[HIGH RISK] V1 "Donation Receipt" widget, in the editor preview.** Set Error Message to `Tom & Jerry's "test"` and Success Message to `Thanks & bye`.
  - Correct: the preview shows exactly those strings.
  - Regression: `&amp;amp;`, `&amp;#039;` or `&amp;quot;`.
  - Background: the value is escaped once at the top of `render()` and must not be escaped again.
- [ ] V1 Login and Register widgets: the editor preview shows the static form. On the live page the form works and the lost-password link is correct.
- [ ] V1 Donation History, Donor Wall, Form Grid, Goal, Multi-Form Goal, Profile Editor, Subscriptions and Totals widgets render on the live page (same output as the shortcodes in 2.11).
- [ ] V2 Campaign, Campaign Grid, Campaign Goal, Campaign Stats, Campaign Comments, Campaign Donations and Campaign Donors widgets render in the editor and on the live page.

---

### 2.14 Campaigns: blocks and campaign pages

**Files:** `src/Campaigns/Blocks/*/render.php`, `resources/views/campaign-page-template.php`, `Actions/RenderDonateButton.php`, `Actions/PreventDeleteDefaultForm.php`, `CampaignsAdminPage.php`

- [ ] Campaign page (front end): title (heading level from the block setting, h1 to h6), cover image (width, height and radius styles), goal, stats (title and value), donate button (modal and new-tab styles), campaign form, and comments.
- [ ] Each block in the editor and on the front end, with a campaign title containing `&` and `'`.
- [ ] Try to trash or delete a campaign's default form. The blocked message shows the form title as plain text.
- [ ] Open a non-existent campaign ID (`...&page=give-campaigns&id=999999`). You get "Campaign not found".
- [ ] Multi-Form Goal block: the progress bar, "raised", "donations" and "goal" labels, and the embedded CSS.

---

### 2.15 Donor dashboard

**Files:** `src/DonorDashboards/resources/views/*`, `RequestHandler.php`, `Admin/*Notice.php`

- [ ] `/?give-embed=donor-dashboard` (or the Donor Dashboard page) loads. The loader spinner uses the accent color. Login works. E2E covers the login route.
- [ ] Admin: the Donor Dashboard success notice (with its page link) and the upgrade notice (image, title, text, button) render if shown.

---

### 2.16 Promotions and admin banners

**Files:** `src/Promotions/*`, `src/BetaFeatures/ServiceProvider.php`, `src/EventTickets/Actions/RegisterEventsMenuItem.php`, `includes/admin/class-i18n-module.php`, `includes/admin/class-addon-activation-banner.php`

- [ ] Donations > Add-ons page: sale banners and the StellarWP sale banner (headline and lead text, formatting kept) render if active.
- [ ] Campaign welcome banner and BFCM 2025 banner (date-dependent). The close `X` button works.
- [ ] Beta features notice, Events menu item.
- [ ] Translation promo notice (switch the site to a non-English locale such as Portuguese) shows the language name.
- [ ] Add-on activation banner (after activating an add-on).

---

### 2.17 Reports

**Files:** `includes/admin/reports/*`, `src/Views/Admin/Pages/templates/reports-template.php`, `src/Views/Admin/DashboardWidgets/templates/reports-template.php`

- [ ] Donations > Reports: the loading text shows "Loading your latest" and "donation activity" on two lines, then the React app loads. E2E covers the REST calls.
- [ ] WP Dashboard GiveWP widget: same loading text, then data.
- [ ] Legacy reports (`edit.php?post_type=give_forms&page=give-reports&legacy=true`): the earnings graph draws (the data points are now `floatval`'d), hover tooltips on points work, the totals table shows currency amounts, and the view dropdown labels, forms, donors and gateways tables and their search boxes all work. The refresh tooltip link (2.1) works.
- [ ] Per-form legacy graph (from the forms report table): title and graph.

---

### 2.18 Tools: import, export, data

**Files:** `includes/admin/tools/**`

- [ ] Tools > Export: every export row renders, including the year and month selects, the form and category and tag selects, and the date fields. Run a donations CSV export and a donors export.
  - **The CSV must contain raw values, not HTML entities.** A donor named `O'Brien & Co` appears as `O'Brien & Co` in the file.
- [ ] PDF reports export: runs, or shows a plain-text error.
- [ ] Tools > Import > Donations: upload a CSV. Each step renders, including the mapping dropdowns and the success screen text with its links.
- [ ] Tools > Import > Subscriptions (needs Recurring): same.
- [ ] Tools > Import > Core Settings: upload a JSON export. The steps and the "is_json_valid" handling work. A unit test covers the nonce.
- [ ] Tools > Data: the form dropdown, date fields and checkboxes render. "Delete test transactions" runs.
- [ ] Tools > Logs and Tools > Migrations mount (E2E covers this).

---

### 2.19 Licenses, add-on updates, plugins page, upgrades

**Files:** `includes/admin/settings/class-settings-license.php`, `includes/class-give-license-handler.php`, `includes/admin/add-ons/actions.php`, `includes/admin/plugins.php`, `includes/admin/upgrades/views/*`, `includes/api/class-give-api.php`, `includes/admin/class-api-keys-table.php`

- [ ] Settings > Licenses:
  - the intro text with the "My Account" link
  - the license activation form (nonce present) and a bad key showing an error
  - the license list rows with activate, reactivate and deactivate buttons
  - the "Last refreshed on ..." text
  - the "Refresh All Licenses" button, which is disabled with a title when the limit is reached
- [ ] Plugins page:
  - the add-on update notices: "There is a new version of X available. View version details / update now", with working links
  - the GiveWP upgrade notice under the plugin row
  - the deactivation survey popup text when you deactivate GiveWP
- [ ] Donations > Tools > (DB) Updates page and the "Updates complete" screen, including the add-on updates list.
- [ ] Legacy API keys (Tools > API, if shown): the key table, user search, and bulk actions.

---

### 2.20 Other cross-cutting bits

- [ ] **[HIGH RISK] "Insert Shortcode" button in the Classic Editor** (needs the Classic Editor plugin). Edit a post. The GiveWP shortcode button above the editor **shows its icon**. The icon is a base64 data-URI `background-image`, which `wp_kses_post()` mangled during the series. Open the dropdown: every shortcode label shows, and inserting one works.
- [ ] **Admin notices registered through `register_notice()` with `description_html`.** These are custom notices that include buttons or inputs, such as the DB upgrade and add-on notices. Their controls must still show (`includes/class-notices.php`).
- [ ] Front-end notices (`Give_Notices::print_frontend_notice`, `print_errors`): dismissible notices keep their close icon, and auto-dismiss still works.
- [ ] Deprecated-function and "doing it wrong" notices (with `WP_DEBUG` on): the messages are readable.
- [ ] AJAX changelog modal in the plugins list for a GiveWP add-on: the formatted changelog shows.
- [ ] Admin icon font: GiveWP admin menu and settings icons show. This checks the `@font-face` URLs in `includes/class-give-scripts.php`.

---

## 3. Existing automated coverage vs. gaps

Short version: most of this PR is markup in view files, and most view files have no direct test. Existing coverage mostly proves pages mount and core flows complete. It does not prove specific attributes, icons or text survive escaping.

| Area | Existing automated coverage (real test names) | Honest gap |
|---|---|---|
| Tooltips / `give_get_attribute_str()` | **PHPUnit (new in this PR):** `tests/Unit/Helpers/GiveGetAttributeStrTest.php` (`testEscapesEveryAttributeValue`, `testDropsAnInvalidAttributeName`, `testTooltipLabelIsEscapedInAriaLabel`), `tests/Unit/Helpers/GiveTooltipsTest.php` (`testPrintRenderHelpEchoesRenderHelpOutput`, `testRenderHelpDoesNotDoubleEscapeAnAlreadyEscapedLabel`) | Unit-level only. No test renders an admin screen and checks its tooltips. |
| System Info | None (`Tests_Give` only checks includes/constants) | Manual only. The WP Cron fix is untested. |
| Onboarding Setup Guide | `tests/Unit/Onboarding/Setup/PageViewTest.php` (`testContentSurroundedByUnmergedTagIsNotScrubbed`, which only tests `render_template('row-item')`) | **No test for the nonce fields**, license activation or dismiss. Manual only. |
| Settings | E2E `admin-pages.spec.ts` › "settings page renders the general tab" (checks `select#success_page` is attached). PHPUnit `tests/includes/legacy/Tests_Notices.php::test_print_admin_notices` | No check of tab labels (this is exactly what missed the 0.1 blocker), `target` attributes, Chosen attributes, or other tabs |
| Gateway settings (Stripe/PayPal admin) | None | Manual only |
| Donations admin | E2E `admin-pages.spec.ts` › "donations list table mounts" (React list only) | Legacy details page, notes, `display:none` placeholder, and test badge untested |
| Donors admin | E2E `admin-pages.spec.ts` › "donors list table mounts" (React list only) | Legacy profile, address formatting, and `readonly` fragment untested |
| Legacy form metabox | None | Manual only |
| Legacy front-end forms | PHPUnit `tests/includes/legacy/Tests_Templates.php::test_get_donation_form` and `test_donation_form_amount_range` (form tag `data-*` attributes, hidden inputs, honeypot, levels; its currency assertion was updated in this PR to `&#036;`). `tests/Unit/Views/Form/Templates/Sequoia/SocialSharingViewTest.php`. `tests/Unit/DonationSummary/SummaryViewTest.php` (template location, enabled flag, heading only). **E2E** `legacy-donation-forms.spec.ts` › "takes an offsite donation out to the gateway and back to the receipt" (Sequoia, iframe, offsite return, receipt) | No test for the Classic template, loader colors, the processing-page redirects, IntlTelInput, Stripe fragments, the Stripe modal `data-is_legacy_form`, or the Donation Summary `onclick` |
| v3 forms | E2E `donation-forms.spec.ts`, `external-embeds.spec.ts`, `form-builder.spec.ts`. PHPUnit `DonationFormViewModelTest` (color accessors), `DonationConfirmationReceiptViewModelTest` (exports), `tests/Feature/Controllers/BlockRenderControllerTest.php` | No assertion that custom CSS survives unescaped (the `>` combinator) |
| Shortcodes / templates | PHPUnit `tests/Unit/Donors/DonorWallShortcodeOutputTest.php`, `tests/Unit/Views/DonationHistoryDonorEscapingTest.php`, `tests/Unit/Views/ShortcodeReceiptDonorEscapingTest.php`, `tests/includes/legacy/Tests_Login_Register.php` (`test_login_form`, `test_register_form` — legend text only), `tests/Unit/MultiFormGoals/MultiFormGoal/ShortcodeTest.php` | Form grid, goal and totals output untested. The donor wall amount fix is untested. |
| Emails | PHPUnit `tests/includes/legacy/Tests_Emails.php` (tags, headers, heading value, `text_to_html`) | No test renders `header-default.php` or `footer-default.php`. Email preview and the donor-register preview JS are untested. |
| Elementor | PHPUnit `tests/Unit/ThirdPartySupport/Elementor/Widgets/V2/Test*Widget.php` (9 V2 widgets, mocked Elementor) | **No V1 widget tests**. The `DonationReceiptWidget` double-escape fix is untested. |
| Campaigns | `tests/GiveTests/Unit/Actions/RenderDonateButtonTest.php`, `BlockRenderControllerTest`, `tests/Unit/Campaigns/Blocks/Campaign{Donations,Donors}BlockDonorNameEscapingTest.php`. E2E `campaigns.spec.ts` | Title, cover, stats and goal `render.php` output untested |
| Donor dashboard | E2E `donor-dashboard.spec.ts` › "front end reaches the login route" | Admin notices untested |
| Promotions / banners | None | Manual only |
| Reports | E2E `reports.spec.ts` (React page and REST routes) | Legacy graph (`class-give-graph.php` JS) untested |
| Tools | PHPUnit `tests/Unit/Admin/CoreSettingsImportNonceTest.php`. E2E `tools-logs.spec.ts`, `tools-migrations.spec.ts` (mount) | Export and import screen markup untested. No test checks that CSV output stays free of entities. |
| Licenses / plugins / upgrades | PHPUnit `tests/Unit/VendorOverrides/Harbor/TestLegacyLicenseCompatibility.php` (license logic, not UI) | License UI, update notices and deactivation popup untested |
| Shortcode button, notices | `Tests_Notices::test_print_admin_notices` (plain-text descriptions only) | `register_notice()` `description_html` with `<input>` untested. Shortcode button icon untested. |

CI note: `tests-e2e.yml` runs on pull requests into `develop`, so the roll-up PR will run the whole Playwright suite. It did not run on the phase PRs (base `SMNTC-2779/base` does not match `epic/**`).

---

## 4. Recommended new tests for Victor (prioritized)

Conventions seen in the repo:
- PHPUnit classes go in `tests/Unit/<Area>/<Name>Test.php`, namespace `Give\Tests\Unit\<Area>`, extend `Give\Tests\TestCase`, use `@since TBD` and `: void`.
- Legacy-style tests go in `tests/includes/legacy/Tests_*.php` and extend `Give_Unit_Test_Case`.
- Playwright specs go in `tests/e2e/<area>.spec.ts`, using `@wordpress/e2e-test-utils-playwright`, `wp()` from `tests/e2e/utils/wp-cli.ts`, `createLegacyForm()` from `utils/legacy-form.ts`, and `watchRestCalls()` / `failedCalls()` from `utils/rest.ts`.

### P0: add before or with the merge

1. **Settings tab labels are not escaped as text** (this exact gap caused the 0.1 blocker)
   - Playwright, extend `tests/e2e/admin-pages.spec.ts`:
     - visit `edit.php?post_type=give_forms&page=give-settings`
     - assert no `.give-nav-tab-wrapper a` has `innerText` containing `<`
     - assert `a[href="https://docs.givewp.com/recurring-link"]` has `target="_blank"` and contains an `img`
   - Or PHPUnit, `tests/Unit/Admin/SettingsNavTabsTest.php`:
     - buffer `Give_Admin_Settings::output()` for `give-settings` with the recurring tab registered
     - assert the output contains `<img` and `target="_blank"`
     - assert it does not contain `&lt;img` or `target=&quot;`

2. **Onboarding nonce fields are present and the forms work**
   - PHPUnit, `tests/Unit/Onboarding/Setup/SetupPageNonceFieldsTest.php`:
     - with no stored licenses, buffer `give(Page::class)->render_page()`
     - assert it contains `name="give_license_activator_nonce"` and `name="_wpnonce"`, both inside a `<form`
   - Playwright, new `tests/e2e/onboarding-setup.spec.ts`:
     - enable the page with `wp('option', 'patch', 'update', 'give_settings', 'setup_page_enabled', 'enabled')`
     - visit `page=give-setup`
     - assert `input[name="give_license_activator_nonce"]` is attached
     - open the "Activate your license" dialog, type a fake key, submit, and assert the admin-ajax response body is not `-1` and the status is not 403 (do not depend on the external license server answering)
     - click "Dismiss Setup Screen" and assert the URL matches `page=give-campaigns`

3. **Success-page redirect JS keeps every query arg**
   - PHPUnit, `tests/Unit/Views/Form/SuccessPageRedirectScriptTest.php`:
     - add a filter on `give_get_success_page_uri` that returns `https://example.test/thanks/?a=1&b=2`
     - buffer `templates/payment-processing.php` and `src/Views/Form/defaultFormDonationProcessing.php`
     - regex out the `window.location = (...);` literal, `json_decode()` it, and assert it equals the filtered URL exactly
     - assert the output does not contain `&#038;`

4. **Iframe URL keeps query args and escapes them once**
   - PHPUnit, `tests/Unit/Views/IframeViewTest.php`:
     - set `$_SERVER['QUERY_STRING'] = 'utm_source=qa&foo="><x>'`
     - render `(new IframeView())->setFormId($legacyFormId)->render()`
     - read the `src`/`data-src` attribute and `html_entity_decode()` it
     - assert it contains `giveDonationFormInIframe=1`, `utm_source=qa` and `foo=`
     - assert the raw HTML has no `"><x>` and no `&amp;#038;`
   - Playwright, extend `tests/e2e/legacy-donation-forms.spec.ts`:
     - `page.goto(`/?post_type=give_forms&p=${formId}&utm_source=qa&x=1`)`
     - assert `iframe[name="give-embed-form"]` `src` contains both params

5. **Tooltip `aria-label` is never empty and the WP Cron row has its icon**
   - PHPUnit, extend `tests/Unit/Helpers/GiveTooltipsTest.php`:
     - use a data provider of labels (plain, apostrophe, `&`, quotes, `<b>`)
     - assert `render_help($label)` has a non-empty `aria-label` and, after `html_entity_decode()`, it equals the input
     - assert the output contains `give-icon-question`
   - PHPUnit, new `tests/Unit/Admin/SystemInfoViewTest.php`:
     - buffer `include` of `html-admin-page-system-info.php` as an admin
     - assert every `<td class="help">` contains `rel="tooltip"` and a non-empty `aria-label`
     - specifically assert the row after `data-export-label="WP Cron"` does

### P1: covers the "broken mid-series" failure modes, so they can't come back

6. **Pre-built attribute fragments stay intact.** PHPUnit tests for:
   - `view-payment-details.php`: a donation with notes contains `style="display:none;"` literally
   - the donor profile: `readonly="readonly"` when the donor has a linked user
   - the Chosen field in `class-admin-settings.php` and `give-metabox-functions.php`: `data-allows-new-values="true"`
   - `tests/Unit/Admin/AttributeFragmentsTest.php` is fine for these.

7. **SVG and data-URI icons are not stripped.**
   - PHPUnit asserts that the output of `AccountManagerSettingField`'s connect button contains `<svg`.
   - PHPUnit asserts that `Give_Shortcode_Button::shortcode_button()` output contains `background-image` with `data:image/svg+xml;base64,`.
   - `tests/Unit/PaymentGateways/Stripe/Admin/ConnectButtonMarkupTest.php` and `tests/Unit/Admin/ShortcodeButtonTest.php` are fine for these.

8. **v3 custom CSS is printed raw.**
   - Extend `tests/Unit/DonationForms/ViewModels/DonationFormViewModelTest.php` and `DonationConfirmationReceiptViewModelTest.php`.
   - Set custom CSS to `a > b { color: red; }</style><script>x</script>`.
   - Assert the output contains `a > b` and not `&gt;`, and does not contain `<script>x`.

9. **Elementor V1 receipt widget escapes once.**
   - New `tests/Unit/ThirdPartySupport/Elementor/Widgets/V1/DonationReceiptWidgetTest.php`, reusing `MockElementorTrait` from V2 with edit mode on.
   - Set `error` to `Tom & Jerry's` and assert the output contains `Tom &amp; Jerry&#039;s` and not `&amp;amp;`.

10. **`register_notice()` keeps controls in `description_html`.**
    - Add a case to `tests/includes/legacy/Tests_Notices.php`.
    - Register a notice with `description_html` containing `<input type="checkbox" name="qa">` and `<button>`, render the admin notices, and assert both tags are present.

11. **Donation Summary keeps its `onclick`.**
    - Extend `tests/Unit/DonationSummary/SummaryViewTest.php`.
    - With multi-step and recurring on, assert the output contains `onclick="GiveDonationSummary.handleNavigateBack(event)"`.

12. **Stripe checkout fragments.**
    - PHPUnit for `CheckoutModal`: `data-is_legacy_form=""` on a non-legacy form and `"1"` on a legacy form, never `"0"`.
    - PHPUnit for `CheckoutHelper`: with `$_GET['session'] = "abc');alert(1);//"`, the script contains `esc_js`-escaped text, not a raw `'`.

### P2: broad safety nets and the sniff-invisible fixes

13. **Visible-entity sweep (Playwright)**, new `tests/e2e/no-visible-entities.spec.ts`:
    - Visit a fixed list of admin screens: every Settings tab, System Info, Tools tabs, legacy reports, legacy donation details, and a legacy donor profile.
    - On each page:
      - fail on any console error
      - fail if `document.body.innerText` matches `/&(amp|quot|#0?39|#038|lt|gt);/`
      - fail if it contains `<span`, `<img` or `<a href`
      - check that every `[rel="tooltip"]` has a non-empty `aria-label`
    - This is cheap and would have caught blocker 0.1.

14. **PII helpers**:
    - `tests/Unit/Donors/GiveGetFormatAddressTest.php`: HTML in address parts comes back escaped.
    - `tests/Unit/LegacyPayments/GiveGetPaymentNoteHtmlTest.php`: the author and content are escaped and `nl2br` is kept.
    - `tests/Unit/Donors/DonorWallShortcodeOutputTest.php`: add a case asserting the amount, not the ID, is what gets escaped.

15. **Email template render.**
    - Add to `tests/includes/legacy/Tests_Emails.php`.
    - Set `blogname` to `A & B's`, build an email with `Give()->emails->build_email('<p><strong>x</strong></p>')`, and assert:
      - `<title>A &amp; B&#039;s</title>`
      - `<strong>x</strong>` is kept
      - the `style=` attributes are not empty

16. **Export stays raw.** A PHPUnit test for the donations CSV export: a donor named `O'Brien & Co` appears as those exact bytes in the generated CSV.

17. **Legacy processing redirect end to end (Playwright).**
    - Needs a fixture mu-plugin that filters `give_get_success_page_uri`, mapped in `.wp-env.json` under `"mappings"`.
    - Complete an Offline Donations payment on a Sequoia form.
    - `page.waitForURL(/qa=1.*second=two|second=two.*qa=1/)`.

Keep manual (not worth automating now): Stripe and PayPal live-sandbox flows, Elementor editor UI, the Classic Editor shortcode button UI, and date-dependent promotion banners.

---

## 5. Test plan (what was done)

- **phpcs** (`WordPress.Security.EscapeOutput`): 0 `OutputNotEscaped` findings in every touched file. Checked file by file per phase, and again on the fully merged final `SMNTC-2779/base` head (including the settings-tab-label fix).
- **Real Plugin Check gate**, run through the Plugin Check CLI in a ddev site, not a phpcs stand-in:
  ```
  wp plugin check give --require=wp-content/plugins/plugin-check/cli.php --categories=security --exclude-directories=.worktrees --format=csv --fields=code,message,file,line,column
  ```
  Result on the final `SMNTC-2779/base` head: **0 `OutputNotEscaped` findings in production code.** 2 remain in `tests/includes/legacy/framework/helpers/class-helper-payment.php` and `class-helper-form.php` — pre-existing test-helper code, never part of the original 1,540-finding report or this ticket's scope, confirmed present at the base commit before any of this work too.
- **`php -l`**: clean on every touched file.
- **Full PHPUnit suite, final run against `SMNTC-2779/base` HEAD (`77df48d4d`):** `Tests: 3169, Assertions: 10358, Warnings: 1, Skipped: 11, Incomplete: 10, Risky: 2. 0 failures.` Matches baseline exactly (the earlier baseline was ~3137-3155 tests as `develop` moved forward during the series; 3169 reflects `develop`'s latest state after merging it into `SMNTC-2779/base` to keep the rollup diff clean — skip/incomplete/risky/warning counts are unchanged throughout).
  - Two earlier attempts at this final run hit a real environment failure (`mysqli object is not fully initialized`, cascading into 324 errors) — traced to the local ddev site itself being paused, not a code issue. Fixed by restarting the site, recreating the test database, and updating the DB port in `tests/wp-tests-config.php`. The clean run above is after that fix.
- **Manual render checks done during implementation:**
  - Tooltip spot-check after the shared-helper change (Phase 0)
  - Full live `[give_form]` embed of a legacy form: attributes, currency symbols, goal JSON, tooltips and terms HTML were correct, with no leaked entities (Phase 8)
  - v3 form iframe and skeleton page with a browser console check, no errors (Phase 10c)
  - Form grid shortcode
  - A payment's legacy details screen
  - System Info page
  - A test email through Mailpit: header, bold body text, link and footer correct (Phase 11b)
  - The Onboarding Setup Guide page, with render and console checks (Phase 13)
  - **Nonce fields confirmed present** by rendering the setup page with `wp eval-file` and grepping the output after the fix (Phase 13)
  - **Settings nav tab labels** (the 0.1 blocker): rendered the Recurring Donations tab label through both the buggy `esc_html()` approach and the final plain-echo approach, confirmed the fix restores the icon and badge markup, and confirmed plain-text tab labels are unaffected
- **Not done:** the planned Playwright screenshot-diff step was never run. Only the manual spot-checks above were done.
- **Code review:** every phase was reviewed by a fresh reviewer who read the diff against the real code. That pass found the mid-series breakages listed in 0.3, and all were fixed before merge. A later pass (during this rollup) found and fixed the settings nav tab label blocker (0.1), which earlier review had missed.
- **New automated tests added by this PR:** `tests/Unit/Helpers/GiveGetAttributeStrTest.php` and `tests/Unit/Helpers/GiveTooltipsTest.php`. One existing assertion was updated in `tests/includes/legacy/Tests_Templates.php`: `data-currency_symbol` is now `&#036;` instead of `&#36;`. It is the same character with different digit padding from `esc_attr()`.

---

## Appendix: phase PRs in this rollup

| PR | Scope |
|---|---|
| #8350 | Shared helpers: `give_get_attribute_str()`, `Give_Tooltips` (+ `print_render_help()`) |
| #8351 | System Info page |
| #8352 | Legacy donation form metabox |
| #8353 | Core settings screens |
| #8354 | Gateway settings fields |
| #8355 | Donors and payments admin screens |
| #8356 | Reports, upgrades, licenses, misc admin |
| #8357 | Tools import/export, email notifications |
| #8358 | Legacy donation form (public) |
| #8359 | Legacy consumer (Fields API) field templates (public) |
| #8360 | Stripe/PayPal gateway form fragments (public) |
| #8361 | Classic template (public) |
| #8362 | Sequoia template, IntlTelInput (public) |
| #8363 | v3 skeleton, iframe, routes (public) |
| #8364 | Form grid, donor wall, donation history shortcodes (public) |
| #8365 | Smaller shortcodes, receipts, profile editor, emails |
| #8366 | `includes/` core: misc, API, notices, scripts |
| #8367 | Onboarding setup guide |
| #8368 | Campaign blocks and misc (public) |
| #8369 | Elementor widgets (public) |
| #8370 | Promotions, dashboards, exports |
| #8371 | Admin pages, service providers, misc |
