# The Giving Block (TGB) – Agent / developer context

## Overview

**The Giving Block** integration is part of **GiveWP** and lives under **Payment Gateways**. It lets nonprofits accept cryptocurrency and stock donations via The Giving Block. Code is in `give/src/PaymentGateways/TheGivingBlock/` and is registered through Give’s `PaymentGateways` service provider.

## Architecture

-   **Namespace:** `Give\PaymentGateways\TheGivingBlock\`
-   **Registration:** GiveWP’s `Give\ServiceProviders\PaymentGateways` registers:
    -   `RegisterTheGivingBlockSettings` – settings page and admin (Get Started, Organization, Options).
    -   `RegisterTheGivingBlockEmbeds` – shortcode, block, styles, popup notice script, and campaign layout filter.
-   **Entry:** The module is loaded as part of GiveWP when the Payment Gateways provider runs.

### Main components

| Area                    | Purpose                                                                                                                                                                                                        |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Actions**             | `RegisterTheGivingBlockSettings`, `RegisterTheGivingBlockEmbeds`, `AddTgbBlockToNewCampaignPage` (adds TGB block to new campaign page layout when option is on).                                               |
| **Admin**               | Settings under GiveWP → Payment Gateways → The Giving Block: tabs Get Started, Organization, Options. Custom field types for each tab; AJAX handlers for onboarding, connect, refresh, disconnect, delete all. |
| **Repositories**        | `OrganizationRepository` – connection flag and organization data (options API).                                                                                                                                |
| **DataTransferObjects** | `Organization` – DTO built from options; includes `widgetCode` (iframe/popup HTML from TGB).                                                                                                                   |
| **API**                 | `TheGivingBlockApi` – communication with the gateway server (onboarding, organization fetch, etc.).                                                                                                            |
| **Embeds**              | Shortcode `[give_tgb_form]` in `Embeds\Shortcodes\GiveTgbForm`; block `give/donation-form-block` in `Embeds\Blocks\DonationFormBlock\` (React editor + `render.php` that builds and runs the shortcode).       |

### Data storage

-   **Options (WordPress):**
    -   `give_tgb_organization_connected` – connection status.
    -   `give_tgb_organization` – organization data including `widgetCode['iframe']` and `widgetCode['popup']` (HTML from TGB).
-   **Give settings:** `give_tgb_add_block_to_new_campaigns` (via Give settings UI) – whether to add the TGB block to new campaign pages; read with `give_get_option('give_tgb_add_block_to_new_campaigns', 'on')` and respected in `AddTgbBlockToNewCampaignPage`.

### Integration flow

1. Admin connects the organization in **Get Started** or **Organization** (onboarding or connect existing).
2. Requests go to the **gateway server**, which talks to The Giving Block API.
3. Gateway returns organization data; plugin stores it in `give_tgb_organization` and sets `give_tgb_organization_connected`.
4. **Embeds:** Shortcode and block read from `Organization::fromOptions()` and output the appropriate widget HTML (iframe or popup). Popup button text is overridden in PHP (`str_replace`). Optional “campaign stats” notice is rendered below the button with a “Learn more” modal (script: `popupNoticeModal.js`).

## Shortcode

-   **Tag:** `give_tgb_form`
-   **Attributes:** `type` (iframe|popup), `popup_button_text`, `popup_button_notice_enable` (use `"true"`/`"false"`; validated with `wp_validate_boolean`), `popup_button_notice_short_text`, `popup_button_notice_long_text`, `popup_button_notice_short_cta`.
-   **Handler:** `Give\PaymentGateways\TheGivingBlock\Embeds\Shortcodes\GiveTgbForm::renderShortcode()`.

## Block

-   **Name:** `give/donation-form-block`
-   **Attributes:** `displayType`, `popupButtonText`, `popupButtonNoticeEnable`, `popupButtonNoticeShortText`, `popupButtonNoticeLongText`, `popupButtonNoticeShortCta` (mirror shortcode).
-   **Render:** `render.php` builds a `[give_tgb_form ...]` string and runs `do_shortcode()`. No client-side render for the form itself.

## Campaign layout

-   **Filter:** `givewp_campaign_page_default_layout` (in GiveWP campaigns).
-   **Action:** `AddTgbBlockToNewCampaignPage` – when the organization is connected and the option to add the block is enabled, inserts the TGB block markup (popup, “Donate Crypto”, notice on) immediately after the campaign donate button in the default layout. Only runs when the layout does not already contain the TGB block.

## Frontend / editor assets

-   **Styles:** `assets/css/tgb-embeds.css` – button (width/typography), notice, modal; enqueued for frontend and block editor.
-   **Scripts:** `popupNoticeModal.js` – “Learn more” modal (frontend and editor; in editor the modal is shown in the parent document and styles are injected so font/layout match).
-   **Block:** Built with GiveWP tooling; editor app in `DonationFormBlock/` (e.g. `edit.tsx`), compiled to `build/tgbDonationFormBlockApp.js`.

## Testing scenarios

1. **Connected, iframe** – Shortcode `type="iframe"` or block display type iframe: output includes `give_tgb_organization['widgetCode']['iframe']`.
2. **Connected, popup** – Shortcode `type="popup"` or block popup: output includes popup widget HTML, custom button text applied in PHP, and optionally the campaign stats notice + modal.
3. **Disconnected** – `give_tgb_organization_connected` false or missing: shortcode and block show the “not configured” error message.
4. **Missing widget code** – Organization stored but no `widgetCode` iframe/popup: form/button area shows the appropriate “not available” error.
5. **New campaign page** – With “Add Donate Crypto button to new campaign pages” enabled, new campaign default layout should include the TGB block below the donate button; with option off, it should not.

## Key files

| Path                                         | Role                                                                                     |
| -------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `Actions/RegisterTheGivingBlockSettings.php` | Registers TGB settings page, custom fields, AJAX, assets for settings.                   |
| `Actions/RegisterTheGivingBlockEmbeds.php`   | Registers shortcode, block, enqueues embeds CSS and popup modal script (front + editor). |
| `Actions/AddTgbBlockToNewCampaignPage.php`   | Filter on campaign default layout to insert TGB block when option is on.                 |
| `Repositories/OrganizationRepository.php`    | Read/write `give_tgb_organization_connected` and `give_tgb_organization`.                |
| `Embeds/Shortcodes/GiveTgbForm.php`          | Shortcode callback; iframe vs popup; button text; notice + modal markup.                 |
| `Embeds/Blocks/DonationFormBlock/`           | Block definition (`block.json`), editor (`edit.tsx`), server render (`render.php`).      |
| `assets/css/tgb-embeds.css`                  | Frontend and editor styles for TGB embed and modal.                                      |
| `assets/js/popupNoticeModal.js`              | “Learn more” modal behavior; parent-document modal in editor.                            |

## Build and standards

-   **Block:** From GiveWP plugin root, `npm run build` or `npm run watch`; block assets are part of the main Give build.
-   **PHP:** PSR-12 / WordPress coding standards; short array syntax.
-   **JS:** ES6+; `const`/`let`; React for the block editor.
