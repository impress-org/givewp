# Payment gateways

The Gateway API connects donation forms to payment processors. It has a PHP half
(`src/Framework/PaymentGateways/`) and a JavaScript half (a registered gateway object on the v3
form), and the boundary between them is a security boundary. Most of this document is about that
boundary.

## The rule

**Funds are never collected on the client. The capture happens server-side, or it doesn't happen.**

This is the anchor. The client is allowed to set up a payment — build an order, tokenize a card,
collect an authorization from the donor. What it must never do is complete the transaction. The
step that actually moves money runs on the server, inside the gateway's `createPayment()`, after
the form has been validated and the payment object has been checked against what the server
expects.

Two supporting rules follow from it:

- **Nothing reaches the processor until the server has validated the complete form.** Client-side
  validation is UX. A browser can skip it, and several field types have rules that exist only
  server-side.
- **Data arriving from the processor is a claim, not a fact.** Whether it's an order the client
  handed you or a webhook the processor sent you, verify it against server-held state before
  acting.

All three have been the subject of fixes. None is theoretical.

## The lifecycle

A v3 form donation runs through these steps:

1. `beforeCreatePayment(values)` — JS, optional. Prepares client-side state and returns extra data
   to merge into the request. **This is not a validation gate.**
2. The form POSTs to the donate route (`Routes/DonateRoute.php`, signed — see
   `DonateRouteSignature`), which runs full server-side validation and then calls the gateway's
   PHP `createPayment()` / `createSubscription()`.
3. `afterCreatePayment(response)` — JS, optional. Completes whatever the server started, using
   values the server returned.

Step 2 is where money moves. Whatever the client did in step 1, the transaction is completed by
the PHP gateway during the server-side donate request.

Stripe: `beforeCreatePayment` calls `elements.submit()` to collect card data and returns settings.
The server validates the form and creates the PaymentIntent, returning a `clientSecret`.
`afterCreatePayment` calls `stripe.confirmPayment()` with that secret — confirming an intent the
server created and owns.

PayPal Commerce: the order is created and approved through PayPal's client checkout flow, and the
donor authorizes it in PayPal's own UI. **None of that captures funds.** The capture happens in
`PayPalCommerce::createPayment()` on the server — see below.

## When the SDK owns the button

PayPal Commerce can't use the flow above, because PayPal's own React buttons intercept the click
and open PayPal's popup directly. Left alone, the donor would be inside PayPal's checkout before
GiveWP's form validation ever ran — and an order would exist on PayPal's side for a donation the
server would have rejected.

So the gateway rebuilds the gate manually, in the buttons' `onClick`, before allowing PayPal to
proceed (`src/PaymentGateways/Gateways/PayPalCommerce/payPalCommerceGateway.tsx`):

1. Check gateway/subscription compatibility → `actions.reject()`
2. Client-side validation via react-hook-form `trigger()` → `actions.reject()`
3. **Server-side validation** — `handleValidationRequest()` POSTs the whole form to
   `DonationForms/Routes/ValidationRoute.php` → `actions.reject()`
4. Only then `actions.resolve()`

The in-code comment explains why step 3 is not redundant with step 2, and it's worth repeating
because it's the part that looks removable:

> *Ideally, the client-side validations should be enough. However, in some cases, these
> validations are reached later when the donation is already created on the PayPal side… #1 -
> Billing Address Block: depending on the selected country, the city, state, and zip fields can be
> required or not and there are custom validation rules on the server side. #2 - Gift Aid Block:
> when users opt-in… required fields… the validation rules for it live only on the server.*

Only then does PayPal's SDK invoke its `createOrder` callback. That callback doesn't call PayPal
directly either — `createOrder.tsx` POSTs the complete form values plus the gateway's own keys to
the `give_paypal_commerce_create_order` AJAX action, and `AjaxRequestHandler::createOrder()` makes
the API call and returns an order id. The card-fields flow (`beforeCreatePayment` →
`cardFieldsForm.submit()`) reaches the same handler without passing through the `onClick` gate, and
the action is registered on `wp_ajax_nopriv` behind a page-rendered nonce, so the handler cannot
rely on anything upstream having validated the request. It does not trust it:

- `validateFrontendRequest()` — form id and donation form nonce
- `ValidateDonationFormRequest` (`src/DonationForms/Actions/`) — the posted values are held to the
  form's own rules through the same validator the validate route uses (v3: the fields-API rules on
  every posted field — amount minimum/maximum when custom amounts are on, required fields, honeypot;
  v2: `give_donation_form_validate_fields()` followed by `give_checkout_error_checks`, the same
  sequence `give_process_donation_form()` runs, which is where the level check and add-on rules
  hang). The gateway never learns form configuration; it asks the form layer and acts on the
  verdict. This is the seam to reuse if another gateway ever has to call its processor before the
  donate route runs.
- for v3 forms, the total sent to PayPal (`give-amount`, fee recovery included) must be at least
  the `amount` the form validated; `createPayment()` reconciles the order to the donation before
  capture, so that is the only bound the handler needs
- for v2 forms, the final amount after the `give_donation_total` filter is checked again: positive
  and within the form's configured maximum

The donor then approves the order inside PayPal's UI. **At this point no money has moved.** An
approved PayPal order is an authorization, not a payment.

### Where the funds are actually collected

`onApprove` stores the order id and clicks the form's real submit button, which runs the normal
signed donate route → full server-side validation → `PayPalCommerce::createPayment()`.

That method is where money moves, and it verifies before it does:

1. Fetch the order from PayPal by id — the server asks PayPal for the order's state rather than
   trusting what the client reported.
2. Branch on status. `COMPLETED` means it was already captured; `APPROVED`/`CREATED` means it
   still needs capturing. Anything else throws.
3. `validate3dSecure()`, and `updateOrderFromDonation()` if `shouldUpdateOrder()` finds the order
   no longer matches the donation.
4. `approveOrder($payPalOrderId)` — despite the name, this issues PayPal's `OrdersCaptureRequest`.
   **This is the capture.**
5. `validatePayPalOrder()` — the capture exists, isn't `DECLINED`, and carries no error details.
6. Return `PaymentComplete::make($transactionId)` using the real capture id.

So the client's entire contribution is an order id. Everything that binds that id to money happens
server-side, after validation, with the order re-fetched from PayPal rather than taken on the
client's word.

The v2/legacy path still exposes capture through the `give_paypal_commerce_approve_order` AJAX
action (`AjaxRequestHandler::approveOrder()`) — a client-triggerable capture endpoint, registered on
both `wp_ajax` and `wp_ajax_nopriv`, that the v2 scripts (`SmartButtons.js`,
`AdvancedCardFields.js`) call right after PayPal's `onApprove` and before the form is submitted. So
on v2, capture happens before the donation record exists and before `give_process_donation` has run.
`approveOrder()` and `updateOrderAmount()` refuse v3 forms outright, which is what makes the rule
above hold without exception on v3. For v2 they run the legacy validator
(`give_donation_form_validate_fields()`, through `ValidateDonationFormRequest`) first, but they still
capture.

**[Judgement]** `createPayment()` already handles `APPROVED` orders and already receives
`payPalOrderId` from the legacy processing path, so it is the natural home for v2 capture too. Until
that moves, treat the v3 shape as the model and the v2 endpoint as legacy.

### Don't generalize the PayPal shape

`handleValidationRequest` is called from exactly two places: the PayPal Commerce gateway and the
multi-step form's `NextButton`. It is a workaround for SDKs that seize the submit, not the house
style. A gateway that submits through the normal donate route already gets server-side validation
for free and should not add a second round trip.

## Webhooks and inbound data

Anything the processor sends you is attacker-reachable. `PayPalStandardWebhook::verifyEventData()`
is the reference for what verification means in practice — it checks, before processing:

- **merchant identity** — `receiver_email` matches the site's configured account
- **amount and currency** — match the stored donation record
- **record linkage** — for refunds and reversals, the parent transaction id matches the donation

Verifying the notification's *signature* is not sufficient. A signature proves the message came
from the processor; it does not prove the message is about a donation on this site, for this
amount, in this currency. Check the payload against your own records.

Stripe webhook listeners live in
`Gateways/Stripe/StripePaymentElementGateway/Webhooks/Listeners/` — one class per event.

Gateway route methods reached by redirect are signed: use `generateSecureGatewayRouteUrl()` rather
than `generateGatewayRouteUrl()` for anything that mutates state, and see `supportsMethodRoute()`
and `callRouteMethod()` for how they dispatch.

## Structure

- `src/Framework/PaymentGateways/` — `PaymentGateway` abstract class, `PaymentGatewayInterface`,
  `PaymentGatewayRegister`, `SubscriptionModule`
- `src/PaymentGateways/Gateways/TestGateway/` — the minimal implementation, start here
- `src/PaymentGateways/Gateways/Stripe/StripePaymentElementGateway/` — full-featured reference
- `src/PaymentGateways/Gateways/PayPalCommerce/` — the SDK-owns-the-button case
- Subscription modes: `src/Subscriptions/ValueObjects/SubscriptionMode.php`

Capability flags on the abstract class tell the rest of the plugin what a gateway can do —
`supportsSubscriptions()`, `supportsRefund()`, `supportsFormVersions()`, `canPauseSubscription()`,
`canUpdateSubscriptionAmount()`, and friends. Declare them honestly; the admin UI and the
subscription code branch on them.

Public docs:
https://givewp.com/documentation/developers/how-to-build-a-gateway-add-on-for-givewp/

## Checklist for a new or changed gateway

- **Can the client complete a transaction on its own?** If any code path lets the browser capture,
  charge, or otherwise finalize payment without the server, that's the bug. Setup and
  authorization on the client are fine; collection is not.
- Does the server re-fetch the payment object from the processor before capturing, rather than
  trusting the id and amounts the client reported?
- If the SDK takes over the submit button, does the pre-flight run server-side validation, not
  just client-side?
- Does the webhook handler verify merchant identity, amount, currency, and record linkage — not
  just the signature?
- Are state-changing gateway routes generated with `generateSecureGatewayRouteUrl()`?
- Are the `supports*` / `can*` flags accurate?
- Is anything sensitive being logged? `Log::redact()` strips keys containing `card`, `password`,
  `secret`, `token` (filterable via `give_log_redaction_list`) — but only keys it recognizes.
