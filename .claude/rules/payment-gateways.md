---
paths:
  - "src/Framework/PaymentGateways/**/*"
  - "src/PaymentGateways/**/*"
  - "src/LegacyPaymentGateways/**/*"
  - "src/Framework/LegacyPaymentGateways/**/*"
---

# Payment gateways

Read `docs/architecture/payment-gateways.md` before changing gateway code — it covers the
lifecycle, the trust boundary, and the webhook verification requirements.

The security invariants, which are not negotiable:

- **Funds are never collected on the client.** The client may set up a payment — build an order,
  tokenize a card, collect the donor's authorization. It must never complete one. The capture runs
  server-side in the gateway's `createPayment()`. PayPal's order is created and approved in the
  client flow but stays an authorization until `PayPalCommerce::createPayment()` issues the
  capture; Stripe confirms an intent the server created.
- **Before capturing, re-fetch the payment object from the processor.** Don't trust the id, amount,
  or status the client reported.
- **Nothing reaches the payment processor until the server has validated the complete form.**
  Client-side validation is UX, not a gate — several field types (billing address conditionals,
  Gift Aid children) have rules that exist only server-side.
- **Data arriving from the processor is a claim, not a fact.** Webhooks must verify merchant
  identity, amount, currency, and record linkage against stored donation data. A valid signature
  proves origin, not that the message is about this site's donation for this amount.
- Use `generateSecureGatewayRouteUrl()` for any gateway route that changes state.

`handleValidationRequest` is a workaround for SDKs that seize the submit button (PayPal Commerce,
multi-step `NextButton`), not the default pattern. Gateways submitting through the normal donate
route already get server-side validation.
