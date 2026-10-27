# Stripe webhook setup

GiveWP creates, checks and deletes the Stripe webhook endpoint of each saved Stripe account, so the admin does
not have to add it in the Stripe Dashboard.

## The rule

The site sends an endpoint request only to a Stripe account saved in Give, using the key saved for
that account and mode. A health check never writes.

Two consequences:

- Every request goes through one of the endpoint actions (list, create, update, delete). No other
  code calls `WebhookEndpoint` directly.
- A setup run covers the mode the site is in (test or live) and no other. A test-mode site does not
  create endpoints on a live Stripe account. Disconnecting is the exception: it deletes in both modes.

## Where it runs

| Trigger | What it does | Writes to Stripe |
|:--|:--|:--|
| Account added (Connect, or manual keys from the give-stripe add-on) | The settings page reloads with `stripe_account=connected`. The modal shows a loader and calls the AJAX action. | Yes |
| **Set up webhooks** button (Stripe settings, Webhooks group) | Runs the same AJAX action and shows the same modal. | Yes |
| Hourly cron `givewp_stripe_webhook_health_check` | Lists the endpoints of every account and emails the site administrator when one has a problem. | No |
| Account disconnected (AJAX `disconnect_stripe_account`) | Deletes this site's endpoint from the account, then removes the account from Give. | Yes (delete) |

The AJAX action `givewp_stripe_ensure_webhook_endpoints` is registered only on `wp_ajax_`, checks a
nonce, and requires `manage_give_settings`.

### Disconnecting an account

`DisconnectStripeAccountController` runs `DeleteSiteWebhookEndpoint` before it calls
`give_stripe_disconnect_account()`, because the request needs the stored key. Every account, Connect
or manual, is disconnected through this AJAX action, so the give-stripe add-on needs no hook. Code
that calls `give_stripe_disconnect_account()` directly skips the delete.

For each mode where the account holds a key, it lists the endpoints, deletes each one whose URL
matches this site's listener URL, and leaves every other endpoint alone. A 404 on delete counts as
deleted. The result per mode is `deleted`, `nothing_matched` or `failed`.

A Stripe failure never stops the disconnect. A revoked OAuth token, a restricted key without write
access and a network error are logged with `give_record_gateway_error()` as `Stripe Webhook
Disconnect`, and the account is removed anyway. The AJAX response is still a success and the admin
sees no message. The endpoint then stays on Stripe.

## What setup decides

`EnsureWebhookEndpoint` runs once per account and mode:

1. List every endpoint of the account (`ListWebhookEndpoints`, 100 per page, auto-paged).
2. Find the endpoint whose URL is this site's listener URL (see below). If several match, it prefers
   one that is enabled and has every required event.
3. Branch:
   - No match, fewer than 16 endpoints: create one with the required events, the API version
     below, and the description `GiveWP`. Result `created`.
   - No match, 16 endpoints: throw `WebhookEndpointLimitReached`. Nothing is created.
   - Match, enabled, every required event (or `*`): change nothing. Result `found`.
   - Match, disabled or missing events: update it. The request carries only what changed:
     `disabled=false`, and `enabled_events` as the existing events plus the missing ones. Extra
     events already on the endpoint are kept. Result `updated`. If Stripe refuses the update,
     `IncompleteWebhookEndpoint` is thrown.

The required events are the ten events in `WebhookTarget::REQUIRED_EVENTS` that the Stripe listeners
in GiveWP and Give Recurring act on.

`CheckWebhookEndpointHealth` runs the same list and match steps and returns `healthy`, `missing`,
`disabled` or `missing_events` without writing.

### The listener URL

`WebhookTarget::url()` returns `https://{base}/?give-listener=stripe`. The base is
`GIVEWP_GATEWAY_ROUTE_BASE_URL` when that constant is defined, and `home_url()` otherwise, which is
the same fallback `GenerateGatewayRouteUrl` uses. The scheme is always `https`.

`WebhookTarget::matches()` compares host (case-insensitive), port, path without a trailing slash,
and `give-listener=stripe`. It ignores the scheme and extra query arguments. It accepts both the
base above and `site_url()`, because the Stripe settings page told admins to use the `site_url()`
form before this feature existed.

## Result statuses and the modal

`WebhookSetupStatus::fromException()` maps an exception to a status. The order matters because
`RateLimitException` extends `InvalidRequestException`.

| Status | Cause | What the modal tells the admin |
|:--|:--|:--|
| `found`, `created`, `updated` | Success | Success message per account |
| `incomplete` | A matching endpoint exists and the update failed | Lists the required events and links to the Stripe Dashboard |
| `limit_reached` | 16 endpoints and none matches | The maximum is 16; delete an endpoint and try again |
| `authorization` | Stripe 401 or 403 | Connect: disconnect and connect again. Manual key: give the restricted key write access to Webhook Endpoints |
| `rate_limited` | Stripe 429 | Wait and try again |
| `connection` | The request never reached Stripe | Check outgoing HTTPS from the server |
| `invalid_request` | Other Stripe 4xx | Shows Stripe's message, escaped |
| `unknown` | Anything else | The manual steps, with a link to the Webhooks group |

`WebhookSetupNotice::build()` turns the per-account results into `{level, title, body, retry}`.
One `unknown` result, or no accounts, makes the whole modal the manual-steps message. Otherwise the
level is `success` when every account succeeded and `warning` when any did not. `retry` adds a
**Try again** button that repeats the request.

`assets/src/js/admin/stripe-admin.js` (`giveStripeSetUpWebhooks`) opens a loader, posts the request,
and renders the result. If the request itself fails, it falls back to the content of the old modal
from the `data-*` attributes of `#give-stripe-connected`. If the admin closes the loader before the
response arrives, the result is not opened.

## Credentials

The endpoint actions read the key saved in `_give_stripe_get_all_accounts`:

- Connect accounts: `live_secret_key` and `test_secret_key` hold the OAuth access token that
  connect.givewp.com returned. The token is sent as the `api_key` option of each SDK call, so the
  endpoint belongs to the connected account.
- Manual accounts (give-stripe add-on): the same fields hold a restricted key (`rk_`). The
  add-on rejects `sk_` keys. An account can hold the key of one mode only, and `account_id`,
  `account_email` and `account_country` can be empty. `GetStripeAccountsForMode` skips an account
  that has no key for the requested mode.

`PreparesWebhookEndpointRequests::requestOptions()` refuses an empty key, and a key that contains
the marker of the other mode (`_test_` in live mode, `_live_` in test mode).

## Stripe constraints

- `api_version` is required when an endpoint is created on a connected account, and it cannot be
  changed afterwards. `EnsureWebhookEndpoint::API_VERSION` is `2026-09-30.endive`. GiveWP's own
  Stripe API calls stay pinned to `2019-05-16`. Moving an endpoint to another version means creating
  a second endpoint, switching to it, and disabling the first.
- An account can have at most 16 endpoints.
- The signing secret (`whsec_`) is in the response of the create request only. The code
  discards it, so the receiver cannot verify signatures.

## Health email

`WebhookHealthMonitor` is scheduled on `admin_init` and runs hourly. It reads the endpoints of
every account for the current mode and sends one email listing the accounts with a problem.

- The recipient is `RECOVERY_MODE_EMAIL` when it is a valid address, and the `admin_email` option
  otherwise. That is the address WordPress sends fatal error emails to.
- The setting `stripe_webhook_health_email` (Stripe settings, Webhooks group) turns the email off.
  It defaults to `enabled`.
- The option `givewp_stripe_webhook_health_notified` stores a fingerprint of the problems in the
  last email, so the same problems are not emailed every hour. It is stored only after `wp_mail()`
  succeeds, and deleted when a run finds no problems.
- A connection error or a 429 produces no problem line, because it says nothing about the endpoint.
  The next run checks again. Other Stripe errors, such as a rejected token, are reported.

## Logging

Entries go to the Give log (GiveWP > Tools > Logs) through `WebhookLogger`, with category `payment`
and source `Stripe Webhooks`. The context holds the account slug, the account type, the mode and the
Stripe identifiers, and never a key or a signing secret.

| Event | Level |
|:--|:--|
| Setup failed with a status that is not `unknown` (limit, authorization, rate limit, connection, invalid request, incomplete) | warning |
| Setup failed with `unknown` | error |
| Disconnect could not delete the endpoint | warning |
| Health problems found (logged once per change in the problems) | warning |
| Health email could not be sent | error |
| Endpoint created, updated or deleted on disconnect, health problems resolved, health email sent | info |
| Health check skipped an account because of a connection error or a 429 | debug |

A failure entry carries `stripe_http_status`, `stripe_error_type` and `stripe_request_id`. The request
id (`req_...`) finds the call in the Stripe Dashboard logs.

The `Log` facade stores `error` and `warning` entries always. It stores `info` only when `WP_DEBUG_LOG`
or `GIVE_DEBUG` is on, and `debug` only when `GIVE_DEBUG` is on. On a production site the record of
which endpoints GiveWP created or deleted is therefore not kept by default.

## Structure

- `src/PaymentGateways/Stripe/Webhooks/Actions/` — `ListWebhookEndpoints`, `CreateWebhookEndpoint`,
  `UpdateWebhookEndpoint`, `DeleteWebhookEndpoint`, `EnsureWebhookEndpoint`,
  `CheckWebhookEndpointHealth`, `DeleteSiteWebhookEndpoint`, `GetStripeAccountsForMode`
- `src/PaymentGateways/Stripe/Webhooks/` — `WebhookTarget`, `WebhookSetupNotice`,
  `WebhookHealthMonitor`, `WebhookLogger`
- `src/PaymentGateways/Stripe/Webhooks/Controllers/EnsureWebhookEndpointsController.php` — the setup
  AJAX action
- `src/PaymentGateways/Stripe/Controllers/DisconnectStripeAccountController.php` — calls
  `DeleteSiteWebhookEndpoint` before removing the account
- `src/PaymentGateways/Stripe/Webhooks/ValueObjects/`, `DataTransferObjects/`, `Exceptions/`,
  `Traits/` — statuses, `WebhookEndpointDto`, `WebhookEndpointException` (carries the Stripe HTTP
  status, error type and request id), `WebhookEndpointLimitReached`, `IncompleteWebhookEndpoint`
- `src/PaymentGateways/Stripe/Admin/WebhookSettingsFields.php` — the button and the email setting
- `assets/src/js/admin/stripe-admin.js` — `giveStripeSetUpWebhooks()`, the loader and result modal
- `src/ServiceProviders/PaymentGateways.php` — `registerStripeWebhookHooks()`
- `includes/gateways/stripe/includes/admin/class-give-stripe-admin-settings.php` — registers the
  `webhooks` group
- `src/PaymentGateways/Stripe/Admin/AccountManagerSettingField.php` — renders `#give-stripe-connected`
  with the nonce for the AJAX action