# The Giving Block (TGB) – Agent / developer context

## Overview

**The Giving Block** integration is part of **GiveWP** and lives under **Payment Gateways**. It lets nonprofits accept cryptocurrency and stock donations via The Giving Block. Code is in `give/src/PaymentGateways/TheGivingBlock/` and is registered through Give’s `PaymentGateways` service provider.

## Architecture

-   **Namespace:** `Give\PaymentGateways\TheGivingBlock\`
-   **Registration:** GiveWP’s `Give\ServiceProviders\PaymentGateways` registers:
    -   `RegisterTheGivingBlockSettings` – settings page and custom field handlers for Get Started and Organization (onboarding, connect, refresh, disconnect, delete all).
    -   `RegisterTheGivingBlockEmbeds` – shortcode, block, and embed styles.
-   **Entry:** The module is loaded as part of GiveWP when the Payment Gateways provider runs.

### Main components

| Area                    | Purpose                                                                                                                                                                                                        |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Actions**             | `RegisterTheGivingBlockSettings`, `RegisterTheGivingBlockEmbeds`.                                                                                                                                               |
| **Admin**               | Settings under GiveWP → Payment Gateways → The Giving Block: groups Get Started, Organization. `Admin/CustomFields/GetStarted/GetStartedSettingField` renders Get Started; `Admin/CustomFields/Organization/OrganizationSettingField` renders Organization (onboarding form or details + data management). Organization uses `Organization/Actions/` for render (RenderOnboardingForm, RenderOrganizationDetails) and AJAX (HandleOnboardingSubmission, HandleConnectingSubmission, HandleApiRefresh, HandleOrganizationDisconnect, HandleOrganizationDeletion). |
| **Repositories**        | `OrganizationRepository` – connection flag and organization data (options API).                                                                                                                                |
| **DataTransferObjects** | `Organization` – DTO built from options; includes `widgetCode` (iframe HTML from TGB).                                                                                                                        |
| **API**                 | `TheGivingBlockApi` – communication with the gateway server (onboarding, organization fetch, etc.).                                                                                                            |
| **Embeds**              | Shortcode `[give_tgb_form]` in `Embeds\Shortcodes\GiveTgbForm`; block `give/donation-form-block` in `Embeds\Blocks\DonationFormBlock\` (React editor + `render.php` that runs the shortcode).                    |

### Data storage

-   **Options (WordPress):**
    -   `give_tgb_organization_connected` – connection status.
    -   `give_tgb_organization` – organization data including `widgetCode['iframe']` (HTML from TGB).

### Integration flow

1. Admin connects the organization in **Get Started** or **Organization** (onboarding or connect existing).
2. Requests go to the **gateway server**, which talks to The Giving Block API.
3. Gateway returns organization data; plugin stores it in `give_tgb_organization` and sets `give_tgb_organization_connected`.
4. **Embeds:** Shortcode and block read from `Organization::fromOptions()` and output the iframe widget HTML.

## Shortcode

-   **Tag:** `give_tgb_form`
-   **Attributes:** `type` – `iframe` (default) or `popup`. Iframe embeds the form on the page; popup shows a button that opens the form in a modal.
-   **Handler:** `Give\PaymentGateways\TheGivingBlock\Embeds\Shortcodes\GiveTgbForm::renderShortcode()`.

## Block

-   **Name:** `give/donation-form-block`
-   **Attributes:** `displayType` – `iframe` (default) or `popup` (mirrors shortcode `type`).
-   **Render:** `render.php` builds `[give_tgb_form type="..."]` and runs `do_shortcode()`. No client-side render for the form itself.

## Frontend / editor assets

-   **Styles:** `assets/css/tgb-embeds.css` – donation form container and button styling; enqueued for frontend and block editor.
-   **Block:** Built with GiveWP tooling; editor app in `DonationFormBlock/` (e.g. `edit.tsx`), compiled to `build/tgbDonationFormBlockApp.js`.

## Testing scenarios

1. **Connected, iframe** – Shortcode `[give_tgb_form]` or `type="iframe"` (default) or block display type iframe: output includes `give_tgb_organization['widgetCode']['iframe']`.
2. **Connected, popup** – Shortcode `type="popup"` or block display type popup: output includes `give_tgb_organization['widgetCode']['popup']` (button that opens the form in a modal).
3. **Disconnected** – `give_tgb_organization_connected` false or missing: shortcode and block show the “not configured” error message.
4. **Missing widget code** – Organization stored but no `widgetCode['iframe']`: form area shows the “not available” error.

## Key files

| Path                                                                 | Role                                                                                     |
| -------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `Actions/RegisterTheGivingBlockSettings.php`                         | Registers TGB settings page, custom field hooks, AJAX actions, assets for settings.       |
| `Actions/RegisterTheGivingBlockEmbeds.php`                          | Registers shortcode, block, enqueues embeds CSS (front + editor).                        |
| `Admin/TheGivingBlockSettingPage.php`                                | Defines section and groups (get-started, organization).                                  |
| `Admin/CustomFields/GetStarted/GetStartedSettingField.php`           | Renders Get Started content (instructions, connected state, widgets, sandbox).            |
| `Admin/CustomFields/Organization/OrganizationSettingField.php`       | Renders Organization: onboarding form or organization details + data management.         |
| `Admin/CustomFields/Organization/Actions/`                           | RenderOnboardingForm, RenderOrganizationDetails; HandleOnboardingSubmission, HandleConnectingSubmission, HandleApiRefresh, HandleOrganizationDisconnect, HandleOrganizationDeletion. |
| `Repositories/OrganizationRepository.php`                             | Read/write `give_tgb_organization_connected` and `give_tgb_organization`.                 |
| `Embeds/Shortcodes/GiveTgbForm.php`                                  | Shortcode callback; iframe or popup by `type` attribute.                                |
| `Embeds/Blocks/DonationFormBlock/`                                   | Block definition (`block.json`), editor (`edit.tsx`), server render (`render.php`).       |
| `assets/css/tgb-embeds.css`                                          | Frontend and editor styles for TGB embed.                                                |

## Build and standards

-   **Block:** From GiveWP plugin root, `npm run build` or `npm run watch`; block assets are part of the main Give build.
-   **PHP:** PSR-12 / WordPress coding standards; short array syntax.
-   **JS:** ES6+; `const`/`let`; React for the block editor.
