# The Giving Block (TGB) – GiveWP integration

Integration with **The Giving Block** for cryptocurrency and stock donations, built into GiveWP under **Payment Gateways**.

## Overview

This module enables nonprofits to accept crypto and stock donations via The Giving Block. Organization data and widget code are managed in **GiveWP → Settings → Payment Gateways → The Giving Block**. Donation forms are embedded using a shortcode or the “The Giving Block by GiveWP” block. Sandbox and live environments are supported via the gateway server.

## Features

-   **Crypto & stock donations** – Accept Bitcoin and other cryptocurrencies and stock donations through TGB.
-   **Organization management** – Connect and manage your TGB organization (onboarding or connect existing) from the GiveWP settings.
-   **Shortcode** – `[give_tgb_form]` with optional `type` (iframe or popup) to choose how the form is displayed.
-   **Gutenberg block** – “The Giving Block by GiveWP” (embed category) with display type (iframe or popup) in block settings.
-   **Sandbox testing** – Use `GIVE_TGB_CONNECT_MODE` and gateway server for testing.

## Where to configure

-   **GiveWP → Settings → Payment Gateways → The Giving Block**
    -   **Get Started** – Connect (new or existing organization), quick start, usage info, widget embed codes.
    -   **Organization** – Organization details, refresh from API, disconnect, data management.

## Usage

### Shortcode

-   `[give_tgb_form]` or `[give_tgb_form type="iframe"]` – Donation form embedded on the page (default).
-   `[give_tgb_form type="popup"]` – Button that opens the donation form in a modal.

### Block

Add the **“The Giving Block by GiveWP”** block from the **Embed** category. In the block sidebar you can set **Display Type** to Iframe (embedded form) or Popup (modal button).

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
