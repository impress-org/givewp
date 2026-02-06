# The Giving Block (TGB) – GiveWP integration

Integration with **The Giving Block** for cryptocurrency and stock donations, built into GiveWP under **Payment Gateways**.

## Overview

This module enables nonprofits to accept crypto and stock donations via The Giving Block. Organization data and widget code are managed in **GiveWP → Settings → Payment Gateways → The Giving Block**. Donation forms are embedded using a shortcode or the “The Giving Block by GiveWP” block. Sandbox and live environments are supported via the gateway server.

## Features

-   **Crypto & stock donations** – Accept Bitcoin and other cryptocurrencies and stock donations through TGB.
-   **Organization management** – Connect and manage your TGB organization (onboarding or connect existing) from the GiveWP settings.
-   **Shortcode** – `[give_tgb_form]` with display type (iframe/popup), custom button text, and optional campaign stats notice.
-   **Gutenberg block** – “The Giving Block by GiveWP” (embed category) with the same options as the shortcode.
-   **Campaign integration** – Option to automatically add a “Donate Crypto” block below the main donate button on **new** campaign pages (can be turned off in Options).
-   **Campaign stats notice** – Optional notice below the popup button explaining that crypto/stock donations are not included in campaign statistics, with a “Learn more” modal.
-   **Sandbox testing** – Use `GIVE_TGB_CONNECT_MODE` and gateway server for testing.

## Where to configure

-   **GiveWP → Settings → Payment Gateways → The Giving Block**
    -   **Get Started** – Connect (new or existing organization), quick start, usage info, widget embed codes.
    -   **Organization** – Organization details, refresh from API, disconnect.
    -   **Options** – **General:** “Add Donate Crypto button to new campaign pages” (on/off). **Data Management:** Delete all organization data.

## Usage

### Shortcode

-   `[give_tgb_form type="iframe"]` – Donation form embedded on the page (default).
-   `[give_tgb_form type="popup"]` – Button that opens the donation form in a modal.

**Attributes (same options as the block):**

| Attribute                        | Description                                                         |
| -------------------------------- | ------------------------------------------------------------------- |
| `type`                           | `iframe` (default) or `popup`.                                      |
| `popup_button_text`              | Button label when type is popup (e.g. `Donate Crypto`).             |
| `popup_button_notice_enable`     | Set to `"true"` to show the campaign stats notice below the button. |
| `popup_button_notice_short_text` | Short text next to the info icon (e.g. “Do not affect stats”).      |
| `popup_button_notice_short_cta`  | Link text that opens the notice modal (e.g. “Learn more”).          |
| `popup_button_notice_long_text`  | Full text shown in the notice modal.                                |

**Example:**

```
[give_tgb_form type="popup" popup_button_text="Donate Crypto" popup_button_notice_enable="true"]
```

### Block

Add the **“The Giving Block by GiveWP”** block from the **Embed** category. In the block sidebar you can set:

-   Display type (iframe / popup)
-   Button text (for popup)
-   Show campaign stats notice and short text, CTA, and long (modal) text

The block outputs the shortcode; all options match the shortcode attributes above.

## Development

### Location

Code lives under GiveWP core:

-   `wp-content/plugins/give/src/PaymentGateways/TheGivingBlock/`

### Dependencies

-   **Gateway server** – API calls go through the GiveWP gateway server (e.g. [givewp-gateway-server](https://github.com/impress-org/givewp-gateway-server)). Configure `GIVE_CONNECT_URL` and `GIVE_TGB_CONNECT_MODE` in `wp-config.php` for local development.
-   **Block assets** – The block is built with the GiveWP build pipeline. From the GiveWP plugin root:
    -   `npm run build` – production
    -   `npm run watch` – development with auto-rebuild

### Configuration (wp-config.php)

```php
define('GIVE_CONNECT_URL', 'https://your-gateway-server.example.com');
define('GIVE_TGB_CONNECT_MODE', 'sandbox'); // or 'live'
```

## Support

-   **The Giving Block:** [Contact](https://thegivingblock.com/about/contact/)
-   **Documentation:** [docs.thegivingblock.com](https://docs.thegivingblock.com)

This integration is part of the GiveWP core.
